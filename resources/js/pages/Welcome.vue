<script setup lang="ts">
import Navbar from "@/components/Navbar.vue";
import { Head } from "@inertiajs/vue3";
import { Star } from "lucide-vue-next";
</script>

<template>
  <Head title="Nabungo">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <section
    class="min-h-screen text-white flex flex-col items-center justify-center px-4 relative overflow-hidden bg-[#08070E]"
  >
    <div
      class="aspect-square bg-white rounded-full blur-[90px] flex-none h-[930px] w-[930px] absolute -left-[395px] -top-[490px] opacity-[0.01] z-0"
    ></div>

    <div
      class="aspect-square bg-[#abffe6] rounded-full blur-[90px] flex-none h-[694px] w-[694px] absolute -left-[347px] -top-[362px] opacity-[0.05] z-0"
    ></div>

    <div
      className="  aspect-square bg-white rounded-full blur-[90px] flex-none w-[930px] h-[930px] opacity-[0.01] absolute top-[-21px right-[-622px]
z-0
  "
    ></div>

    <div
      class="aspect-square bg-[#abffe6] rounded-full blur-[90px] flex-none h-[694px] w-[694px] absolute -right-[504px] top-[97px] opacity-[0.05] z-0"
    ></div>

    <Navbar />
    <div class="h-24 w-full"></div>

    <div class="flex flex-col gap-6 justify-center max-w-2xl text-center">
      <h1 class="font-dm-sans text-7xl tracking-wide leading-18">
        Take Control of Your Digital Assets
      </h1>
      <span class="font-dm-sans text-[#D5D5D5] text-xl"
        >Cryptix offers a seamless, secure experience for managing your digital assets.
        Instant transactions, optimized fees, and premium design.</span
      >

      <button
        className="w-fit mx-auto mt-4 px-5 py-3 rounded-full bg-[#00FFB2] font-bold tracking-wide st font-dm-sans uppercase transform hover:scale-105 hover:bg-[#00FFB2]/90 duration-300 text-black cursor-pointer transition-transform btn-neon"
      >
        Get Started
      </button>

      <div class="mt-8 flex flex-col gap-2 mx-auto">
        <span class="text-[#D5D5D5] text-sm font-light">Lorem ipsum dolor.</span>
        <div class="flex justify-center items-center gap-2">
          <Star :size="14" />
          <Star :size="14" />
          <Star :size="14" />
          <Star :size="14" />
          <Star :size="14" />
        </div>
      </div>
    </div>

    <div class="aspect-video max-w-6xl relative w-full">
      <div class="contents">
        <div
          class="rounded-[16px] flex-none h-full left-0 overflow-hidden absolute top-0 w-full"
        >
          <div class="absolute rounded-inherit top-0 right-0 bottom-0 left-0">
            <img
              class="block w-full h-full rounded-inherit object-center object-cover opacity-90"
              src="/images/dashboard-dark.png"
            />
          </div>
          <div></div>
          <div></div>
        </div>
      </div>
      <div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
  </section>
</template>
