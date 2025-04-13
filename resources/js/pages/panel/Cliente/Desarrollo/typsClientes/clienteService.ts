import { useToast } from '@/components/ui/toast';
import { Cliente } from './typesCliente';

export const useClienteService = () => {
    const { toast } = useToast();   
    const fetchClientes = async (page = 1, pageSize = 10, search = ''): Promise<{ data: Cliente[], total: number;
        pagination: { currentPage: number; lastPage: number; perPage: number; total: number; } }> => {
        try {
            const response = await fetch(`/cliente?page=${page}&per_page=${pageSize}&search=${encodeURIComponent(search)}`);
            const result = await response.json();
    
            if (result && result.data && result.meta) {
                return {
                    data: result.data.map((cliente: any) => formatClienteData(cliente)),
                    total: result.total || 0, // Asegúrate de 
                    pagination: {
                        currentPage: result.meta.current_page,
                        lastPage: result.meta.last_page,
                        perPage: result.meta.per_page,
                        total: result.meta.total,
                    },
                };
            } else {
                handleApiError('Formato de respuesta inesperado');
                return { data: [],total: 0 , pagination: { currentPage: 1, lastPage: 1, perPage: pageSize, total: 0 } };
            }
        } catch (error) {
            handleApiError('Error al obtener clientes', error);
            return { data: [], total: 0 , pagination: { currentPage: 1, lastPage: 1, perPage: pageSize, total: 0 } };
        }
    };
    
    const formatClienteData = (clienteData: any): Cliente => {
        return {
            ...clienteData,
            nombres: clienteData.nombres || '',
            apellidos: clienteData.apellidos || '',
            direccion: clienteData.direccion || '',
            centro_trabajo: clienteData.centro_trabajo || '',
            celular: clienteData.celular || '',
            dni: clienteData.dni || '',
            foto: clienteData.foto || '',
            recomendacion: clienteData.recomendacion || '',
            capital_del_mes: Number(clienteData.capital_del_mes || 0),
            capital_actual: Number(clienteData.capital_actual || 0),
            interes_actual: Number(clienteData.interes_actual || 0),
            interes_total: Number(clienteData.interes_total || 0),
            total: Number(clienteData.total || 0),
        };
    };

    const handleApiError = (message: string, error?: any) => {
        console.error(message, error);
        toast({
            title: 'Error',
            description: typeof error === 'string' ? error : message,
            variant: 'destructive',
        });
    };

    const notifySuccess = (title: string, description: string) => {
        toast({
            title,
            description,
        });
    };

    return {
        fetchClientes,
        formatClienteData,
        handleApiError,
        notifySuccess,
    };
};
