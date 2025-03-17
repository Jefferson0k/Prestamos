<script setup lang="ts">
import type {
  ColumnDef,
  ColumnFiltersState,
  ExpandedState,
  SortingState,
  VisibilityState,
} from "@tanstack/vue-table";
import { valueUpdater } from "@/utils";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  FlexRender,
  getCoreRowModel,
  getExpandedRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from "@tanstack/vue-table";
import {
  ArrowUpDown,
  ChevronDown,
  Edit,
  Eye,
  MoreHorizontal,
  Trash,
} from "lucide-vue-next";
import { h, ref, onMounted } from "vue";
import { useToast } from "@/components/ui/toast";

interface Cliente {
  id: number;
  nombres: string;
  apellidos: string;
  direccion: string;
  centro_trabajo: string;
  celular: string;
  dni: string;
  fecha_inicio: string | null;
  fecha_vencimiento: string | null;
  tasa_interes_diario: number | null;
  capital_inicial: number | null;
  capital_del_mes: number;
  capital_actual: number;
  interes_actual: number;
  interes_total: number;
  total: number;
  numero_cuotas: number | null;
  estado_cliente: string | null;
  recomendacion: string | null;
  foto: string;
}

// Estado para almacenar los clientes
const clientes = ref<Cliente[]>([]);
const isLoading = ref(true);
const toast = useToast();

// Columnas para la tabla
const columns: ColumnDef<Cliente>[] = [
  {
    id: "select",
    header: ({ table }) =>
      h(Checkbox, {
        modelValue:
          table.getIsAllPageRowsSelected() ||
          (table.getIsSomePageRowsSelected() && "indeterminate"),
        "onUpdate:modelValue": (value) => table.toggleAllPageRowsSelected(!!value),
        ariaLabel: "Seleccionar todos",
      }),
    cell: ({ row }) =>
      h(Checkbox, {
        modelValue: row.getIsSelected(),
        "onUpdate:modelValue": (value) => row.toggleSelected(!!value),
        ariaLabel: "Seleccionar fila",
      }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: "id",
    header: "ID",
    cell: ({ row }) => h("div", { class: "font-medium" }, row.getValue("id")),
  },
  {
    accessorKey: "nombres",
    header: ({ column }) => {
      return h(
        Button,
        {
          variant: "ghost",
          onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
        },
        () => ["Nombres", h(ArrowUpDown, { class: "ml-2 h-4 w-4" })]
      );
    },
    cell: ({ row }) => h("div", {}, row.getValue("nombres")),
  },
  {
    accessorKey: "apellidos",
    header: ({ column }) => {
      return h(
        Button,
        {
          variant: "ghost",
          onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
        },
        () => ["Apellidos", h(ArrowUpDown, { class: "ml-2 h-4 w-4" })]
      );
    },
    cell: ({ row }) => h("div", {}, row.getValue("apellidos")),
  },
  {
    accessorKey: "dni",
    header: "DNI",
    cell: ({ row }) => h("div", {}, row.getValue("dni")),
  },
  {
    accessorKey: "celular",
    header: "Celular",
    cell: ({ row }) => h("div", {}, row.getValue("celular")),
  },
  {
    accessorKey: "capital_actual",
    header: () => h("div", { class: "text-right" }, "Capital"),
    cell: ({ row }) => {
      const amount = Number(row.getValue("capital_actual"));
      const formatted = new Intl.NumberFormat("es-PE", {
        style: "currency",
        currency: "PEN",
      }).format(amount);

      return h("div", { class: "text-right font-medium" }, formatted);
    },
  },
  {
    accessorKey: "interes_actual",
    header: () => h("div", { class: "text-right" }, "Interés"),
    cell: ({ row }) => {
      const amount = Number(row.getValue("interes_actual"));
      const formatted = new Intl.NumberFormat("es-PE", {
        style: "currency",
        currency: "PEN",
      }).format(amount);

      return h("div", { class: "text-right font-medium" }, formatted);
    },
  },
  {
    accessorKey: "total",
    header: () => h("div", { class: "text-right" }, "Total"),
    cell: ({ row }) => {
      const amount = Number(row.getValue("total"));
      const formatted = new Intl.NumberFormat("es-PE", {
        style: "currency",
        currency: "PEN",
      }).format(amount);

      return h("div", { class: "text-right font-medium" }, formatted);
    },
  },
  {
    id: "actions",
    enableHiding: false,
    cell: ({ row }) => {
      const cliente = row.original;

      return h("div", { class: "relative flex justify-end" }, [
        h(
          DropdownMenu,
          {},
          {
            default: () => [
              h(
                DropdownMenuTrigger,
                { asChild: true },
                {
                  default: () =>
                    h(
                      Button,
                      { variant: "ghost", class: "h-8 w-8 p-0" },
                      {
                        default: () => h(MoreHorizontal, { class: "h-4 w-4" }),
                      }
                    ),
                }
              ),
              h(
                DropdownMenuContent,
                { align: "end" },
                {
                  default: () => [
                    h(
                      DropdownMenuItem,
                      {
                        onClick: () => viewCliente(cliente),
                      },
                      {
                        default: () => [
                          h(Eye, { class: "mr-2 h-4 w-4" }),
                          "Ver detalles",
                        ],
                      }
                    ),
                    h(
                      DropdownMenuItem,
                      {
                        onClick: () => editCliente(cliente),
                      },
                      {
                        default: () => [h(Edit, { class: "mr-2 h-4 w-4" }), "Editar"],
                      }
                    ),
                    h(DropdownMenuSeparator),
                    h(
                      DropdownMenuItem,
                      {
                        onClick: () => deleteCliente(cliente),
                        class: "text-destructive focus:text-destructive",
                      },
                      {
                        default: () => [h(Trash, { class: "mr-2 h-4 w-4" }), "Eliminar"],
                      }
                    ),
                    h(
                      DropdownMenuItem,
                      {
                        onClick: () => row.toggleExpanded(),
                      },
                      {
                        default: () => "Ver información completa",
                      }
                    ),
                  ],
                }
              ),
            ],
          }
        ),
      ]);
    },
  },
];

// Estados reactivos para la tabla
const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});
const expanded = ref<ExpandedState>({});

// Inicializar la tabla
const table = useVueTable({
  get data() {
    return clientes.value;
  },
  columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getExpandedRowModel: getExpandedRowModel(),
  onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue) =>
    valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
  onExpandedChange: (updaterOrValue) => valueUpdater(updaterOrValue, expanded),
  state: {
    get sorting() {
      return sorting.value;
    },
    get columnFilters() {
      return columnFilters.value;
    },
    get columnVisibility() {
      return columnVisibility.value;
    },
    get rowSelection() {
      return rowSelection.value;
    },
    get expanded() {
      return expanded.value;
    },
  },
});

// Funciones para acciones de cliente
const viewCliente = (cliente: Cliente) => {
  toast({
    title: "Ver cliente",
    description: `Visualizando a ${cliente.nombres} ${cliente.apellidos}`,
  });
};

const editCliente = (cliente: Cliente) => {
  toast({
    title: "Editar cliente",
    description: `Editando a ${cliente.nombres} ${cliente.apellidos}`,
  });
};

const deleteCliente = (cliente: Cliente) => {
  toast({
    variant: "destructive",
    title: "Eliminar cliente",
    description: `¿Estás seguro de eliminar a ${cliente.nombres} ${cliente.apellidos}?`,
  });
};

// Cargar datos al montar el componente
onMounted(async () => {
  try {
    const response = await fetch("http://localhost:8000/api/cliente");
    const result = await response.json();
    clientes.value = result.data;
  } catch (error) {
    console.error("Error al cargar los datos:", error);
    toast({
      variant: "destructive",
      title: "Error",
      description: "No se pudieron cargar los datos de clientes",
    });
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="w-full">
    <div class="flex items-center justify-between py-4">
      <div class="flex items-center gap-2">
        <Input
          class="max-w-sm"
          placeholder="Buscar por nombres..."
          :model-value="table.getColumn('nombres')?.getFilterValue() as string"
          @update:model-value="table.getColumn('nombres')?.setFilterValue($event)"
        />
        <Input
          class="max-w-sm"
          placeholder="Buscar por DNI..."
          :model-value="table.getColumn('dni')?.getFilterValue() as string"
          @update:model-value="table.getColumn('dni')?.setFilterValue($event)"
        />
      </div>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="outline" class="ml-auto">
            Columnas <ChevronDown class="ml-2 h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuCheckboxItem
            v-for="column in table
              .getAllColumns()
              .filter((column) => column.getCanHide())"
            :key="column.id"
            class="capitalize"
            :model-value="column.getIsVisible()"
            @update:model-value="
              (value) => {
                column.toggleVisibility(!!value);
              }
            "
          >
            {{ column.id }}
          </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="isLoading">
            <TableRow>
              <TableCell :colspan="columns.length" class="h-24 text-center">
                Cargando datos...
              </TableCell>
            </TableRow>
          </template>
          <template v-else-if="table.getRowModel().rows?.length">
            <template v-for="row in table.getRowModel().rows" :key="row.id">
              <TableRow :data-state="row.getIsSelected() && 'selected'">
                <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                  <FlexRender
                    :render="cell.column.columnDef.cell"
                    :props="cell.getContext()"
                  />
                </TableCell>
              </TableRow>
              <TableRow v-if="row.getIsExpanded()">
                <TableCell :colspan="columns.length">
                  <div class="p-4 bg-muted/20 rounded-md">
                    <h3 class="font-bold text-lg mb-2">
                      Información completa del cliente
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">Dirección</p>
                        <p>{{ row.original.direccion || "No especificada" }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Centro de trabajo
                        </p>
                        <p>{{ row.original.centro_trabajo || "No especificado" }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Fecha de inicio
                        </p>
                        <p>{{ row.original.fecha_inicio || "No establecida" }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Fecha de vencimiento
                        </p>
                        <p>{{ row.original.fecha_vencimiento || "No establecida" }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Tasa de interés diario
                        </p>
                        <p>
                          {{
                            row.original.tasa_interes_diario
                              ? row.original.tasa_interes_diario + "%"
                              : "No establecida"
                          }}
                        </p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Capital inicial
                        </p>
                        <p>
                          {{
                            row.original.capital_inicial
                              ? new Intl.NumberFormat("es-PE", {
                                  style: "currency",
                                  currency: "PEN",
                                }).format(row.original.capital_inicial)
                              : "No establecido"
                          }}
                        </p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Capital del mes
                        </p>
                        <p>
                          {{
                            new Intl.NumberFormat("es-PE", {
                              style: "currency",
                              currency: "PEN",
                            }).format(row.original.capital_del_mes)
                          }}
                        </p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Interés total
                        </p>
                        <p>
                          {{
                            new Intl.NumberFormat("es-PE", {
                              style: "currency",
                              currency: "PEN",
                            }).format(row.original.interes_total)
                          }}
                        </p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Número de cuotas
                        </p>
                        <p>{{ row.original.numero_cuotas || "No establecido" }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Estado del cliente
                        </p>
                        <p>{{ row.original.estado_cliente || "No establecido" }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">
                          Recomendación
                        </p>
                        <p>{{ row.original.recomendacion || "Ninguna" }}</p>
                      </div>
                    </div>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </template>

          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              No se encontraron resultados.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="flex items-center justify-between space-x-2 py-4">
      <div class="flex-1 text-sm text-muted-foreground">
        {{ table.getFilteredSelectedRowModel().rows.length }} de
        {{ table.getFilteredRowModel().rows.length }} cliente(s) seleccionado(s).
      </div>
      <div class="space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="!table.getCanPreviousPage()"
          @click="table.previousPage()"
        >
          Anterior
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="!table.getCanNextPage()"
          @click="table.nextPage()"
        >
          Siguiente
        </Button>
      </div>
    </div>
  </div>
</template>
