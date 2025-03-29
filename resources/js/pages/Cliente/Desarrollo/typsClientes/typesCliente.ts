export interface Cliente {
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
    nombre_completo?: string;
}

export type EstadoCliente = 1 | 2 | null;

export interface TableState {
    sorting: SortingState;
    columnVisibility: VisibilityState;
    columnFilters: ColumnFiltersState;
    pagination: {
        pageIndex: number;
        pageSize: number;
    };
}

import type { 
    SortingState, 
    VisibilityState, 
    ColumnFiltersState,
    ColumnDef
} from '@tanstack/vue-table';

export type { 
    SortingState, 
    VisibilityState, 
    ColumnFiltersState,
    ColumnDef 
};
