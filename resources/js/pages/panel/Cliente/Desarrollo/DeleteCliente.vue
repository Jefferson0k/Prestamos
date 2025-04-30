<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    visible: Boolean,
    product: Object,
});
const emit = defineEmits(['update:visible', 'deleted']);

const toast = useToast();

const localVisible = ref(false);

watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

function closeDialog() {
    emit('update:visible', false);
}

async function deleteProduct() {
    try {
        await axios.delete(`/cliente/${props.product.id}`);
        emit('deleted');
        closeDialog();
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Cliente eliminado correctamente', life: 3000 });
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error eliminando el cliente', life: 3000 });
    }
}
</script>
<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '450px' }" header="Confirmar" :modal="true"
        @update:visible="closeDialog">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="product">¿Estás seguro de eliminar al cliente <b>{{ product.nombre_completo }}</b>?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteProduct" />
        </template>
    </Dialog>
</template>