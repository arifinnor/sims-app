<script setup lang="ts">
import { Link, Form, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import GuardianController from '@/actions/App/Http/Controllers/GuardianController';
import type { Guardian } from './columns';
import { computed } from 'vue';

interface Props {
    guardian: Guardian;
    onDeleteClick: (
        guardian: Guardian,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onRestoreClick?: (
        guardian: Guardian,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onForceDeleteClick?: (
        guardian: Guardian,
        submit: () => void,
        processing: () => boolean,
    ) => void;
}

const props = defineProps<Props>();

const isDeleted = computed(() => !!props.guardian.deletedAt);

const handleRestore = () => {
    router.post(`/guardians/${props.guardian.id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['guardians'] });
        },
    });
};

const handleForceDelete = () => {
    router.delete(`/guardians/${props.guardian.id}/force-delete`, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['guardians'] });
        },
    });
};
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <template v-if="!isDeleted">
            <Button as-child size="sm" variant="secondary">
                <Link :href="GuardianController.edit.url(guardian.id)">
                    Edit
                </Link>
            </Button>
            <Form
                :action="GuardianController.destroy.url(guardian.id)"
                method="delete"
                class="inline-flex"
                v-slot="{ processing, submit }"
            >
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    :disabled="processing"
                    @click="onDeleteClick(guardian, () => submit(), () => processing)"
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
                @click="onRestoreClick ? onRestoreClick(guardian, handleRestore, () => false) : handleRestore()"
            >
                Restore
            </Button>
            <Button
                type="button"
                variant="destructive"
                size="sm"
                :disabled="false"
                @click="onForceDeleteClick ? onForceDeleteClick(guardian, handleForceDelete, () => false) : handleForceDelete()"
            >
                Force Delete
            </Button>
        </template>
    </div>
</template>

