<template>
    <Card>
        <CardHeader>
            <CardTitle>Usuarios</CardTitle>
            <CardDescription>ss.</CardDescription>
        </CardHeader>
        <CardContent>
            <LoadingTable/>
        </CardContent>
        <CardFooter class="flex justify-between px-6 pb-6">
            
        </CardFooter>
    </Card>
</template>
<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {
    useVueTable,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    FlexRender
} from '@tanstack/vue-table';
import LoadingTable from '@/components/loadingTable.vue';

</script>