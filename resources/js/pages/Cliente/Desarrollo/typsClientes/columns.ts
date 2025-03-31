import { h } from 'vue';
import { ColumnDef } from '@tanstack/vue-table';
import { Cliente } from './typesCliente';
import { getEstadoBadgeVariant, getInitials } from './columnUtils';

// UI Components
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

// Icons
import { MoreHorizontal, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';

// Interfaces for callback functions
interface ClienteCallbacks {
    viewClienteDetails: (cliente: Cliente) => void;
    editCliente: (cliente: Cliente) => void;
    deleteCliente: (cliente: Cliente) => void;
}

export const createColumns = (callbacks: ClienteCallbacks): ColumnDef<Cliente>[] => [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'checked': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            'checked': row.getIsSelected(),
            'onUpdate:checked': (value: boolean) => {
                row.toggleSelected(!!value);
                event?.stopPropagation();
            },
            'ariaLabel': 'Select row',
            'onClick': (e: Event) => e.stopPropagation()
        }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        id: 'dni',
        accessorKey: 'dni',
        header: () => 'DNI',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'foto',
        accessorKey: 'foto',
        header: () => 'Foto',
        cell: ({ row }) => h('div', {}, [
            h(Avatar, {}, {
                default: () => [
                    h(AvatarImage, {
                        src: row.original.foto || '',
                        alt: `Foto de ${row.original.nombres}`
                    }),
                    h(AvatarFallback, {}, () => getInitials(row.original.nombres, row.original.apellidos))
                ]
            })
        ]),
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'nombre_completo',
        accessorKey: 'nombre_completo',
        header: () => 'Nombre completo',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'celular',
        accessorKey: 'celular',
        header: () => 'Celular',
        cell: (info) => info.getValue(),
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'direccion',
        accessorKey: 'direccion',
        header: () => 'Dirección',
        cell: (info) => info.getValue(),
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'centro_trabajo',
        accessorKey: 'centro_trabajo',
        header: () => 'Centro de Trabajo',
        cell: (info) => info.getValue(),
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'estado',
        accessorKey: 'estado_cliente',
        header: () => 'Estado',
        cell: ({ row }) => h(Badge, {
            variant: getEstadoBadgeVariant(Number(row.original.estado_cliente) || null)
        }, () => {
            const estado = Number(row.original.estado_cliente);
            return estado === 1 ? 'Paga' : estado === 2 ? 'Moroso' : 'Sin estado';
        }),
        enableSorting: true,
    },    
    {
        id: 'fecha_inicio',
        accessorKey: 'fecha_inicio',
        header: () => 'Inicio',
        cell: ({ row }) => row.original.fecha_inicio || '11-11-2021',
        enableSorting: true,
    },
    {
        id: 'fecha_vencimiento',
        accessorKey: 'fecha_vencimiento',
        header: () => 'Vencimiento',
        cell: ({ row }) => row.original.fecha_vencimiento || '11-11-2021',
        enableSorting: true,
    },
    {
        id: 'tasa_interes_diario',
        accessorKey: 'tasa_interes_diario',
        header: () => 'Interés Diario',
        cell: ({ row }) => row.original.tasa_interes_diario ? `${row.original.tasa_interes_diario}%` : 'N/A',
        enableSorting: true,
    },
    {
        id: 'capital_inicial',
        accessorKey: 'capital_inicial',
        header: () => 'Capital Inicial',
        cell: ({ row }) => row.original.capital_inicial || 'N/A',
        enableSorting: true,
    },
    {
        id: 'capital_del_mes',
        accessorKey: 'capital_del_mes',
        header: () => 'Capital del Mes',
        cell: ({ row }) => row.original.capital_del_mes || 'N/A',
        enableSorting: true,
    },
    {
        id: 'capital_actual',
        accessorKey: 'capital_actual',
        header: () => 'Capital Actual',
        cell: ({ row }) => row.original.capital_actual || 'N/A',
        enableSorting: true,
    },
    {
        id: 'interes_actual',
        accessorKey: 'interes_actual',
        header: () => 'Interés Actual',
        cell: ({ row }) => row.original.interes_actual || 'N/A',
        enableSorting: true,
    },
    {
        id: 'interes_total',
        accessorKey: 'interes_total',
        header: () => 'Interés Total',
        cell: ({ row }) => row.original.interes_total || 'N/A',
        enableSorting: true,
    },
    {
        id: 'total',
        accessorKey: 'total',
        header: () => 'Total',
        cell: ({ row }) => row.original.total || 'N/A',
        enableSorting: true,
    },
    {
        id: 'numero_cuotas',
        accessorKey: 'numero_cuotas',
        header: () => 'Nº de Cuotas',
        cell: ({ row }) => row.original.numero_cuotas || 'N/A',
        enableSorting: true,
    },
    {
        id: 'recomendacion',
        accessorKey: 'recomendacion',
        header: () => 'Recomendación',
        cell: ({ row }) => row.original.recomendacion || 'Sin recomendación',
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'actions',
        header: () => '',
        cell: ({ row }) => h('div', { onClick: (e: Event) => e.stopPropagation() }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, () =>
                        h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () =>
                            h(MoreHorizontal, { class: 'h-4 w-4' })
                        )
                    ),
                    h(DropdownMenuContent, { align: 'end' }, () => [
                        h(DropdownMenuItem, { onClick: () => callbacks.viewClienteDetails(row.original) }, () => 'Ver detalles'),
                        h(DropdownMenuItem, { onClick: () => callbacks.editCliente(row.original) }, () => 'Editar'),
                        h(DropdownMenuSeparator),
                        h(DropdownMenuItem, {
                            onClick: () => callbacks.deleteCliente(row.original),
                            class: 'text-destructive focus:text-destructive'
                        }, () => 'Eliminar')
                    ])
                ]
            })
        ]),
        enableSorting: false,
        enableHiding: false,
    },
];