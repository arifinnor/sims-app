<script setup lang="ts">
import { Link, Form, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import StudentController from '@/actions/App/Http/Controllers/StudentController';
import type { Student } from './columns';
import { computed } from 'vue';

interface Props {
    student: Student;
    onDeleteClick: (
        student: Student,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onRestoreClick?: (
        student: Student,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onForceDeleteClick?: (
        student: Student,
        submit: () => void,
        processing: () => boolean,
    ) => void;
}

const props = defineProps<Props>();

const isDeleted = computed(() => !!props.student.deletedAt);

const handleRestore = () => {
    router.post(`/students/${props.student.id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['students'] });
        },
    });
};

const handleForceDelete = () => {
    router.delete(`/students/${props.student.id}/force-delete`, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['students'] });
        },
    });
};
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <template v-if="!isDeleted">
            <Button as-child size="sm" variant="secondary">
                <Link :href="StudentController.edit.url(student.id)">
                    Edit
                </Link>
            </Button>
            <Form
                :action="StudentController.destroy.url(student.id)"
                method="delete"
                class="inline-flex"
                v-slot="{ processing, submit }"
            >
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    :disabled="processing"
                    @click="onDeleteClick(student, () => submit(), () => processing)"
                >
                    Delete
                </Button>
            </Form>
        </template>
        <template v-else>
            <Button
                type="button"
                size="sm"
                variant="secondary"
                :disabled="false"
                @click="onRestoreClick ? onRestoreClick(student, handleRestore, () => false) : handleRestore()"
            >
                Restore
            </Button>
            <Button
                type="button"
                variant="destructive"
                size="sm"
                :disabled="false"
                @click="onForceDeleteClick ? onForceDeleteClick(student, handleForceDelete, () => false) : handleForceDelete()"
            >
                Force Delete
            </Button>
        </template>
    </div>
</template>

