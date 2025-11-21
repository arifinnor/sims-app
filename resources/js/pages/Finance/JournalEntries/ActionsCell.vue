<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import JournalEntryController from '@/actions/App/Http/Controllers/Finance/JournalEntryController';
import type { JournalEntry } from './columns';
import { computed } from 'vue';

interface Props {
    entry: JournalEntry;
    onVoidClick: (entry: JournalEntry) => void;
}

const props = defineProps<Props>();

const canVoid = computed(() => props.entry.status === 'POSTED');
const canEdit = computed(() => props.entry.status === 'DRAFT');
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <Button as-child size="sm" variant="secondary">
            <Link :href="JournalEntryController.show.url(entry.id)">
                View
            </Link>
        </Button>
        <Button
            v-if="canEdit"
            as-child
            size="sm"
            variant="secondary"
        >
            <Link :href="JournalEntryController.edit.url(entry.id)">
                Edit
            </Link>
        </Button>
        <Button
            v-if="canVoid"
            type="button"
            variant="destructive"
            size="sm"
            @click="onVoidClick(entry)"
        >
            Void
        </Button>
    </div>
</template>

