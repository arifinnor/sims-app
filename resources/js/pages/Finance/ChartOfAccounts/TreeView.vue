<script setup lang="ts">
import { computed, ref } from 'vue';
import { Collapsible, CollapsibleContent } from '@/components/ui/collapsible';
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
import TreeNode from './TreeNode.vue';
import type { Account } from './columns';

interface TreeNodeData extends Account {
    children?: TreeNodeData[];
    depth?: number;
}

interface Props {
    accounts: Account[];
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

const expandedNodes = ref<Set<string>>(new Set());

const buildTree = (accounts: Account[]): TreeNodeData[] => {
    const accountMap = new Map<string, TreeNodeData>();
    const rootNodes: TreeNodeData[] = [];

    // First pass: create all nodes
    accounts.forEach((account) => {
        accountMap.set(account.id, {
            ...account,
            children: [],
            depth: 0,
        });
    });

    // Second pass: build tree structure
    accounts.forEach((account) => {
        const node = accountMap.get(account.id)!;
        const parentId = account.parentAccountId;

        if (parentId && accountMap.has(parentId)) {
            const parent = accountMap.get(parentId)!;
            if (!parent.children) {
                parent.children = [];
            }
            parent.children.push(node);
            node.depth = (parent.depth || 0) + 1;
            // Expand all parent nodes by default
            expandedNodes.value.add(parentId);
        } else {
            rootNodes.push(node);
        }
    });

    // Sort children by account number
    const sortChildren = (nodes: TreeNodeData[]): void => {
        nodes.sort((a, b) => {
            if (a.accountNumber < b.accountNumber) {
                return -1;
            }
            if (a.accountNumber > b.accountNumber) {
                return 1;
            }
            return 0;
        });
        nodes.forEach((node) => {
            if (node.children && node.children.length > 0) {
                sortChildren(node.children);
            }
        });
    };

    sortChildren(rootNodes);

    return rootNodes;
};

const treeNodes = computed(() => buildTree(props.accounts));

const toggleNode = (nodeId: string): void => {
    if (expandedNodes.value.has(nodeId)) {
        expandedNodes.value.delete(nodeId);
    } else {
        expandedNodes.value.add(nodeId);
    }
};

const isExpanded = (nodeId: string): boolean => {
    return expandedNodes.value.has(nodeId);
};
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-sidebar-border/60 shadow-sm dark:border-sidebar-border">
        <div class="bg-muted/20 border-b border-sidebar-border/60 px-4 py-3">
            <div class="grid grid-cols-12 gap-4 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                <div class="col-span-3">Account</div>
                <div class="col-span-2 text-right">Type</div>
                <div class="col-span-1 text-right">Category</div>
                <div class="col-span-2 text-right">Balance</div>
                <div class="col-span-2 text-right">Status</div>
                <div class="col-span-2 text-right">Actions</div>
            </div>
        </div>
        <div v-if="treeNodes.length === 0" class="px-4 py-6 text-center text-sm text-muted-foreground">
            No accounts found.
        </div>
        <div v-else class="divide-y divide-sidebar-border/60">
            <TreeNode
                v-for="node in treeNodes"
                :key="node.id"
                :node="node"
                :expanded="isExpanded(node.id)"
                :is-expanded="isExpanded"
                :toggle-node="toggleNode"
                :on-delete-click="onDeleteClick"
                :on-restore-click="onRestoreClick"
                :on-force-delete-click="onForceDeleteClick"
            />
        </div>
    </div>
</template>
