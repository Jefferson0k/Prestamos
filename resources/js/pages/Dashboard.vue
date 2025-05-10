<script setup>
import StatsWidget from '@/components/dashboard/StatsWidget.vue';
import AppLayout from '@/layout/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Password from './settings/Password.vue';

const page = usePage();
const mustReset = page.props.mustReset;
</script>

<template>

    <Head title="Dashboard" />

    <div v-if="mustReset">
        <div>
            <Password />
        </div>
    </div>

    <AppLayout v-else>
        <div class="grid grid-cols-12 gap-8">
            <StatsWidget />
        </div>
    </AppLayout>
</template>
