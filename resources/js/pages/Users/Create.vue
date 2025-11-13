<script setup lang="ts">
import UserController from '@/actions/App/Http/Controllers/UserController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: UserController.index().url,
    },
    {
        title: 'Create user',
        href: UserController.create().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create user" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        title="Create user"
                        description="Add a new user to the platform"
                    />
                </div>
                <Button as-child variant="secondary" class="w-full md:w-auto">
                    <Link :href="UserController.index().url"> Cancel </Link>
                </Button>
            </CardHeader>
            <Form
                v-bind="UserController.store.form()"
                class="contents"
                :reset-on-success="[
                    'name',
                    'email',
                    'password',
                    'password_confirmation',
                ]"
                v-slot="{ errors, processing }"
            >
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            autocomplete="name"
                            required
                            placeholder="Full name"
                            autofocus
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            required
                            placeholder="email@example.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            required
                            placeholder="Password"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Confirm password</Label
                        >
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            required
                            placeholder="Confirm password"
                        />
                        <InputError
                            :message="errors.password_confirmation"
                        />
                    </div>
                </CardContent>
                <CardFooter class="justify-end">
                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="create-user-button"
                    >
                        <Spinner v-if="processing" />
                        Create user
                    </Button>
                </CardFooter>
            </Form>
        </Card>
    </AppLayout>
</template>

