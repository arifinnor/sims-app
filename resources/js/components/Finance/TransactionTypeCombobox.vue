<script setup lang="ts">
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Check, ChevronsUpDown } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

interface TransactionAccount {
    id: string;
    transaction_type_id: string;
    role: string;
    label: string;
    direction: string;
    account_type: string;
    chart_of_account_id: string | null;
    chart_of_account?: {
        id: string;
        code: string;
        name: string;
        account_type: string;
    } | null;
}

interface TransactionType {
    id: string;
    code: string;
    name: string;
    category: string;
    accounts: TransactionAccount[];
}

interface Props {
    modelValue?: string | null;
    transactionTypes: TransactionType[];
    placeholder?: string;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    transactionTypes: () => [],
    placeholder: 'Select transaction type...',
});

const emit = defineEmits<{
    'update:modelValue': [value: string | null];
}>();

const open = ref(false);

const selectedType = computed(() => {
    if (!props.modelValue) {
        return null;
    }

    return props.transactionTypes.find((type) => type.id === props.modelValue) || null;
});

const displayValue = computed(() => {
    if (selectedType.value) {
        return selectedType.value.name;
    }

    return props.placeholder;
});

const selectType = (typeId: string | null) => {
    emit('update:modelValue', typeId);
    open.value = false;
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                :class="cn(
                    'w-full justify-between',
                    !selectedType && 'text-muted-foreground',
                    props.class,
                )"
            >
                {{ displayValue }}
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-full p-0" :style="{ width: 'var(--radix-popover-trigger-width)' }">
            <Command>
                <CommandInput placeholder="Search by name..." />
                <CommandList>
                    <CommandEmpty>No transaction type found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="type in props.transactionTypes"
                            :key="type.id"
                            :value="`${type.name} ${type.category}`"
                            @select="() => selectType(type.id)"
                        >
                            <Check
                                :class="cn(
                                    'mr-2 h-4 w-4',
                                    selectedType?.id === type.id ? 'opacity-100' : 'opacity-0',
                                )"
                            />
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium">{{ type.name }}</span>
                                    <Badge
                                        class="text-[10px] h-5 px-1 bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900"
                                    >
                                        {{ type.category }}
                                    </Badge>
                                </div>
                            </div>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>

