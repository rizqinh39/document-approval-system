<template>
  <div v-if="isAuthPage" class="min-h-screen bg-slate-900 font-sans">
    <router-view />
  </div>

  <div v-else class="min-h-screen bg-slate-50 text-slate-900 flex flex-col font-sans">
    <!-- Top Header Navigation Bar -->
    <header class="fixed top-0 left-0 w-full z-50 flex items-center justify-between px-6 h-16 bg-white border-b border-slate-200 shadow-xs">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-xl bg-blue-900 flex items-center justify-center text-white shadow-md shadow-blue-900/20">
          <span class="material-symbols-outlined text-xl">verified_user</span>
        </div>
        <div>
          <span class="font-bold text-base text-slate-900 tracking-tight block">Sistem Dokumen Kelayakan</span>
          <span class="text-[11px] text-slate-500 font-medium block -mt-1">Instansi Pemerintah Portal</span>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <!-- Search bar -->
        <div class="hidden md:flex items-center bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
          <span class="material-symbols-outlined text-slate-400 text-lg">search</span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nomor permohonan / judul..."
            class="bg-transparent border-none focus:outline-none text-xs text-slate-700 w-56 px-2"
          />
        </div>

        <!-- Role Badge -->
        <div
          :class="{
            'bg-blue-50 text-blue-700 border-blue-200': authStore.userRole === 'pemohon',
            'bg-purple-50 text-purple-700 border-purple-200': authStore.userRole === 'penilai'
          }"
          class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-semibold uppercase tracking-wider"
        >
          <span class="material-symbols-outlined text-sm">badge</span>
          {{ authStore.userRole || 'User' }}
        </div>

        <!-- User Profile & Logout -->
        <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
          <div class="h-9 w-9 rounded-full bg-blue-900 text-white font-semibold flex items-center justify-center text-xs shadow-xs">
            {{ userInitial }}
          </div>
          <div class="hidden sm:block text-left">
            <p class="text-xs font-semibold text-slate-800 leading-tight">{{ userName }}</p>
            <p class="text-[10px] text-slate-500 leading-tight">{{ userEmail }}</p>
          </div>
          <button
            @click="showLogoutModal = true"
            class="p-2 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors ml-1"
            title="Keluar / Logout"
          >
            <span class="material-symbols-outlined text-xl">logout</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Layout Container -->
    <div class="flex flex-1 pt-16">
      <!-- Side Navigation Bar -->
      <aside class="fixed left-0 top-16 h-[calc(100vh-4rem)] w-64 bg-white border-r border-slate-200 flex flex-col p-4 z-40 shadow-xs">
        <div class="mb-6 px-3 py-2 bg-slate-50 rounded-xl border border-slate-100">
          <p class="text-xs font-semibold text-slate-800">Menu Utama</p>
          <p class="text-[11px] text-slate-500">Kelola & Penilaian Permohonan</p>
        </div>

        <nav class="space-y-1.5 flex-1">
          <router-link
            :to="authStore.userRole === 'penilai' ? '/penilai' : '/dashboard'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs transition-all"
            :class="['/dashboard', '/penilai'].includes($route.path) ? 'bg-blue-900 text-white font-semibold shadow-md shadow-blue-900/10' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
          >
            <span class="material-symbols-outlined text-lg">dashboard</span>
            <span>Dashboard</span>
          </router-link>

          <router-link
            v-if="authStore.userRole === 'pemohon'"
            to="/permohonan/create"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs transition-all"
            :class="$route.path === '/permohonan/create' ? 'bg-blue-900 text-white font-semibold shadow-md shadow-blue-900/10' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
          >
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>Pengajuan Baru</span>
          </router-link>

          <div class="pt-4 pb-2 px-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
            Sistem & Keamanan
          </div>

          <div class="px-3 py-2 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-600 space-y-1">
            <div class="flex justify-between items-center">
              <span class="font-medium text-slate-500">Watermark:</span>
              <span class="font-semibold text-emerald-600">Active</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-medium text-slate-500">Engine:</span>
              <span class="font-mono text-[10px] text-slate-700">Qi-Platform</span>
            </div>
          </div>
        </nav>

        <!-- Footer Info -->
        <div class="pt-4 border-t border-slate-100 text-center">
          <p class="text-[10px] text-slate-400">Technical Test Fullstack 2026</p>
        </div>
      </aside>

      <!-- Main Content Area -->
      <main class="ml-64 flex-1 p-8 bg-slate-50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto">
          <router-view />
        </div>
      </main>
    </div>

    <!-- Logout Confirmation Modal Dialog -->
    <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 space-y-4 relative text-center">
        <div class="h-12 w-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
          <span class="material-symbols-outlined text-2xl">logout</span>
        </div>

        <div>
          <h3 class="font-bold text-slate-900 text-base">Konfirmasi Logout</h3>
          <p class="text-xs text-slate-500 mt-1">Apakah Anda yakin ingin keluar dari akun ini?</p>
        </div>

        <div class="flex justify-center gap-3 pt-2">
          <button
            @click="showLogoutModal = false"
            class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
          >
            Batal
          </button>
          <button
            @click="confirmLogout"
            class="px-5 py-2 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-md transition-colors"
          >
            Ya, Logout Sekarang
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const searchQuery = ref('');
const showLogoutModal = ref(false);

const isAuthPage = computed(() => ['/login', '/register'].includes(route.path));

const userName = computed(() => authStore.user?.name || 'User');
const userEmail = computed(() => authStore.user?.email || '');

const userInitial = computed(() => {
  const name = userName.value;
  return name.charAt(0).toUpperCase();
});

const confirmLogout = async () => {
  showLogoutModal.value = false;
  await authStore.logout();
  router.push('/login');
};
</script>
