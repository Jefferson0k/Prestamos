<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppMenuItem from './AppMenuItem.vue';
import Avatar from 'primevue/avatar';

const page = usePage();

// Acceder a permisos del usuario
const permissions = computed(() => page.props.auth.user?.permissions ?? []);
const hasPermission = (perm) => permissions.value.includes(perm);

// Estructura del menú
const model = computed(() => [
    {
        label: 'Home',
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/dashboard' }
        ]
    },
    {
        label: 'Gestión Administrativa',
        items: [
            hasPermission('ver tipo_cliente') && { label: 'Tipos Clientes', icon: 'pi pi-fw pi-id-card', to: '/tipos-clientes' },
            hasPermission('ver clientes') && { label: 'Clientes', icon: 'pi pi-fw pi-users', to: '/clientes' },
            hasPermission('ver prestamos') && { label: 'Préstamos', icon: 'pi pi-fw pi-briefcase', to: '/prestamos' },
            hasPermission('ver pagos') && { label: 'Pagos', icon: 'pi pi-fw pi-credit-card', to: '/pagos' },
            hasPermission('ver reportes') && { label: 'Reportes', icon: 'pi pi-fw pi-chart-line', to: '/reportes' }
        ].filter(Boolean),
    },
    {
        label: 'Usuarios',
        items: [
            hasPermission('ver usuarios') && { label: 'Gestión de Usuarios', icon: 'pi pi-fw pi-user-edit', to: '/usuario' },
            hasPermission('ver roles') && { label: 'Roles', icon: 'pi pi-fw pi-check-square', to: '/roles' },
        ].filter(Boolean),
    }
].filter(section => section.items.length > 0));
</script>

<template>
    <!-- Perfil del usuario centrado -->
    <div class="flex flex-col items-center justify-center text-center p-4">
        <Avatar 
            image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png"
            size="xlarge"
            shape="circle"
            class="mb-3 !w-32 !h-32"
        />
        <div class="text-base font-medium">{{ page.props.auth.user.name }}</div>
        <div class="text-sm font-bold">1234567890</div>
    </div>

    <!-- Menú lateral -->
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="i">
            <app-menu-item :item="item" :index="i" />
        </template>
    </ul>
</template>

<style scoped lang="scss">

</style>