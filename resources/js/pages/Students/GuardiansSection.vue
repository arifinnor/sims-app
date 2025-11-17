<script setup lang="ts">
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { computed } from 'vue';

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

const guardians = computed(() => props.student.guardians || []);
</script>

<template>
    <Card class="mt-6">
        <CardHeader class="flex flex-col gap-4 pb-4 md:flex-row md:items-center md:justify-between">
            <div class="w-full">
                <h3 class="text-lg font-semibold">Guardians</h3>
                <p class="text-sm text-muted-foreground">Guardians for this student</p>
            </div>
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
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

