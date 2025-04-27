<script setup lang="ts">
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Plus, LoaderCircle } from "lucide-vue-next";
import InputError from "@/components/InputError.vue";
import { useAddCliente } from "./typsClientes/useAddCliente";
import { AddClienteEmits } from "./typsClientes/AddCliente.types";

const emit = defineEmits<AddClienteEmits>();
const {
  isOpen,
  form,
  errors,
  loading,
  loadingDni,
  buscarPorDni,
  handleKeydown,
  handleFileChange,
  submitForm,
} = useAddCliente(emit);
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button>
        <Plus class="h-4 mr-2" />
        Crear Cliente
      </Button>
    </DialogTrigger>

    <DialogContent
      class="w-full max-w-[95vw] sm:max-w-[600px] p-0 rounded-xl shadow-lg max-h-[90dvh] overflow-hidden"
    >
      <DialogHeader class="p-6 pb-0">
        <DialogTitle class="text-lg font-semibold">
          Registro de Cliente
        </DialogTitle>
        <DialogDescription class="text-sm text-muted-foreground">
          Introduzca los datos personales y de contacto para registrar un nuevo cliente en el sistema.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submitForm" class="overflow-y-auto px-6 py-4 space-y-4 max-h-[60vh]">
        <!-- DNI -->
        <div class="space-y-2">
          <Label for="dni">DNI <span class="text-red-500 text-sm">*</span></Label>
          <div class="relative">
            <Input
              id="dni"
              type="text"
              inputmode="numeric"
              pattern="[0-9]*"
              maxlength="8"
              v-model="form.dni"
              placeholder="DNI"
              :class="{ 'border-red-500': errors.dni }"
              @keydown="(e: KeyboardEvent) => {
                if (e.key === 'Enter') {
                  e.preventDefault();
                  buscarPorDni();
                }
              }"
            />
            <LoaderCircle
              v-if="loadingDni"
              class="absolute right-2 top-1/2 transform -translate-y-1/2 h-5 w-5 animate-spin"
            />
          </div>
          <InputError v-if="errors.dni" :message="errors.dni" />
        </div>

        <!-- Nombre -->
        <div class="space-y-2">
          <Label for="nombre">Nombre <span class="text-red-500 text-sm">*</span></Label>
          <Input
            id="nombre"
            type="text"
            v-model="form.nombre"
            placeholder="Nombre"
            disabled
            :class="{ 'border-red-500': errors.nombre }"
          />
          <InputError v-if="errors.nombre" :message="errors.nombre" />
        </div>

        <!-- Apellidos -->
        <div class="space-y-2">
          <Label for="apellidos">Apellidos <span class="text-red-500 text-sm">*</span></Label>
          <Input
            id="apellidos"
            type="text"
            v-model="form.apellidos"
            placeholder="Apellidos"
            disabled
            :class="{ 'border-red-500': errors.apellidos }"
          />
          <InputError v-if="errors.apellidos" :message="errors.apellidos" />
        </div>

        <!-- Dirección -->
        <div class="space-y-2">
          <Label for="direccion">Dirección <span class="text-red-500 text-sm">*</span></Label>
          <Textarea
            id="direccion"
            v-model="form.direccion"
            placeholder="Dirección"
            disabled
            :class="{ 'border-red-500': errors.direccion }"
          />
          <InputError v-if="errors.direccion" :message="errors.direccion" />
        </div>

        <!-- Teléfono -->
        <div class="space-y-2">
          <Label for="telefono">Nº Teléfono <span class="text-red-500 text-sm">*</span></Label>
          <Input
            id="telefono"
            type="number"
            v-model="form.telefono"
            placeholder="Teléfono"
            :class="{ 'border-red-500': errors.telefono }"
            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'correo')"
          />
          <InputError v-if="errors.telefono" :message="errors.telefono" />
        </div>

        <!-- Correo -->
        <div class="space-y-2">
          <Label for="correo">Correo Electrónico <span class="text-red-500 text-sm">*</span></Label>
          <Input
            id="correo"
            type="email"
            v-model="form.correo"
            placeholder="email@example.com"
            :class="{ 'border-red-500': errors.correo }"
            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'centro_trabajo')"
          />
          <InputError v-if="errors.correo" :message="errors.correo" />
        </div>

        <!-- Centro de trabajo -->
        <div class="space-y-2">
          <Label for="centro_trabajo">Centro de Trabajo <span class="text-red-500 text-sm">*</span></Label>
          <Input
            id="centro_trabajo"
            type="text"
            v-model="form.centro_trabajo"
            placeholder="Centro de Trabajo"
            :class="{ 'border-red-500': errors.centro_trabajo }"
            @keydown="(e: KeyboardEvent) => handleKeydown(e, 'foto')"
          />
          <InputError v-if="errors.centro_trabajo" :message="errors.centro_trabajo" />
        </div>

        <!-- Foto -->
        <div class="space-y-2">
          <Label for="foto">Foto <span class="text-red-500 text-sm">*</span></Label>
          <Input
            id="foto"
            type="file"
            accept="image/jpeg,image/png,image/jpg"
            @change="handleFileChange"
            :class="{ 'border-red-500': errors.foto }"
          />
          <InputError v-if="errors.foto" :message="errors.foto" />
        </div>
      </form>

      <DialogFooter class="p-6 pt-0">
        <Button type="submit" :disabled="loading" @click="submitForm">
          {{ loading ? "Guardando..." : "Guardar" }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
