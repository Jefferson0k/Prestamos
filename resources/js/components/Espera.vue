<template>
    <div>
        <div class="card">
            <Skeleton height="5rem" class="mb-2" borderRadius="16px"></Skeleton>
            <br />
            <DataTable :value="products">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <Skeleton width="20rem" height="4rem" borderRadius="16px"></Skeleton>
                        <div class="flex flex-wrap gap-2">
                            <Skeleton width="10rem" height="4rem" borderRadius="16px"></Skeleton>
                        </div>
                    </div>
                </template>

                <Column field="code" header="">
                    <template #body>
                        <Skeleton></Skeleton>
                    </template>
                </Column>
                <Column field="Nombre" header="">
                    <template #body>
                        <Skeleton></Skeleton>
                    </template>
                </Column>
                <Column field="Estado" header="">
                    <template #body>
                        <Skeleton></Skeleton>
                    </template>
                </Column>
                <Column field="Fecha" header="">
                    <template #body>
                        <Skeleton></Skeleton>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Skeleton from 'primevue/skeleton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const products = ref(new Array(15));
</script>
