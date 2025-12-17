<template>
    <Head title="Crear Cuenta" />

    <div class="min-h-screen flex">
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%)">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-20 w-64 h-64 border-4 border-white rounded-full"></div>
                <div class="absolute bottom-20 right-20 w-48 h-48 border-4 border-white rounded-full"></div>
            </div>

            <div class="relative z-10 flex flex-col justify-center items-center w-full p-12 text-white">
                <div class="mb-8">
                    <svg class="w-32 h-32" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="35" y="10" width="30" height="80" rx="15" fill="white"/>
                        <path d="M35 25 L65 35 L35 45 L65 55 L35 65 L65 75" stroke="#DC2626" stroke-width="8" stroke-linecap="round"/>
                        <circle cx="50" cy="15" r="8" fill="#DC2626"/>
                        <circle cx="50" cy="85" r="8" fill="#DC2626"/>
                    </svg>
                </div>
                <h1 class="text-5xl font-bold mb-4 text-center">Únete al Club</h1>
                <p class="text-2xl font-semibold text-red-500 -mt-2">Faceritos Barber Club</p>
                <p class="text-xl text-gray-300 text-center max-w-md mt-4">
                    Crea tu cuenta y agenda tu próxima cita en segundos.
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 overflow-y-auto" :style="{ backgroundColor: 'var(--bg-primary)' }">
            <div class="w-full max-w-md py-8">
                <div class="lg:hidden text-center mb-8">
                    <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
                        Faceritos <span style="color: #DC2626">Barber Club</span>
                    </h1>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
                        Crea tu cuenta 💈
                    </h2>
                    <p :style="{ color: 'var(--text-secondary)' }">
                        Forma parte de nuestra comunidad exclusiva
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--text-primary)' }">Nombre</label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-2 rounded-lg border-2 transition-all focus:border-red-500 outline-none"
                                :style="{ backgroundColor: 'var(--bg-primary)', borderColor: 'var(--text-secondary)', color: 'var(--text-primary)' }"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--text-primary)' }">Apellido</label>
                            <input
                                v-model="form.apellido"
                                type="text"
                                class="w-full px-4 py-2 rounded-lg border-2 transition-all focus:border-red-500 outline-none"
                                :style="{ backgroundColor: 'var(--bg-primary)', borderColor: 'var(--text-secondary)', color: 'var(--text-primary)' }"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--text-primary)' }">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-4 py-2 rounded-lg border-2 transition-all focus:border-red-500 outline-none"
                            :style="{ backgroundColor: 'var(--bg-primary)', borderColor: 'var(--text-secondary)', color: 'var(--text-primary)' }"
                            placeholder="tu@email.com"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--text-primary)' }">Teléfono</label>
                        <input
                            v-model="form.telefono"
                            type="text"
                            class="w-full px-4 py-2 rounded-lg border-2 transition-all focus:border-red-500 outline-none"
                            :style="{ backgroundColor: 'var(--bg-primary)', borderColor: 'var(--text-secondary)', color: 'var(--text-primary)' }"
                            placeholder="+591 ..."
                        />
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--text-primary)' }">Contraseña</label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                class="w-full px-4 py-2 rounded-lg border-2 transition-all focus:border-red-500 outline-none"
                                :style="{ backgroundColor: 'var(--bg-primary)', borderColor: 'var(--text-secondary)', color: 'var(--text-primary)' }"
                                placeholder="••••••••"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--text-primary)' }">Confirmar Contraseña</label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="w-full px-4 py-2 rounded-lg border-2 transition-all focus:border-red-500 outline-none"
                                :style="{ backgroundColor: 'var(--bg-primary)', borderColor: 'var(--text-secondary)', color: 'var(--text-primary)' }"
                                placeholder="••••••••"
                            />
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 mt-4 rounded-lg font-semibold text-white transition-all transform hover:scale-[1.02] active:scale-95 disabled:opacity-50"
                        style="background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%)"
                    >
                        {{ form.processing ? 'Registrando...' : 'Crear mi cuenta' }}
                    </button>

                    <div class="text-center pt-4">
                        <p :style="{ color: 'var(--text-secondary)' }">
                            ¿Ya tienes una cuenta?
                            <Link :href="route('login')" class="font-medium hover:underline ml-1" style="color: #DC2626">
                                Inicia sesión aquí
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    apellido: '',
    email: '',
    telefono: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>