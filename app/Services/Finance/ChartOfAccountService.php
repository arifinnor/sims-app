<?php

namespace App\Services\Finance;

use App\Models\Finance\ChartOfAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChartOfAccountService
{
    /**
     * Suggest the next code for a chart of account based on parent hierarchy.
     *
     *
     * @throws ModelNotFoundException
     */
    public function suggestNextCode(?string $parentId): string
    {
        // Root code logic (no parent)
        if ($parentId === null) {
            return $this->suggestRootCode();
        }

        // Find parent account
        $parent = ChartOfAccount::query()->findOrFail($parentId);

        // Check if parent has existing children
        $children = ChartOfAccount::query()
            ->where('parent_id', $parentId)
            ->orderBy('code')
            ->get(['code']);

        if ($children->isEmpty()) {
            // First child logic
            return $this->suggestFirstChildCode($parent->code);
        }

        // Sibling logic - find max code and increment
        return $this->suggestSiblingCode($children);
    }

    /**
     * Suggest next root code (e.g., '1-0000' -> '2-0000').
     */
    private function suggestRootCode(): string
    {
        $rootAccounts = ChartOfAccount::query()
            ->whereNull('parent_id')
            ->orderBy('code')
            ->pluck('code');

        if ($rootAccounts->isEmpty()) {
            return '1-0000';
        }

        $maxPrefix = 0;

        foreach ($rootAccounts as $code) {
            $parts = explode('-', $code);
            if (count($parts) === 2 && is_numeric($parts[0])) {
                $prefix = (int) $parts[0];
                if ($prefix > $maxPrefix) {
                    $maxPrefix = $prefix;
                }
            }
        }

        return ($maxPrefix + 1).'-0000';
    }

    /**
     * Suggest code for first child based on parent code pattern.
     */
    private function suggestFirstChildCode(string $parentCode): string
    {
        $parts = explode('-', $parentCode);

        if (count($parts) !== 2) {
            // Fallback: append '01' if parsing fails
            return $parentCode.'-01';
        }

        [$prefix, $suffix] = $parts;

        // Analyze trailing zeros to determine hierarchy level
        $trailingZeros = $this->countTrailingZeros($suffix);

        // Level 1: X-0000 -> X-1000
        if ($trailingZeros === 4) {
            return $prefix.'-1000';
        }

        // Level 2: X-Y000 -> X-Y100
        if ($trailingZeros === 3) {
            $base = substr($suffix, 0, 1);

            return $prefix.'-'.$base.'100';
        }

        // Level 3+: Append hierarchical segment (e.g., 1-2101 -> 1-210101)
        // For hierarchical accounts, append "01" to create child code
        return $parentCode.'01';
    }

    /**
     * Suggest code for sibling based on existing children.
     */
    private function suggestSiblingCode($children): string
    {
        // Find child with maximum code
        $maxCode = $children->max('code');

        if ($maxCode === null) {
            throw new \RuntimeException('Cannot suggest sibling code: no children found.');
        }

        $parts = explode('-', $maxCode);

        if (count($parts) !== 2) {
            // Fallback: try simple increment
            return $maxCode.'-01';
        }

        [$prefix, $suffix] = $parts;

        // Check if this is a hierarchical code (ends with 2+ digits that can be incremented)
        // For codes like "1-210101", "1-210102", etc., increment the last segment
        // Extract the last 2 digits as the segment number
        $suffixLength = strlen($suffix);

        if ($suffixLength >= 2) {
            // Try to extract the last segment (last 2 digits)
            $lastSegment = substr($suffix, -2);
            $baseCode = substr($suffix, 0, -2);

            if (is_numeric($lastSegment) && is_numeric($baseCode)) {
                $segmentNum = (int) $lastSegment;
                $nextSegment = $segmentNum + 1;
                $paddedNext = str_pad((string) $nextSegment, 2, '0', STR_PAD_LEFT);

                return $prefix.'-'.$baseCode.$paddedNext;
            }
        }

        // Fallback: Parse last numeric segment and increment
        $numericPart = (int) $suffix;
        $nextNumeric = $numericPart + 1;
        $paddedNext = str_pad((string) $nextNumeric, strlen($suffix), '0', STR_PAD_LEFT);

        return $prefix.'-'.$paddedNext;
    }

    /**
     * Count trailing zeros in a numeric string.
     */
    private function countTrailingZeros(string $value): int
    {
        $count = 0;
        $length = strlen($value);

        for ($i = $length - 1; $i >= 0; $i--) {
            if ($value[$i] === '0') {
                $count++;
            } else {
                break;
            }
        }

        return $count;
    }
}
