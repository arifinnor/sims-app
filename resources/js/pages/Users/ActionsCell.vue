<script setup lang="ts">
import { Link, Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import UserController from '@/actions/App/Http/Controllers/UserController';
import type { User } from './columns';

interface Props {
    user: User;
    onDeleteClick: (
        user: User,
        submit: () => void,
        processing: () => boolean,
    ) => void;
}

defineProps<Props>();
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <Button as-child size="sm" variant="secondary">
            <Link :href="UserController.edit.url(user.id)">
                Edit
            </Link>
        </Button>
        <Form
            v-bind="UserController.destroy.form(user.id)"
            class="inline-flex"
            v-slot="{ processing, submit }"
        >
            <Button
                type="button"
                variant="destructive"
                size="sm"
                :disabled="processing"
                @click="onDeleteClick(user, () => submit(), () => processing)"
            >
                Delete
            </Button>
        </Form>
    </div>
</template>

