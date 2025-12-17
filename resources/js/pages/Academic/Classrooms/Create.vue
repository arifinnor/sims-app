<script setup lang="ts">
import ClassroomController from '@/actions/App/Http/Controllers/Academic/ClassroomController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

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

interface Props {
    academicYears: AcademicYear[];
    teachers: Teacher[];
}

const props = defineProps<Props>();

const selectedAcademicYearId = ref<string>('');
const selectedTeacherId = ref<string>('');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Classrooms',
        href: ClassroomController.index().url,
    },
    {
        title: 'Create Classroom',
        href: ClassroomController.create().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Classroom" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        title="Create Classroom"
                        description="Add a new classroom (Rombongan Belajar)"
                    />
                </div>
                <Button as-child variant="secondary" class="w-full md:w-auto">
                    <Link :href="ClassroomController.index().url"> Cancel </Link>
                </Button>
            </CardHeader>
            <Form
                v-bind="ClassroomController.store.form()"
                class="contents"
                :reset-on-success="['grade_level', 'name', 'capacity']"
                @success="selectedAcademicYearId = ''; selectedTeacherId = ''"
                v-slot="{ errors, processing }"
            >
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="academic_year_id">Academic Year</Label>
                        <Select
                            v-model="selectedAcademicYearId"
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
                        <input
                            type="hidden"
                            name="academic_year_id"
                            :value="selectedAcademicYearId"
                        />
                        <InputError :message="errors.academic_year_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="homeroom_teacher_id">Homeroom Teacher (Wali Kelas)</Label>
                        <Select
                            v-model="selectedTeacherId"
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Select teacher (optional)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">None</SelectItem>
                                <SelectItem
                                    v-for="teacher in teachers"
                                    :key="teacher.id"
                                    :value="teacher.id"
                                >
                                    {{ teacher.name }} ({{ teacher.teacherNumber }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <input
                            type="hidden"
                            name="homeroom_teacher_id"
                            :value="selectedTeacherId || ''"
                        />
                        <InputError :message="errors.homeroom_teacher_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="grade_level">Grade Level</Label>
                        <Select name="grade_level" required>
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
                            :default-value="30"
                            min="1"
                            max="100"
                            placeholder="30"
                        />
                        <InputError :message="errors.capacity" />
                    </div>
                </CardContent>
                <CardFooter>
                    <Button type="submit" :disabled="processing"> Create Classroom </Button>
                </CardFooter>
            </Form>
        </Card>
    </AppLayout>
</template>
