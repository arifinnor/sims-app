<script setup lang="ts">
import { CommandItem as CommandItemPrimitive, type CommandItemProps, useForwardPropsEmits } from 'reka-ui'
import { cn } from '@/lib/utils'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<CommandItemProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<{ select: [event: CustomEvent<{ value: string }>] }>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <CommandItemPrimitive
    v-bind="forwarded"
    :class="cn(
      'relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none aria-selected:bg-accent aria-selected:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
      props.class
    )"
  >
    <slot />
  </CommandItemPrimitive>
</template>

