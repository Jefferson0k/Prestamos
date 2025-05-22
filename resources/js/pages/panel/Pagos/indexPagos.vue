<template>
    <Head title="Pagos" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera />
            </template>
            <template v-else>
                <div class="card">
                  <AddPago/>
                  <ListPagos/>
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
import AddPago from './Desarrollo/AddPago.vue';
import ListPagos from './Desarrollo/ListPagos.vue';

const isLoading = ref(true);
const refresh = ref(0);
const listRef = ref();

const handleListado = () => {
    refresh.value++;
};

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});
</script>
