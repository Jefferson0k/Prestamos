<script setup lang="ts">
import { ref, reactive } from "vue";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import InputError from "@/components/InputError.vue";
import { useToast } from "@/components/ui/toast/use-toast";
import { ToastAction } from "@/components/ui/toast";
import { h } from "vue";
import axios from "axios";
import { Plus } from 'lucide-vue-next'

const { toast } = useToast();
const isOpen = ref(false);
const emit = defineEmits(['cliente-added']);

const form = reactive({
    dni: "",
    nombre: "",
    apellidos: "",
    telefono: "",
    direccion: "",
    correo: "",
    centro_trabajo: "",
    foto: null as File | null,
});

const errors = reactive({
    dni: "",
    nombre: "",
    apellidos: "",
    telefono: "",
    direccion: "",
    correo: "",
    centro_trabajo: "",
    foto: "",
});

const loading = ref(false);

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
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline"  size="sm">
                <Plus class="h-4"></Plus>
                Create New
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[600px] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle class="text-lg font-semibold">Registro de Cliente</DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Introduzca los datos personales y de contacto para registrar un nuevo cliente en el sistema.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-2 gap-4 py-4 overflow-y-auto px-6 max-h-[60vh]">
                    <div class="grid gap-2">
                        <Label for="dni">DNI <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="dni" type="number" v-model="form.dni" placeholder="Dni"
                            @keydown="(e) => handleKeydown(e, 'nombre')" />
                        <InputError v-if="errors.dni" :message="errors.dni" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="nombre">Nombre <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="nombre" type="text" v-model="form.nombre" placeholder="Nombre"
                            @keydown="(e) => handleKeydown(e, 'apellidos')" />
                        <InputError v-if="errors.nombre" :message="errors.nombre" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="apellidos">Apellidos <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="apellidos" type="text" v-model="form.apellidos" placeholder="Apellidos"
                            @keydown="(e) => handleKeydown(e, 'telefono')" />
                        <InputError v-if="errors.apellidos" :message="errors.apellidos" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="telefono">Nº Teléfono <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="telefono" type="number" v-model="form.telefono" placeholder="Telefono"
                            @keydown="(e) => handleKeydown(e, 'direccion')" />
                        <InputError v-if="errors.telefono" :message="errors.telefono" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="direccion">Dirección <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="direccion" type="text" v-model="form.direccion" placeholder="Direccion"
                            @keydown="(e) => handleKeydown(e, 'correo')" />
                        <InputError v-if="errors.direccion" :message="errors.direccion" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="correo">Email address <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="correo" type="email" v-model="form.correo" required autofocus :tabindex="1"
                            autocomplete="email" placeholder="email@example.com"
                            @keydown="(e) => handleKeydown(e, 'centro_trabajo')" />
                        <InputError v-if="errors.correo" :message="errors.correo" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="centro_trabajo">Centro de Trabajo <span
                                class="text-red-500 text-sm">*</span></Label>
                        <Input id="centro_trabajo" type="text" v-model="form.centro_trabajo"
                            placeholder="Centro Trabajo" @keydown="(e) => handleKeydown(e, 'foto')" />
                        <InputError v-if="errors.centro_trabajo" :message="errors.centro_trabajo" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="foto">Foto <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="foto" type="file" @change="handleFileChange"
                            accept="image/jpeg,image/png,image/jpg" />
                        <InputError v-if="errors.foto" :message="errors.foto" />
                    </div>
                </div>
                <DialogFooter class="p-6 pt-0">
                    <Button type="submit" :disabled="loading">
                        {{ loading ? "Guardando..." : "Guardar" }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
