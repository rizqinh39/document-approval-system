<template>
  <div class="space-y-8">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 rounded-2xl p-8 text-white shadow-xl shadow-purple-900/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
      <div class="space-y-2 z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-purple-200 border border-white/10">
          <span class="material-symbols-outlined text-sm">fact_check</span>
          Portal Tim Penilai Dokumen
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Antrean Penilaian Dokumen Kelayakan</h1>
        <p class="text-purple-200 text-sm max-w-xl">
          Tinjau pengajuan dokumen dari perusahaan/pemohon, berikan verifikasi administrasi, catatan evaluasi, dan tetapkan keputusan persetujuan.
        </p>
      </div>

      <div class="flex items-center gap-3 z-10">
        <div class="bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/10 text-right">
          <p class="text-xs text-purple-200">Perlu Penilaian</p>
          <p class="text-xl font-bold text-white">{{ reviewQueue.length }} Permohonan</p>
        </div>
      </div>

      <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
    </div>

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-2">
          <span class="material-symbols-outlined p-2.5 rounded-xl bg-amber-50 text-amber-700 text-lg">pending_actions</span>
          <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">Pending</span>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ reviewQueue.length }}</p>
        <p class="text-xs text-slate-500 mt-1">Permohonan Menunggu Review</p>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-2">
          <span class="material-symbols-outlined p-2.5 rounded-xl bg-blue-50 text-blue-700 text-lg">history_edu</span>
          <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">Revisi</span>
        </div>
        <p class="text-2xl font-bold text-slate-900">5</p>
        <p class="text-xs text-slate-500 mt-1">Dalam Proses Perbaikan</p>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-2">
          <span class="material-symbols-outlined p-2.5 rounded-xl bg-emerald-50 text-emerald-700 text-lg">verified</span>
          <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">Approved</span>
        </div>
        <p class="text-2xl font-bold text-slate-900">48</p>
        <p class="text-xs text-slate-500 mt-1">Telah Disetujui</p>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-2">
          <span class="material-symbols-outlined p-2.5 rounded-xl bg-rose-50 text-rose-700 text-lg">cancel</span>
          <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md">Rejected</span>
        </div>
        <p class="text-2xl font-bold text-slate-900">3</p>
        <p class="text-xs text-slate-500 mt-1">Ditolak</p>
      </div>
    </div>

    <!-- Review Queue Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <div>
          <h2 class="font-bold text-slate-900 text-lg">Antrean Pengajuan Masuk (Real Database)</h2>
          <p class="text-xs text-slate-500">Pilih dokumen yang diajukan untuk dilakukan evaluasi dan keputusan</p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
            <tr>
              <th class="px-6 py-4">No. Registrasi</th>
              <th class="px-6 py-4">Pemohon</th>
              <th class="px-6 py-4">Judul Project</th>
              <th class="px-6 py-4">Tanggal Pengajuan</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Aksi Penilaian</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="permohonanStore.loading">
              <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                Memuat antrean penilaian dari server...
              </td>
            </tr>
            <tr v-else-if="reviewQueue.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">task_alt</span>
                Tidak ada antrean permohonan berstatus submitted saat ini.
              </td>
            </tr>
            <tr
              v-else
              v-for="item in reviewQueue"
              :key="item.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <td class="px-6 py-4 font-mono font-bold text-purple-900">
                {{ item.nomor_permohonan }}
              </td>
              <td class="px-6 py-4">
                <p class="font-semibold text-slate-900">{{ item.pemohon?.name || 'Pemohon' }}</p>
                <p class="text-slate-500 text-[11px]">{{ item.pemohon?.email || '' }}</p>
              </td>
              <td class="px-6 py-4">
                <p class="font-semibold text-slate-900 text-sm mb-0.5">{{ item.judul_project }}</p>
                <p class="text-slate-500 line-clamp-1 max-w-md">{{ item.deskripsi }}</p>
              </td>
              <td class="px-6 py-4 text-slate-500">
                {{ formatDate(item.submitted_at || item.created_at) }}
              </td>
              <td class="px-6 py-4">
                <StatusBadge :status="item.status" />
              </td>
              <td class="px-6 py-4 text-right space-x-2">
                <button
                  @click="openReviewModal(item)"
                  class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-purple-900 hover:bg-purple-800 text-white font-semibold transition-colors shadow-2xs"
                >
                  <span class="material-symbols-outlined text-sm">rate_review</span>
                  Nilai Dokumen
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Review Decision Modal Component -->
    <ReviewModal
      :is-open="isModalOpen"
      :permohonan="selectedItem"
      @close="isModalOpen = false"
      @submit-review="handleReviewSubmitted"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePermohonanStore } from '../stores/permohonan';
import StatusBadge from '../components/StatusBadge.vue';
import ReviewModal from '../components/ReviewModal.vue';

const permohonanStore = usePermohonanStore();
const isModalOpen = ref(false);
const selectedItem = ref(null);

onMounted(() => {
  permohonanStore.fetchPenilaiQueue();
});

const reviewQueue = computed(() => permohonanStore.penilaiQueue || []);

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

const openReviewModal = (item) => {
  selectedItem.value = item;
  isModalOpen.value = true;
};

const handleReviewSubmitted = async ({ id, action, notes }) => {
  await permohonanStore.reviewPermohonan(id, action, notes);
  isModalOpen.value = false;
};
</script>
