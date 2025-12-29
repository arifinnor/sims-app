<script setup lang="ts">
import ClassroomController from '@/actions/App/Http/Controllers/Academic/ClassroomController';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Student {
    id: string;
    studentNumber: string;
    name: string;
    email: string | null;
}

interface Classroom {
    id: string;
    name: string;
    grade_level: number;
    capacity: number;
    students: Array<{
        id: string;
        studentNumber: string;
        name: string;
        email: string | null;
        pivot: {
            status: string;
        };
    }>;
}

interface Props {
    classroom: Classroom;
    unassignedStudents: Student[];
}

const props = defineProps<Props>();

const isAddStudentsDialogOpen = ref(false);
const selectedStudentIds = ref<string[]>([]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Classrooms',
        href: ClassroomController.index().url,
    },
    {
        title: props.classroom.name,
        href: ClassroomController.enrollment(props.classroom.id).url,
    },
];

const toggleStudent = (studentId: string) => {
    const index = selectedStudentIds.value.indexOf(studentId);
    if (index > -1) {
        selectedStudentIds.value.splice(index, 1);
    } else {
        selectedStudentIds.value.push(studentId);
    }
};

const openAddStudentsDialog = () => {
    selectedStudentIds.value = [];
    isAddStudentsDialogOpen.value = true;
};

const enrollStudents = () => {
    router.post(
        ClassroomController.enrollStudents(props.classroom.id).url,
        {
            student_ids: selectedStudentIds.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isAddStudentsDialogOpen.value = false;
                selectedStudentIds.value = [];
            },
        },
    );
};

const removeStudent = (studentId: string) => {
    router.delete(
        ClassroomController.removeStudent(props.classroom.id, studentId).url,
        {
            preserveScroll: true,
        },
    );
};

const activeStudents = computed(() => {
    return props.classroom.students.filter(
        (s) => s.pivot.status === 'ACTIVE',
    );
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Manage Students - ${classroom.name}`" />

        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <Heading
                    :title="`Manage Students - ${classroom.name}`"
                    description="Add or remove students from this classroom"
                />
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="ClassroomController.edit(classroom.id).url"> Edit Classroom </Link>
                    </Button>
                    <Button @click="openAddStudentsDialog"> Add Students </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Enrolled Students</h3>
                        <span class="text-sm text-muted-foreground">
                            {{ activeStudents.length }} / {{ classroom.capacity }}
                        </span>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="activeStudents.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                        No students enrolled in this classroom.
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="student in activeStudents"
                            :key="student.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div>
                                <div class="font-medium">{{ student.name }}</div>
                                <div class="text-sm text-muted-foreground">
                                    {{ student.studentNumber }}
                                    <span v-if="student.email"> ? {{ student.email }}</span>
                                </div>
                            </div>
                            <Form
                                v-bind="ClassroomController.removeStudent.form(classroom.id, student.id)"
                                @submit="removeStudent(student.id)"
                            >
                                <Button type="submit" variant="destructive" size="sm">
                                    Remove
                                </Button>
                            </Form>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="isAddStudentsDialogOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Add Students</DialogTitle>
                    <DialogDescription>
                        Select students to enroll in {{ classroom.name }}. Only students not enrolled
                        in another active classroom in the same academic year are shown.
                    </DialogDescription>
                </DialogHeader>
                <div class="max-h-[400px] overflow-y-auto">
                    <div v-if="unassignedStudents.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                        No unassigned students available.
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="student in unassignedStudents"
                            :key="student.id"
                            class="flex items-center space-x-2 rounded-lg border p-3"
                        >
                            <Checkbox
                                :id="`student-${student.id}`"
                                :checked="selectedStudentIds.includes(student.id)"
                                @update:checked="toggleStudent(student.id)"
                            />
                            <label
                                :for="`student-${student.id}`"
                                class="flex-1 cursor-pointer text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                <div>{{ student.name }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ student.studentNumber }}
                                    <span v-if="student.email"> ? {{ student.email }}</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isAddStudentsDialogOpen = false">
                        Cancel
                    </Button>
                    <Button @click="enrollStudents" :disabled="selectedStudentIds.length === 0">
                        Enroll {{ selectedStudentIds.length }} Student(s)
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
