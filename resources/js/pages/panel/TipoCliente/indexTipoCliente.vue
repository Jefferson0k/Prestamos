<template>

    <Head title="Clientes" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera />
            </template>

            <template v-else>
                <div class="card">
                    <AddTipoCliente @tipo-cliente-agregado="refrescarListado"/>
                    <ListTipoCliente :refresh="refreshKey"/>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Espera from '@/components/Espera.vue';
import ListTipoCliente from './Desarollo/ListTipoCliente.vue';
import AddTipoCliente from './Desarollo/AddTipoCliente.vue';

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
