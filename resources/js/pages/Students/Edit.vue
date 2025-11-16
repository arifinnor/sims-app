<script setup lang="ts">
import StudentController from '@/actions/App/Http/Controllers/StudentController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import GuardianController from '@/actions/App/Http/Controllers/GuardianController';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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

interface StudentFormData {
    id: string;
    studentNumber: string;
    name: string;
    email: string | null;
    phone: string | null;
    guardians?: Guardian[];
}

interface Props {
    student: StudentFormData;
}

const props = defineProps<Props>();

const guardians = ref<Guardian[]>(props.student.guardians || []);

watch(() => props.student.guardians, (newGuardians) => {
    guardians.value = newGuardians || [];
}, { immediate: true });

const handleGuardianCreated = () => {
    router.reload({ only: ['student'] });
};

const handleGuardianUpdated = () => {
    router.reload({ only: ['student'] });
};

const handleGuardianRelationshipUpdated = () => {
    router.reload({ only: ['student'] });
};

const handleGuardianDetached = (guardianId: string) => {
    router.delete(
        `/students/${props.student.id}/guardians/${guardianId}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['student'] });
            },
        },
    );
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Students',
        href: StudentController.index().url,
    },
    {
        title: props.student.name,
        href: StudentController.show.url(props.student.id),
    },
    {
        title: 'Edit',
        href: StudentController.edit.url(props.student.id),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit ${props.student.name}`" />

        <Card>
            <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
                <div class="w-full">
                    <Heading
                        :title="`Edit ${props.student.name}`"
                        description="Update the student details"
                    />
                </div>
                <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <Button
                        as-child
                        variant="secondary"
                        class="w-full md:w-auto"
                    >
                        <Link :href="StudentController.show.url(props.student.id)">
                            View student
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="ghost"
                        class="w-full md:w-auto"
                    >
                        <Link :href="StudentController.index().url">
                            Back to students
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <Form
                :action="StudentController.update.url(props.student.id)"
                method="put"
                class="contents"
                :options="{ preserveScroll: true }"
                v-slot="{ errors, processing, recentlySuccessful }"
            >
                <CardContent class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="student_number">Student Number</Label>
                        <Input
                            id="student_number"
                            name="student_number"
                            type="text"
                            required
                            :default-value="props.student.studentNumber"
                        />
                        <InputError :message="errors.student_number" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            autocomplete="name"
                            :default-value="props.student.name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            :default-value="props.student.email || ''"
                            placeholder="email@example.com"
                        />
                        <InputError :message="errors.email" />
                        <p class="text-sm text-muted-foreground">
                            Optional - students under 17 may not have an email address
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            type="tel"
                            autocomplete="tel"
                            :default-value="props.student.phone || ''"
                            placeholder="+1234567890"
                        />
                        <InputError :message="errors.phone" />
                        <p class="text-sm text-muted-foreground">
                            Optional - students under 17 may not have a phone number
                        </p>
                    </div>
                </CardContent>

                <CardFooter class="flex flex-col gap-3 md:flex-row md:items-center md:justify-end">
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-show="recentlySuccessful"
                            class="text-sm text-neutral-600 dark:text-neutral-300"
                        >
                            Changes saved.
                        </p>
                    </Transition>

                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="update-student-button"
                    >
                        Save changes
                    </Button>
                </CardFooter>
            </Form>
        </Card>

        <Card class="mt-6">
            <CardHeader>
                <Heading
                    title="Guardians"
                    description="Manage guardians for this student"
                />
            </CardHeader>

            <CardContent class="space-y-6">
                <div>
                    <h4 class="mb-4 text-base font-semibold">Add New Guardian</h4>
                    <Form
                        :action="StudentController.storeAndAttachGuardian.url(props.student.id)"
                        method="post"
                        class="space-y-4"
                        :options="{ preserveScroll: true }"
                        v-slot="{ errors, processing, recentlySuccessful }"
                        @success="handleGuardianCreated"
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="new_guardian_name">Name</Label>
                                <Input
                                    id="new_guardian_name"
                                    name="name"
                                    type="text"
                                    required
                                    autocomplete="name"
                                    placeholder="Full name"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="new_guardian_email">Email address</Label>
                                <Input
                                    id="new_guardian_email"
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="email@example.com"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="new_guardian_phone">Phone</Label>
                                <Input
                                    id="new_guardian_phone"
                                    name="phone"
                                    type="tel"
                                    autocomplete="tel"
                                    placeholder="+1234567890"
                                />
                                <InputError :message="errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="new_guardian_relationship">Relationship</Label>
                                <Input
                                    id="new_guardian_relationship"
                                    name="relationship"
                                    type="text"
                                    placeholder="e.g., Mother, Father, Legal Guardian"
                                />
                                <InputError :message="errors.relationship" />
                            </div>

                            <div class="grid gap-2 md:col-span-2">
                                <Label for="new_guardian_address">Address</Label>
                                <Textarea
                                    id="new_guardian_address"
                                    name="address"
                                    placeholder="Full address"
                                    :rows="3"
                                />
                                <InputError :message="errors.address" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="new_guardian_relationship_type">Relationship Type (to Student)</Label>
                                <Input
                                    id="new_guardian_relationship_type"
                                    name="relationship_type"
                                    type="text"
                                    placeholder="e.g., Mother, Father, Legal Guardian"
                                />
                                <InputError :message="errors.relationship_type" />
                            </div>

                            <div class="flex items-center gap-2 pt-6">
                                <Checkbox
                                    id="new_guardian_is_primary"
                                    name="is_primary"
                                    value="1"
                                />
                                <Label
                                    for="new_guardian_is_primary"
                                    class="cursor-pointer"
                                >
                                    Set as primary guardian
                                </Label>
                                <InputError :message="errors.is_primary" />
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-4">
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-sm text-neutral-600 dark:text-neutral-300"
                                >
                                    Guardian created and attached.
                                </p>
                            </Transition>
                            <Button
                                type="submit"
                                :disabled="processing"
                            >
                                Create and Attach Guardian
                            </Button>
                        </div>
                    </Form>
                </div>

                <div
                    v-if="guardians.length > 0"
                    class="space-y-6 border-t pt-6"
                >
                    <h4 class="text-base font-semibold">Existing Guardians</h4>
                    <div
                        v-for="guardian in guardians"
                        :key="guardian.id"
                        class="space-y-4 rounded-lg border border-sidebar-border/60 p-4 dark:border-sidebar-border"
                    >
                        <div class="flex items-center justify-between">
                            <h5 class="font-semibold">{{ guardian.name }}</h5>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="handleGuardianDetached(guardian.id)"
                            >
                                Detach
                            </Button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <h6 class="mb-3 text-sm font-medium text-muted-foreground">
                                    Guardian Details
                                </h6>
                                <Form
                                    :action="GuardianController.update.url(guardian.id)"
                                    method="put"
                                    class="space-y-3"
                                    :options="{ preserveScroll: true }"
                                    v-slot="{ errors: guardianErrors, processing: guardianProcessing, recentlySuccessful: guardianSuccess }"
                                    @success="handleGuardianUpdated"
                                >
                                    <div class="grid gap-2">
                                        <Label for="guardian_name_{{ guardian.id }}">Name</Label>
                                        <Input
                                            :id="`guardian_name_${guardian.id}`"
                                            name="name"
                                            type="text"
                                            required
                                            :default-value="guardian.name"
                                        />
                                        <InputError :message="guardianErrors.name" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label :for="`guardian_email_${guardian.id}`">Email address</Label>
                                        <Input
                                            :id="`guardian_email_${guardian.id}`"
                                            name="email"
                                            type="email"
                                            :default-value="guardian.email || ''"
                                            placeholder="email@example.com"
                                        />
                                        <InputError :message="guardianErrors.email" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label :for="`guardian_phone_${guardian.id}`">Phone</Label>
                                        <Input
                                            :id="`guardian_phone_${guardian.id}`"
                                            name="phone"
                                            type="tel"
                                            :default-value="guardian.phone || ''"
                                            placeholder="+1234567890"
                                        />
                                        <InputError :message="guardianErrors.phone" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label :for="`guardian_relationship_${guardian.id}`">Relationship</Label>
                                        <Input
                                            :id="`guardian_relationship_${guardian.id}`"
                                            name="relationship"
                                            type="text"
                                            :default-value="guardian.relationship || ''"
                                            placeholder="e.g., Mother, Father, Legal Guardian"
                                        />
                                        <InputError :message="guardianErrors.relationship" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label :for="`guardian_address_${guardian.id}`">Address</Label>
                                        <Textarea
                                            :id="`guardian_address_${guardian.id}`"
                                            name="address"
                                            :default-value="guardian.address || ''"
                                            placeholder="Full address"
                                            :rows="3"
                                        />
                                        <InputError :message="guardianErrors.address" />
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <Transition
                                            enter-active-class="transition ease-in-out"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition ease-in-out"
                                            leave-to-class="opacity-0"
                                        >
                                            <p
                                                v-show="guardianSuccess"
                                                class="text-sm text-neutral-600 dark:text-neutral-300"
                                            >
                                                Guardian updated.
                                            </p>
                                        </Transition>
                                        <Button
                                            type="submit"
                                            size="sm"
                                            :disabled="guardianProcessing"
                                        >
                                            Update Guardian
                                        </Button>
                                    </div>
                                </Form>
                            </div>

                            <div>
                                <h6 class="mb-3 text-sm font-medium text-muted-foreground">
                                    Student Relationship
                                </h6>
                                <Form
                                    :action="StudentController.updateGuardianRelationship.url({ student: props.student.id, guardian: guardian.id })"
                                    method="put"
                                    class="space-y-3"
                                    :options="{ preserveScroll: true }"
                                    v-slot="{ errors: relationshipErrors, processing: relationshipProcessing, recentlySuccessful: relationshipSuccess }"
                                    @success="handleGuardianRelationshipUpdated"
                                >
                                    <div class="grid gap-2">
                                        <Label :for="`relationship_type_${guardian.id}`">Relationship Type</Label>
                                        <Input
                                            :id="`relationship_type_${guardian.id}`"
                                            name="relationship_type"
                                            type="text"
                                            :default-value="guardian.pivot?.relationshipType || ''"
                                            placeholder="e.g., Mother, Father, Legal Guardian"
                                        />
                                        <InputError :message="relationshipErrors.relationship_type" />
                                    </div>

                                    <div class="flex items-center gap-2 pt-2">
                                        <Checkbox
                                            :id="`is_primary_${guardian.id}`"
                                            name="is_primary"
                                            :checked="guardian.pivot?.isPrimary || false"
                                            value="1"
                                        />
                                        <Label
                                            :for="`is_primary_${guardian.id}`"
                                            class="cursor-pointer"
                                        >
                                            Primary guardian
                                        </Label>
                                        <InputError :message="relationshipErrors.is_primary" />
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <Transition
                                            enter-active-class="transition ease-in-out"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition ease-in-out"
                                            leave-to-class="opacity-0"
                                        >
                                            <p
                                                v-show="relationshipSuccess"
                                                class="text-sm text-neutral-600 dark:text-neutral-300"
                                            >
                                                Relationship updated.
                                            </p>
                                        </Transition>
                                        <Button
                                            type="submit"
                                            size="sm"
                                            :disabled="relationshipProcessing"
                                        >
                                            Update Relationship
                                        </Button>
                                    </div>
                                </Form>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="border-t pt-6 text-center text-muted-foreground"
                >
                    <p>No guardians assigned to this student.</p>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>

