<template>
    <Card class="mb-4">
        <CardContent class="pt-6 pb-4">
            <div class="flex items-center justify-between mb-4">
                <AddUsuario />
            </div>
        </CardContent>
    </Card>
        <ListUsuario />
</template>
<script setup lang="ts">
import AddUsuario from './AddUsuario.vue';
import ListUsuario from './ListUsuario.vue';
import {
    Card,
    CardContent
} from '@/components/ui/card'
</script>