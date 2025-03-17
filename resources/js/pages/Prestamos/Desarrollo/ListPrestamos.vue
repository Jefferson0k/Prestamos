<template>
    <AddPrestamos/>
    <Card class="my-6">
        <CardHeader>
            <CardTitle>Listado de Prestamos</CardTitle>
            <CardDescription>Aquí puedes ver todos los prestamos registrados requeridos por los clientes</CardDescription>
        </CardHeader>
        <CardContent>
            
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
    import { ref } from 'vue';
    import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
    import AddPrestamos from './AddPrestamos.vue';
</script>
