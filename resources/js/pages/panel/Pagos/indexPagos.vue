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
            <ListPagos :cuotas="cuotasSeleccionadas" @abrir-dialogo="abrirDialogoPago"
              @imprimir-comprobante="onImprimirComprobante" @abrir-actualizacion="abrirDialogoPagoActualizar" />

          </div>
          <DialogPagos v-model:visible="dialogVisible" :cuotaId="cuotaIdParaDialogo || 0"
            @pago-guardado="refrescarCuotas" />
          <UpdatePago v-model:visible="dialogVisible1" :cuotaId="cuotaIdParaDialogo1 || 0"
            @pago-actualizado="refrescarCuotas" />
          <ComprobantePago v-model:visible="mostrarComprobante" :comprobante-id="comprobanteId" />

        </template>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import AddPago from './Desarrollo/AddPago.vue';
import ListPagos from './Desarrollo/ListPagos.vue';
import DialogPagos from './Desarrollo/DialogPagos.vue';
import { Head } from '@inertiajs/vue3';
import Skeleton from 'primevue/skeleton';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import ComprobantePago from './Desarrollo/ComprobantePago.vue';
import UpdatePago from './Desarrollo/UpdatePago.vue';

const isLoading = ref(true);
const isLoadingListPagos = ref(true);  // Agregar esta variable específica para ListPagos
const toast = useToast();
const dialogVisible = ref(false);
const dialogVisible1 = ref(false);
const cuotaIdParaDialogo = ref(0);
const cuotaIdParaDialogo1 = ref(0);
const prestamoIdActual = ref(null);
const comprobanteId = ref(0);

const mostrarComprobante = ref(false);

const onImprimirComprobante = (id) => {
  comprobanteId.value = id;
  mostrarComprobante.value = true;
};

onMounted(() => {
  setTimeout(() => {
    isLoading.value = false;
  }, 1000);
});

const cuotasSeleccionadas = ref([]);

const mostrarCuotas = async (idPrestamo) => {
  if (!idPrestamo) {
    toast.add({
      severity: 'warn',
      summary: 'Advertencia',
      detail: 'ID de préstamo no válido',
      life: 3000,
    });
    return;
  }

  try {
    isLoadingListPagos.value = true;  // Activar carga solo para ListPagos
    const response = await axios.get(`/cuota/${idPrestamo}`);
    cuotasSeleccionadas.value = response.data.data;
    prestamoIdActual.value = idPrestamo;
    console.log("Cuotas cargadas:", cuotasSeleccionadas.value);
  } catch (error) {
    console.error("Error al cargar cuotas:", error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'No se pudieron cargar las cuotas',
      life: 3000,
    });
  } finally {
    isLoadingListPagos.value = false;  // Desactivar carga solo para ListPagos
  }
};

const abrirDialogoPago = (idCuota) => {
  // Asegurarse de que idCuota no sea null o undefined
  if (idCuota) {
    cuotaIdParaDialogo.value = idCuota;
    dialogVisible.value = true;
  } else {
    toast.add({
      severity: 'warn',
      summary: 'Advertencia',
      detail: 'No se ha seleccionado una cuota válida',
      life: 3000,
    });
  }
};

const abrirDialogoPagoActualizar = (idCuota) => {
  // Asegurarse de que idCuota no sea null o undefined
  if (idCuota) {
    cuotaIdParaDialogo1.value = idCuota;
    dialogVisible1.value = true;
  } else {
    toast.add({
      severity: 'warn',
      summary: 'Advertencia',
      detail: 'No se ha seleccionado una cuota válida',
      life: 3000,
    });
  }
};

const refrescarCuotas = async (result) => {
  console.log("Refrescando cuotas después de guardar pago:", result);

  if (prestamoIdActual.value) {
    await mostrarCuotas(prestamoIdActual.value);
  } else {
    if (cuotasSeleccionadas.value.length > 0) {
      const anyCuota = cuotasSeleccionadas.value[0];
      if (anyCuota.prestamo_id) {
        prestamoIdActual.value = anyCuota.prestamo_id;
        await mostrarCuotas(anyCuota.prestamo_id);
      } else {
        toast.add({
          severity: 'warn',
          summary: 'Advertencia',
          detail: 'No se ha encontrado el préstamo relacionado',
          life: 3000,
        });
      }
    } else {
      toast.add({
        severity: 'info',
        summary: 'Información',
        detail: 'No hay cuotas para actualizar',
        life: 3000,
      });
    }
  }
};
</script>
