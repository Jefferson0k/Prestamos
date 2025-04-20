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
                <DialogTitle class="text-lg font-semibold">Registro de Usuarios</DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Introduzca los datos personales y de contacto para registrar un nuevo cliente en el sistema.
                </DialogDescription>
            </DialogHeader>
            <div class="grid grid-cols-1 gap-4 py-4 overflow-y-auto px-6 max-h-[60vh]">
            
            </div>
            <DialogFooter class="p-6 pt-0">
                <Button type="submit" >
                    Guardando...
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from "vue";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Plus } from 'lucide-vue-next';
const isOpen = ref(false);
</script>