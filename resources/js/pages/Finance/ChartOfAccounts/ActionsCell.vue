<script setup lang="ts">
import { Link, Form, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
import type { Account } from './columns';
import { computed } from 'vue';

interface Props {
    account: Account;
    onDeleteClick: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onRestoreClick?: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void;
    onForceDeleteClick?: (
        account: Account,
        submit: () => void,
        processing: () => boolean,
    ) => void;
}

const props = defineProps<Props>();

const isDeleted = computed(() => !!props.account.deletedAt);

const handleRestore = () => {
    router.post(`/finance/accounts/${props.account.id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['accounts'] });
        },
    });
};

const handleForceDelete = () => {
    router.delete(`/finance/accounts/${props.account.id}/force-delete`, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['accounts'] });
        },
    });
};
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <template v-if="!isDeleted">
            <Button as-child size="sm" variant="secondary">
                <Link :href="AccountController.edit.url(account.id)">
                    Edit
                </Link>
            </Button>
            <Form
                :action="AccountController.destroy.url(account.id)"
                method="delete"
                class="inline-flex"
                v-slot="{ processing, submit }"
            >
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    :disabled="processing"
                    @click="onDeleteClick(account, () => submit(), () => processing)"
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
                @click="onRestoreClick ? onRestoreClick(account, handleRestore, () => false) : handleRestore()"
            >
                Restore
            </Button>
            <Button
                type="button"
                variant="destructive"
                size="sm"
                :disabled="false"
                @click="onForceDeleteClick ? onForceDeleteClick(account, handleForceDelete, () => false) : handleForceDelete()"
            >
                Force Delete
            </Button>
        </template>
    </div>
</template>

