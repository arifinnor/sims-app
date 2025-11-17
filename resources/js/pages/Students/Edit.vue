<script setup lang="ts">
import StudentController from '@/actions/App/Http/Controllers/StudentController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface StudentFormData {
    id: string;
    studentNumber: string;
    name: string;
    email: string | null;
    phone: string | null;
}

interface Props {
    student: StudentFormData;
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
    {
        title: 'Edit',
        href: StudentController.edit.url(props.student.id),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${props.student.name}`" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        :title="`Edit ${props.student.name}`"
                        description="Update the student details"
                    />
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button
                        as-child
                        variant="secondary"
                        class="w-full md:w-auto"
                    >
                        <Link :href="StudentController.show.url(props.student.id)">
                            View student
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="ghost"
                        class="w-full md:w-auto"
                    >
                        <Link :href="StudentController.index().url">
                            Back to students
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <Form
                :action="StudentController.update.url(props.student.id)"
                method="put"
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
                            :default-value="props.student.name"
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
                            :default-value="props.student.email || ''"
                            placeholder="email@example.com"
                        />
                        <InputError :message="errors.email" />
                        <p class="text-sm text-muted-foreground">
                            Optional - students under 17 may not have an email address
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            type="tel"
                            autocomplete="tel"
                            :default-value="props.student.phone || ''"
                            placeholder="+1234567890"
                        />
                        <InputError :message="errors.phone" />
                        <p class="text-sm text-muted-foreground">
                            Optional - students under 17 may not have a phone number
                        </p>
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
                        data-test="update-student-button"
                    >
                        Save changes
                    </Button>
                </CardFooter>
            </Form>
        </Card>

    </AppLayout>
</template>

