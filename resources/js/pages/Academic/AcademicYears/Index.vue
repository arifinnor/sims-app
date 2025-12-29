<script setup lang="ts">
import AcademicYearController from '@/actions/App/Http/Controllers/Academic/AcademicYearController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ref, watch, onMounted } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Form } from '@inertiajs/vue3';

interface CursorPaginated<T> {
    data: T[];
    next_cursor: string | null;
    prev_cursor: string | null;
    path: string;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface AcademicYear {
    id: string;
    name: string;
    start_date: string;
    end_date: string;
    is_active: boolean;
    created_at: string | null;
    deleted_at: string | null;
}

interface Props {
    academicYears: CursorPaginated<AcademicYear>;
    perPageOptions: number[];
}

defineProps<Props>();

const searchQuery = ref<string>('');
const withTrashedFilter = ref<string>('none');
const isInitialMount = ref(true);

const updateFilters = useDebounceFn(() => {
    const url = new URL(AcademicYearController.index().url, window.location.origin);
    if (searchQuery.value) {
        url.searchParams.set('search', searchQuery.value);
    } else {
        url.searchParams.delete('search');
    }
    if (withTrashedFilter.value !== 'none') {
        url.searchParams.set('with_trashed', withTrashedFilter.value);
    } else {
        url.searchParams.delete('with_trashed');
    }
    url.searchParams.delete('cursor');

    router.visit(url.pathname + url.search, {
        only: ['academicYears'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch([searchQuery, withTrashedFilter], () => {
    if (!isInitialMount.value) {
        updateFilters();
    }
});

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    searchQuery.value = urlParams.get('search') || '';
    withTrashedFilter.value = urlParams.get('with_trashed') || 'none';
    isInitialMount.value = false;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Academic Years',
        href: AcademicYearController.index().url,
    },
];

const isDeleteDialogOpen = ref(false);
const isToggleActiveDialogOpen = ref(false);
const pendingAcademicYear = ref<AcademicYear | null>(null);
const pendingDeleteAction = ref<{
    submit: () => void;
    processing: () => boolean;
} | null>(null);
const pendingToggleAction = ref<{
    submit: () => void;
    processing: () => boolean;
} | null>(null);

const openDeleteDialog = (
    academicYear: AcademicYear,
    submit: () => void,
    processing: () => boolean,
) => {
    pendingAcademicYear.value = academicYear;
    pendingDeleteAction.value = {
        submit,
        processing,
    };
    isDeleteDialogOpen.value = true;
};

const openToggleActiveDialog = (
    academicYear: AcademicYear,
    submit: () => void,
    processing: () => boolean,
) => {
    pendingAcademicYear.value = academicYear;
    pendingToggleAction.value = {
        submit,
        processing,
    };
    isToggleActiveDialogOpen.value = true;
};

const confirmDelete = () => {
    if (pendingDeleteAction.value) {
        pendingDeleteAction.value.submit();
    }
    isDeleteDialogOpen.value = false;
    pendingDeleteAction.value = null;
    pendingAcademicYear.value = null;
};

const confirmToggleActive = () => {
    if (pendingToggleAction.value) {
        pendingToggleAction.value.submit();
    }
    isToggleActiveDialogOpen.value = false;
    pendingToggleAction.value = null;
    pendingAcademicYear.value = null;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Academic Years" />

        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <Heading
                    title="Academic Years"
                    description="Manage academic years for scheduling and billing"
                />
                <Button as-child>
                    <Link :href="AcademicYearController.create().url"> Create Academic Year </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 md:flex-row">
                <div class="flex-1">
                    <Input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search academic years..."
                        class="w-full"
                    />
                </div>
                <Select v-model="withTrashedFilter">
                    <SelectTrigger class="w-full md:w-[180px]">
                        <SelectValue placeholder="Filter" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">Active Only</SelectItem>
                        <SelectItem value="only">Deleted Only</SelectItem>
                        <SelectItem value="all">All</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-lg border">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Start Date</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">End Date</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="academicYear in academicYears.data"
                                :key="academicYear.id"
                                class="border-b transition-colors hover:bg-muted/50"
                            >
                                <td class="px-4 py-3">
                                    <Link
                                        :href="AcademicYearController.edit(academicYear.id).url"
                                        class="font-medium hover:underline"
                                    >
                                        {{ academicYear.name }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3 text-sm text-muted-foreground">
                                    {{ new Date(academicYear.start_date).toLocaleDateString() }}
                                </td>
                                <td class="px-4 py-3 text-sm text-muted-foreground">
                                    {{ new Date(academicYear.end_date).toLocaleDateString() }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="academicYear.is_active"
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                                    >
                                        Active
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800"
                                    >
                                        Inactive
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Form
                                            v-bind="
                                                AcademicYearController.toggleActive.form(
                                                    academicYear.id,
                                                )
                                            "
                                            @submit="
                                                openToggleActiveDialog(academicYear, $event.submit, () =>
                                                    $event.processing,
                                                )
                                            "
                                        >
                                            <Button
                                                type="submit"
                                                variant="outline"
                                                size="sm"
                                                :disabled="academicYear.deleted_at !== null"
                                            >
                                                {{ academicYear.is_active ? 'Deactivate' : 'Activate' }}
                                            </Button>
                                        </Form>
                                        <Form
                                            v-bind="AcademicYearController.destroy.form(academicYear.id)"
                                            @submit="
                                                openDeleteDialog(academicYear, $event.submit, () =>
                                                    $event.processing,
                                                )
                                            "
                                        >
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                size="sm"
                                                :disabled="academicYear.deleted_at !== null"
                                            >
                                                Delete
                                            </Button>
                                        </Form>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="academicYears.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-muted-foreground">
                                    No academic years found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-if="academicYears.next_page_url || academicYears.prev_page_url"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Showing {{ academicYears.data.length }} of {{ academicYears.per_page }} per page
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="academicYears.prev_page_url"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="academicYears.prev_page_url">Previous</Link>
                    </Button>
                    <Button
                        v-if="academicYears.next_page_url"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="academicYears.next_page_url">Next</Link>
                    </Button>
                </div>
            </div>
        </div>

        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Academic Year</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete
                        <strong>{{ pendingAcademicYear?.name }}</strong>? This action cannot be
                        undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="isDeleteDialogOpen = false"> Cancel </Button>
                    <Button variant="destructive" @click="confirmDelete"> Delete </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="isToggleActiveDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Toggle Active Status</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to
                        {{ pendingAcademicYear?.is_active ? 'deactivate' : 'activate' }}
                        <strong>{{ pendingAcademicYear?.name }}</strong>?
                        {{ pendingAcademicYear?.is_active ? '' : 'This will deactivate all other academic years.' }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="isToggleActiveDialogOpen = false">
                        Cancel
                    </Button>
                    <Button @click="confirmToggleActive"> Confirm </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
