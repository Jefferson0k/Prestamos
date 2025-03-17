<script setup lang="ts">
    import { ref } from 'vue';
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogContent,
        DialogFooter,
        DialogHeader,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="outline">Nuevo</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[600px] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle class="text-lg font-semibold">Registro de Prestamos</DialogTitle>
            </DialogHeader>
            <div class="grid grid-cols-2 gap-4 py-4 overflow-y-auto px-6 max-h-[60vh]">
                <div class="grid gap-2">
                    <Label for="dni">DNI <span class="text-red-500 text-sm">*</span></Label>
                    <Input id="dni" type="number" placeholder="Dni" />
                </div>
                <div class="grid gap-2">
                    <Label for="nombre">Nombre <span class="text-red-500 text-sm">*</span></Label>
                    <Input id="nombre" type="text" placeholder="Nombre" />
                </div>
                <div class="grid gap-2">
                    <Label for="apellido">Apellido <span class="text-red-500 text-sm">*</span></Label>
                    <Input id="apellido" type="text" placeholder="Apellido"/>
                </div>
                <div class="grid gap-2">
                    <Label for="telefono">Nº Teléfono <span class="text-red-500 text-sm">*</span></Label>
                    <Input id="telefono" type="number" placeholder="Telefono"/>
                </div>
                <div class="grid gap-2">
                    <Label for="direccion">Dirección <span class="text-red-500 text-sm">*</span></Label>
                    <Input id="direccion" type="text" placeholder="Direccion" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email address <span class="text-red-500 text-sm">*</span></Label>
                    <Input id="email" type="email" required autofocus :tabindex="1" autocomplete="email"
                        placeholder="email@example.com" />
                </div>
                <div class="grid gap-2">
                    <Label for="centro_trabajo">Centro de Trabajo <span class="text-red-500 text-sm">*</span></Label>
                    <Input id="centro_trabajo" type="text" placeholder="Centro Trabajo" />
                </div>
                <div class="grid gap-2">
                    <Label for="foto">Foto <span class="text-red-500 text-sm">*</span></Label>
                    <Input id="foto" type="file" />
                </div>
            </div>
            <DialogFooter class="p-6 pt-0 flex justify-end">
                <Button type="submit">Guardar</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
