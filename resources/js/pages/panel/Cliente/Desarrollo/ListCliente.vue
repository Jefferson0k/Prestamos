<!-- src/components/clientes/ClientesPage.vue -->
<template>
  <Toaster />
  <div class="grid grid-cols-1 gap-6">
    <Card class="mb-4">
      <CardContent class="pt-6 pb-4">
        <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-4">
          <div class="flex items-center gap-2 flex-wrap">
            <AddCliente @cliente-added="refreshData" />
            <Button @click="refreshData" variant="secondary">
              <RefreshCcw class="h-4 w-4 mr-2" />
              Actualizar
            </Button>
            <ClienteFilter @filter-change="onFilterChange" />
          </div>
          <Button variant="destructive" @click="exportClientes">
            <Download class="h-4 mr-2" />
            Exportar
          </Button>
        </div>
      </CardContent>
    </Card>
    
    <ClienteTable 
      ref="clienteTable" 
      @cliente-selected="openClienteDetails"
      @edit-cliente="editCliente"
      @delete-cliente="confirmDelete"
    />
    
    <!-- Diálogo de confirmación para eliminar cliente -->
    <DeleteClienteDialog
      v-model:show="showDeleteDialog"
      :cliente="clienteToDelete"
      @confirm="deleteCliente"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/toast';
import { useToast } from '@/components/ui/toast';
import { RefreshCcw, Download } from 'lucide-vue-next';
import AddCliente from './AddCliente.vue';
import ClienteTable from './ClienteTable.vue';
import ClienteFilter from './ClienteFilter.vue';
import DeleteClienteDialog from './DeleteClienteDialog.vue';
import { Cliente } from './typsClientes/typesCliente';
import { clienteService } from './typsClientes/clienteService';

const { toast } = useToast();
const clienteTable = ref<InstanceType<typeof ClienteTable> | null>(null);
const selectedCliente = ref<Cliente | null>(null);
const isClienteDetailsOpen = ref(false);
const showDeleteDialog = ref(false);
const clienteToDelete = ref<Cliente | null>(null);

const refreshData = () => {
  clienteTable.value?.refreshData();
  toast({
    title: "Actualizado",
    description: "Los datos de clientes han sido actualizados.",
  });
};

const onFilterChange = (filter: string | null) => {
  clienteTable.value?.setFilter(filter);
};

const openClienteDetails = (cliente: Cliente) => {
  selectedCliente.value = cliente;
  isClienteDetailsOpen.value = true;
  // Implement your modal display logic here
};

const editCliente = (cliente: Cliente) => {
  toast({
    title: "Función en desarrollo",
    description: `Editar cliente: ${cliente.nombres} ${cliente.apellidos}`,
  });
  // Implement your edit logic here
};

const confirmDelete = (cliente: Cliente) => {
  clienteToDelete.value = cliente;
  showDeleteDialog.value = true;
};

const deleteCliente = async (id: number) => {
  try {
    await clienteService.deleteCliente(id);
    
    showDeleteDialog.value = false;
    clienteToDelete.value = null;
    
    toast({
      title: "Cliente eliminado",
      description: "El cliente ha sido eliminado correctamente.",
    });
    
    refreshData();
  } catch (error) {
    console.error('Error al eliminar cliente:', error);
    toast({
      title: "Error",
      description: "No se pudo eliminar el cliente. Verifica los permisos o inténtalo nuevamente.",
      variant: "destructive",
    });
  }
};

const exportClientes = async () => {
  try {
    const blob = await clienteService.exportClientes();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `clientes-export-${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    a.remove();
    
    toast({
      title: "Exportación exitosa",
      description: "Los datos han sido exportados correctamente.",
    });
  } catch (error) {
    console.error('Error exporting clients:', error);
    toast({
      title: "Error",
      description: "No se pudo exportar los datos. Intente nuevamente.",
      variant: "destructive",
    });
  }
};
</script>