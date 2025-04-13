<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const cuotaId = ref('');
const fechaPago = ref('');
const cuotas = ref([]);
const prestamos = ref([]);
const selectedPrestamo = ref(null);

onMounted(async () => {
    try {
        const response = await axios.get('/pago/create');
        prestamos.value = response.data;
    } catch (error) {
        console.error('Error al obtener los préstamos:', error);
    }
});

const cargarCuotas = async () => {
    if (!selectedPrestamo.value) return;
    try {
        const response = await axios.get(`/pago/1/cuotas`);
        cuotas.value = response.data;
    } catch (error) {
        console.error('Error al obtener cuotas:', error);
    }
};

const guardarPago = async () => {
    try {
        await axios.post('/pago', {
            cuota_id: 1,
            fecha_pago: fechaPago.value,
        });
        alert('Pago registrado correctamente.');
    } catch (error) {
        console.error('Error al registrar el pago:', error);
    }
};
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="outline">Agregar Pago</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Registrar Pago</DialogTitle>
                <DialogDescription>Selecciona la cuota y la fecha para registrar el pago.</DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div>
                    <Label>Seleccionar Préstamo</Label>
                    <Select v-model="selectedPrestamo" @update:model-value="cargarCuotas">
                        <SelectTrigger>
                            <SelectValue placeholder="Selecciona un préstamo" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="prestamo in prestamos" :key="prestamo.id" :value="prestamo.id">
                                {{ prestamo.cliente.nombre }} - {{ prestamo.id }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <Label>Seleccionar Cuota</Label>
                    <Select v-model="cuotaId">
                        <SelectTrigger>
                            <SelectValue placeholder="Selecciona una cuota" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="cuota in cuotas" :key="cuota.id" :value="cuota.id">
                                Cuota #{{ cuota.numero_cuota }} - {{ cuota.monto }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <Label>Fecha de Pago</Label>
                    <Input v-model="fechaPago" type="date" />
                </div>
            </div>
            <DialogFooter>
                <Button @click="guardarPago">Guardar Pago</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
