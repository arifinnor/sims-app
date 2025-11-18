<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Collapsible, CollapsibleContent } from '@/components/ui/collapsible';
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
import ActionsCell from './ActionsCell.vue';
import type { Account } from './columns';

interface TreeNodeData extends Account {
    children?: TreeNodeData[];
    depth?: number;
}

interface Props {
    node: TreeNodeData;
    expanded: boolean;
    isExpanded: (nodeId: string) => boolean;
    toggleNode: (nodeId: string) => void;
    onDeleteClick: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onRestoreClick?: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onForceDeleteClick?: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void;
}

const props = defineProps<Props>();


const formatBalance = (balance: string, currency: string): string => {
    const numBalance = parseFloat(balance);
    const formatted = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numBalance);

    return `Rp ${formatted}`;
};

const getTypeColor = (type: string): string => {
    const colors: Record<string, string> = {
        asset: 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-100',
        liability: 'bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100',
        equity: 'bg-green-500/10 text-green-700 dark:bg-green-500/20 dark:text-green-100',
        revenue: 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
        expense: 'bg-orange-500/10 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
    };

    return colors[type] || 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100';
};

const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
        active: 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
        inactive: 'bg-yellow-500/10 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-100',
        archived: 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100',
    };

    return colors[status] || 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100';
};

const hasChildren = computed(() => {
    return props.node.children !== undefined && props.node.children.length > 0;
});
</script>

<template>
    <div>
        <div
            class="grid grid-cols-12 gap-4 px-4 py-3 transition hover:bg-muted/30"
            :style="{ paddingLeft: `${(props.node.depth || 0) * 24 + 16}px` }"
        >
            <div class="col-span-3 flex items-center gap-2">
                <button
                    v-if="hasChildren"
                    type="button"
                    class="flex items-center justify-center"
                    @click="props.toggleNode(props.node.id)"
                >
                    <ChevronRight
                        :class="[
                            'size-4 transition-transform',
                            props.expanded ? 'rotate-90' : '',
                        ]"
                    />
                </button>
                <div v-else class="w-4"></div>
                <div class="flex flex-col">
                    <div class="font-medium">{{ props.node.accountNumber }}</div>
                    <div class="text-xs text-muted-foreground font-mono">{{ props.node.fullAccountNumber }}</div>
                    <Link
                        :href="AccountController.show.url(props.node.id)"
                        class="text-sm font-medium text-primary underline-offset-4 transition hover:underline"
                    >
                        {{ props.node.name }}
                    </Link>
                </div>
            </div>
            <div class="col-span-2 flex items-center justify-end">
                <Badge
                    :class="`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${getTypeColor(props.node.type)}`"
                >
                    {{ props.node.type.charAt(0).toUpperCase() + props.node.type.slice(1) }}
                </Badge>
            </div>
            <div class="col-span-1 flex items-center justify-end">
                <span class="text-sm text-muted-foreground text-right w-full">
                    {{ props.node.category || '—' }}
                </span>
            </div>
            <div class="col-span-2 flex items-center justify-end">
                <span class="text-sm font-medium text-right w-full">
                    {{ formatBalance(props.node.balance, props.node.currency) }}
                </span>
            </div>
            <div class="col-span-2 flex items-center justify-end gap-2">
                <Badge
                    :class="`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${getStatusColor(props.node.status)}`"
                >
                    {{ props.node.status.charAt(0).toUpperCase() + props.node.status.slice(1) }}
                </Badge>
                <Badge
                    v-if="props.node.deletedAt"
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100"
                >
                    Deleted
                </Badge>
            </div>
            <div class="col-span-2 flex items-center justify-end">
                <ActionsCell
                    :account="props.node"
                    :on-delete-click="onDeleteClick"
                    :on-restore-click="onRestoreClick"
                    :on-force-delete-click="onForceDeleteClick"
                />
            </div>
        </div>
        <Collapsible v-if="hasChildren" :open="props.expanded">
            <CollapsibleContent>
                <div class="divide-y divide-sidebar-border/60">
                    <TreeNode
                        v-for="child in props.node.children"
                        :key="child.id"
                        :node="child"
                        :expanded="props.isExpanded(child.id)"
                        :is-expanded="props.isExpanded"
                        :toggle-node="props.toggleNode"
                        :on-delete-click="onDeleteClick"
                        :on-restore-click="onRestoreClick"
                        :on-force-delete-click="onForceDeleteClick"
                    />
                </div>
            </CollapsibleContent>
        </Collapsible>
    </div>
</template>

