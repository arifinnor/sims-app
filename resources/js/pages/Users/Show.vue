<script setup lang="ts">
import UserController from '@/actions/App/Http/Controllers/UserController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';

interface UserDetails {
    id: number;
    name: string;
    email: string;
    emailVerifiedAt: string | null;
    createdAt: string;
    updatedAt: string;
}

interface Props {
    user: UserDetails;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: UserController.index().url,
    },
    {
        title: props.user.name,
        href: UserController.show.url(props.user.id),
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
        <Head :title="props.user.name" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        :title="props.user.name"
                        description="User details and activity"
                    />
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button
                        as-child
                        class="w-full md:w-auto"
                    >
                        <Link :href="UserController.edit.url(props.user.id)">
                            Edit user
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="secondary"
                        class="w-full md:w-auto"
                    >
                        <Link :href="UserController.index().url">
                            Back to users
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
                            {{ props.user.name }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Email
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ props.user.email }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Email status
                        </dt>
                        <dd class="mt-2">
                            <span
                                :class="[
                                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
                                    props.user.emailVerifiedAt
                                        ? 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100'
                                        : 'bg-amber-500/10 text-amber-700 dark:bg-amber-500/20 dark:text-amber-100',
                                ]"
                            >
                                {{
                                    props.user.emailVerifiedAt
                                        ? `Verified on ${formatDateTime(
                                              props.user.emailVerifiedAt,
                                          )}`
                                        : 'Pending verification'
                                }}
                            </span>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Created at
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ formatDateTime(props.user.createdAt) }}
                        </dd>
                    </div>
                    <div class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border">
                        <dt class="text-sm font-medium text-muted-foreground">
                            Last updated
                        </dt>
                        <dd class="mt-2 text-base font-semibold text-foreground">
                            {{ formatDateTime(props.user.updatedAt) }}
                        </dd>
                    </div>
                </dl>
            </CardContent>
        </Card>
    </AppLayout>
</template>

