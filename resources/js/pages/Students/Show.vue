<script setup lang="ts">
import StudentController from '@/actions/App/Http/Controllers/StudentController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import GuardiansSection from './GuardiansSection.vue';

interface Guardian {
    id: string;
    name: string;
    email: string | null;
    phone: string | null;
    relationship: string | null;
    address: string | null;
    pivot?: {
        relationshipType: string | null;
        isPrimary: boolean;
    };
}

interface Student {
    id: string;
    studentNumber: string;
    name: string;
    email: string | null;
    phone: string | null;
    createdAt: string;
    updatedAt: string;
    guardians?: Guardian[];
}

interface Props {
    student: Student;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Students',
        href: StudentController.index().url,
    },
    {
        title: props.student.name,
        href: StudentController.show.url(props.student.id),
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.student.name" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        :title="props.student.name"
                        description="Student details and activity"
                    />
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button
                        as-child
                        class="w-full md:w-auto"
                    >
                        <Link :href="StudentController.edit.url(props.student.id)">
                            Edit student
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="secondary"
                        class="w-full md:w-auto"
                    >
                        <Link :href="StudentController.index().url">
                            Back to students
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <dl class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Student Number
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.student.studentNumber }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Name
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.student.name }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Email
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.student.email || '—' }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Phone
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.student.phone || '—' }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Created at
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ formatDateTime(props.student.createdAt) }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Last updated
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ formatDateTime(props.student.updatedAt) }}
                        </dd>
                    </div>
                </dl>
            </CardContent>
        </Card>

        <GuardiansSection
            :student="props.student"
            :readonly="true"
        />
    </AppLayout>
</template>

