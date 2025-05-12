<template>
    <AppLayout>

        <Head title="Perfil" />
        <div class="card">
            <template v-if="isLoading">
                <div
                    class="rounded border border-surface-200 dark:border-surface-700 p-10 bg-surface-0 dark:bg-surface-900">
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
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold mb-1">Configuración</h1>
                    <p class="py-4">Administra tu perfil y la configuración de tu cuenta</p>
                </div>
                <Tabs value="0">
                    <TabList>
                        <Tab value="0">Perfil</Tab>
                        <Tab value="1">Contraseña</Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel value="0">
                            <perfil :mustVerifyEmail="mustVerifyEmail" :status="status" />
                        </TabPanel>
                        <TabPanel value="1">
                            <PasswordPerdil />
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </template>
        </div>
    </AppLayout>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layout/AppLayout.vue';
import Skeleton from 'primevue/skeleton';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import PasswordPerdil from './PasswordPerdil.vue';
import perfil from './perfil.vue';

const isLoading = ref(true);
const page = usePage();

// Obtener las propiedades necesarias para el componente perfil
const mustVerifyEmail = page.props.mustVerifyEmail || false;
const status = page.props.status || null;

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});
</script>
<style scoped></style>