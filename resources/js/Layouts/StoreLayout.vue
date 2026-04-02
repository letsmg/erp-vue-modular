<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { 
    Search, ShoppingBag, Cloud, User as UserIcon, 
    Settings, Package, LogOut, ChevronDown 
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { onClickOutside } from '@vueuse/core';

const page = usePage();
const auth = computed(() => page.props.auth);

// Dropdown state
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

onClickOutside(dropdownRef, () => {
    isDropdownOpen.value = false;
});

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

// Recebe o valor da busca do Index
defineProps({
    searchTerm: String
});

// Avisa o Index que o usuário digitou algo
const emit = defineEmits(['update:searchTerm']);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-slate-200 to-slate-100 text-slate-900 font-sans pb-20">
        <!-- ... resto do template ... -->
        <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white py-2 px-6 flex justify-center items-center gap-4 shadow-md">
            <div class="flex items-center gap-2">
                <Cloud class="w-4 h-4 animate-pulse" />
                <span class="text-[10px] font-black uppercase tracking-widest">Infraestrutura Oracle Cloud Ativa</span>
            </div>
            <a href="https://whatismyipaddress.com/ip/147.15.80.52" target="_blank" class="text-[10px] font-bold underline hover:text-orange-100 transition">
                Verificar IP da Instância →
            </a>
        </div>

        <nav class="sticky top-0 z-50 bg-slate-900 shadow-2xl">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link href="/" class="text-2xl font-black tracking-tighter uppercase text-white">
                    Erp<span class="text-indigo-500">Vue Laravel</span>
                </Link>
                
                <div class="hidden md:flex flex-1 max-w-md mx-10 relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                    <input 
                        :value="searchTerm" 
                        @input="$emit('update:searchTerm', $event.target.value)"
                        type="text" 
                        placeholder="Buscar na loja..."
                        class="w-full bg-slate-800 border-transparent rounded-2xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:bg-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all outline-none"
                    />
                </div>

                <div class="flex items-center gap-6">
                    <!-- Se logado como cliente -->
                    <div v-if="auth.user && auth.user.is_client" class="relative" ref="dropdownRef">
                        <button 
                            @click="toggleDropdown"
                            class="flex items-center gap-3 bg-slate-800 hover:bg-slate-700 px-4 py-2.5 rounded-2xl border border-slate-700 transition-all group shadow-xl"
                        >
                            <div class="bg-primary p-1.5 rounded-xl group-hover:scale-110 transition-transform shadow-lg shadow-primary/20">
                                <UserIcon class="w-3.5 h-3.5 text-white" />
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-300 group-hover:text-white transition-colors">
                                    Olá, {{ auth.user.first_name }}
                                </span>
                                <span class="text-[8px] font-bold uppercase tracking-tight text-slate-500">Minha Conta</span>
                            </div>
                            <ChevronDown 
                                class="w-4 h-4 text-slate-500 group-hover:text-white transition-all"
                                :class="{ 'rotate-180': isDropdownOpen }"
                            />
                        </button>

                        <!-- Submenu Dropdown -->
                        <transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="transform scale-95 opacity-0 -translate-y-2"
                            enter-to-class="transform scale-100 opacity-100 translate-y-0"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="transform scale-100 opacity-100 translate-y-0"
                            leave-to-class="transform scale-95 opacity-0 -translate-y-2"
                        >
                            <div 
                                v-if="isDropdownOpen"
                                class="absolute right-0 mt-3 w-56 bg-white rounded-3xl shadow-2xl border border-slate-100 py-3 z-[60] overflow-hidden"
                            >
                                <div class="px-5 py-3 border-b border-slate-50 mb-2 bg-slate-50/50">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Navegação Rápida</p>
                                </div>

                                <Link 
                                    :href="route('client.dashboard')"
                                    class="flex items-center gap-3 px-5 py-3.5 text-slate-600 hover:bg-slate-50 hover:text-primary transition-all group"
                                    @click="isDropdownOpen = false"
                                >
                                    <div class="p-2 bg-slate-100 rounded-xl group-hover:bg-primary/10 transition-colors">
                                        <Settings class="w-4 h-4 group-hover:text-primary" />
                                    </div>
                                    <span class="text-xs font-black uppercase tracking-widest">Meus Dados</span>
                                </Link>

                                <Link 
                                    href="#"
                                    class="flex items-center gap-3 px-5 py-3.5 text-slate-600 hover:bg-slate-50 hover:text-primary transition-all group"
                                    @click="isDropdownOpen = false"
                                >
                                    <div class="p-2 bg-slate-100 rounded-xl group-hover:bg-primary/10 transition-colors">
                                        <Package class="w-4 h-4 group-hover:text-primary" />
                                    </div>
                                    <span class="text-xs font-black uppercase tracking-widest">Meus Pedidos</span>
                                </Link>

                                <div class="h-px bg-slate-100 my-2 mx-5"></div>

                                <Link 
                                    :href="route('client.logout')" 
                                    method="post" 
                                    as="button"
                                    class="w-full flex items-center gap-3 px-5 py-3.5 text-red-500 hover:bg-red-50 transition-all group"
                                    @click="isDropdownOpen = false"
                                >
                                    <div class="p-2 bg-red-50 rounded-xl group-hover:bg-red-100 transition-colors">
                                        <LogOut class="w-4 h-4" />
                                    </div>
                                    <span class="text-xs font-black uppercase tracking-widest text-left">Sair da Conta</span>
                                </Link>
                            </div>
                        </transition>
                    </div>

                    <!-- Se não logado ou logado como staff -->
                    <div v-else class="flex flex-col items-end">
                        <Link v-if="!auth.user" :href="route('client.login')" class="text-[9px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition">Área do Cliente</Link>
                        <Link v-if="auth.user && auth.user.is_staff" :href="route('dashboard')" class="text-[9px] font-black uppercase tracking-widest text-primary hover:text-white transition font-bold">Painel Admin</Link>
                        <Link v-if="!auth.user" :href="route('login')" class="text-[9px] font-black uppercase tracking-widest text-slate-500 hover:text-white transition">Painel Admin</Link>
                    </div>

                    <button class="bg-primary text-white p-3 rounded-2xl hover:bg-primary-hover transition shadow-lg relative shadow-primary/20">
                        <ShoppingBag class="w-5 h-5" />
                        <span class="absolute -top-1 -right-1 bg-white text-primary text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-black shadow-sm">0</span>
                    </button>
                </div>
            </div>
        </nav>

        <slot />

        <footer class="max-w-7xl mx-auto px-6 mt-20 text-center text-slate-400 text-xs font-bold uppercase tracking-widest border-t border-slate-300 pt-10">
            &copy; 2026 Erp Vue Laravel - SaaS Edition
        </footer>
    </div>
</template>