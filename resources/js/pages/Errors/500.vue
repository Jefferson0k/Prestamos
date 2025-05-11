<script setup>
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import Button from 'primevue/button';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Error del servidor" />
  <FloatingConfigurator />
  <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
    <div class="flex flex-col items-center justify-center">
      <div class="rounded-[56px] p-1" style="background: linear-gradient(180deg, rgba(244, 67, 54, 0.4) 10%, rgba(244, 67, 54, 0) 30%)">
        <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20 flex flex-col items-center rounded-[53px]">
          <div class="gap-4 flex flex-col items-center">
            <div class="flex justify-center items-center border-2 border-red-500 rounded-full w-14 h-14">
              <i class="pi pi-fw pi-cog text-red-500 text-2xl"></i>
            </div>
            <h1 class="text-surface-900 dark:text-surface-0 font-bold text-5xl mb-2">Error del servidor</h1>
            <span class="text-muted-color mb-8 text-center">Estamos trabajando para solucionarlo. Por favor, vuelve más tarde.</span>

            <!-- SVG hombre arreglando servidor -->
            <div class="mb-8 w-full max-w-xl">
              <svg viewBox="0 0 1024 1024" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                <path fill="#f44336" d="M912 112H112c-26.51 0-48 21.49-48 48v704c0 26.51 21.49 48 48 48h800c26.51 0 48-21.49 48-48V160c0-26.51-21.49-48-48-48zM912 864H112V160h800v704z"/>
                <path fill="#2196f3" d="M800 256H224c-8.84 0-16 7.16-16 16v96h608v-96c0-8.84-7.16-16-16-16zM320 416h384c8.84 0 16 7.16 16 16v320c0 8.84-7.16 16-16 16H320c-8.84 0-16-7.16-16-16V432c0-8.84 7.16-16 16-16z"/>
                <circle cx="512" cy="656" r="40" fill="#fff"/>
                <path fill="#4caf50" d="M180 720h80v40h-80zM764 720h80v40h-80z"/>
                <path fill="#ff9800" d="M512 96c-17.673 0-32 14.327-32 32v32h64v-32c0-17.673-14.327-32-32-32z"/>
                <path fill="#607d8b" d="M480 160h64v48h-64z"/>
                <path fill="#795548" d="M472 208h80v16h-80z"/>
              </svg>
            </div>

            <div class="col-span-12 mt-8 text-center">
              <Link href="/dashboard" class="no-underline">
                <Button label="Ir al panel de control" severity="danger" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
