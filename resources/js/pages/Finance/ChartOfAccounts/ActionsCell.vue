<script setup lang="ts">
import { Link, Form, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ChartOfAccountController from '@/actions/App/Http/Controllers/Finance/ChartOfAccountController';
import type { ChartOfAccount } from './columns';
import { computed } from 'vue';

interface Props {
    account: ChartOfAccount;
    onDeleteClick: (
        account: ChartOfAccount,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onRestoreClick?: (
        account: ChartOfAccount,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onForceDeleteClick?: (
        account: ChartOfAccount,
        submit: () => void,
        processing: () => boolean,
    ) => void;
}

const props = defineProps<Props>();

const isDeleted = computed(() => !!props.account.deleted_at);

const handleRestore = () => {
    router.post(`/finance/chart-of-accounts/${props.account.id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['accounts'] });
        },
    });
};

const handleForceDelete = () => {
    router.delete(`/finance/chart-of-accounts/${props.account.id}/force-delete`, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['accounts'] });
        },
    });
};
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <template v-if="!isDeleted">
            <Button as-child size="sm" variant="secondary">
                <Link :href="ChartOfAccountController.edit.url(account.id)">
                    Edit
                </Link>
            </Button>
            <Form
                :action="ChartOfAccountController.destroy.url(account.id)"
                method="delete"
                class="inline-flex"
                v-slot="{ processing, submit }"
            >
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    :disabled="processing"
                    @click="onDeleteClick(account, () => submit(), () => processing)"
                >
                    Delete
                </Button>
            </Form>
        </template>
        <template v-else>
            <Button
                type="button"
                size="sm"
                variant="secondary"
                :disabled="false"
                @click="onRestoreClick ? onRestoreClick(account, handleRestore, () => false) : handleRestore()"
            >
                Restore
            </Button>
            <Button
                type="button"
                variant="destructive"
                size="sm"
                :disabled="false"
                @click="onForceDeleteClick ? onForceDeleteClick(account, handleForceDelete, () => false) : handleForceDelete()"
            >
                Force Delete
            </Button>
        </template>
    </div>
</template>

