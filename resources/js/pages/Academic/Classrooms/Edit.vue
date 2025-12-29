<script setup lang="ts">
import ClassroomController from '@/actions/App/Http/Controllers/Academic/ClassroomController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface AcademicYear {
    id: string;
    name: string;
}

interface Teacher {
    id: string;
    name: string;
    teacherNumber: string;
}

interface Classroom {
    id: string;
    academic_year_id: string;
    homeroom_teacher_id: string | null;
    grade_level: number;
    name: string;
    capacity: number;
}

interface Props {
    classroom: Classroom;
    academicYears: AcademicYear[];
    teachers: Teacher[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Classrooms',
        href: ClassroomController.index().url,
    },
    {
        title: 'Edit Classroom',
        href: ClassroomController.edit(props.classroom.id).url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Classroom" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        title="Edit Classroom"
                        description="Update classroom information"
                    />
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child class="w-full md:w-auto">
                        <Link :href="ClassroomController.index().url"> Cancel </Link>
                    </Button>
                    <Button variant="secondary" as-child class="w-full md:w-auto">
                        <Link :href="ClassroomController.enrollment(classroom.id).url">
                            Manage Students
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <Form
                v-bind="ClassroomController.update.form(classroom.id)"
                class="contents"
                v-slot="{ errors, processing }"
            >
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="academic_year_id">Academic Year</Label>
                        <Select
                            name="academic_year_id"
                            :default-value="classroom.academic_year_id"
                            required
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select academic year" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="year in academicYears"
                                    :key="year.id"
                                    :value="year.id"
                                >
                                    {{ year.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.academic_year_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="homeroom_teacher_id">Homeroom Teacher (Wali Kelas)</Label>
                        <Select
                            name="homeroom_teacher_id"
                            :default-value="classroom.homeroom_teacher_id || undefined"
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select homeroom teacher (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="teacher in teachers"
                                    :key="teacher.id"
                                    :value="teacher.id"
                                >
                                    {{ teacher.name }} ({{ teacher.teacherNumber }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.homeroom_teacher_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="grade_level">Grade Level</Label>
                        <Select name="grade_level" :default-value="String(classroom.grade_level)" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Select grade level" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="i in 12" :key="i" :value="String(i)">
                                    Grade {{ i }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.grade_level" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Classroom Name</Label>
                        <Input
                            id="name"
                            type="text"
                            name="name"
                            :default-value="classroom.name"
                            required
                            placeholder="e.g., 10-IPA-1"
                            autofocus
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="capacity">Capacity</Label>
                        <Input
                            id="capacity"
                            type="number"
                            name="capacity"
                            :default-value="classroom.capacity"
                            min="1"
                            max="100"
                            placeholder="30"
                        />
                        <InputError :message="errors.capacity" />
                    </div>
                </CardContent>
                <CardFooter>
                    <Button type="submit" :disabled="processing"> Update Classroom </Button>
                </CardFooter>
            </Form>
        </Card>
    </AppLayout>
</template>
