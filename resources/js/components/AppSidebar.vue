<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { DollarSign, FileText, Folder, LayoutGrid, Shield, Users, UserSquare,BookOpen } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Clientes',
        href: '/clientes',
        icon: Users,
    },
    {
        title: 'Préstamos',
        href: '/prestamos',
        icon: Folder,
    },
    {
        title: 'Pagos',
        href: '/pagos',
        icon: DollarSign,
    },
    {
        title: 'Reportes',
        href: '/reportes',
        icon: FileText,
    },
    {
        title: 'Usuarios',
        href: '/usuario',
        icon: UserSquare,
    },
];

const mainNavItems2: NavItem[] = [
    {
        title: 'Seguridad',
        href: '#',
        icon: Shield,
        items: [
            {
                title: 'Usuarios',
                href: '/panel/users',
            },
            {
                title: 'Roles',
                href: '#',
            },
            {
                title: 'Permisos',
                href: '#',
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                        <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" :items2="mainNavItems2" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
