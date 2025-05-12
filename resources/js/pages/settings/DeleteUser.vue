<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';

const visible = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    visible.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <br>
    <br>
    <br>
    <div class="space-y-4">
        <!-- Encabezado -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">ELiminar Cuenta</h2>
            <br>
            <p>Elimina tu cuenta y todos sus recursos..</p>
        </div>

        <!-- Advertencia -->
         <br>
        <Message severity="error" :closable="false">
            <h4>Advertencia</h4>
            <p>This action is permanent and cannot be undone.</p>
            <Button label="Delete account" severity="danger" @click="visible = true"/>
        </Message>

        <!-- Botón para abrir el diálogo -->

        <!-- Diálogo de confirmación -->
        <Dialog v-model:visible="visible" modal header="Are you sure?" :style="{ width: '30rem' }">
            <form @submit="deleteUser" class="flex flex-col gap-4">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Once your account is deleted, all data will be lost permanently.
                    Please enter your password to confirm this action.
                </p>

                <div class="flex flex-col gap-1">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <InputText
                        id="password"
                        type="password"
                        v-model="form.password"
                        ref="passwordInput"
                        placeholder="Enter your password"
                        class="w-full"
                    />
                    <small class="text-red-500" v-if="form.errors.password">{{ form.errors.password }}</small>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Cancel" severity="secondary" @click="closeModal" type="button" />
                    <Button
                        label="Delete"
                        severity="danger"
                        type="submit"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </Dialog>
    </div>
</template>
