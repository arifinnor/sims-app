<script setup lang="ts">
import type { CalendarRootEmits, CalendarRootProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import {
    CalendarRoot,
    CalendarHeader,
    CalendarHeading,
    CalendarPrev,
    CalendarNext,
    CalendarGrid,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarGridBody,
    CalendarCell,
    CalendarCellTrigger,
    useForwardPropsEmits,
} from 'reka-ui'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const props = withDefaults(
    defineProps<CalendarRootProps & { class?: HTMLAttributes['class'] }>(),
    {
        layout: 'month-and-year',
    },
)

const emits = defineEmits<CalendarRootEmits>()

const delegatedProps = reactiveOmit(props, 'class')

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <CalendarRoot
        data-slot="calendar"
        v-slot="{ grid, weekDays, date }"
        v-bind="forwarded"
        :class="cn('p-3', props.class)"
    >
        <slot
            :grid="grid"
            :week-days="weekDays"
            :date="date"
        >
            <CalendarHeader class="flex items-center justify-between pb-4">
                <CalendarHeading class="text-sm font-medium" />
                <div class="flex items-center gap-1">
                    <CalendarPrev
                        as-child
                        class="h-7 w-7"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>
                    </CalendarPrev>
                    <CalendarNext
                        as-child
                        class="h-7 w-7"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </CalendarNext>
                </div>
            </CalendarHeader>
            <CalendarGrid class="w-full border-collapse space-y-1">
                <CalendarGridHead>
                    <CalendarGridRow>
                        <CalendarHeadCell
                            v-for="day in weekDays"
                            :key="day"
                            class="w-9 rounded-md text-[0.8rem] font-normal text-muted-foreground"
                        >
                            {{ day }}
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>
                <CalendarGridBody>
                    <template
                        v-for="(monthGrid, monthIndex) in grid"
                        :key="monthIndex"
                    >
                        <CalendarGridRow
                            v-for="(week, weekIndex) in monthGrid.rows"
                            :key="`${monthIndex}-${weekIndex}`"
                            class="mt-2 w-full"
                        >
                            <CalendarCell
                                v-for="day in week"
                                :key="day.toString()"
                                :date="day"
                                class="relative p-0 text-center text-sm focus-within:relative focus-within:z-20 [&:has([aria-selected])]:bg-accent first:[&:has([aria-selected])]:rounded-l-md last:[&:has([aria-selected])]:rounded-r-md"
                            >
                                <CalendarCellTrigger
                                    :day="day"
                                    :month="date"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-md p-0 text-sm font-normal ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none aria-selected:opacity-100 data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[selected]:font-medium data-[today]:bg-accent data-[today]:text-accent-foreground data-[outside-view]:text-muted-foreground data-[outside-view]:opacity-50 data-[disabled]:text-muted-foreground data-[disabled]:opacity-50 data-[unavailable]:text-muted-foreground data-[unavailable]:opacity-50 data-[outside-visible-view]:hidden"
                                />
                            </CalendarCell>
                        </CalendarGridRow>
                    </template>
                </CalendarGridBody>
            </CalendarGrid>
        </slot>
    </CalendarRoot>
</template>

