<template>
  <div class="min-h-screen bg-slate-900 flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Ambient Blur Background -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl"></div>

    <div class="max-w-4xl w-full bg-slate-800/80 backdrop-blur-xl rounded-3xl border border-slate-700/50 shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 relative z-10">
      <!-- Left Branding Banner -->
      <div class="p-8 lg:p-12 bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-900 text-white flex flex-col justify-between border-r border-slate-700/50 relative">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs font-semibold text-blue-200 border border-white/10 mb-6">
            <span class="material-symbols-outlined text-sm">verified_user</span>
            Portal Resmi Pemerintah
          </div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-3">Sistem Informasi Persetujuan Dokumen Kelayakan</h1>
          <p class="text-blue-200/80 text-xs leading-relaxed">
            Platform terpadu pengelolaan pengajuan, evaluasi teknis, dan penetapan status kelayakan dokumen permohonan secara efisien dan transparan.
          </p>
        </div>

        <div class="pt-8 border-t border-white/10 space-y-1">
          <p class="text-[11px] text-blue-200/60">Engineered with Qi Security Watermark</p>
          <p class="text-[10px] font-mono text-emerald-400">Status: Verified System Operational</p>
        </div>
      </div>

      <!-- Right Login Form -->
      <div class="p-8 lg:p-12 flex flex-col justify-center space-y-6">
        <div>
          <h2 class="text-xl font-bold text-white mb-1">Masuk ke Akun</h2>
          <p class="text-xs text-slate-400">Silakan masukkan kredensial untuk mengakses portal</p>
        </div>

        <div v-if="errorMessage" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs p-3 rounded-xl">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-slate-300">Alamat Email</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-500 text-lg">mail</span>
              <input
                v-model="email"
                type="email"
                required
                placeholder="nama@example.com"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-slate-300">Kata Sandi</label>
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-500 text-lg">lock</span>
              <input
                v-model="password"
                type="password"
                required
                placeholder="••••••••"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/60 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="authStore.loading"
            class="w-full py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-semibold text-xs rounded-xl shadow-lg shadow-blue-600/25 transition-all"
          >
            {{ authStore.loading ? 'Memproses...' : 'Masuk Portal' }}
          </button>
        </form>

        <!-- Quick Demo Login Buttons -->
        <div class="pt-4 border-t border-slate-700/50 space-y-2">
          <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center">Quick Demo Login (Sanctum Real Token)</p>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="quickLogin('pemohon')"
              class="px-3 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-medium text-blue-300 text-center transition-colors"
            >
              Demo Pemohon
            </button>
            <button
              type="button"
              @click="quickLogin('penilai')"
              class="px-3 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-medium text-purple-300 text-center transition-colors"
            >
              Demo Penilai
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('pemohon1@example.com');
const password = ref('password');
const errorMessage = ref('');

const handleLogin = async () => {
  errorMessage.value = '';
  try {
    await authStore.login(email.value, password.value);
    const target = authStore.userRole === 'penilai' ? '/penilai' : '/dashboard';
    router.push(target);
  } catch (err) {
    errorMessage.value = authStore.error || 'Gagal login, kredensial tidak cocok';
  }
};

const quickLogin = async (role) => {
  errorMessage.value = '';
  const demoEmail = role === 'pemohon' ? 'pemohon1@example.com' : 'penilai1@example.com';
  try {
    await authStore.login(demoEmail, 'password');
    const target = role === 'penilai' ? '/penilai' : '/dashboard';
    router.push(target);
  } catch (err) {
    errorMessage.value = authStore.error || 'Gagal login demo';
  }
};
</script>
