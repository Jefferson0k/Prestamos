<script setup>
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import Button from 'primevue/button';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Servicio no disponible" />
  <FloatingConfigurator />
  <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
    <div class="flex flex-col items-center justify-center">
      <div class="rounded-[56px] p-1" style="background: linear-gradient(180deg, rgba(255, 193, 7, 0.4) 10%, rgba(255, 193, 7, 0) 30%)">
        <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20 flex flex-col items-center rounded-[53px]">
          <div class="gap-4 flex flex-col items-center">
            <div class="flex justify-center items-center border-2 border-yellow-500 rounded-full w-14 h-14">
              <i class="pi pi-fw pi-wrench text-yellow-500 text-2xl"></i>
            </div>
            <h1 class="text-surface-900 dark:text-surface-0 font-bold text-5xl mb-2">Servicio no disponible</h1>
            <span class="text-muted-color mb-8 text-center">
              Estamos realizando tareas de mantenimiento. Por favor, vuelve más tarde.
            </span>

            <!-- SVG de mantenimiento -->
            <div class="mb-8 w-full max-w-xl">
              <svg viewBox="0 0 1024 1024" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                <path fill="#FFC107" d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64z"/>
                <path fill="#FFF" d="M688 336l-32-32-96 96-96-96-32 32 96 96-96 96 32 32 96-96 96 96 32-32-96-96z"/>
                <circle cx="512" cy="512" r="320" stroke="#000" stroke-opacity="0.1" stroke-width="40"/>
              </svg>
            </div>

            <div class="col-span-12 mt-8 text-center">
              <Link href="/dashboard" class="no-underline">
                <Button label="Ir al panel de control" severity="warning" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
