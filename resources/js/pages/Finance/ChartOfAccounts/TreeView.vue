<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import ChartOfAccountController from '@/actions/App/Http/Controllers/Finance/ChartOfAccountController';
import { ChevronRight, ChevronDown, Folder, FileText } from 'lucide-vue-next';
import type { ChartOfAccount } from './columns';

interface TreeNode extends ChartOfAccount {
    children?: TreeNode[];
    has_children?: boolean;
}

interface Props {
    nodes: TreeNode[];
    level?: number;
}

const props = withDefaults(defineProps<Props>(), {
    level: 0,
});

const expandedNodes = ref<Set<string>>(new Set());

const toggleNode = (nodeId: string) => {
    if (expandedNodes.value.has(nodeId)) {
        expandedNodes.value.delete(nodeId);
    } else {
        expandedNodes.value.add(nodeId);
    }
};

const isExpanded = (nodeId: string): boolean => {
    return expandedNodes.value.has(nodeId);
};

const getAccountTypeBadgeClass = (type: string): string => {
    const classes: Record<string, string> = {
        ASSET: 'bg-blue-500/10 text-blue-700 dark:bg-blue-500/20 dark:text-blue-100',
        LIABILITY: 'bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-100',
        EQUITY: 'bg-purple-500/10 text-purple-700 dark:bg-purple-500/20 dark:text-purple-100',
        REVENUE: 'bg-green-500/10 text-green-700 dark:bg-green-500/20 dark:text-green-100',
        EXPENSE: 'bg-orange-500/10 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
    };
    return classes[type] || 'bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100';
};
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-sidebar-border/60 shadow-sm dark:border-sidebar-border">
        <div class="divide-y divide-sidebar-border/60">
            <template v-for="node in nodes" :key="node.id">
                <div
                    class="group flex items-center gap-2 bg-background px-4 py-3 transition hover:bg-muted/30"
                    :style="{ paddingLeft: `${props.level * 1.5 + 1}rem` }"
                >
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <Button
                            v-if="node.has_children"
                            variant="ghost"
                            size="sm"
                            class="h-6 w-6 p-0"
                            @click="toggleNode(node.id)"
                        >
                            <ChevronRight
                                v-if="!isExpanded(node.id)"
                                class="h-4 w-4 transition-transform"
                            />
                            <ChevronDown
                                v-else
                                class="h-4 w-4 transition-transform"
                            />
                        </Button>
                        <div v-else class="w-6" />
                        <component
                            :is="node.is_header ? Folder : FileText"
                            class="h-4 w-4 flex-shrink-0 text-muted-foreground"
                        />
                        <Link
                            :href="ChartOfAccountController.show.url(node.id)"
                            class="font-mono text-sm text-primary underline-offset-4 transition hover:underline flex-shrink-0"
                        >
                            {{ node.code }}
                        </Link>
                        <Link
                            :href="ChartOfAccountController.show.url(node.id)"
                            class="font-medium text-sm text-foreground underline-offset-4 transition hover:underline flex-1 min-w-0 truncate"
                        >
                            {{ node.name }}
                        </Link>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <Badge
                                :class="`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${getAccountTypeBadgeClass(node.account_type)}`"
                            >
                                {{ node.account_type }}
                            </Badge>
                            <Badge
                                v-if="node.is_header"
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100"
                            >
                                Header
                            </Badge>
                            <Badge
                                v-if="node.is_cash"
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-yellow-500/10 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-100"
                            >
                                Cash
                            </Badge>
                            <Badge
                                v-if="!node.is_active"
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-500/10 text-gray-700 dark:bg-gray-500/20 dark:text-gray-100"
                            >
                                Inactive
                            </Badge>
                        </div>
                    </div>
                </div>
                <TreeView
                    v-if="node.has_children && node.children && isExpanded(node.id)"
                    :nodes="node.children"
                    :level="props.level + 1"
                />
            </template>
        </div>
        <div
            v-if="nodes.length === 0"
            class="px-4 py-6 text-center text-sm text-muted-foreground"
        >
            No accounts found.
        </div>
    </div>
</template>

