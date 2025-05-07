<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    visible: Boolean,
    prestamo: Object,
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

async function deletePrestamo() {
    try {
        await axios.delete(`/prestamo/${props.prestamo.id}/destroy`);
        emit('deleted');
        closeDialog();
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Prestamo eliminado correctamente', life: 3000 });
    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando el Prestamo';
        if (error.response) {
            errorMessage = error.response.data.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>
<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '450px' }" header="Confirmar" :modal="true"
        @update:visible="closeDialog">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="prestamo">¿Estás seguro de eliminar este prestamo <b>{{ prestamo.NombreCompleto }}</b> ?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deletePrestamo" />
        </template>
    </Dialog>
</template>