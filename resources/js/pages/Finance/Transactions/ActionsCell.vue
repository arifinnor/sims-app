<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Transaction } from './columns';
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    entry: Transaction;
    onVoidClick: (entry: Transaction) => void;
}

const props = defineProps<Props>();

const canVoid = computed(() => props.entry.status === 'POSTED');
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <Button
            v-if="canVoid"
            type="button"
            variant="destructive"
            size="sm"
            @click="onVoidClick(entry)"
        >
            <Trash2 class="mr-1 h-4 w-4" />
            Void
        </Button>
    </div>
</template>


