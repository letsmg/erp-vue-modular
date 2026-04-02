<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    Save, ArrowLeft, User, Mail, Lock, Shield, 
    Smartphone, FileText, CheckCircle2, AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    client: Object,
    auth: Object
});

const form = useForm({
    name: props.client.name,
    document_type: props.client.document_type,
    document_number: props.client.document_number,
    phone1: props.client.phone1 || '',
    contact1: props.client.contact1 || '',
    user_name: props.client.user?.name || '',
    user_email: props.client.user?.email || '',
    user_password: '',
    user_password_confirmation: '',
    is_active: Boolean(props.client.is_active),
});

const submit = () => {
    form.put(route('clients.update', props.client.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="'Editar: ' + client.name" />

        <div class="max-w-5xl mx-auto pb-20">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <Link :href="route('clients.index')" class="text-xs font-black text-primary hover:text-primary-hover flex items-center gap-2 transition uppercase tracking-widest">
                        <ArrowLeft class="w-4 h-4" /> Voltar para lista
                    </Link>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tighter uppercase italic mt-2">Editar Cliente</h2>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Coluna Principal -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Dados Pessoais/Empresariais -->
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-100">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-3 bg-primary/10 text-primary rounded-2xl">
                                <User class="w-6 h-6" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter italic">Informações Básicas</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nome Completo / Razão Social</label>
                                <input v-model="form.name" type="text" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-primary transition-all outline-none" placeholder="Ex: João Silva ou Empresa LTDA">
                                <p v-if="form.errors.name" class="mt-2 text-[10px] font-black text-rose-500 uppercase ml-1">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Tipo de Pessoa</label>
                                <select v-model="form.document_type" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-primary transition-all outline-none appearance-none">
                                    <option value="CPF">Pessoa Física (CPF)</option>
                                    <option value="CNPJ">Pessoa Jurídica (CNPJ)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">{{ form.document_type }}</label>
                                <input v-model="form.document_number" type="text" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-primary transition-all outline-none" placeholder="000.000.000-00">
                                <p v-if="form.errors.document_number" class="mt-2 text-[10px] font-black text-rose-500 uppercase ml-1">{{ form.errors.document_number }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dados de Acesso -->
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-100">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-3 bg-indigo-100 text-indigo-600 rounded-2xl">
                                <Lock class="w-6 h-6" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter italic">Credenciais de Acesso</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">E-mail de Login</label>
                                <input v-model="form.user_email" type="email" required class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-primary transition-all outline-none" placeholder="cliente@email.com">
                                <p v-if="form.errors.user_email" class="mt-2 text-[10px] font-black text-rose-500 uppercase ml-1">{{ form.errors.user_email }}</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Alterar Senha (Deixe vazio para manter)</label>
                                <input v-model="form.user_password" type="password" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-primary transition-all outline-none" placeholder="••••••••">
                                <p v-if="form.errors.user_password" class="mt-2 text-[10px] font-black text-rose-500 uppercase ml-1">{{ form.errors.user_password }}</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Confirmar Nova Senha</label>
                                <input v-model="form.user_password_confirmation" type="password" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-primary transition-all outline-none" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra Lateral -->
                <div class="space-y-8">
                    <!-- Status e Ações -->
                    <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-2xl text-white">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-6">Atualização</h3>
                        
                        <div class="flex items-center justify-between mb-10">
                            <span class="text-xs font-black uppercase tracking-widest">Status Atual</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.is_active" class="sr-only peer" :disabled="auth.user.access_level !== 1 && auth.user.access_level !== 0">
                                <div class="w-14 h-7 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full btn-primary py-5 rounded-2xl shadow-primary/20 flex items-center justify-center gap-3 group"
                        >
                            <Save class="w-5 h-5 group-hover:scale-110 transition-transform" />
                            <span v-if="form.processing">Atualizando...</span>
                            <span v-else>Salvar Alterações</span>
                        </button>
                    </div>

                    <!-- Informações do Registro -->
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-100">
                        <div class="flex items-center gap-3 mb-6">
                            <FileText class="w-5 h-5 text-slate-400" />
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">Histórico</h3>
                        </div>
                        
                        <div class="space-y-4 text-[10px] font-bold uppercase tracking-tight text-slate-500">
                            <div class="flex justify-between">
                                <span>Cadastrado em:</span>
                                <span class="text-slate-900">{{ new Date(client.created_at).toLocaleDateString() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Último Login:</span>
                                <span class="text-slate-900">{{ client.user?.last_login_at || 'Nunca' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
