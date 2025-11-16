<script setup lang="ts">
import GuardianController from '@/actions/App/Http/Controllers/GuardianController';
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
import { Textarea } from '@/components/ui/textarea';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Guardians',
        href: GuardianController.index().url,
    },
    {
        title: 'Create guardian',
        href: GuardianController.create().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create guardian" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        title="Create guardian"
                        description="Add a new guardian to the platform"
                    />
                </div>
                <Button as-child variant="secondary" class="w-full md:w-auto">
                    <Link :href="GuardianController.index().url"> Cancel </Link>
                </Button>
            </CardHeader>
            <Form
                v-bind="GuardianController.store.form()"
                class="contents"
                :reset-on-success="['name', 'email', 'phone', 'relationship', 'address']"
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
                            placeholder="email@example.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            type="tel"
                            name="phone"
                            autocomplete="tel"
                            placeholder="+1234567890"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="relationship">Relationship</Label>
                        <Input
                            id="relationship"
                            type="text"
                            name="relationship"
                            placeholder="e.g., Mother, Father, Legal Guardian"
                        />
                        <InputError :message="errors.relationship" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address">Address</Label>
                        <Textarea
                            id="address"
                            name="address"
                            placeholder="Full address"
                            rows="3"
                        />
                        <InputError :message="errors.address" />
                    </div>
                </CardContent>
                <CardFooter class="justify-end">
                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="create-guardian-button"
                    >
                        <Spinner v-if="processing" />
                        Create guardian
                    </Button>
                </CardFooter>
            </Form>
        </Card>
    </AppLayout>
</template>

