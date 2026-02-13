<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search } from 'lucide-vue-next'
import { mainNavItems } from '@/constants'
import { Button } from '@/components/ui/button'
import {
  CommandDialog,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command'

const open = ref(false)

const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
    e.preventDefault()
    open.value = !open.value
  }
}

const handleSelect = (url: string) => {
  open.value = false
  router.visit(url)
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div>
    <Button
      variant="outline"
      class="relative h-9 w-full justify-start rounded-md text-sm text-muted-foreground sm:pr-12 md:w-40 lg:w-64"
      @click="open = true"
    >
      <Search class="mr-2 h-4 w-4" />
      <span class="hidden lg:inline-flex">Search...</span>
      <span class="inline-flex lg:hidden">Search...</span>
      <kbd
        class="pointer-events-none absolute right-1.5 top-1.5 hidden h-6 select-none items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium opacity-100 sm:flex"
      >
        <span class="text-xs">⌘</span>K
      </kbd>
    </Button>

    <CommandDialog v-model:open="open">
      <CommandInput placeholder="Type a command or search..." />
      <CommandList>
        <CommandEmpty>No results found.</CommandEmpty>
        <CommandGroup heading="Navigation">
          <CommandItem
            v-for="item in mainNavItems"
            :key="item.url"
            :value="item.title"
            @select="handleSelect(item.url)"
          >
            <component :is="item.icon" class="mr-2 h-4 w-4" />
            <span>{{ item.title }}</span>
          </CommandItem>
        </CommandGroup>
      </CommandList>
    </CommandDialog>
  </div>
</template>
