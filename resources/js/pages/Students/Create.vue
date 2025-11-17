<script setup lang="ts">
import StudentController from '@/actions/App/Http/Controllers/StudentController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { ref } from 'vue';

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const handleSubmit = (e: Event) => {
    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);
    const data: Record<string, any> = {
        name: formData.get('name'),
        email: formData.get('email') || null,
        phone: formData.get('phone') || null,
    };

    processing.value = true;
    router.post(StudentController.store.url(), data, {
        preserveScroll: true,
        onError: (pageErrors) => {
            errors.value = pageErrors as Record<string, string>;
            processing.value = false;
        },
        onSuccess: () => {
            processing.value = false;
        },
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Students',
        href: StudentController.index().url,
    },
    {
        title: 'Create student',
        href: StudentController.create().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Create student" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading title="Create student" description="Add a new student to the platform" />
                </div>
                <Button as-child variant="secondary" class="w-full md:w-auto">
                    <Link :href="StudentController.index().url"> Cancel </Link>
                </Button>
            </CardHeader>
            <form :action="StudentController.store.url()" method="post" class="contents" @submit.prevent="handleSubmit">
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" type="text" name="name" autocomplete="name" required placeholder="Full name"
                            autofocus />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input id="email" type="email" name="email" autocomplete="email"
                            placeholder="email@example.com" />
                        <InputError :message="errors.email" />
                        <p class="text-sm text-muted-foreground">
                            Optional - students under 17 may not have an email address
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input id="phone" type="tel" name="phone" autocomplete="tel" placeholder="+1234567890" />
                        <InputError :message="errors.phone" />
                        <p class="text-sm text-muted-foreground">
                            Optional - students under 17 may not have a phone number
                        </p>
                    </div>
                </CardContent>
                <CardFooter class="justify-end">
                    <Button type="submit" :disabled="processing" data-test="create-student-button">
                        <Spinner v-if="processing" />
                        Create student
                    </Button>
                </CardFooter>
            </form>
        </Card>

    </AppLayout>
</template>
