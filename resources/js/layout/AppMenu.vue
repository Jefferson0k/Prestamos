<script setup>
import { ref } from 'vue';
import AppMenuItem from './AppMenuItem.vue';

const model = ref([
    {
        label: 'Home',
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/dashboard' }
        ]
    },
    {
        label: 'Gestión Administrativa',
        items: [
            { label: 'Clientes', icon: 'pi pi-fw pi-users', to: '/clientes' },
            { label: 'Préstamos', icon: 'pi pi-fw pi-briefcase', to: '/prestamos' },
            { label: 'Pagos', icon: 'pi pi-fw pi-credit-card', to: '/pagos' },
            { label: 'Reportes', icon: 'pi pi-fw pi-chart-line', to: '/reportes' }
        ]
    },
    {
        label: 'Usuarios',
        items: [
            { label: 'Gestión de Usuarios', icon: 'pi pi-fw pi-user-edit', to: '/usuario' },
            { label: 'Roles', icon: 'pi pi-fw pi-check-square', to: '/roles' },
        ]
    }
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="i">
            <app-menu-item v-if="!item.separator" :item="item" :index="i" />
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped>
/* Puedes agregar estilos si quieres aquí */
</style>
