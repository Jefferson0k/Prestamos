<template>
    <Head title="Roles y Permisos" />
    <AppLayout>
        <div>
            <div class="card">
                <!-- Mostrar skeleton si está cargando -->
                <template v-if="isLoading">
                    <div class="rounded border border-surface-200 dark:border-surface-700 p-10 bg-surface-0 dark:bg-surface-900">
                        <div class="flex items-center mb-8">
                            <Skeleton shape="circle" size="6rem" class="mr-6" />
                            <div>
                                <Skeleton width="15rem" height="2rem" class="mb-4" />
                                <Skeleton width="8rem" height="1.5rem" class="mb-2" />
                                <Skeleton width="12rem" height="0.75rem" />
                            </div>
                        </div>
                        <Skeleton width="100%" height="500px" class="mb-8" />
                        <div class="flex justify-between mt-6">
                            <Skeleton width="6rem" height="3rem" />
                            <Skeleton width="6rem" height="3rem" />
                        </div>
                    </div>
                </template>

                <!-- Mostrar contenido real cuando ya esté listo -->
                <template v-else>
                    <AddRoles @rol-agregado="refrescarListado" />
                    <ListRoles :refresh="refreshKey"/>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import ListRoles from './Desarrollo/ListRoles.vue';
import AddRoles from './Desarrollo/AddRoles.vue';
import { Head } from '@inertiajs/vue3';
import Skeleton from 'primevue/skeleton';

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
