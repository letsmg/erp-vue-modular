<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'

defineOptions({ layout: StoreLayout })

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  document_number: '',
  phone: '',
})

const formatDocument = (event: Event) => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  
  if (value.length <= 11) {
    // CPF: 000.000.000-00
    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  } else {
    // CNPJ: 00.000.000/0000-00
    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
  }
  
  input.value = value
  form.document_number = value
}

const formatPhone = (event: Event) => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  
  if (value.length <= 10) {
    // Telefone fixo: (00) 0000-0000
    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
  } else {
    // Celular: (00) 00000-0000
    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  }
  
  input.value = value
  form.phone = value
}

const submit = () => {
  form.post(route('client.register.post'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Cadastro Cliente">
    <meta name="robots" content="follow" />
  </Head>

  <div class="min-h-[60vh] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-xl">
      <div class="bg-white py-8 px-4 shadow-2xl rounded-3xl sm:px-10 border border-slate-100">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter">Criar conta</h1>
          <p class="mt-2 text-sm font-bold text-slate-500 uppercase tracking-widest">
            Preencha os dados abaixo para se cadastrar
          </p>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label for="name" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Nome completo *
            </label>
            <div class="mt-1">
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                required
                autocomplete="name"
                placeholder="Seu Nome Completo"
              />
            </div>
            <p v-if="form.errors.name" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.name }}
            </p>
          </div>

          <div>
            <label for="email" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              E-mail *
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                required
                autocomplete="email"
                placeholder="seu@email.com"
              />
            </div>
            <p v-if="form.errors.email" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label for="document_number" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              CPF/CNPJ *
            </label>
            <div class="mt-1">
              <input
                id="document_number"
                v-model="form.document_number"
                type="text"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                required
                maxlength="18"
                placeholder="000.000.000-00"
                @input="formatDocument"
              />
            </div>
            <p v-if="form.errors.document_number" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.document_number }}
            </p>
          </div>

          <div>
            <label for="phone" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Telefone
            </label>
            <div class="mt-1">
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                maxlength="15"
                placeholder="(00) 00000-0000"
                @input="formatPhone"
              />
            </div>
            <p v-if="form.errors.phone" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.phone }}
            </p>
          </div>

          <div class="md:col-span-1">
            <label for="password" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Senha *
            </label>
            <div class="mt-1">
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                required
                autocomplete="new-password"
                placeholder="••••••••"
              />
            </div>
            <p v-if="form.errors.password" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.password }}
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              Confirmar senha *
            </label>
            <div class="mt-1">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="block w-full bg-slate-50 border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary transition-all outline-none shadow-sm"
                required
                autocomplete="new-password"
                placeholder="••••••••"
              />
            </div>
            <p v-if="form.errors.password_confirmation" class="mt-2 text-xs font-bold text-red-600 uppercase">
              {{ form.errors.password_confirmation }}
            </p>
          </div>

          <div class="md:col-span-2 mt-4">
            <button
              type="submit"
              class="btn-primary w-full py-4 rounded-2xl shadow-lg shadow-primary/20 text-sm uppercase tracking-widest font-black"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              Criar minha conta
            </button>
          </div>
        </form>

        <div class="mt-8 pt-8 border-t border-slate-100 text-center">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">
            Já tem uma conta?
            <Link
              :href="route('client.login')"
              class="ml-1 font-black text-primary hover:text-primary-hover transition"
            >
              Fazer login
            </Link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
