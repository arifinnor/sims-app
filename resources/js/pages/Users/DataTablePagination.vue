<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<Props>();
</script>

<template>
    <nav
        v-if="props.links.length > 1"
        class="flex items-center justify-between gap-2"
        aria-label="Pagination"
    >
        <span class="text-sm text-muted-foreground">
            Showing
            {{ props.from ?? 0 }}-
            {{ props.to ?? 0 }} of
            {{ props.total }}
        </span>
        <div class="flex items-center gap-1">
            <Link
                v-for="link in props.links"
                :key="link.label"
                :href="link.url ?? ''"
                preserve-scroll
                :class="[
                    'rounded-md px-3 py-1 text-sm transition',
                    !link.url
                        ? 'pointer-events-none text-muted-foreground/60'
                        : 'hover:bg-muted/70',
                    link.active
                        ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                        : 'text-foreground',
                ]"
            >
                <span v-html="link.label" />
            </Link>
        </div>
    </nav>
</template>

