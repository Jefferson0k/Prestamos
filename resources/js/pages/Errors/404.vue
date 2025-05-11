<script setup>
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import Button from 'primevue/button';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="No encontrado" />
  <FloatingConfigurator />
  <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
    <div class="flex flex-col items-center justify-center">
      <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, rgba(233, 30, 99, 0.4) 10%, rgba(33, 150, 243, 0) 30%)">
        <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20 flex flex-col items-center" style="border-radius: 53px">
          <div class="gap-4 flex flex-col items-center">
            <div class="flex justify-center items-center border-2 border-pink-500 rounded-full" style="height: 3.2rem; width: 3.2rem">
              <i class="pi pi-fw pi-exclamation-circle !text-2xl text-pink-500"></i>
            </div>
            <h1 class="text-surface-900 dark:text-surface-0 font-bold text-5xl mb-2">Se ha producido un error</h1>
            <span class="text-muted-color mb-8">El recurso solicitado no está disponible.</span>
            <img src="/imagenes/access/asset-error.svg" alt="Error" class="mb-8" width="80%" />
            <div class="col-span-12 mt-8 text-center">
              <!-- Opción 1: Usar Link de Inertia -->
              <Link href="/dashboard" class="no-underline">
                <Button label="Ir al panel de control" severity="danger" />
              </Link>
              
              <!-- Opción 2: Usar el evento onClick (alternativa) -->
              <!-- <Button label="Ir al panel de control" severity="danger" @click="$inertia.visit('/dashboard')" /> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>