<script setup lang="ts">
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import Password from 'primevue/password';
import Message from 'primevue/message';
import { router } from '@inertiajs/vue3';

interface PrimeVueComponent {
    focus?: () => void;
    [key: string]: any;
}

const passwordInput = ref<PrimeVueComponent | null>(null);
const currentPasswordInput = ref<PrimeVueComponent | null>(null);
const formSubmitted = ref(false);
const errorMessage = ref('');

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const logFormState = () => {
    console.log('Estado del formulario:', {
        values: {
            current_password: form.current_password ? '****' : '',
            password: form.password ? '****' : '',
            password_confirmation: form.password_confirmation ? '****' : '',
        },
        processing: form.processing,
        errors: form.errors,
    });
};

const updatePassword = () => {
    formSubmitted.value = true;
    errorMessage.value = '';
    
    // Verificar que los campos estén completos
    if (!form.current_password || !form.password || !form.password_confirmation) {
        console.error('Formulario incompleto');
        return;
    }
    
    // Verificar que las contraseñas coincidan
    if (form.password !== form.password_confirmation) {
        errorMessage.value = 'Las contraseñas no coinciden';
        return;
    }
    
    console.log('Enviando formulario...');
    
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Contraseña actualizada con éxito');
            form.reset();
            // Redireccionamos al Dashboard después de la actualización
            router.get(route('dashboard')); // ← Esto redirige al Dashboard
        },
        onError: (errors: any) => {
            console.error('Errores en el formulario:', errors);
            
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                // Enfocar el campo password usando querySelector como alternativa segura
                setTimeout(() => {
                    document.querySelector('#password input')?.focus();
                }, 100);
            }

            if (errors.current_password) {
                form.reset('current_password');
                // Enfocar el campo current_password usando querySelector como alternativa segura
                setTimeout(() => {
                    document.querySelector('#current_password input')?.focus();
                }, 100);
            }
        },
    });
};

</script>

<template>
    <FloatingConfigurator />

    <Head title="Password" />
    <div
        class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
        <div class="flex flex-col items-center justify-center">
            <div
                style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                    <div class="text-center mb-8">
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Actualizar
                            Contraseña</div>
                        <span class="text-muted-color font-medium">Asegúrate de usar una contraseña segura y difícil de
                            adivinar.</span>
                    </div>

                    <form @submit.prevent="updatePassword">
                        <div>
                            <label for="current_password"
                                class="block text-surface-900 dark:text-surface-0 text-xl font-medium mb-2">Contraseña
                                Actual</label>
                            <Password id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                                class="w-full md:w-[30rem] mb-1" toggleMask inputClass="w-full" :feedback="false" />
                            <small v-if="form.errors.current_password || (formSubmitted && !form.current_password)"
                                class="p-error">
                                {{ form.errors.current_password || 'Este campo es obligatorio' }}
                            </small>
                            <Message v-if="form.errors.current_password" severity="error" :closable="false"
                                class="mt-2">
                                {{ form.errors.current_password }}
                            </Message>
                        </div>
                        <div>
                            <label for="password"
                                class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2 mt-4">Nueva
                                Contraseña</label>
                            <Password id="password" ref="passwordInput" v-model="form.password" toggleMask
                                class="w-full md:w-[30rem] mb-1" />
                            <small v-if="form.errors.password || (formSubmitted && !form.password)" class="p-error">
                                {{ form.errors.password || 'Este campo es obligatorio' }}
                            </small>
                        </div>
                        <div>
                            <label for="password_confirmation"
                                class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2 mt-4">Confirmar
                                Contraseña</label>
                            <Password id="password_confirmation" v-model="form.password_confirmation" toggleMask
                                class="w-full md:w-[30rem] mb-1" inputClass="w-full" :feedback="false" />
                            <small
                                v-if="form.errors.password_confirmation || (formSubmitted && !form.password_confirmation)"
                                class="p-error">
                                {{ form.errors.password_confirmation || 'Este campo es obligatorio' }}
                            </small>
                        </div>
                        <br>
                        <Message v-if="errorMessage" severity="error" :closable="false" class="mb-4 w-full">
                            {{ errorMessage }}
                        </Message>
                        <Button type="submit" label="Guardar Contraseña" class="w-full" :loading="form.processing"
                            @click="logFormState" />
                        <Message v-if="form.recentlySuccessful" severity="success" :closable="false" :life="3000">
                            ¡Contraseña actualizada!
                        </Message>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}

:deep(.p-password) {
    width: 100%;
}

:deep(.p-password-input) {
    width: 100%;
}

:deep(.p-invalid) {
    border-color: var(--red-500);
}

:deep(.p-message) {
    border-width: 0;
    border-radius: 6px;
}

:deep(.p-inline-message) {
    border-width: 0;
    padding: 0.3rem 0.5rem;
    margin-top: 0.25rem;
}
</style>