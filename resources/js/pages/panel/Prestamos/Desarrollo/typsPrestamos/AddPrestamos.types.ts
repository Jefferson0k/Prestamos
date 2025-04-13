import { CalendarDate } from '@internationalized/date';

export interface Cliente {
    value: number;
    label: string;
}

export interface PrestamoForm {
    cliente_id: number | null;
    fecha_inicio: string | null;
    fecha_vencimiento: string | null;
    capital: number | null;
    numero_cuotas: number | null;
    tasa_interes_diario: number | null;
    estado_cliente: number;
    recomendacion: string;
}

export interface DateRange {
    start: CalendarDate | null;
    end: CalendarDate | null;
}

export interface PrestamoFormErrors {
    cliente_id?: string[];
    fecha_inicio?: string[];
    fecha_vencimiento?: string[];
    capital?: string[];
    numero_cuotas?: string[];
    tasa_interes_diario?: string[];
    estado_cliente?: string[];
    recomendacion?: string[];
}

export interface AddPrestamoEmits {
    (e: 'prestamo-agregado', data: any): void;
}