<script setup lang="ts">
import ClassroomController from '@/actions/App/Http/Controllers/Academic/ClassroomController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ref, watch, onMounted, computed } from 'vue';
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

interface Classroom {
    id: string;
    name: string;
    grade_level: number;
    capacity: number;
    academic_year: {
        id: string;
        name: string;
    } | null;
    homeroom_teacher: {
        id: string;
        name: string;
    } | null;
    deleted_at: string | null;
}

interface Props {
    classrooms: CursorPaginated<Classroom>;
    perPageOptions: number[];
}

const props = defineProps<Props>();

const searchQuery = ref<string>('');
const withTrashedFilter = ref<string>('none');
const isInitialMount = ref(true);

const updateFilters = useDebounceFn(() => {
    const url = new URL(ClassroomController.index().url, window.location.origin);
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
        only: ['classrooms'],
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
        title: 'Classrooms',
        href: ClassroomController.index().url,
    },
];

const groupedByGrade = computed(() => {
    const grouped: Record<number, Classroom[]> = {};
    props.classrooms.data.forEach((classroom) => {
        if (!grouped[classroom.grade_level]) {
            grouped[classroom.grade_level] = [];
        }
        grouped[classroom.grade_level].push(classroom);
    });
    return grouped;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Classrooms" />

        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <Heading
                    title="Classrooms"
                    description="Manage classrooms for each academic year"
                />
                <Button as-child>
                    <Link :href="ClassroomController.create().url"> Create Classroom </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 md:flex-row">
                <div class="flex-1">
                    <Input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search classrooms..."
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

            <div class="space-y-6">
                <div
                    v-for="(classrooms, gradeLevel) in groupedByGrade"
                    :key="gradeLevel"
                    class="space-y-4"
                >
                    <h3 class="text-lg font-semibold">Grade {{ gradeLevel }}</h3>
                    <div class="rounded-lg border">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium">
                                            Academic Year
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium">
                                            Homeroom Teacher
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-medium">Capacity</th>
                                        <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="classroom in classrooms"
                                        :key="classroom.id"
                                        class="border-b transition-colors hover:bg-muted/50"
                                    >
                                        <td class="px-4 py-3">
                                            <Link
                                                :href="ClassroomController.edit(classroom.id).url"
                                                class="font-medium hover:underline"
                                            >
                                                {{ classroom.name }}
                                            </Link>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted-foreground">
                                            {{ classroom.academic_year?.name || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted-foreground">
                                            {{ classroom.homeroom_teacher?.name || 'Not assigned' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted-foreground">
                                            {{ classroom.capacity }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button
                                                    as-child
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    <Link :href="ClassroomController.show(classroom.id).url">
                                                        Show
                                                    </Link>
                                                </Button>
                                                <Button
                                                    as-child
                                                    variant="outline"
                                                    size="sm"
                                                    :disabled="classroom.deleted_at !== null"
                                                >
                                                    <Link :href="ClassroomController.enrollment(classroom.id).url">
                                                        Manage Students
                                                    </Link>
                                                </Button>
                                                <Form
                                                    v-bind="ClassroomController.destroy.form(classroom.id)"
                                                >
                                                    <Button
                                                        type="submit"
                                                        variant="destructive"
                                                        size="sm"
                                                        :disabled="classroom.deleted_at !== null"
                                                    >
                                                        Delete
                                                    </Button>
                                                </Form>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div v-if="classrooms.data.length === 0" class="text-center text-sm text-muted-foreground">
                    No classrooms found.
                </div>
            </div>

            <div
                v-if="classrooms.next_page_url || classrooms.prev_page_url"
                class="flex items-center justify-between"
            >
                <div class="text-sm text-muted-foreground">
                    Showing {{ classrooms.data.length }} of {{ classrooms.per_page }} per page
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="classrooms.prev_page_url"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="classrooms.prev_page_url">Previous</Link>
                    </Button>
                    <Button
                        v-if="classrooms.next_page_url"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="classrooms.next_page_url">Next</Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
