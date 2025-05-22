<template>
    <Head title="Roles y Permisos" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera />
            </template>
            <template v-else>
                <div class="card">
                    <AddRoles @rol-agregado="refrescarListado" />
                    <ListRoles :refresh="refreshKey" />
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import ListRoles from './Desarrollo/ListRoles.vue';
import AddRoles from './Desarrollo/AddRoles.vue';
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
