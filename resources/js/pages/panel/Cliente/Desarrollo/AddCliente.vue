<script setup lang="ts">
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
import { Plus } from 'lucide-vue-next';
import { useAddCliente } from './typsClientes/useAddCliente';
import { AddClienteEmits } from './typsClientes/AddCliente.types';

const emit = defineEmits<AddClienteEmits>();
const {
    isOpen,
    form,
    errors,
    loading,
    handleKeydown,
    handleFileChange,
    submitForm
} = useAddCliente(emit);
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
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
                        <Input id="dni" type="number" v-model="form.dni" placeholder="DNI"
                            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'nombre')" />
                        <InputError v-if="errors.dni" :message="errors.dni" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="nombre">Nombre <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="nombre" type="text" v-model="form.nombre" placeholder="Nombre"
                            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'apellidos')" />
                        <InputError v-if="errors.nombre" :message="errors.nombre" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="apellidos">Apellidos <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="apellidos" type="text" v-model="form.apellidos" placeholder="Apellidos"
                            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'telefono')" />
                        <InputError v-if="errors.apellidos" :message="errors.apellidos" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="telefono">Nº Teléfono <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="telefono" type="number" v-model="form.telefono" placeholder="Teléfono"
                            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'direccion')" />
                        <InputError v-if="errors.telefono" :message="errors.telefono" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="direccion">Dirección <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="direccion" type="text" v-model="form.direccion" placeholder="Dirección"
                            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'correo')" />
                        <InputError v-if="errors.direccion" :message="errors.direccion" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="correo">Correo Electrónico <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="correo" type="email" v-model="form.correo" placeholder="email@example.com"
                            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'centro_trabajo')" />
                        <InputError v-if="errors.correo" :message="errors.correo" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="centro_trabajo">Centro de Trabajo <span class="text-red-500 text-sm">*</span></Label>
                        <Input id="centro_trabajo" type="text" v-model="form.centro_trabajo"
                            placeholder="Centro de Trabajo" @keydown="(e: KeyboardEvent) => handleKeydown(e, 'foto')" />
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