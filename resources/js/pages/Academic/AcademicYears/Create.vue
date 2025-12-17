<script setup lang="ts">
import AcademicYearController from '@/actions/App/Http/Controllers/Academic/AcademicYearController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Academic Years',
        href: AcademicYearController.index().url,
    },
    {
        title: 'Create Academic Year',
        href: AcademicYearController.create().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Academic Year" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        title="Create Academic Year"
                        description="Add a new academic year for scheduling and billing"
                    />
                </div>
                <Button as-child variant="secondary" class="w-full md:w-auto">
                    <Link :href="AcademicYearController.index().url"> Cancel </Link>
                </Button>
            </CardHeader>
            <Form
                v-bind="AcademicYearController.store.form()"
                class="contents"
                :reset-on-success="['name', 'start_date', 'end_date', 'is_active']"
                v-slot="{ errors, processing }"
            >
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            required
                            placeholder="e.g., 2024/2025 Ganjil"
                            autofocus
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="start_date">Start Date</Label>
                        <Input id="start_date" type="date" name="start_date" required />
                        <InputError :message="errors.start_date" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="end_date">End Date</Label>
                        <Input id="end_date" type="date" name="end_date" required />
                        <InputError :message="errors.end_date" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="is_active"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="h-4 w-4 rounded border-gray-300"
                        />
                        <Label for="is_active" class="cursor-pointer"> Set as active academic year </Label>
                    </div>
                    <InputError :message="errors.is_active" />
                </CardContent>
                <CardFooter>
                    <Button type="submit" :disabled="processing"> Create Academic Year </Button>
                </CardFooter>
            </Form>
        </Card>
    </AppLayout>
</template>
