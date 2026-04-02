<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    Home, User, ShoppingCart, Package, Heart, LogOut, Menu, X, ArrowLeft
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value.user);

// Mobile
const isMobileMenuOpen = ref(false);
const toggleMobileMenu = () => isMobileMenuOpen.value = !isMobileMenuOpen.value;

watch(() => page.url, () => {
    isMobileMenuOpen.value = false;
});

// Menu cliente
const menuCliente = [
    { nome: 'Início', icone: Home, rota: route('client.dashboard'), ativo: true },
    { nome: 'Meus Dados', icone: User, rota: route('client.profile'), ativo: true },
    { nome: 'Pedidos', icone: Package, rota: '#', ativo: false },
    { nome: 'Carrinho', icone: ShoppingCart, rota: route('store.index'), ativo: true },
    { nome: 'Favoritos', icone: Heart, rota: '#', ativo: false }
];
</script>

<template>
<div class="min-h-screen bg-slate-50 flex overflow-x-hidden font-sans">

    <!-- Overlay -->
    <div v-if="isMobileMenuOpen"
        @click="isMobileMenuOpen = false"
        class="fixed inset-0 bg-slate-900/60 z-30 md:hidden backdrop-blur-sm transition-all">
    </div>

    <!-- Sidebar -->
    <aside :class="[
        'fixed inset-y-0 left-0 w-72 bg-white border-r border-slate-100 flex flex-col z-40 transition-all duration-300 md:translate-x-0 shadow-2xl md:shadow-none',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
    ]">

        <!-- Logo / Loja -->
        <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-900">
            <div>
                <Link href="/" class="text-xl font-black tracking-tighter uppercase text-white">
                    Erp<span class="text-primary">Vue</span>
                </Link>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mt-1">Área do Cliente</p>
            </div>

            <button @click="isMobileMenuOpen = false" class="md:hidden text-white hover:text-primary transition-colors">
                <X class="w-6 h-6"/>
            </button>
        </div>

        <!-- Perfil Resumo -->
        <div class="p-6 bg-slate-50/50 border-b border-slate-50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary rounded-2xl flex items-center justify-center shadow-lg shadow-primary/20">
                    <span class="text-white font-black text-lg">{{ user.first_name[0] }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-black text-slate-900 uppercase tracking-tight truncate w-40">{{ user.name }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cliente Prime</span>
                </div>
            </div>
        </div>

        <!-- Menu -->
        <nav class="flex-1 p-6 space-y-2">
            <div v-for="item in menuCliente" :key="item.nome">
                <!-- Ativo -->
                <Link v-if="item.ativo"
                    :href="item.rota"
                    class="flex items-center gap-4 p-4 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-primary transition-all group border border-transparent hover:border-slate-100"
                    :class="{ 'bg-primary/5 text-primary border-primary/10 font-black': page.url === item.rota }">
                    <component :is="item.icone" class="w-5 h-5 group-hover:scale-110 transition-transform"/>
                    <span class="text-xs font-black uppercase tracking-widest">{{ item.nome }}</span>
                </Link>

                <!-- Desabilitado -->
                <div v-else
                    class="flex items-center gap-4 p-4 rounded-2xl text-slate-300 cursor-not-allowed opacity-50 grayscale border border-dashed border-slate-100">
                    <component :is="item.icone" class="w-5 h-5"/>
                    <span class="text-xs font-black uppercase tracking-widest">{{ item.nome }} (Breve)</span>
                </div>
            </div>
        </nav>

        <!-- Voltar Loja & Logout -->
        <div class="p-6 space-y-3 border-t border-slate-50 bg-slate-50/30">
            <Link href="/"
                class="flex items-center gap-4 text-slate-500 hover:text-slate-900 p-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all hover:bg-white border border-transparent hover:border-slate-100 shadow-sm">
                <ArrowLeft class="w-4 h-4"/>
                Voltar para Loja
            </Link>

            <Link :href="route('client.logout')" method="post" as="button"
                class="w-full flex items-center gap-4 text-red-500 hover:bg-red-50 p-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all border border-transparent hover:border-red-100 shadow-sm">
                <LogOut class="w-4 h-4"/>
                Sair da Conta
            </Link>
        </div>
    </aside>

    <!-- Conteúdo -->
    <div class="flex-1 md:ml-72 flex flex-col transition-all duration-300">

        <!-- Header -->
        <header class="h-24 bg-white/80 backdrop-blur-xl border-b border-slate-100 flex items-center justify-between px-6 md:px-12 sticky top-0 z-20 shadow-sm">

            <div class="flex items-center gap-6">
                <button @click="toggleMobileMenu" class="md:hidden p-2 hover:bg-slate-50 rounded-xl transition-colors">
                    <Menu class="w-6 h-6 text-slate-600"/>
                </button>

                <div class="flex flex-col">
                    <h1 class="font-black text-slate-900 uppercase tracking-tighter text-xl">
                        Painel do Cliente
                    </h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gestão de conta e compras</p>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-900 uppercase tracking-tight">IP da Sessão</p>
                    <p class="text-[10px] font-bold text-primary font-mono uppercase tracking-widest">{{ page.props.auth.userIp || '0.0.0.0' }}</p>
                </div>
            </div>
        </header>

        <main class="p-6 md:p-12 max-w-7xl">
            <slot />
        </main>
    </div>
</div>
</template>

<style scoped>
.font-sans {
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}
</style>