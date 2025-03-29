import { h } from 'vue';
import { ColumnDef } from '@tanstack/vue-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { MoreHorizontal } from 'lucide-vue-next';
import { Cliente } from './listcliente';

// Action handlers - define these in your component
const verDetalleCliente = (cliente: Cliente) => {};
const editarCliente = (cliente: Cliente) => {};
const eliminarCliente = (cliente: Cliente) => {};

export const columns: ColumnDef<Cliente>[] = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'checked': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': value => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            'checked': row.getIsSelected(),
            'onUpdate:checked': (value) => {
                row.toggleSelected(!!value);
                event?.stopPropagation();
            },
            'ariaLabel': 'Select row',
            'onClick': (e) => e.stopPropagation()
        }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        id: 'id',
        accessorKey: 'id',
        header: () => 'ID',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'id_cliente',
        accessorKey: 'id_cliente',
        header: () => 'ID Cliente',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'dni',
        accessorKey: 'dni',
        header: () => 'DNI',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'NombreCompleto',
        accessorKey: 'NombreCompleto',
        header: () => 'Nombre Completo',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'fecha_inicio',
        accessorKey: 'fecha_inicio',
        header: () => 'Fecha Inicio',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'fecha_vencimiento',
        accessorKey: 'fecha_vencimiento',
        header: () => 'Fecha Vencimiento',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'capital',
        accessorKey: 'capital',
        header: () => 'Capital',
        cell: (info) => `S/ ${Number(info.getValue()).toFixed(2)}`,
        enableSorting: true,
    },
    {
        id: 'numero_cuotas',
        accessorKey: 'numero_cuotas',
        header: () => 'N° Cuotas',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'estado_cliente',
        accessorKey: 'estado_cliente',
        header: () => 'Estado',
        cell: ({ row }) => {
            const estado = row.original.estado_cliente;
            return h(Badge, {
                variant: estado === 1 ? 'success' : 'destructive'
            }, () => estado === 1 ? 'Activo' : 'Inactivo');
        },
        enableSorting: true,
    },
    {
        id: 'recomendacion',
        accessorKey: 'recomendacion',
        header: () => 'Recomendación',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'tasa_interes_diario',
        accessorKey: 'tasa_interes_diario',
        header: () => 'Tasa Interés Diario',
        cell: (info) => `${Number(info.getValue()).toFixed(2)}%`,
        enableSorting: true,
    },
    {
        id: 'actions',
        header: () => '',
        cell: ({ row }) => h('div', { onClick: (e) => e.stopPropagation() }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, () =>
                        h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () =>
                            h(MoreHorizontal, { class: 'h-4 w-4' })
                        )
                    ),
                    h(DropdownMenuContent, { align: 'end' }, () => [
                        h(DropdownMenuItem, { onClick: () => verDetalleCliente(row.original) }, () => 'Ver detalles'),
                        h(DropdownMenuItem, { onClick: () => editarCliente(row.original) }, () => 'Editar'),
                        h(DropdownMenuSeparator),
                        h(DropdownMenuItem, {
                            onClick: () => eliminarCliente(row.original),
                            class: 'text-destructive focus:text-destructive'
                        }, () => 'Eliminar')
                    ])
                ]
            })
        ]),
        enableSorting: false,
    }
];
