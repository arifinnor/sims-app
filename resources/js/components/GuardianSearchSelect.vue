<script setup lang="ts">
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Spinner } from '@/components/ui/spinner';

interface Guardian {
    id: string;
    name: string;
    email: string | null;
    phone: string | null;
}

interface Props {
    modelValue?: string;
    excludedIds?: string[];
    placeholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    excludedIds: () => [],
    placeholder: 'Search guardian...',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    'select': [guardian: Guardian];
}>();

const open = ref(false);
const searchQuery = ref('');
const guardians = ref<Guardian[]>([]);
const loading = ref(false);
const selectedGuardian = ref<Guardian | null>(null);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const searchGuardians = async (query: string) => {
    if (query.length < 2) {
        guardians.value = [];
        return;
    }

    loading.value = true;
    try {
        const url = new URL('/guardians/search', window.location.origin);
        url.searchParams.set('search', query);
        url.searchParams.set('limit', '20');
        
        const response = await fetch(url.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        guardians.value = data.filter((g: Guardian) => !props.excludedIds.includes(g.id));
    } catch (error) {
        console.error('Error searching guardians:', error);
        guardians.value = [];
    } finally {
        loading.value = false;
    }
};

watch(searchQuery, (newQuery) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        searchGuardians(newQuery);
    }, 300);
});

watch(() => props.modelValue, (newValue) => {
    if (newValue && !selectedGuardian.value) {
        // If modelValue is set but we don't have the guardian data, we'll get it from the select event
        // This handles the case where the component receives an ID
    }
}, { immediate: true });

const selectGuardian = (guardian: Guardian) => {
    selectedGuardian.value = guardian;
    emit('update:modelValue', guardian.id);
    emit('select', guardian);
    open.value = false;
    searchQuery.value = '';
    guardians.value = [];
};

const displayValue = computed(() => {
    if (selectedGuardian.value) {
        return selectedGuardian.value.name;
    }
    return props.placeholder;
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                class="w-full justify-between"
            >
                {{ displayValue }}
                <ChevronsUpDownIcon class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[400px] p-0">
            <div class="flex items-center border-b px-3">
                <Input
                    v-model="searchQuery"
                    placeholder="Search by name, email, or phone..."
                    class="h-11 border-0 focus-visible:ring-0"
                />
            </div>
            <div class="max-h-[300px] overflow-y-auto">
                <div
                    v-if="loading"
                    class="flex items-center justify-center py-6"
                >
                    <Spinner />
                </div>
                <div
                    v-else-if="guardians.length === 0 && searchQuery.length >= 2"
                    class="py-6 text-center text-sm text-muted-foreground"
                >
                    No guardians found.
                </div>
                <div
                    v-else-if="searchQuery.length < 2"
                    class="py-6 text-center text-sm text-muted-foreground"
                >
                    Type at least 2 characters to search...
                </div>
                <div
                    v-else
                    class="p-1"
                >
                    <div
                        v-for="guardian in guardians"
                        :key="guardian.id"
                        :class="cn(
                            'relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground',
                            selectedGuardian?.id === guardian.id && 'bg-accent text-accent-foreground'
                        )"
                        @click="selectGuardian(guardian)"
                    >
                        <CheckIcon
                            :class="cn(
                                'mr-2 h-4 w-4',
                                selectedGuardian?.id === guardian.id ? 'opacity-100' : 'opacity-0'
                            )"
                        />
                        <div class="flex-1">
                            <div class="font-medium">{{ guardian.name }}</div>
                            <div
                                v-if="guardian.email || guardian.phone"
                                class="text-xs text-muted-foreground"
                            >
                                <span v-if="guardian.email">{{ guardian.email }}</span>
                                <span v-if="guardian.email && guardian.phone"> • </span>
                                <span v-if="guardian.phone">{{ guardian.phone }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

