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
import { Textarea } from '@/components/ui/textarea';

interface GuardianFormData {
    id: string;
    name: string;
    email: string | null;
    phone: string | null;
    relationship: string | null;
    address: string | null;
}

interface Props {
    guardian: GuardianFormData;
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
    {
        title: 'Edit',
        href: GuardianController.edit.url(props.guardian.id),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${props.guardian.name}`" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        :title="`Edit ${props.guardian.name}`"
                        description="Update the guardian details"
                    />
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button
                        as-child
                        variant="secondary"
                        class="w-full md:w-auto"
                    >
                        <Link :href="GuardianController.show.url(props.guardian.id)">
                            View guardian
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="ghost"
                        class="w-full md:w-auto"
                    >
                        <Link :href="GuardianController.index().url">
                            Back to guardians
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <Form
                v-bind="GuardianController.update.form(props.guardian.id)"
                class="contents"
                :options="{ preserveScroll: true }"
                v-slot="{ errors, processing, recentlySuccessful }"
            >
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            autocomplete="name"
                            :default-value="props.guardian.name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            :default-value="props.guardian.email || ''"
                            placeholder="email@example.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            type="tel"
                            autocomplete="tel"
                            :default-value="props.guardian.phone || ''"
                            placeholder="+1234567890"
                        />
                        <InputError :message="errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="relationship">Relationship</Label>
                        <Input
                            id="relationship"
                            name="relationship"
                            type="text"
                            :default-value="props.guardian.relationship || ''"
                            placeholder="e.g., Mother, Father, Legal Guardian"
                        />
                        <InputError :message="errors.relationship" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address">Address</Label>
                        <Textarea
                            id="address"
                            name="address"
                            :default-value="props.guardian.address || ''"
                            placeholder="Full address"
                            rows="3"
                        />
                        <InputError :message="errors.address" />
                    </div>
                </CardContent>

                <CardFooter class="flex flex-col gap-3 md:flex-row md:items-center md:justify-end">
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-show="recentlySuccessful"
                            class="text-sm text-neutral-600 dark:text-neutral-300"
                        >
                            Changes saved.
                        </p>
                    </Transition>

                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="update-guardian-button"
                    >
                        Save changes
                    </Button>
                </CardFooter>
            </Form>
        </Card>
    </AppLayout>
</template>

