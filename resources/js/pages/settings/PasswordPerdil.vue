<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

// PrimeVue components
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Message from 'primevue/message';

const currentPasswordInput = ref<HTMLInputElement | null>(null);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.updateProfile'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }

            if (errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Encabezado -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Actualizar contraseña</h2>
            <p>
                Asegúrese de que su cuenta esté usando una contraseña larga y aleatoria para mantenerse segura.
            </p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-6">
            <!-- Contraseña actual -->
            <div class="flex flex-col gap-2">
                <label for="current_password" class="text-sm font-medium">Contraseña actual</label>
                <InputText
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Contraseña actual"
                    class="w-full"
                />
                <small class="text-red-500" v-if="form.errors.current_password">
                    {{ form.errors.current_password }}
                </small>
            </div>

            <!-- Nueva contraseña -->
            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium">Nueva contraseña</label>
                <InputText
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Nueva contraseña"
                    class="w-full"
                />
                <small class="text-red-500" v-if="form.errors.password">
                    {{ form.errors.password }}
                </small>
            </div>

            <!-- Confirmación -->
            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-sm font-medium">Confirmar contraseña</label>
                <InputText
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Confirmar contraseña"
                    class="w-full"
                />
                <small class="text-red-500" v-if="form.errors.password_confirmation">
                    {{ form.errors.password_confirmation }}
                </small>
            </div>

            <!-- Botón y mensaje -->
            <div class="flex items-center gap-4">
                <Button
                    label="Actualizar"
                    type="submit"
                    :loading="form.processing"
                    severity="primary"
                />
                <transition
                    enter-active-class="transition-opacity duration-300"
                    enter-from-class="opacity-0"
                    leave-active-class="transition-opacity duration-300"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                        Saved.
                    </p>
                </transition>
            </div>
        </form>
    </div>
</template>
