<template>
    <div class="w-full">
        <div v-if="loading" class="flex justify-center items-center p-8">
            <div class="animate-spin h-8 w-8 border-4 border-primary border-t-transparent rounded-full"></div>
            <span class="ml-3">Cargando datos...</span>
        </div>
        <div v-else-if="error" class="p-4 bg-red-100 text-red-800 rounded-md">
            {{ error }}
        </div>
        <div v-else>
            <!-- Cliente info header -->
            <div class="mb-4 border rounded-md">
                <table class="w-full">
                    <tr>
                        <td class="font-bold p-2 border-r" style="width: 120px;">Cliente:</td>
                        <td class="p-2 bg-gray-100">{{ cliente ? prestamo.cliente_id : 'Cargando...' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Filtro y controles -->
            <div class="flex items-center py-4">
                <Input class="max-w-sm" placeholder="Filtrar por número de cuota..."
                    :model-value="table.getColumn('numero_cuota')?.getFilterValue() as string"
                    @update:model-value="table.getColumn('numero_cuota')?.setFilterValue($event)" />
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="ml-auto">
                            Columnas
                            <ChevronDown class="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuCheckboxItem
                            v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                            :key="column.id" class="capitalize" :model-value="column.getIsVisible()"
                            @update:model-value="(value) => {
                                column.toggleVisibility(!!value)
                            }">
                            {{ getColumnLabel(column.id) }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <!-- Tabla de cuotas estilizada según la imagen -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border">
                    <thead>
                        <tr>
                            <th class="p-2 border bg-yellow-300 text-center">N°CUOTAS</th>
                            <th class="p-2 border bg-yellow-300 text-center">CAPITAL</th>
                            <th class="p-2 border text-center">FECHA DE INICIO</th>
                            <th class="p-2 border text-center">FECHA DE PAGO</th>
                            <th class="p-2 border bg-yellow-300 text-center">DIAS DE INTERES</th>
                            <th class="p-2 border bg-yellow-300 text-center">TASA DE INTERES DIARIO</th>
                            <th class="p-2 border bg-yellow-300 text-center">MONTO DE INTERES A PAGAR</th>
                            <th class="p-2 border bg-green-300 text-center">MONTO DE CAPITAL A PAGAR</th>
                            <th class="p-2 border bg-yellow-300 text-center">SALDO DE CAPITAL</th>
                            <th class="p-2 border bg-yellow-300 text-center">DE CAPITAL MÁS INTERES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="prestamo.tasa_interes_diario" class="bg-yellow-100">
                            <td class="border"></td>
                            <td class="border"></td>
                            <td class="border"></td>
                            <td class="border"></td>
                            <td class="border text-center">{{ prestamo.tasa_interes_diario }}%</td>
                            <td class="border"></td>
                            <td class="border"></td>
                            <td class="border"></td>
                            <td class="border"></td>
                            <td class="border"></td>
                        </tr>
                        <template v-if="simulacion.length">
                            <tr v-for="(cuota, index) in simulacion" :key="index" class="hover:bg-gray-50">
                                <td class="p-2 border text-center bg-yellow-100">{{ cuota.numero_cuota === 0 ? '' : `MES
                                    ${cuota.numero_cuota}` }}</td>
                                <td class="p-2 border text-center bg-yellow-100">{{ formatCurrency(cuota.capital, false)
                                    }}</td>
                                <td class="p-2 border text-center">{{ formatDate(cuota.fecha_inicio) }}</td>
                                <td class="p-2 border text-center">{{ formatDate(cuota.fecha_pago) }}</td>
                                <td class="p-2 border text-center bg-yellow-100">{{ cuota.dias_interes }}</td>
                                <td class="p-2 border text-center bg-yellow-100">{{ cuota.tasa_interes_diario }}</td>
                                <td class="p-2 border text-center bg-yellow-100">{{
                                    formatCurrency(cuota.monto_interes_pagar, false) }}</td>
                                <td class="p-2 border text-center bg-green-300">{{
                                    formatCurrency(cuota.monto_capital_pagar, false) }}</td>
                                <td class="p-2 border text-center bg-yellow-100">{{ formatCurrency(cuota.saldo_capital,
                                    false) }}</td>
                                <td class="p-2 border text-center bg-yellow-100">{{
                                    formatCurrency(cuota.monto_capital_mas_interes, false) }}</td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="10" class="h-24 text-center border">
                                No hay resultados.
                            </td>
                        </tr>
                        <tr class="font-bold">
                            <td class="p-2 border">TOTAL</td>
                            <td class="p-2 border"></td>
                            <td class="p-2 border"></td>
                            <td class="p-2 border"></td>
                            <td class="p-2 border"></td>
                            <td class="p-2 border"></td>
                            <td class="p-2 border text-center">{{ formatCurrency(getTotalInteres(), false) }}</td>
                            <td class="p-2 border text-center">{{ formatCurrency(getTotalCapital(), false) }}</td>
                            <td class="p-2 border"></td>
                            <td class="p-2 border text-center">{{ formatCurrency(getTotalPago(), false) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="flex items-center justify-end space-x-2 py-4">
                <div class="flex-1 text-sm text-muted-foreground">
                    Detalles del préstamo: Capital inicial {{ formatCurrency(prestamo.capital) }} |
                    Tasa diaria {{ prestamo.tasa_interes_diario }}% |
                    {{ prestamo.numero_cuotas }} cuotas
                </div>
                <div class="space-x-2">
                    <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()"
                        @click="table.previousPage()">
                        Anterior
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">
                        Siguiente
                    </Button>
                </div>
            </div>

            <AddPago />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import type {
    ColumnDef,
    ColumnFiltersState,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table'
import { valueUpdater } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table'
import { ArrowUpDown, ChevronDown } from 'lucide-vue-next'
import { h } from 'vue'
import AddPago from './AddPago.vue'

// Interfaces para los datos
interface Cliente {
    id: number
    nombre_completo: string
}

interface Prestamo {
    id: number
    cliente_id: number
    fecha_inicio: string
    fecha_vencimiento: string
    capital: string
    numero_cuotas: number
    estado_cliente: number
    recomendacion: string
    tasa_interes_diario: string
    monto_total: string
    created_at: string
    updated_at: string
}

interface Cuota {
    numero_cuota: number
    capital: number
    fecha_inicio: string
    fecha_pago: string
    dias_interes: number
    tasa_interes_diario: string
    monto_interes_pagar: number
    monto_capital_pagar: number
    saldo_capital: string
    monto_capital_mas_interes: number
}

// Estado del componente
const cliente = ref<Cliente | null>(null)
const prestamo = ref<Prestamo>({} as Prestamo)
const simulacion = ref<Cuota[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

// Estado de la tabla
const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})

// Función para formatear moneda
const formatCurrency = (value: number | string, includeSymbol: boolean = true): string => {
    const amount = typeof value === 'string' ? parseFloat(value) : value

    if (isNaN(amount)) return '0,00'

    if (includeSymbol) {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN',
            minimumFractionDigits: 2
        }).format(amount)
    } else {
        // Formato similar al de la imagen (sin símbolo, coma para miles, punto para decimales)
        return new Intl.NumberFormat('es-PE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount).replace(',', '.').replace(/\./g, ',').replace(/,(\d+)$/, ',$1')
    }
}

// Función para formatear fechas
const formatDate = (dateString: string): string => {
    if (!dateString) return ''

    // Si la fecha ya viene formateada como DD/MM/YYYY, la dejamos así
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
        return dateString
    }

    // Si la fecha viene en formato ISO (YYYY-MM-DD)
    try {
        const date = new Date(dateString)
        const day = date.getDate().toString().padStart(2, '0')
        const month = (date.getMonth() + 1).toString().padStart(2, '0')
        const year = date.getFullYear()

        return `${day}/${month}/${year}`
    } catch (e) {
        // Si hay un error, devolvemos la fecha original
        return dateString
    }
}

// Cálculo de totales
const getTotalInteres = () => {
    return simulacion.value.reduce((sum, cuota) => sum + (cuota.monto_interes_pagar || 0), 0)
}

const getTotalCapital = () => {
    return simulacion.value.reduce((sum, cuota) => sum + (cuota.monto_capital_pagar || 0), 0)
}

const getTotalPago = () => {
    return simulacion.value.reduce((sum, cuota) => sum + (cuota.monto_capital_mas_interes || 0), 0)
}

// Función para obtener etiquetas de columnas más amigables
const getColumnLabel = (columnId: string): string => {
    const labels: Record<string, string> = {
        'numero_cuota': 'Nº Cuota',
        'capital': 'Capital',
        'fecha_inicio': 'Fecha Inicio',
        'fecha_pago': 'Fecha Pago',
        'dias_interes': 'Días Interés',
        'tasa_interes_diario': 'Tasa Interés',
        'monto_interes_pagar': 'Interés a Pagar',
        'monto_capital_pagar': 'Capital a Pagar',
        'saldo_capital': 'Saldo Capital',
        'monto_capital_mas_interes': 'Capital más Interés'
    }
    return labels[columnId] || columnId
}

// Definición de columnas
const columns: ColumnDef<Cuota>[] = [
    {
        accessorKey: 'numero_cuota',
        header: 'Nº Cuota',
        cell: ({ row }) => {
            const numero = row.getValue('numero_cuota') as number
            return numero === 0 ? '' : `MES ${numero}`
        },
    },
    {
        accessorKey: 'capital',
        header: 'Capital',
        cell: ({ row }) => formatCurrency(row.getValue('capital'), false),
    },
    {
        accessorKey: 'fecha_inicio',
        header: 'Fecha Inicio',
        cell: ({ row }) => formatDate(row.getValue('fecha_inicio')),
    },
    {
        accessorKey: 'fecha_pago',
        header: 'Fecha Pago',
        cell: ({ row }) => formatDate(row.getValue('fecha_pago')),
    },
    {
        accessorKey: 'dias_interes',
        header: 'Días',
        cell: ({ row }) => row.getValue('dias_interes'),
    },
    {
        accessorKey: 'tasa_interes_diario',
        header: 'Tasa Interés',
        cell: ({ row }) => row.getValue('tasa_interes_diario'),
    },
    {
        accessorKey: 'monto_interes_pagar',
        header: 'Interés a Pagar',
        cell: ({ row }) => formatCurrency(row.getValue('monto_interes_pagar'), false),
    },
    {
        accessorKey: 'monto_capital_pagar',
        header: 'Capital a Pagar',
        cell: ({ row }) => formatCurrency(row.getValue('monto_capital_pagar'), false),
    },
    {
        accessorKey: 'saldo_capital',
        header: 'Saldo Capital',
        cell: ({ row }) => formatCurrency(row.getValue('saldo_capital'), false),
    },
    {
        accessorKey: 'monto_capital_mas_interes',
        header: 'Capital más Interés',
        cell: ({ row }) => formatCurrency(row.getValue('monto_capital_mas_interes'), false),
    },
]

// Fetch de datos
const fetchData = async () => {
    try {
        loading.value = true
        // Usamos la URL exacta que proporcionaste
        const response = await fetch('http://localhost/prestamo/1/simulacion')
        if (!response.ok) {
            throw new Error(`Error al cargar los datos: ${response.status} ${response.statusText}`)
        }

        const data = await response.json()
        prestamo.value = data.prestamo
        simulacion.value = data.simulacion

        // Obtener los datos del cliente (si es que la API lo devuelve)
        if (data.cliente) {
            cliente.value = data.cliente
        } else {
            // Si no, podríamos hacer una petición adicional para obtener el cliente
            try {
                const clienteResponse = await fetch(`http://localhost/cliente/${prestamo.value.cliente_id}`)
                if (clienteResponse.ok) {
                    cliente.value = await clienteResponse.json()
                }
            } catch (e) {
                console.error('Error al cargar los datos del cliente:', e)
            }
        }
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Error desconocido al cargar los datos'
        console.error('Error al cargar los datos:', e)
    } finally {
        loading.value = false
    }
}

// Configuración de la tabla
const table = useVueTable({
    get data() { return simulacion.value },
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
        get columnVisibility() { return columnVisibility.value },
    },
    initialState: {
        pagination: {
            pageSize: 15,
        },
    },
})

// Cargar datos al montar el componente
onMounted(() => {
    fetchData()
})
</script>