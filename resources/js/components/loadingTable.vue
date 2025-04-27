<template>
    <div class="w-full space-y-4">
        <!-- Buscador deshabilitado -->
        <div class="flex items-center justify-between">
            <div class="max-w-sm w-full">
                <Skeleton class="h-10 w-full rounded-md" />
            </div>
            <Skeleton class="h-10 w-24 rounded-md" />
        </div>

        <!-- Tabla -->
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead v-for="n in props.headers" :key="n">
                        <Skeleton class="h-4 w-full rounded-md" />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="rowIndex in props.rowCount" :key="rowIndex">
                    <TableCell v-for="colIndex in props.headers" :key="colIndex">
                        <Skeleton class="h-4 w-full rounded-md" />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>

<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

const props = withDefaults(
    defineProps<{
        headers?: number
        rowCount?: number
    }>(),
    {
        headers: 5,
        rowCount: 10,
    },
)
</script>

<style scoped></style>