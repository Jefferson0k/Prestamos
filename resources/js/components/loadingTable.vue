<template>
    <div class="w-full">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead v-for="n in props.headers" :key="n" class="w-[100px]">
                        <Skeleton class="h-4 w-full" />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="rowIndex in rowCount" :key="rowIndex">
                    <TableCell v-for="ColumnIndex in props.headers" :key="ColumnIndex">
                        <Skeleton class="h-4 w-full" />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
<script setup lang="ts">
import { Skeleton } from './ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';

const props = withDefaults(
    defineProps<{
        headers?: number;
        rowCount?: number;
    }>(),
    {
        headers: 7,
        rowCount: 15,
    },
);
</script>
<style scoped></style>
