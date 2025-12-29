<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { router, Form } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuardianController from '@/actions/App/Http/Controllers/GuardianController';

interface Props {
    studentId: string;
    existingGuardianIds?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    existingGuardianIds: () => [],
});

const emit = defineEmits<{
    success: [];
}>();

const guardianId = ref<string>('');
const relationshipType = ref<string>('');
const isPrimary = ref<boolean>(false);

const handleSubmit = () => {
    router.post(
        `/students/${props.studentId}/guardians`,
        {
            guardian_id: guardianId.value,
            relationship_type: relationshipType.value || null,
            is_primary: isPrimary.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('success');
            },
        },
    );
};
</script>

<template>
    <Form
        @submit.prevent="handleSubmit"
        class="space-y-4"
    >
        <div class="grid gap-2">
            <Label for="guardian_id">Guardian ID</Label>
            <Input
                id="guardian_id"
                v-model="guardianId"
                type="text"
                placeholder="Enter guardian ID (UUID)"
                required
            />
            <p class="text-sm text-muted-foreground">
                You can find guardian IDs in the
                <a
                    :href="GuardianController.index().url"
                    target="_blank"
                    class="text-primary underline"
                >
                    Guardians
                </a>
                section.
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="relationship_type">Relationship Type</Label>
            <Input
                id="relationship_type"
                v-model="relationshipType"
                type="text"
                placeholder="e.g., Mother, Father, Legal Guardian"
            />
        </div>

        <div class="flex items-center gap-2">
            <Checkbox
                id="is_primary"
                v-model:checked="isPrimary"
            />
            <Label for="is_primary" class="cursor-pointer">
                Set as primary guardian
            </Label>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <Button type="submit">
                Attach Guardian
            </Button>
        </div>
    </Form>
</template>


