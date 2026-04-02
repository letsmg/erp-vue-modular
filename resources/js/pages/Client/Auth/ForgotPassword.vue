<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import StoreLayout from '@/Layouts/StoreLayout.vue'

defineOptions({ layout: StoreLayout })

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('client.forgot.password.post'))
}
</script>

<template>
  <Head title="Recuperar Senha">
    <meta name="robots" content="follow" />
  </Head>

  <div class="min-h-[60vh] flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-2xl rounded-3xl sm:px-10 border border-slate-100">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter">Esqueceu a senha?</h1>
          <p class="mt-2 text-sm font-bold text-slate-500 uppercase tracking-widest">
            Digite seu e-mail e enviaremos um link para redefinir sua senha
          </p>
        </div>

        <div v-if="form.recentlySuccessful" class="mb-4 rounded-2xl bg-green-50 p-4 border border-green-100">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-xs font-bold text-green-800 uppercase tracking-widest">
                Link de redefinição enviado com sucesso!
              </p>
            </div>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="email" class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">
              E-mail
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
            <button
              type="submit"
              class="btn-primary w-full py-4 rounded-2xl shadow-lg shadow-primary/20 text-sm uppercase tracking-widest font-black"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              Enviar link de redefinição
            </button>
          </div>

          <div class="mt-8 pt-8 border-t border-slate-100 text-center space-y-4">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">
              Lembrou sua senha?
              <Link
                :href="route('client.login')"
                class="ml-1 font-black text-primary hover:text-primary-hover transition"
              >
                Voltar para o login
              </Link>
            </p>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">
              Ainda não tem uma conta?
              <Link
                :href="route('client.register')"
                class="ml-1 font-black text-primary hover:text-primary-hover transition"
              >
                Cadastre-se agora
              </Link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
