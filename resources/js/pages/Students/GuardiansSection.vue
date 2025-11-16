<script setup lang="ts">
import StudentController from '@/actions/App/Http/Controllers/StudentController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { router, Form } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AttachGuardianDialog from './AttachGuardianDialog.vue';

interface Guardian {
    id: string;
    name: string;
    email: string | null;
    phone: string | null;
    relationship: string | null;
    address: string | null;
    pivot?: {
        relationshipType: string | null;
        isPrimary: boolean;
    };
}

interface Student {
    id: string;
    studentNumber: string;
    name: string;
    email: string | null;
    phone: string | null;
    guardians?: Guardian[];
}

interface Props {
    student: Student;
    readonly?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    readonly: false,
});

const isAttachDialogOpen = ref(false);
const isDetachDialogOpen = ref(false);
const pendingDetachGuardian = ref<Guardian | null>(null);

const guardians = computed(() => props.student.guardians || []);

const openDetachDialog = (guardian: Guardian) => {
    pendingDetachGuardian.value = guardian;
    isDetachDialogOpen.value = true;
};

const confirmDetach = () => {
    if (!pendingDetachGuardian.value) {
        return;
    }

    router.delete(
        `/students/${props.student.id}/guardians/${pendingDetachGuardian.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['student'] });
            },
        },
    );

    isDetachDialogOpen.value = false;
};

const cancelDetach = () => {
    isDetachDialogOpen.value = false;
};

const handleAttachSuccess = () => {
    isAttachDialogOpen.value = false;
    router.reload({ only: ['student'] });
};

const handleUpdateRelationship = (guardianId: string, relationshipType: string | null, isPrimary: boolean) => {
    router.put(
        `/students/${props.student.id}/guardians/${guardianId}`,
        {
            relationship_type: relationshipType,
            is_primary: isPrimary,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['student'] });
            },
        },
    );
};

watch(isDetachDialogOpen, (open) => {
    if (!open) {
        pendingDetachGuardian.value = null;
    }
});
</script>

<template>
    <Card class="mt-6">
        <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
            <div class="w-full">
                <h3 class="text-lg font-semibold">Guardians</h3>
                <p class="text-sm text-muted-foreground">
                    {{ props.readonly ? 'Guardians for this student' : 'Manage guardians for this student' }}
                </p>
            </div>
            <Dialog
                v-if="!props.readonly"
                v-model:open="isAttachDialogOpen"
            >
                <DialogTrigger as-child>
                    <Button class="w-full md:w-auto">
                        Add Guardian
                    </Button>
                </DialogTrigger>
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Attach Guardian</DialogTitle>
                        <DialogDescription>
                            Add a guardian to this student
                        </DialogDescription>
                    </DialogHeader>
                    <AttachGuardianDialog
                        :student-id="props.student.id"
                        :existing-guardian-ids="guardians.map(g => g.id)"
                        @success="handleAttachSuccess"
                    />
                </DialogContent>
            </Dialog>
        </CardHeader>
        <CardContent>
            <div v-if="guardians.length === 0" class="text-center py-8 text-muted-foreground">
                <p>No guardians assigned to this student.</p>
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="guardian in guardians"
                    :key="guardian.id"
                    class="rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold">{{ guardian.name }}</h4>
                                <Badge
                                    v-if="guardian.pivot?.isPrimary"
                                    class="bg-primary text-primary-foreground"
                                >
                                    Primary
                                </Badge>
                            </div>
                            <div class="mt-2 space-y-1 text-sm text-muted-foreground">
                                <p v-if="guardian.email">Email: {{ guardian.email }}</p>
                                <p v-if="guardian.phone">Phone: {{ guardian.phone }}</p>
                                <p v-if="guardian.pivot?.relationshipType">
                                    Relationship: {{ guardian.pivot.relationshipType }}
                                </p>
                                <p v-else-if="guardian.relationship">
                                    Relationship: {{ guardian.relationship }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="!props.readonly"
                            class="flex gap-2"
                        >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="handleUpdateRelationship(
                                    guardian.id,
                                    guardian.pivot?.relationshipType || null,
                                    !guardian.pivot?.isPrimary
                                )"
                            >
                                {{ guardian.pivot?.isPrimary ? 'Unset Primary' : 'Set Primary' }}
                            </Button>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="openDetachDialog(guardian)"
                            >
                                Remove
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>

    <Dialog
        v-if="!props.readonly"
        v-model:open="isDetachDialogOpen"
    >
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>Remove Guardian</DialogTitle>
                <DialogDescription>
                    Are you sure you want to remove
                    <span class="font-medium text-foreground">
                        {{ pendingDetachGuardian?.name ?? 'this guardian' }}
                    </span>
                    from this student?
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:space-x-0">
                <Button
                    type="button"
                    variant="outline"
                    @click="cancelDetach"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    @click="confirmDetach"
                >
                    Remove
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

