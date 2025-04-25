<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
                <Plus class="h-4" />
                Crear Nuevo
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[700px] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle class="text-lg font-semibold">Registro de Usuarios</DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Introduzca los datos personales y de contacto para registrar un nuevo usuario en el sistema.
                </DialogDescription>
            </DialogHeader>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-4 overflow-y-auto px-6 max-h-[60vh]">
                <div>
                    <Label for="dni">DNI <span class="text-red-500">*</span></Label>
                    <Input v-model="dni" type="text" placeholder="Dni" class="w-full" :class="{'border-red-500': errors.dni}" @input="resetFieldError('dni')" />
                    <p v-if="errors.dni && errors.dni.length" class="text-red-500 text-sm">{{ errors.dni[0] }}</p>
                </div>
                <div>
                    <Label for="name">Nombre <span class="text-red-500">*</span></Label>
                    <Input v-model="name" type="text" placeholder="Nombre(s)" class="w-full" :class="{'border-red-500': errors.name}" @input="resetFieldError('name')" />
                    <p v-if="errors.name && errors.name.length" class="text-red-500 text-sm">{{ errors.name[0] }}</p>
                </div>
                <div>
                    <Label for="lastname">Apellidos <span class="text-red-500">*</span></Label>
                    <Input v-model="lastname" type="text" placeholder="Apellidos" class="w-full" :class="{'border-red-500': errors.apellidos}" @input="resetFieldError('apellidos')" />
                    <p v-if="errors.apellidos && errors.apellidos.length" class="text-red-500 text-sm">{{ errors.apellidos[0] }}</p>
                </div>
                <div>
                    <Label for="birthdate">Nacimiento <span class="text-red-500">*</span></Label>
                    <Input v-model="nacimiento" class="w-full" placeholder="00-00-0000" :class="{'border-red-500': errors.nacimiento}" @input="resetFieldError('nacimiento')" />
                    <p v-if="errors.nacimiento && errors.nacimiento.length" class="text-red-500 text-sm">{{ errors.nacimiento[0] }}</p>
                </div>
                <div>
                    <Label for="username">Usuario <span class="text-red-500">*</span></Label>
                    <Input v-model="username" type="text" placeholder="Nombre de usuario" class="w-full" :class="{'border-red-500': errors.username}" @input="resetFieldError('username')" />
                    <p v-if="errors.username && errors.username.length" class="text-red-500 text-sm">{{ errors.username[0] }}</p>
                </div>
                <div>
                    <Label for="email">Email <span class="text-red-500">*</span></Label>
                    <Input v-model="email" type="email" placeholder="Correo electrónico" class="w-full" :class="{'border-red-500': errors.email}" @input="resetFieldError('email')" />
                    <p v-if="errors.email && errors.email.length" class="text-red-500 text-sm">{{ errors.email[0] }}</p>
                </div>
                <div>
                    <Label for="password">Contraseña <span class="text-red-500">*</span></Label>
                    <Input v-model="password" type="password" placeholder="Contraseña" class="w-full" :class="{'border-red-500': errors.password}" @input="resetFieldError('password')" />
                    <p v-if="errors.password && errors.password.length" class="text-red-500 text-sm">{{ errors.password[0] }}</p>
                </div>
                <div>
                    <Label for="status">Estado <span class="text-red-500">*</span></Label>
                    <Select v-model="status" :class="{'border-red-500': errors.status}" @input="resetFieldError('status')">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Estado" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="1">Activo</SelectItem>
                                <SelectItem value="0">Inactivo</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <p v-if="errors.status && errors.status.length" class="text-red-500 text-sm">{{ errors.status[0] }}</p>
                </div>
            </div>
            <DialogFooter class="p-6 pt-0">
                <Button type="submit" class="w-full sm:w-auto" @click="submitForm">
                    Guardar
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Plus } from "lucide-vue-next";
import axios from "axios";

const isOpen = ref(false);
const dni = ref("");
const name = ref("");
const lastname = ref("");
const nacimiento = ref("");
const username = ref("");
const email = ref("");
const password = ref("");
const status = ref("1");

const errors = ref({
    dni: null,
    name: null,
    lastname: null,
    nacimiento: null,
    username: null,
    email: null,
    password: null,
    status: null,
});

// Reset de error al escribir
const resetFieldError = (field: string) => {
    if (errors.value[field]) {
        errors.value[field] = null; 
    }
};

const submitForm = async () => {
    try {
        const response = await axios.post("/usuarios", {
            dni: dni.value,
            name: name.value,
            apellidos: lastname.value,
            nacimiento: nacimiento.value,
            username: username.value,
            email: email.value,
            password: password.value,
            status: status.value,
        });
        console.log("Usuario creado:", response.data);        
        errors.value = {
            dni: null,
            name: null,
            lastname: null,
            nacimiento: null,
            username: null,
            email: null,
            password: null,
            status: null,
        };

        isOpen.value = false;
    } catch (error) {
        if (error.response && error.response.data.errors) {
            errors.value = error.response.data.errors;
        }
    }
};
</script>

<style scoped>
</style>
