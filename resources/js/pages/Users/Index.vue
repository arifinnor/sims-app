<script setup lang="ts">
import UserController from '@/actions/App/Http/Controllers/UserController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { ref, watch, computed } from 'vue';
import DataTable from './DataTable.vue';
import DataTablePagination from './DataTablePagination.vue';
import { createColumns, type User } from './columns';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
}

interface Props {
    users: Paginated<User>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: UserController.index().url,
    },
];

const isDeleteDialogOpen = ref(false);
const pendingDeleteUser = ref<User | null>(null);
const pendingDeleteAction = ref<{
    submit: () => void;
    processing: () => boolean;
} | null>(null);

const openDeleteDialog = (
    user: User,
    submit: () => void,
    processing: () => boolean,
) => {
    pendingDeleteUser.value = user;
    pendingDeleteAction.value = {
        submit,
        processing,
    };
    isDeleteDialogOpen.value = true;
};

const confirmDelete = () => {
    if (!pendingDeleteAction.value) {
        return;
    }

    pendingDeleteAction.value.submit();
    isDeleteDialogOpen.value = false;
};

const cancelDelete = () => {
    isDeleteDialogOpen.value = false;
};

watch(isDeleteDialogOpen, (open) => {
    if (!open) {
        pendingDeleteUser.value = null;
        pendingDeleteAction.value = null;
    }
});

const columns = computed(() => createColumns(openDeleteDialog));
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Users" />

        <Card>
                <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                    <div class="w-full">
                        <Heading
                            title="Users"
                            description="Manage users across the platform"
                        />
                    </div>
                    <Button as-child class="w-full md:w-auto">
                        <Link :href="UserController.create().url">
                            Create user
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-6">
                    <DataTable
                        :data="props.users.data"
                        :columns="columns"
                    />

                    <DataTablePagination
                        :links="props.users.links"
                        :from="props.users.from"
                        :to="props.users.to"
                        :total="props.users.total"
                    />
            </CardContent>
        </Card>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Delete user</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete
                        <span class="font-medium text-foreground">
                            {{ pendingDeleteUser?.name ?? 'this user' }}
                        </span>
                        ?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:space-x-0">
                    <Button
                        type="button"
                        variant="outline"
                        @click="cancelDelete"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="pendingDeleteAction?.processing()"
                        @click="confirmDelete"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

