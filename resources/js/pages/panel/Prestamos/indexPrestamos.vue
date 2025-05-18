<template>
    <Head title="Prestamos" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera />
            </template>
            <template v-else>
                <div class="card">

                    <AddPrestamos @prestamoAgregado="handlePrestamoAgregado" />
                    <ListPrestamos ref="listRef" :refresh="refresh" />
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import ListPrestamos from './Desarrollo/ListPrestamos.vue';
import AddPrestamos from './Desarrollo/AddPrestamos.vue';
import { Head } from '@inertiajs/vue3';
import Espera from '@/components/Espera.vue';

const isLoading = ref(true);
const refresh = ref(0);
const listRef = ref();

const handlePrestamoAgregado = () => {
    refresh.value++;
};

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});
</script>
