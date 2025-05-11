<template>
    <div>
        <h1 class="text-xl font-bold mb-2">Información del perfil</h1>
        <p class="mb-6">Actualiza tu nombre y dirección de correo electrónico</p>
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Name -->
            <div class="grid gap-2">
                <label for="name" class="font-medium">Name</label>
                <InputText id="name" class="w-full" v-model="form.name" required placeholder="Full name" />
                <Message v-if="form.errors.name" severity="error" class="mt-2">{{ form.errors.name }}</Message>
            </div>
            <!-- Email -->
            <div class="grid gap-2">
                <label for="email" class="font-medium">Dirección de correo electrónico</label>
                <InputText id="email" type="email" class="w-full" v-model="form.email" required
                    placeholder="Dirección de correo electrónico" />
                <Message v-if="form.errors.email" severity="error" class="mt-2">{{ form.errors.email }}</Message>
            </div>
            <!-- Email Verification -->
            <div v-if="mustVerifyEmail && !user.email_verified_at" class="text-sm text-gray-600">
                <p>
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button"
                        class="text-blue-600 underline hover:text-blue-800 ml-1">
                        Click here to resend the verification email.
                    </Link>
                </p>
                <p v-if="status === 'verification-link-sent'" class="mt-2 text-green-600 font-medium">
                    A new verification link has been sent to your email address.
                </p>
            </div>
            <!-- Submit -->
            <div class="flex items-center gap-4">
                <Button type="submit" :loading="form.processing" label="Actualizar" />
                <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
                    leave-active-class="transition-opacity duration-300" leave-to-class="opacity-0">
                    <p v-show="form.recentlySuccessful" class="text-sm text-green-600">Saved.</p>
                </Transition>
            </div>
        </form>
        <DeleteUser />
    </div>
</template>

<script setup lang="ts">
import { useForm, usePage, Link } from '@inertiajs/vue3'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { Transition } from 'vue'
import DeleteUser from './DeleteUser.vue'

// Props recibidas desde Profile.vue
interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}
const props = defineProps<Props>();
const mustVerifyEmail = props.mustVerifyEmail;
const status = props.status;

// Obtener user desde props globales de Inertia
const page = usePage<{ auth: { user: { name: string; email: string; email_verified_at: string | null } } }>();
const user = page.props.auth.user;

// Formulario
const form = useForm({
    name: user.name,
    email: user.email,
});

// Enviar formulario
const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>
