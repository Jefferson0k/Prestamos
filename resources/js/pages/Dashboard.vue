<script setup>
import StatsWidget from '@/components/dashboard/StatsWidget.vue';
import AppLayout from '@/layout/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>
    <Head title="Dashboard"></Head>
    <AppLayout>
        <div class="grid grid-cols-12 gap-8">
        <StatsWidget />
    </div>
    </AppLayout>
</template>
