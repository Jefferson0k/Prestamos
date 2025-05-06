<script setup lang="ts">
import { onMounted, shallowRef, ref, markRaw } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Skeleton from 'primevue/skeleton';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import { defineAsyncComponent } from 'vue';
import ReportCapitalEmprestado from './Desarrollo/ReportCapitalEmprestado.vue';

const isLoading = ref(true);
const activeTab = ref('0');

const ReportInteresMensuales = shallowRef();
const ReportNumeroClientes = shallowRef();
const ReportNumeroMorosos = shallowRef();

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});

const handleTabChange = (val: string | number) => {
    activeTab.value = String(val);

    if (val === '1' && !ReportInteresMensuales.value) {
        ReportInteresMensuales.value = markRaw(defineAsyncComponent(() => import('./Desarrollo/ReportInteresMensuales.vue')));
    } else if (val === '2' && !ReportNumeroClientes.value) {
        ReportNumeroClientes.value = markRaw(defineAsyncComponent(() => import('./Desarrollo/ReportNumeroClientes.vue')));
    } else if (val === '3' && !ReportNumeroMorosos.value) {
        ReportNumeroMorosos.value = markRaw(defineAsyncComponent(() => import('./Desarrollo/ReportNumeroMorosos.vue')));
    }
};
</script>

<template>
    <Head title="Reportes" />
    <AppLayout>
        <div class="card">
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
            <template v-else>
                <Tabs :value="activeTab" @update:value="handleTabChange">
                    <TabList>
                        <Tab value="0" as="div" class="flex items-center gap-2">
                            <span class="font-bold whitespace-nowrap">Capital Total Emprestada</span>
                        </Tab>
                        <Tab value="1" as="div" class="flex items-center gap-2">
                            <span class="font-bold whitespace-nowrap">Interés Generado en el mes</span>
                        </Tab>
                        <Tab value="2" as="div" class="flex items-center gap-2">
                            <span class="font-bold whitespace-nowrap">Número de Clientes</span>
                        </Tab>
                        <Tab value="3" as="div" class="flex items-center gap-2">
                            <span class="font-bold whitespace-nowrap">Número de Morosos</span>
                        </Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel value="0">
                            <ReportCapitalEmprestado />
                        </TabPanel>
                        <TabPanel value="1">
                            <component :is="ReportInteresMensuales" v-if="ReportInteresMensuales" />
                        </TabPanel>
                        <TabPanel value="2">
                            <component :is="ReportNumeroClientes" v-if="ReportNumeroClientes" />
                        </TabPanel>
                        <TabPanel value="3">
                            <component :is="ReportNumeroMorosos" v-if="ReportNumeroMorosos" />
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </template>
        </div>
    </AppLayout>
</template>
