import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from '@/components/ui/toast';
import { CalendarDate } from '@internationalized/date';
import { Cliente, DateRange, PrestamoFormErrors } from './AddPrestamos.types';

export function useAddPrestamo(emit: (event: 'prestamo-agregado', data: any) => void) {
    const isOpen = ref(false);
    const loading = ref(false);
    const clientes = ref<Cliente[]>([]);
    const selectedCliente = ref<Cliente | null>(null);
    const errors = ref<PrestamoFormErrors>({});

    const today = new Date();

    const dateRange = ref<DateRange>({
        start: new CalendarDate(
            today.getFullYear(),
            today.getMonth() + 1,
            today.getDate()
        ),
        end: null
    });

    const capital = ref<number | null>(null);
    const numeroCuotas = ref<number | null>(null);
    const tasaInteresDiario = ref<number | null>(null);
    const recomendacion = ref('');
    const estadoCliente = ref(1);

    const recommendationCharLimit = 255;
    const remainingChars = computed(() => {
        return recommendationCharLimit - (recomendacion.value?.length || 0);
    });

    const isDateDisabled = (date: CalendarDate) => {
        const currentDate = new CalendarDate(
            today.getFullYear(),
            today.getMonth() + 1,
            today.getDate()
        );
        return date.compare(currentDate) < 0;
    };

    const fetchClientes = async (query = '', page = 1) => {
        try {
            const { data } = await axios.get('/prestamo/cliente', {
                params: { search: query, page }
            });
            clientes.value = data.data;
        } catch (error) {
            console.error('Error al cargar clientes:', error);
            toast({
                title: "Error",
                description: "No se pudieron cargar los clientes",
                variant: "destructive"
            });
        }
    };

    const handleSubmit = async () => {
        errors.value = {};
        loading.value = true;

        const submissionData = {
            cliente_id: selectedCliente.value?.value,
            fecha_inicio: dateRange.value.start?.toString(),
            fecha_vencimiento: dateRange.value.end?.toString(),
            capital: capital.value,
            numero_cuotas: numeroCuotas.value,
            tasa_interes_diario: tasaInteresDiario.value,
            estado_cliente: estadoCliente.value,
            recomendacion: recomendacion.value
        };

        try {
            const response = await axios.post('/prestamo', submissionData);

            toast({
                title: "¡Éxito!",
                description: "Préstamo registrado exitosamente",
            });
            emit('prestamo-agregado', response.data);

            isOpen.value = false;
            resetForm();
        } catch (error: any) {
            if (error.response?.data?.errors) {
                errors.value = error.response.data.errors;
                toast({
                    title: "Error al guardar",
                    description: "Por favor, revise los datos ingresados",
                    variant: "destructive"
                });
            }
        } finally {
            loading.value = false;
        }
    };

    const resetForm = () => {
        selectedCliente.value = null;
        dateRange.value = {
            start: new CalendarDate(
                today.getFullYear(),
                today.getMonth() + 1,
                today.getDate()
            ),
            end: null
        };
        capital.value = null;
        numeroCuotas.value = null;
        tasaInteresDiario.value = null;
        recomendacion.value = '';
        estadoCliente.value = 1;
        errors.value = {};
    };

    onMounted(() => {
        fetchClientes();
    });

    return {
        isOpen,
        loading,
        clientes,
        selectedCliente,
        errors,
        dateRange,
        capital,
        numeroCuotas,
        tasaInteresDiario,
        recomendacion,
        estadoCliente,
        today,
        recommendationCharLimit,
        remainingChars,
        isDateDisabled,
        fetchClientes,
        handleSubmit,
        resetForm
    };
}