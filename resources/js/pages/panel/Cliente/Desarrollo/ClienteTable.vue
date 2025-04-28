<template>
  <Card class="rounded-md border overflow-x-auto">
    <CardHeader class="pb-0">
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
          <CardTitle>Clientes</CardTitle>
          <CardDescription>Gestiona tu cartera de clientes fácilmente</CardDescription>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
          <div class="w-full sm:w-auto relative">
            <Input placeholder="Buscar clientes..." class="h-9 w-full sm:w-[250px] pl-8" v-model="searchQuery"
              @input="onSearchChange" />
            <Search class="h-4 w-4 absolute left-2.5 top-2.5 text-muted-foreground" />
          </div>
          <DataTableViewOptions :table="table" />
        </div>
      </div>
    </CardHeader>
    <CardContent class="pt-4">
      <div class="rounded-md border overflow-hidden">
        <div class="overflow-x-auto">
          <Table class="w-full">
            <TableHeader>
              <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                <TableHead v-for="header in headerGroup.headers" :key="header.id" :class="[
                  header.column.getCanSort() ? 'cursor-pointer select-none' : '',
                  getColumnWidthClass(header.column.id)
                ]" @click="header.column.getCanSort() ? header.column.toggleSorting() : null">
                  <div class="flex items-center justify-between space-x-2" v-if="!header.isPlaceholder">
                    <component :is="() => h(FlexRender, {
                      render: header.column.columnDef.header,
                      props: header.getContext()
                    })" />
                    <div v-if="header.column.getCanSort()">
                      <ArrowUpDown class="h-4 w-4" v-if="!header.column.getIsSorted()" />
                      <ArrowUp class="h-4 w-4" v-else-if="header.column.getIsSorted() === 'asc'" />
                      <ArrowDown class="h-4 w-4" v-else />
                    </div>
                  </div>
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="isLoading">
                <TableRow v-for="i in 5" :key="`skeleton-${i}`">
                  <TableCell :colspan="table.getAllColumns().length" class="h-14">
                    <Skeleton class="h-8 w-full" />
                  </TableCell>
                </TableRow>
              </template>
              <template v-else-if="table.getRowModel().rows?.length">
                <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                  :data-state="row.getIsSelected() && 'selected'" class="cursor-pointer hover:bg-muted/50"
                  @click="openClienteDetails(row.original)">
                  <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id"
                    :class="getColumnWidthClass(cell.column.id)">
                    <div class="truncate">
                      <component :is="() => h(FlexRender, {
                        render: cell.column.columnDef.cell,
                        props: cell.getContext()
                      })" />
                    </div>
                  </TableCell>
                </TableRow>
              </template>
              <template v-else>
                <TableRow>
                  <TableCell :colspan="columns.length" class="h-24 text-center">
                    <div class="flex flex-col items-center justify-center py-4">
                      <FileX class="h-10 w-10 text-muted-foreground mb-2" />
                      <p class="text-sm text-muted-foreground">No hay resultados</p>
                    </div>
                  </TableCell>
                </TableRow>
              </template>
            </TableBody>
          </Table>
        </div>
      </div>
    </CardContent>
    <CardFooter>
      <div class="w-full">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2">
          <div class="text-sm text-muted-foreground">
            Mostrando <span class="font-medium">{{ table.getRowModel().rows.length }}</span>
            de <span class="font-medium">{{ totalItems }}</span> clientes
          </div>
          <div class="flex flex-wrap items-center gap-4 justify-center sm:justify-end">
            <div class="flex items-center space-x-2">
              <p class="text-sm font-medium">Filas</p>
              <Select v-model="pageSize" @update:modelValue="onPageSizeChange">
                <SelectTrigger class="h-8 w-16">
                  <SelectValue :placeholder="pageSizeString" />
                </SelectTrigger>
                <SelectContent side="top">
                  <SelectItem v-for="size in [5, 10, 20, 50]" :key="size" :value="size">{{ size }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <template v-if="totalPageCount > 0">
              <p class="text-sm font-medium whitespace-nowrap">
                Página {{ currentPage + 1 }} de {{ totalPageCount }}
              </p>

              <div class="flex items-center space-x-1">
                <Button variant="outline" class="hidden h-8 w-8 p-0 md:flex" :disabled="currentPage === 0"
                  @click="goToPage(0)" aria-label="Primera página">
                  <DoubleArrowLeftIcon class="h-4 w-4" />
                </Button>
                <Button variant="outline" class="h-8 w-8 p-0" :disabled="currentPage === 0"
                  @click="goToPage(currentPage - 1)" aria-label="Página anterior">
                  <ChevronLeftIcon class="h-4 w-4" />
                </Button>
                <Button variant="outline" class="h-8 w-8 p-0" :disabled="currentPage >= totalPageCount - 1"
                  @click="goToPage(currentPage + 1)" aria-label="Página siguiente">
                  <ChevronRightIcon class="h-4 w-4" />
                </Button>
                <Button variant="outline" class="hidden h-8 w-8 p-0 md:flex"
                  :disabled="currentPage >= totalPageCount - 1" @click="goToPage(totalPageCount - 1)"
                  aria-label="Última página">
                  <DoubleArrowRightIcon class="h-4 w-4" />
                </Button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, watch, h, defineEmits } from 'vue';
import {
  useVueTable,
  getCoreRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  FlexRender
} from '@tanstack/vue-table';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import DataTableViewOptions from './DataTableViewOptions.vue';
import { Cliente, SortingState, VisibilityState, ColumnFiltersState } from './typsClientes/typesCliente';
import { getColumnWidthClass } from './typsClientes/columnUtils';
import { createColumns } from './typsClientes/columns';
import { clienteService } from './typsClientes/clienteService';
import { useToast } from '@/components/ui/toast';
import {
  RefreshCcw,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  Search,
  FileX
} from 'lucide-vue-next';
import {
  ChevronRightIcon,
  ChevronLeftIcon,
  DoubleArrowLeftIcon,
  DoubleArrowRightIcon
} from "@radix-icons/vue";

const emit = defineEmits(['cliente-selected']);
const { toast } = useToast();

// State variables
const clientes = ref<Cliente[]>([]);
const isLoading = ref(true);
const searchQuery = ref('');
const debouncedSearchQuery = ref('');
const estadoClienteFilter = ref<string | null>(null);
const pageSize = ref(10);
const currentPage = ref(0);
const totalItems = ref(0);
const totalPageCount = ref(0);
const sorting = ref<SortingState>([]);
const columnVisibility = ref<VisibilityState>({
  'direccion': false,
  'centro_trabajo': false,
  'recomendacion': false,
  'foto': false,
  'celular': false,
});
const columnFilters = ref<ColumnFiltersState>([]);

// Computed props
const pageSizeString = computed(() => String(pageSize.value));

// Methods
const openClienteDetails = (cliente: Cliente) => {
  emit('cliente-selected', cliente);
};

const viewClienteDetails = (cliente: Cliente) => {
  emit('cliente-selected', cliente);
};

const editCliente = (cliente: Cliente) => {
  emit('edit-cliente', cliente);
};

const deleteCliente = (cliente: Cliente) => {
  emit('delete-cliente', cliente);
};

const columns = createColumns({
  viewClienteDetails,
  editCliente,
  deleteCliente
});

const getSortParams = () => {
  if (sorting.value.length > 0) {
    const [sort] = sorting.value;
    return {
      sortBy: sort.id,
      sortDirection: sort.desc ? 'desc' : 'asc'
    };
  }
  return {};
};

const fetchClientes = async () => {
  isLoading.value = true;

  try {
    const result = await clienteService.getClientes({
      page: currentPage.value,
      perPage: pageSize.value,
      search: debouncedSearchQuery.value,
      estadoCliente: estadoClienteFilter.value || undefined,
      ...getSortParams()
    });

    clientes.value = result.data.map((cliente: any) => ({
      ...cliente,
      nombres: cliente.nombres || '',
      apellidos: cliente.apellidos || '',
      direccion: cliente.direccion || '',
      centro_trabajo: cliente.centro_trabajo || '',
      celular: cliente.celular || '',
      dni: cliente.dni || '',
      foto: cliente.foto || '',
      capital_del_mes: Number(cliente.capital_del_mes || 0),
      capital_actual: Number(cliente.capital_actual || 0),
      interes_actual: Number(cliente.interes_actual || 0),
      interes_total: Number(cliente.interes_total || 0),
      total: Number(cliente.total || 0)
    }));

    totalItems.value = result.meta.total;
    totalPageCount.value = result.meta.last_page;

  } catch (error) {
    console.error('Error al obtener clientes:', error);
    toast({
      title: "Error",
      description: "No se pudieron cargar los clientes. Verifica la conexión.",
      variant: "destructive",
    });
  } finally {
    isLoading.value = false;
  }
};

const onSearchChange = () => {
  // Debounce search input to avoid too many API calls
  clearTimeout(window.searchTimeout);
  window.searchTimeout = setTimeout(() => {
    debouncedSearchQuery.value = searchQuery.value;
    goToPage(0); // Reset to first page on search
  }, 300);
};

const onPageSizeChange = (newSize: number) => {
  pageSize.value = newSize;
  goToPage(0); // Reset to first page when changing page size
};

const goToPage = (page: number) => {
  currentPage.value = page;
};

const onSortingChange = () => {
  goToPage(0); // Reset to first page on sort change
  fetchClientes();
};

const onFilterChange = (filter: string | null) => {
  estadoClienteFilter.value = filter;
  goToPage(0); // Reset to first page on filter change
  fetchClientes();
};

// Table configuration
const table = useVueTable({
  get data() {
    return clientes.value;
  },
  columns,
  state: {
    get sorting() {
      return sorting.value;
    },
    get columnVisibility() {
      return columnVisibility.value;
    },
    get columnFilters() {
      return columnFilters.value;
    },
    get pagination() {
      return {
        pageIndex: 0, // We handle pagination manually with the server
        pageSize: pageSize.value,
      };
    },
  },
  onSortingChange: (updater) => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    onSortingChange();
  },
  onColumnVisibilityChange: (updater) => {
    columnVisibility.value = typeof updater === 'function' ? updater(columnVisibility.value) : updater;
  },
  onColumnFiltersChange: (updater) => {
    columnFilters.value = typeof updater === 'function' ? updater(columnFilters.value) : updater;
  },
  manualPagination: true, // We're handling pagination on the server
  manualSorting: true, // We're handling sorting on the server
  manualFiltering: true, // We're handling filtering on the server
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  enableColumnResizing: true,
  enableRowSelection: true,
  debugTable: false,
});

// Watchers
watch([pageSize, currentPage, debouncedSearchQuery, estadoClienteFilter, sorting], () => {
  fetchClientes();
}, { deep: true });

// Initial fetch
fetchClientes();

// Expose methods for parent component
defineExpose({
  refreshData: fetchClientes,
  setFilter: onFilterChange
});
</script>

<style scoped>
:deep(.truncate) {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

:deep(th),
:deep(td) {
  padding: 0.5rem 0.75rem;
}

:deep(.table-container) {
  width: 100%;
  overflow-x: auto;
}
</style>