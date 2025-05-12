<script setup>
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import InlineMessage from 'primevue/inlinemessage';

defineProps({
    status: String,
    canResetPassword: Boolean
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <FloatingConfigurator />
    <Head title="Log in" />
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
        <div class="flex flex-col items-center justify-center">
            <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                    <div class="text-center mb-8">
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">¡Bienvenido!</div>
                        <span class="text-muted-color font-medium">Inicia sesión para continuar</span>
                    </div>
                    
                    <Message v-if="status" severity="success" :closable="false" class="mb-4">{{ status }}</Message>
                    
                    <form @submit.prevent="submit">
                        <div>
                            <label for="username" class="block text-surface-900 dark:text-surface-0 text-xl font-medium mb-2">Usuario</label>
                            <InputText 
                                id="username" 
                                type="text" 
                                placeholder="Usuario" 
                                class="w-full md:w-[30rem] mb-1" 
                                v-model="form.username"
                                :class="{ 'p-invalid': form.errors.username }"
                                autofocus
                                required
                                autocomplete="username"
                            />
                            <InlineMessage v-if="form.errors.username" severity="error" class="w-full mb-4">{{ form.errors.username }}</InlineMessage>
                            
                            <label for="password" class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2 mt-4">Contraseña</label>
                            <Password 
                                id="password" 
                                v-model="form.password" 
                                placeholder="Contraseña" 
                                :toggleMask="true" 
                                class="w-full mb-1" 
                                :class="{ 'p-invalid': form.errors.password }"
                                :feedback="false"
                                required
                                autocomplete="current-password"
                                inputClass="w-full"
                            />
                            <InlineMessage v-if="form.errors.password" severity="error" class="w-full mb-4">{{ form.errors.password }}</InlineMessage>
                            
                            <div class="flex items-center justify-between mt-4 mb-8 gap-8">
                                <div class="flex items-center">
                                    <Checkbox v-model="form.remember" id="remember" binary class="mr-2"></Checkbox>
                                    <label for="remember" class="text-surface-600 dark:text-surface-300">Recordarme</label>
                                </div>
                                <a 
                                    v-if="canResetPassword" 
                                    :href="route('password.request')" 
                                    class="font-medium no-underline ml-2 text-right cursor-pointer text-primary"
                                >
                                    ¿Olvidó su contraseña?
                                </a>
                            </div>
                            
                            <Button 
                                type="submit" 
                                label="Iniciar sesión" 
                                class="w-full" 
                                :loading="form.processing"
                                :disabled="form.processing"
                            />
                        </div>
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