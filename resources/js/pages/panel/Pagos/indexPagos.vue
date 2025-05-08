<template>

  <Head title="Pagos" />
  <AppLayout>
    <div>
      <div class="card">
        <template v-if="isLoading">
          <div class="rounded border border-surface-200 dark:border-surface-700 p-10 bg-surface-0 dark:bg-surface-900">
            <div class="flex items-center mb-8">
              <Skeleton shape="circle" size="6rem" class="mr-6" />
              <div>
                <Skeleton width="15rem" height="2rem" class="mb-4" />
                <Skeleton width="8rem" height="1.5rem" class="mb-2" />
                <Skeleton width="12rem" height="0.75rem" />
              </div>
            </div>
            <Skeleton width="100%" height="500px" class="mb-8" />
            <div class="flex justify-between mt-6">
              <Skeleton width="6rem" height="3rem" />
              <Skeleton width="6rem" height="3rem" />
            </div>
          </div>
        </template>
        <template v-else>
          <div class="mb-4">
            <AddPago @ver-cuotas="mostrarCuotas" />
          </div>
          <div>
            <ListPagos :cuotas="cuotasSeleccionadas" />
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import AddPago from './Desarrollo/AddPago.vue';
import ListPagos from './Desarrollo/ListPagos.vue';
import { Head } from '@inertiajs/vue3';
import Skeleton from 'primevue/skeleton';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const isLoading = ref(true);
const cuotasSeleccionadas = ref([]);
const toast = useToast();

onMounted(() => {
  setTimeout(() => {
    isLoading.value = false;
  }, 1000);
});

const mostrarCuotas = async (idPrestamo: number) => {
  try {
    const response = await axios.get(`/cuota/${idPrestamo}`);
    cuotasSeleccionadas.value = response.data.data;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'No se pudieron cargar las cuotas',
      life: 3000,
    });
  }
};
</script>
