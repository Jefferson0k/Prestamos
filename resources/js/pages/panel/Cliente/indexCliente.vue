<template>

    <Head title="Clientes" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera />
            </template>

            <template v-else>
                <div class="card">
                    <AddCliente @cliente-agregado="refrescarListado" />
                    <ListCliente :refresh="refreshKey" />
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import ListCliente from './Desarrollo/ListCliente.vue';
import AddCliente from './Desarrollo/AddCliente.vue';
import { Head } from '@inertiajs/vue3';
import Espera from '@/components/Espera.vue';

const refreshKey = ref(0);
const isLoading = ref(true);

function refrescarListado() {
    refreshKey.value++;
}

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});
</script>
