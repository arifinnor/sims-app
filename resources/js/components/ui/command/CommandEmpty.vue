<script setup lang="ts">
import { CommandEmpty as CommandEmptyPrimitive, type CommandEmptyProps, useForwardProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<CommandEmptyProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const forwarded = useForwardProps(delegatedProps)
</script>

<template>
  <CommandEmptyPrimitive
    v-bind="forwarded"
    :class="cn('py-6 text-center text-sm', props.class)"
  >
    <slot />
  </CommandEmptyPrimitive>
</template>

