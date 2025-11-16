<script setup lang="ts">
import GuardianController from '@/actions/App/Http/Controllers/GuardianController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';

interface Guardian {
    id: string;
    name: string;
    email: string | null;
    phone: string | null;
    relationship: string | null;
    address: string | null;
    createdAt: string;
    updatedAt: string;
}

interface Props {
    guardian: Guardian;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Guardians',
        href: GuardianController.index().url,
    },
    {
        title: props.guardian.name,
        href: GuardianController.show.url(props.guardian.id),
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
        <Head :title="props.guardian.name" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        :title="props.guardian.name"
                        description="Guardian details and activity"
                    />
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button
                        as-child
                        class="w-full md:w-auto"
                    >
                        <Link :href="GuardianController.edit.url(props.guardian.id)">
                            Edit guardian
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="secondary"
                        class="w-full md:w-auto"
                    >
                        <Link :href="GuardianController.index().url">
                            Back to guardians
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <dl class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Name
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.guardian.name }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Email
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.guardian.email || '—' }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Phone
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.guardian.phone || '—' }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Relationship
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.guardian.relationship || '—' }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border md:col-span-2">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Address
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.guardian.address || '—' }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Created at
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ formatDateTime(props.guardian.createdAt) }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Last updated
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ formatDateTime(props.guardian.updatedAt) }}
                        </dd>
                    </div>
                </dl>
            </CardContent>
        </Card>
    </AppLayout>
</template>

