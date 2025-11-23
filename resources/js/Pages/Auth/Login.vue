<template>
    <Head title="Iniciar Sesión" />

    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%)">
            <!-- Decorative Elements -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-20 w-64 h-64 border-4 border-white rounded-full"></div>
                <div class="absolute bottom-20 right-20 w-48 h-48 border-4 border-white rounded-full"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 border-4 border-white rounded-full"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center w-full p-12 text-white">
                <!-- Logo/Icon -->
                <div class="mb-8">
                    <svg class="w-32 h-32" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Barber Pole -->
                        <rect x="35" y="10" width="30" height="80" rx="15" fill="white"/>
                        <path d="M35 25 L65 35 L35 45 L65 55 L35 65 L65 75" stroke="#DC2626" stroke-width="8" stroke-linecap="round"/>
                        <circle cx="50" cy="15" r="8" fill="#DC2626"/>
                        <circle cx="50" cy="85" r="8" fill="#DC2626"/>
                    </svg>
                </div>

                <h1 class="text-5xl font-bold mb-4">Faceritos</h1>
                <p class="text-2xl font-semibold text-red-500 -mt-2">Barber Club</p>
                <p class="text-xl text-gray-300 text-center max-w-md">
                    Sistema de Gestión Profesional
                </p>

                <!-- Features -->
                <div class="mt-12 space-y-4 w-full max-w-md">
                    <div class="flex items-center gap-4 p-4 bg-white/10 rounded-lg backdrop-blur-sm">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Gestión de Citas</h3>
                            <p class="text-sm text-gray-300">Agenda y organiza turnos</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 bg-white/10 rounded-lg backdrop-blur-sm">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Control de Inventario</h3>
                            <p class="text-sm text-gray-300">Productos y servicios</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 bg-white/10 rounded-lg backdrop-blur-sm">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold">Reportes y Estadísticas</h3>
                            <p class="text-sm text-gray-300">Análisis de rendimiento</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8" :style="{ backgroundColor: 'var(--bg-primary)' }">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <svg class="w-20 h-20 mx-auto mb-4" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="35" y="10" width="30" height="80" rx="15" :fill="'var(--text-primary)'"/>
                        <path d="M35 25 L65 35 L35 45 L65 55 L35 65 L65 75" stroke="#DC2626" stroke-width="8" stroke-linecap="round"/>
                        <circle cx="50" cy="15" r="8" fill="#DC2626"/>
                        <circle cx="50" cy="85" r="8" fill="#DC2626"/>
                    </svg>
                    <h1 class="text-3xl font-bold" :style="{ color: 'var(--text-primary)' }">
                        Faceritos <span style="color: #DC2626">Barber Club</span>
                    </h1>
                </div>

                <!-- Welcome Text -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold mb-2" :style="{ color: 'var(--text-primary)' }">
                        ¡Bienvenido de vuelta! 💈
                    </h2>
                    <p :style="{ color: 'var(--text-secondary)' }">
                        Ingresa tus credenciales para continuar
                    </p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200">
                    <p class="text-sm font-medium text-green-600">{{ status }}</p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full px-4 py-3 rounded-lg border-2 transition-all focus:outline-none focus:border-red-500"
                            :style="{ 
                                backgroundColor: 'var(--bg-primary)',
                                borderColor: form.errors.email ? '#EF4444' : 'var(--text-secondary)',
                                color: 'var(--text-primary)'
                            }"
                            placeholder="tu@email.com"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-primary)' }">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-lg border-2 transition-all focus:outline-none focus:border-red-500"
                            :style="{ 
                                backgroundColor: 'var(--bg-primary)',
                                borderColor: form.errors.password ? '#EF4444' : 'var(--text-secondary)',
                                color: 'var(--text-primary)'
                            }"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ml-2 text-sm" :style="{ color: 'var(--text-primary)' }">
                                Recordarme
                            </span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-medium hover:underline"
                            style="color: #DC2626"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 rounded-lg font-semibold text-white transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        style="background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%)"
                    >
                        <span v-if="!form.processing">Iniciar Sesión</span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Iniciando...
                        </span>
                    </button>

                    <!-- Register Link -->
                    <div class="text-center pt-4">
                        <p :style="{ color: 'var(--text-secondary)' }">
                            ¿No tienes una cuenta?
                            <Link :href="route('register')" class="font-medium hover:underline ml-1" style="color: #DC2626">
                                Regístrate aquí
                            </Link>
                        </p>
                    </div>
                </form>

                <!-- Footer -->
                <div class="mt-8 text-center text-sm" :style="{ color: 'var(--text-secondary)' }">
                    <p>© 2025 Faceritos Barber Club. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
