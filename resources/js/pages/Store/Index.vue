<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useStoreIndex } from './useStoreIndex';
import { Search, SlidersHorizontal, ShoppingBag, Info } from 'lucide-vue-next';

const props = defineProps({
    products: Object, // Paginação do Laravel
    filters: Object,
    brands: Array
});

const { search, minPrice, maxPrice, brand, category } = useStoreIndex(props);
</script>

<template>
    <Head title="Vitrine de Produtos" />

    <div class="min-h-screen bg-white text-gray-900 font-sans pb-20">
        <nav class="border-b border-gray-100 sticky top-0 bg-white/80 backdrop-blur-md z-50">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <h1 class="text-2xl font-black tracking-tighter uppercase">Minha<span class="text-indigo-600">Loja</span></h1>
                
                <div class="hidden md:flex flex-1 max-w-md mx-10 relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Buscar produtos (digite 3 letras...)"
                        class="w-full bg-gray-100 border-none rounded-2xl pl-11 pr-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 transition"
                    />
                </div>

                <div class="flex items-center gap-4">
                    <Link :href="route('login')" class="text-xs font-black uppercase tracking-widest hover:text-indigo-600 transition">Entrar</Link>
                    <button class="bg-black text-white p-3 rounded-2xl hover:scale-105 transition active:scale-95 shadow-xl">
                        <ShoppingBag class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-6 py-10 flex flex-col md:flex-row gap-10">
            <aside class="w-full md:w-64 space-y-8">
                <div>
                    <h3 class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-6">
                        <SlidersHorizontal class="w-3 h-3" /> Filtros
                    </h3>
                    
                    <div class="space-y-4">
                        <label class="block text-xs font-bold uppercase">Preço (R$)</label>
                        <div class="flex items-center gap-2">
                            <input v-model="minPrice" type="number" placeholder="Mín" class="w-full bg-gray-50 border-gray-100 rounded-xl text-xs font-bold focus:ring-indigo-500" />
                            <input v-model="maxPrice" type="number" placeholder="Máx" class="w-full bg-gray-50 border-gray-100 rounded-xl text-xs font-bold focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div class="mt-8 space-y-4">
                        <label class="block text-xs font-bold uppercase">Marca</label>
                        <select v-model="brand" class="w-full bg-gray-50 border-gray-100 rounded-xl text-xs font-bold focus:ring-indigo-500">
                            <option value="">Todas as marcas</option>
                            <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>
                </div>
            </aside>

            <section class="flex-1">
                
                <div v-if="products.links.length > 3" class="mb-8 flex justify-center">
                    <div class="flex items-center gap-1 bg-gray-50 border border-gray-100 p-1 rounded-2xl">
                        <template v-for="(link, k) in products.links" :key="k">
                            <div v-if="link.url === null" 
                                class="px-3 py-1.5 text-gray-300 text-[10px] font-black uppercase"
                                v-html="link.label" 
                            />
                            <Link 
                                v-else
                                :href="link.url"
                                class="px-3 py-1.5 text-[10px] font-black uppercase rounded-xl transition-all"
                                :class="{ 'bg-white shadow-sm text-indigo-600': link.active, 'text-gray-400 hover:text-gray-600': !link.active }"
                                v-html="link.label"
                                preserve-scroll
                            />
                        </template>
                    </div>
                </div>

                <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="product in products.data" :key="product.id" class="group">
                        <div class="relative aspect-[4/5] rounded-[2.5rem] overflow-hidden bg-gray-100 mb-4 border border-gray-50 shadow-sm group-hover:shadow-2xl transition-all duration-500">
                            <img 
                                :src="product.images[0] ? '/storage/products/' + product.images[0].path : 'https://placehold.co/600x800?text=Sem+Foto'" 
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                            />
                            
                            <div class="absolute bottom-6 left-6 right-6 p-4 bg-white/70 backdrop-blur-xl rounded-3xl flex items-center justify-between translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                <div>
                                    <p class="text-[8px] font-black uppercase text-gray-500 leading-none mb-1">A partir de</p>
                                    <p class="text-lg font-black tracking-tighter">R$ {{ product.sale_price }}</p>
                                </div>
                                <button class="bg-indigo-600 text-white p-3 rounded-2xl shadow-lg">
                                    <ShoppingBag class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <h3 class="text-sm font-black uppercase tracking-tight px-2 group-hover:text-indigo-600 transition">{{ product.description }}</h3>
                        <p class="text-[10px] font-bold text-gray-400 px-2 uppercase">{{ product.brand }} • {{ product.model }}</p>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="bg-gray-50 p-6 rounded-full mb-4 text-gray-300">
                        <Info class="w-10 h-10" />
                    </div>
                    <p class="font-black uppercase text-gray-400 text-xs tracking-widest">Nenhum produto encontrado</p>
                </div>

                <div v-if="products.links.length > 3" class="mt-16 flex justify-center">
                    <div class="flex items-center gap-1 bg-gray-100 p-1.5 rounded-2xl shadow-inner">
                        <template v-for="(link, k) in products.links" :key="k">
                            <div v-if="link.url === null" 
                                class="px-4 py-2 text-gray-400 text-xs font-black uppercase tracking-widest"
                                v-html="link.label" 
                            />
                            <Link 
                                v-else
                                :href="link.url"
                                class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200"
                                :class="{ 'bg-white shadow-md text-indigo-600': link.active, 'hover:bg-gray-200 text-gray-500': !link.active }"
                                v-html="link.label"
                                preserve-scroll
                            />
                        </template>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>