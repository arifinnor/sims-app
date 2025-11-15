<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

interface Props {
    nextCursor: string | null;
    prevCursor: string | null;
    hasNextPage?: boolean;
    hasPrevPage?: boolean;
    path: string;
    perPage: number;
    dataCount: number;
}

const props = withDefaults(defineProps<Props>(), {
    hasNextPage: false,
    hasPrevPage: false,
});

const perPageOptions = [15, 50, 100, 200];
const selectedPerPage = ref(props.perPage);

watch(() => props.perPage, (newValue) => {
    selectedPerPage.value = newValue;
});

const updatePerPage = (value: string) => {
    selectedPerPage.value = Number(value);
    const url = new URL(props.path, window.location.origin);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('cursor');

    const currentSearch = new URLSearchParams(window.location.search).get('search');
    if (currentSearch) {
        url.searchParams.set('search', currentSearch);
    }

    router.visit(url.pathname + url.search, {
        only: ['users'],
        preserveState: false,
        preserveScroll: false,
        replace: true,
    });
};

const getNextUrl = (): string | null => {
    if (!props.nextCursor) {
        return null;
    }

    const url = new URL(props.path, window.location.origin);
    url.searchParams.set('cursor', props.nextCursor);

    const currentSearch = new URLSearchParams(window.location.search).get('search');
    if (currentSearch) {
        url.searchParams.set('search', currentSearch);
    }

    const currentPerPage = new URLSearchParams(window.location.search).get('per_page');
    if (currentPerPage) {
        url.searchParams.set('per_page', currentPerPage);
    }

    return url.pathname + url.search;
};

const getPrevUrl = (): string | null => {
    if (!props.prevCursor) {
        return null;
    }

    const url = new URL(props.path, window.location.origin);
    url.searchParams.set('cursor', props.prevCursor);

    const currentSearch = new URLSearchParams(window.location.search).get('search');
    if (currentSearch) {
        url.searchParams.set('search', currentSearch);
    }

    const currentPerPage = new URLSearchParams(window.location.search).get('per_page');
    if (currentPerPage) {
        url.searchParams.set('per_page', currentPerPage);
    }

    return url.pathname + url.search;
};

const nextUrl = computed(() => getNextUrl());
const prevUrl = computed(() => getPrevUrl());
</script>

<template>
    <nav
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        aria-label="Pagination"
    >
        <div class="flex items-center gap-2">
            <Label for="per-page" class="text-sm text-muted-foreground whitespace-nowrap">
                Per page:
            </Label>
            <select
                id="per-page"
                :value="selectedPerPage"
                @change="updatePerPage(($event.target as HTMLSelectElement).value)"
                class="h-9 w-20 rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30 dark:border-input dark:text-foreground hover:bg-accent/50 dark:hover:bg-input/50 cursor-pointer"
            >
                <option
                    v-for="option in perPageOptions"
                    :key="option"
                    :value="option"
                    class="bg-background text-foreground dark:bg-input dark:text-foreground"
                >
                    {{ option }}
                </option>
            </select>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-muted-foreground">
                Showing {{ props.dataCount }} {{ props.dataCount === 1 ? 'result' : 'results' }}
            </span>
            <div class="flex items-center gap-2">
                <Button
                    v-if="prevUrl"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <Link :href="prevUrl" preserve-scroll>
                        Previous
                    </Link>
                </Button>
                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    disabled
                >
                    Previous
                </Button>
                <Button
                    v-if="nextUrl"
                    as-child
                    variant="outline"
                    size="sm"
                >
                    <Link :href="nextUrl" preserve-scroll>
                        Next
                    </Link>
                </Button>
                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    disabled
                >
                    Next
                </Button>
            </div>
        </div>
    </nav>
</template>

