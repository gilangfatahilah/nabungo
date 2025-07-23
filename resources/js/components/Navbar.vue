<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";
import { Link } from "@inertiajs/vue3";

const isOpen = ref(false);
const isScrolled = ref(false);

const handleScroll = () => {
  isScrolled.value = window.scrollY > 80; // 80px = top-20
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});

const navLinks = [
  { label: "Home", href: "#hero" },
  { label: "Features", href: "#features" },
  { label: "Integrations", href: "#integrations" },
  { label: "FAQs", href: "#faqs" },
];
</script>

<template>
  <header
    :class="[
      'fixed w-full top-0 z-50 mx-auto',
      isScrolled ? 'bg-[#08070ecc] backdrop-blur-md border-b-[1px]' : 'bg-transparent',
    ]"
  >
    <div class="container w-full mx-auto">
      <div class="py-6">
        <div class="grid grid-cols-2 items-center p-2 px-4 md:pr-2">
          <!-- Logo -->
          <div class="flex items-center gap-8">
            <img src="/images/logo.svg" alt="Logo Image" class="h-4 md:h-6 w-auto ml-2" />

            <nav class="flex gap-6 font-dm-sans text-[#D5D5D5] text-lg">
              <a
                v-for="link in navLinks"
                :key="link.label"
                :href="link.href"
                class="relative group"
              >
                <span>{{ link.label }}</span>
                <!-- <span
                  class="absolute bottom-0 left-0 h-[2px] w-0 bg-lime-400 transition-all duration-300 group-hover:w-full rounded-full"
                /> -->
              </a>
            </nav>
          </div>

          <!-- Menu & Buttons -->
          <div class="flex justify-end gap-4 items-center">
            <!-- Mobile Menu Button -->
            <button @click="isOpen = !isOpen" class="md:hidden p-1">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <line
                  x1="3"
                  y1="18"
                  x2="21"
                  y2="18"
                  :class="
                    isOpen
                      ? 'transform -rotate-45 translate-y-1 transition'
                      : 'transition'
                  "
                ></line>
                <line
                  x1="3"
                  y1="12"
                  x2="21"
                  y2="12"
                  :class="isOpen ? 'opacity-0 transition-opacity' : 'transition-opacity'"
                ></line>
                <line
                  x1="3"
                  y1="6"
                  x2="21"
                  y2="6"
                  :class="
                    isOpen
                      ? 'transform rotate-45 -translate-y-1 transition'
                      : 'transition'
                  "
                ></line>
              </svg>
            </button>

            <!-- Desktop Buttons -->
            <Link :href="route('login')">
              <button
                class="hidden md:inline-flex items-center bg-[#00FFB2] rounded-full px-6 py-2 transition text-black font-dm-sans font-medium border-[2px] border-[rgba(255, 255, 255, 0.12)] cursor-pointer"
              >
                Log In
              </button>
            </Link>
          </div>
        </div>

        <!-- Mobile Navigation Dropdown -->
        <div v-if="isOpen" class="overflow-hidden">
          <div class="flex flex-col items-center gap-4 py-2">
            <a
              v-for="link in navLinks"
              :key="link.label"
              :href="link.href"
              class="relative group"
            >
              <span>{{ link.label }}</span>
              <span
                class="absolute bottom-0 left-0 h-[2px] w-0 bg-lime-400 transition-all duration-300 group-hover:w-full rounded-full"
              />
            </a>
            <button
              class="bg-transparent border border-lime-400 text-lime-400 rounded-full px-4 py-1 hover:bg-lime-400 hover:text-black transition"
            >
              Log In
            </button>
            <button
              class="bg-lime-400 text-black rounded-full px-4 py-1 hover:bg-lime-500 transition"
            >
              Sign Up
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Spacer to offset fixed navbar -->
  <div class="pb-[86px] md:pb-[98px] lg:pb-[130px]" />
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: max-height 0.3s ease, opacity 0.3s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  max-height: 0;
  opacity: 0;
}
.slide-fade-enter-to,
.slide-fade-leave-from {
  max-height: 500px;
  opacity: 1;
}
</style>
