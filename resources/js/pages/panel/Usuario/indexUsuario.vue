<template>
    <Head title="Usuarios" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera />
            </template>
            <template v-else>
                <div class="card">
                    <AddUsuario @usuario-agregado="refrescarListado" />
                    <ListUsuario :refresh="refreshKey" />
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import ListUsuario from './Desarrollo/ListUsuario.vue';
import AddUsuario from './Desarrollo/AddUsuario.vue';
import { Head } from '@inertiajs/vue3';
import Espera from '@/components/Espera.vue';

const isLoading = ref(true);
const refreshKey = ref(0);

function refrescarListado() {
    refreshKey.value++;
}

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});
</script>
