export const getColumnWidthClass = (columnId: string): string => {
    const columnWidths: Record<string, string> = {
        'select': 'w-10',
        'dni': 'w-28',
        'foto': 'w-14',
        'nombres': 'w-10 min-w-[20px]',
        'apellidos': 'w-40 min-w-[120px]',
        'celular': 'w-32',
        'direccion': 'w-48 min-w-[180px] max-w-md',
        'centro_trabajo': 'w-40 min-w-[150px] max-w-md',
        'estado': 'w-28',
        'fecha_inicio': 'w-32',
        'fecha_vencimiento': 'w-32',
        'tasa_interes_diario': 'w-36',
        'capital_inicial': 'w-32',
        'capital_del_mes': 'w-32',
        'capital_actual': 'w-32',
        'interes_actual': 'w-32',
        'interes_total': 'w-32',
        'total': 'w-28',
        'numero_cuotas': 'w-24',
        'recomendacion': 'w-48 min-w-[180px] max-w-xs',
        'actions': 'w-10'
    };

    return columnWidths[columnId] || '';
};

export function getEstadoBadgeVariant(estado: number | null): "default" | "secondary" | "destructive" | "outline" | null {
    switch (estado) {
        case 1:
            return "destructive";
        case 2:
            return "secondary";
        case 3:
            return "destructive";
        default:
            return "outline";
    }
}


export const getInitials = (firstName: string = '', lastName: string = ''): string => {
    return `${firstName.charAt(0) || ''}${lastName.charAt(0) || ''}`.toUpperCase() || 'NA';
};