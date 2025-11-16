<script setup lang="ts">
import { CommandRoot, type CommandRootEmits, type CommandRootProps, useForwardPropsEmits } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<CommandRootProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<CommandRootEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <CommandRoot
    data-slot="command"
    v-bind="forwarded"
    :class="cn('flex h-full w-full flex-col overflow-hidden rounded-md bg-popover text-popover-foreground', props.class)"
  >
    <slot />
  </CommandRoot>
</template>

