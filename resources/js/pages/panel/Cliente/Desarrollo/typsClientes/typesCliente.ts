// src/components/clientes/typsClientes/typesCliente.ts
import { SortingState as TanstackSortingState } from '@tanstack/vue-table';
import { VisibilityState as TanstackVisibilityState } from '@tanstack/vue-table';
import { ColumnFiltersState as TanstackColumnFiltersState } from '@tanstack/vue-table';

export interface Cliente {
  id: number;
  nombres: string;
  apellidos: string;
  direccion: string;
  centro_trabajo: string;
  celular: string;
  dni: string;
  foto: string;
  correo?: string;
  telefono?: string;
  recomendacion?: string;
  capital_del_mes: number;
  capital_actual: number;
  interes_actual: number;
  interes_total: number;
  total: number;
  created_at?: string;
  updated_at?: string;
  estado_cliente?: string;
  prestamos?: Prestamo[];
}

export interface Prestamo {
  id: number;
  cliente_id: number;
  monto: number;
  interes: number;
  fecha_prestamo: string;
  fecha_vencimiento: string;
  estado: string;
  pagos?: Pago[];
}

export interface Pago {
  id: number;
  prestamo_id: number;
  monto: number;
  fecha_pago: string;
  tipo: string;
}

export type SortingState = TanstackSortingState;
export type VisibilityState = TanstackVisibilityState;
export type ColumnFiltersState = TanstackColumnFiltersState;