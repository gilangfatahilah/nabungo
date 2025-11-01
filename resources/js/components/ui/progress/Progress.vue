<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import {
  ProgressIndicator,
  ProgressRoot,
  type ProgressRootProps,
} from 'reka-ui'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip"
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<ProgressRootProps & { class?: HTMLAttributes['class'], bgColor?: string, tooltip?: string }>(),
  {
    modelValue: 0,
    bgColor: 'bg-primary',
    max: 100,
  },
)

const delegatedProps = reactiveOmit(props, 'class')
</script>

<template>
  <TooltipProvider v-if="!!tooltip">
    <Tooltip>
      <TooltipTrigger as-child>
        <ProgressRoot
          data-slot="progress"
          v-bind="delegatedProps"
          :max="max"
          :class="
            cn(
              'relative h-2 w-full overflow-hidden rounded-full',
              `${props.class} bg-secondary`
            )
          "
        >
          <ProgressIndicator
            data-slot="progress-indicator"
            :class="cn('h-full w-full flex-1 transition-all', bgColor)"
            :style="`transform: translateX(-${100 - (props.modelValue ?? 0)}%);`"
          />
        </ProgressRoot>
      </TooltipTrigger>
      <TooltipContent>
        <p>{{ tooltip }}</p>
      </TooltipContent>
    </Tooltip>
  </TooltipProvider>

  <ProgressRoot
    v-else
    data-slot="progress"
    v-bind="delegatedProps"
    :max="max"
    :class="
      cn(
        'relative h-2 w-full overflow-hidden rounded-full',
        `${props.class} bg-secondary`
      )
    "
  >
    <ProgressIndicator
      data-slot="progress-indicator"
      :class="cn('h-full w-full flex-1 transition-all', bgColor)"
      :style="`transform: translateX(-${100 - (props.modelValue ?? 0)}%);`"
    />
  </ProgressRoot>
</template>
