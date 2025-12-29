<script setup lang="ts">
import ClassroomController from '@/actions/App/Http/Controllers/Academic/ClassroomController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface AcademicYear {
    id: string;
    name: string;
    startDate: string;
    endDate: string;
}

interface Teacher {
    id: string;
    teacherNumber: string;
    name: string;
    email: string;
}

interface Student {
    id: string;
    studentNumber: string;
    name: string;
    email: string;
    pivot: {
        status: string;
    };
}

interface Classroom {
    id: string;
    name: string;
    gradeLevel: number;
    capacity: number;
    academicYear: AcademicYear | null;
    homeroomTeacher: Teacher | null;
    students: Student[];
    activeStudentsCount: number;
    createdAt: string;
    updatedAt: string;
    deletedAt: string | null;
}

interface Props {
    classroom: Classroom;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Classrooms',
        href: ClassroomController.index().url,
    },
    {
        title: props.classroom.name,
        href: ClassroomController.show(props.classroom.id).url,
    },
];

const formatDateTime = (value: string) =>
    new Date(value).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'inactive':
            return 'secondary';
        case 'graduated':
            return 'outline';
        case 'transferred':
            return 'destructive';
        default:
            return 'secondary';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.classroom.name" />

        <div class="flex flex-col gap-6">
            <Card>
                <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                    <div class="w-full">
                        <Heading
                            :title="props.classroom.name"
                            description="Classroom details and enrolled students"
                        />
                    </div>
                    <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                        <Button
                            as-child
                            class="w-full md:w-auto"
                            :disabled="props.classroom.deletedAt !== null"
                        >
                            <Link :href="ClassroomController.edit(props.classroom.id).url">
                                Edit Classroom
                            </Link>
                        </Button>
                        <Button
                            as-child
                            variant="secondary"
                            class="w-full md:w-auto"
                            :disabled="props.classroom.deletedAt !== null"
                        >
                            <Link :href="ClassroomController.enrollment(props.classroom.id).url">
                                Manage Students
                            </Link>
                        </Button>
                        <Button
                            as-child
                            variant="outline"
                            class="w-full md:w-auto"
                        >
                            <Link :href="ClassroomController.index().url">
                                Back to Classrooms
                            </Link>
                        </Button>
                    </div>
                </CardHeader>

                <CardContent>
                    <dl class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                            <dt class="text-sm font-medium text-muted-foreground">
                                Classroom Name
                            </dt>
                            <dd class="mt-2 text-base font-semibold text-foreground">
                                {{ props.classroom.name }}
                            </dd>
                        </div>
                        <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                            <dt class="text-sm font-medium text-muted-foreground">
                                Grade Level
                            </dt>
                            <dd class="mt-2 text-base font-semibold text-foreground">
                                Grade {{ props.classroom.gradeLevel }}
                            </dd>
                        </div>
                        <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                            <dt class="text-sm font-medium text-muted-foreground">
                                Capacity
                            </dt>
                            <dd class="mt-2 text-base font-semibold text-foreground">
                                {{ props.classroom.activeStudentsCount }} / {{ props.classroom.capacity }} students
                            </dd>
                        </div>
                        <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                            <dt class="text-sm font-medium text-muted-foreground">
                                Academic Year
                            </dt>
                            <dd class="mt-2 text-base font-semibold text-foreground">
                                {{ props.classroom.academicYear?.name || '—' }}
                            </dd>
                        </div>
                        <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                            <dt class="text-sm font-medium text-muted-foreground">
                                Homeroom Teacher
                            </dt>
                            <dd class="mt-2 text-base font-semibold text-foreground">
                                {{ props.classroom.homeroomTeacher?.name || 'Not assigned' }}
                            </dd>
                            <dd v-if="props.classroom.homeroomTeacher" class="mt-1 text-sm text-muted-foreground">
                                {{ props.classroom.homeroomTeacher.teacherNumber }}
                            </dd>
                        </div>
                        <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                            <dt class="text-sm font-medium text-muted-foreground">
                                Status
                            </dt>
                            <dd class="mt-2">
                                <Badge :variant="props.classroom.deletedAt ? 'destructive' : 'default'">
                                    {{ props.classroom.deletedAt ? 'Deleted' : 'Active' }}
                                </Badge>
                            </dd>
                        </div>
                        <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                            <dt class="text-sm font-medium text-muted-foreground">
                                Created at
                            </dt>
                            <dd class="mt-2 text-base font-semibold text-foreground">
                                {{ formatDateTime(props.classroom.createdAt) }}
                            </dd>
                        </div>
                        <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                            <dt class="text-sm font-medium text-muted-foreground">
                                Last updated
                            </dt>
                            <dd class="mt-2 text-base font-semibold text-foreground">
                                {{ formatDateTime(props.classroom.updatedAt) }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <Heading
                        title="Enrolled Students"
                        :description="`${props.classroom.students?.length || 0} students enrolled in this classroom`"
                    />
                </CardHeader>
                <CardContent>
                    <div v-if="props.classroom.students?.length > 0" class="rounded-lg border">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b bg-muted/50">
                                        <th class="px-4 py-3 text-left text-sm font-medium">Student Number</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium">Email</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="student in props.classroom.students"
                                        :key="student.id"
                                        class="border-b transition-colors last:border-b-0 hover:bg-muted/50"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium">
                                            {{ student.studentNumber }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ student.name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-muted-foreground">
                                            {{ student.email }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge :variant="getStatusVariant(student.pivot.status)">
                                                {{ student.pivot.status }}
                                            </Badge>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-sm text-muted-foreground">
                        No students enrolled in this classroom yet.
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>


