<template>

    <Head title="Detalle del Prestamo" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <div class="card">
                    <Fieldset>
                        <template #legend>
                            <div class="flex items-center pl-2">
                                <Skeleton shape="circle" size="4rem" class="mr-2"></Skeleton>
                                <span class="font-bold p-2">
                                    <Skeleton class="mb-2" borderRadius="16px"></Skeleton>
                                </span>
                            </div>
                        </template>
                        <p class="m-0">
                        <div class="flex flex-wrap gap-6 py-4">
                            <div class="flex-1">
                                <Skeleton class="mb-2" borderRadius="16px"></Skeleton>
                            </div>
                            <div class="flex-1">
                                <Skeleton class="mb-2" borderRadius="16px"></Skeleton>
                            </div>
                            <div class="flex-1">
                                <Skeleton class="mb-2" borderRadius="16px"></Skeleton>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-6">
                            <div class="flex-1">
                                <Skeleton class="mb-2" borderRadius="16px"></Skeleton>
                            </div>
                            <div class="flex-1">
                                <Skeleton class="mb-2" borderRadius="16px"></Skeleton>
                            </div>
                            <div class="flex-1">
                                <Skeleton class="mb-2" borderRadius="16px"></Skeleton>
                            </div>
                        </div>
                        </p>
                    </Fieldset>
                </div>
            </template>
            <template v-else>
                <div class="card">
                    <Fieldset>
                        <template #legend>
                            <div class="flex items-center pl-2">
                                <Avatar :image="prestamos.data.foto" shape="circle" />
                                <span class="font-bold p-2">{{ prestamos.data.nombre }}</span>
                            </div>
                        </template>
                        <p class="m-0">
                        <div class="flex flex-wrap gap-6 py-4">
                            <div class="flex-1"><strong>Nombre:</strong> {{ prestamos.data.nombre }}</div>
                            <div class="flex-1"><strong>DNI:</strong> {{ prestamos.data.dni }}</div>
                            <div class="flex-1">
                                <strong>Capital:</strong>
                                <Tag severity="success" :value="'S/ ' + prestamos.data.capital"></Tag>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-6">
                            <div class="flex-1"><strong>Inicio:</strong> {{ prestamos.data.Fecha_Inicio }}</div>
                            <div class="flex-1"><strong>Vencimiento:</strong> {{ prestamos.data.Fecha_Vencimiento }}
                            </div>
                            <div class="flex-1">
                                <strong>I. Diario:</strong>
                                <Tag severity="info" :value="prestamos.data.tasa_interes_diario + '%'" />
                            </div>
                        </div>
                        </p>
                    </Fieldset>
                </div>
            </template>
            <template v-if="isLoading">
                <Espera />
            </template>
            <template v-else>
                <div class="card py-4">
                    <AddClientePago />
                    <ListClientePago :idPrestamo="prestamos.data.idPrestamo" :refresh="refresh" />
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Espera from '@/components/Espera.vue'
import ListClientePago from './Desarrollo/ListClientePago.vue';
import AddClientePago from './Desarrollo/AddClientePago.vue';
import Fieldset from 'primevue/fieldset';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';
import Skeleton from 'primevue/skeleton';

const isLoading = ref(true);
const refresh = ref(0);
const listRef = ref();

defineProps({
    prestamos: Object,
    state: Boolean,
    message: String
});

const handleListado = () => {
    refresh.value++;
};

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});
</script>
