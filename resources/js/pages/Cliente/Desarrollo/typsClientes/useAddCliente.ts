import { ref, reactive } from "vue";
import { ClienteForm, ClienteFormErrors } from "./AddCliente.types";
import axios from "axios";
import { useToast } from "@/components/ui/toast/use-toast";
import { ToastAction } from "@/components/ui/toast";
import { h } from "vue";

export function useAddCliente(emit: (event: 'cliente-added') => void) {
    const { toast } = useToast();
    const isOpen = ref(false);
    const loading = ref(false);

    const form = reactive<ClienteForm>({
        dni: "",
        nombre: "",
        apellidos: "",
        telefono: "",
        direccion: "",
        correo: "",
        centro_trabajo: "",
        foto: null,
    });

    const errors = reactive<ClienteFormErrors>({
        dni: "",
        nombre: "",
        apellidos: "",
        telefono: "",
        direccion: "",
        correo: "",
        centro_trabajo: "",
        foto: "",
    });

    const handleKeydown = (event: KeyboardEvent, nextFieldId: string | null) => {
        if (event.key === "Enter") {
            event.preventDefault();
            if (nextFieldId) {
                const nextInput = document.getElementById(nextFieldId);
                if (nextInput) {
                    nextInput.focus();
                }
            }
        }
    };

    const handleFileChange = (event: Event) => {
        const input = event.target as HTMLInputElement;
        if (input.files && input.files.length > 0) {
            form.foto = input.files[0];
        }
    };

    const resetForm = () => {
        form.dni = "";
        form.nombre = "";
        form.apellidos = "";
        form.telefono = "";
        form.direccion = "";
        form.correo = "";
        form.centro_trabajo = "";
        form.foto = null;

        Object.keys(errors).forEach((key) => {
            errors[key as keyof typeof errors] = "";
        });
    };

    const submitForm = async () => {
        loading.value = true;
        Object.keys(errors).forEach((key) => {
            errors[key as keyof typeof errors] = "";
        });
        try {
            const formData = new FormData();
            formData.append("dni", form.dni);
            formData.append("nombre", form.nombre);
            formData.append("apellidos", form.apellidos);
            formData.append("telefono", form.telefono);
            formData.append("direccion", form.direccion);
            formData.append("correo", form.correo);
            formData.append("centro_trabajo", form.centro_trabajo);
            if (form.foto) {
                formData.append("foto", form.foto);
            }
            const response = await axios.post("/cliente", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });
            toast({
                title: "¡Éxito!",
                description: "Cliente registrado exitosamente",
            });
            emit('cliente-added');
            isOpen.value = false;
            resetForm();
        } catch (error: any) {
            if (error.response && error.response.data && error.response.data.errors) {
                const validationErrors = error.response.data.errors;
                Object.keys(validationErrors).forEach((field) => {
                    errors[field as keyof typeof errors] = validationErrors[field][0];
                });
            } else {
                toast({
                    title: "Error al registrar cliente",
                    description: "Ocurrió un problema con la solicitud",
                    variant: "destructive",
                    action: h(
                        ToastAction,
                        {
                            altText: "Intentar de nuevo",
                        },
                        {
                            default: () => "Intentar de nuevo",
                        }
                    ),
                });
            }
        } finally {
            loading.value = false;
        }
    };

    return {
        isOpen,
        form,
        errors,
        loading,
        handleKeydown,
        handleFileChange,
        resetForm,
        submitForm
    };
}