<template>
  <div class="space-y-8">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 rounded-2xl p-8 text-white shadow-xl shadow-blue-900/10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
      <div class="space-y-2 z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-blue-200 border border-white/10">
          <span class="material-symbols-outlined text-sm">shield_person</span>
          Portal Pemohon Dokumen
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Kelola Pengajuan Dokumen Kelayakan</h1>
        <p class="text-blue-200 text-sm max-w-xl">
          Pantau status pengajuan, lakukan revisi dokumen, dan lihat riwayat persetujuan dari tim penilai secara real-time.
        </p>
      </div>

      <router-link
        to="/permohonan/create"
        class="z-10 inline-flex items-center gap-2 bg-white text-blue-900 font-semibold px-5 py-3 rounded-xl hover:bg-blue-50 transition-all shadow-lg shadow-black/10 active:scale-95 text-xs sm:text-sm"
      >
        <span class="material-symbols-outlined text-lg">add</span>
        Buat Pengajuan Baru
      </router-link>

      <!-- Decorative background blur -->
      <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl"></div>
    </div>

    <!-- Stat Cards Bento Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div
        v-for="stat in statCards"
        :key="stat.label"
        class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs hover:border-blue-300 transition-all group"
      >
        <div class="flex items-center justify-between mb-3">
          <span :class="['material-symbols-outlined p-2.5 rounded-xl text-lg', stat.iconBg]">
            {{ stat.icon }}
          </span>
          <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Total</span>
        </div>
        <p class="text-2xl font-bold text-slate-900 tracking-tight group-hover:scale-105 transition-transform origin-left">
          {{ stat.value }}
        </p>
        <p class="text-xs font-medium text-slate-500 mt-1">{{ stat.label }}</p>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Area Chart: Monthly Trends -->
      <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
              <span class="material-symbols-outlined text-blue-900">trending_up</span>
              Tren Pengajuan Permohonan (Bulanan)
            </h3>
            <p class="text-xs text-slate-500">Statistik jumlah dokumen yang diajukan per bulan</p>
          </div>
        </div>
        <div class="h-64">
          <apexchart type="area" height="100%" :options="areaChartOptions" :series="areaChartSeries" />
        </div>
      </div>

      <!-- Doughnut Chart: Status Ratio -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
        <div>
          <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-900">pie_chart</span>
            Rasio Status Dokumen
          </h3>
          <p class="text-xs text-slate-500">Distribusi persentase keputusan</p>
        </div>
        <div class="h-64 flex items-center justify-center">
          <apexchart type="donut" height="100%" :options="donutChartOptions" :series="donutChartSeries" />
        </div>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="font-bold text-slate-900 text-lg">Daftar Permohonan Saya</h2>
          <p class="text-xs text-slate-500">Riwayat pengajuan dokumen kelayakan Anda di database real</p>
        </div>

        <!-- Filter Status Tabs -->
        <div class="flex flex-wrap gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
          <button
            v-for="filter in filters"
            :key="filter.value"
            @click="activeFilter = filter.value"
            :class="[
              'px-3 py-1.5 rounded-lg font-semibold transition-all',
              activeFilter === filter.value ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'
            ]"
          >
            {{ filter.label }}
          </button>
        </div>
      </div>

      <!-- Table Content -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
            <tr>
              <th class="px-6 py-4">No. Registrasi</th>
              <th class="px-6 py-4">Judul Project</th>
              <th class="px-6 py-4">Tanggal Buat</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="permohonanStore.loading">
              <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                Memuat data dari server...
              </td>
            </tr>
            <tr v-else-if="filteredList.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">folder_off</span>
                Belum ada data permohonan yang sesuai.
              </td>
            </tr>
            <tr
              v-else
              v-for="item in filteredList"
              :key="item.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <td class="px-6 py-4 font-mono font-bold text-blue-900">
                {{ item.nomor_permohonan }}
              </td>
              <td class="px-6 py-4">
                <p class="font-semibold text-slate-900 text-sm mb-0.5">{{ item.judul_project }}</p>
                <p class="text-slate-500 line-clamp-1 max-w-md">{{ item.deskripsi }}</p>
              </td>
              <td class="px-6 py-4 text-slate-500">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="px-6 py-4">
                <StatusBadge :status="item.status" />
              </td>
              <td class="px-6 py-4 text-right space-x-2">
                <router-link
                  :to="`/permohonan/${item.id}`"
                  class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-colors"
                >
                  <span class="material-symbols-outlined text-sm">visibility</span>
                  Detail
                </router-link>

                <button
                  v-if="['draft', 'revision'].includes(item.status)"
                  @click="handleSubmit(item.id)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white font-semibold transition-colors shadow-2xs"
                >
                  <span class="material-symbols-outlined text-sm">send</span>
                  Kirim
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePermohonanStore } from '../stores/permohonan';
import StatusBadge from '../components/StatusBadge.vue';
import apexchart from 'vue3-apexcharts';

const permohonanStore = usePermohonanStore();
const activeFilter = ref('all');

const filters = [
  { label: 'Semua', value: 'all' },
  { label: 'Draft', value: 'draft' },
  { label: 'Diajukan', value: 'submitted' },
  { label: 'Revisi', value: 'revision' },
  { label: 'Disetujui', value: 'approved' },
  { label: 'Ditolak', value: 'rejected' },
];

onMounted(() => {
  permohonanStore.fetchPermohonan();
});

const permohonanList = computed(() => permohonanStore.items || []);

const filteredList = computed(() => {
  if (activeFilter.value === 'all') return permohonanList.value;
  return permohonanList.value.filter(item => item.status === activeFilter.value);
});

const statCards = computed(() => {
  const list = permohonanList.value;
  return [
    { label: 'Total Permohonan', value: list.length, icon: 'folder', iconBg: 'bg-blue-50 text-blue-700' },
    { label: 'Draft Saya', value: list.filter(i => i.status === 'draft').length, icon: 'edit_document', iconBg: 'bg-slate-100 text-slate-700' },
    { label: 'Menunggu Review', value: list.filter(i => i.status === 'submitted').length, icon: 'hourglass_top', iconBg: 'bg-amber-50 text-amber-700' },
    { label: 'Perlu Revisi', value: list.filter(i => i.status === 'revision').length, icon: 'history_edu', iconBg: 'bg-blue-50 text-blue-700' },
    { label: 'Disetujui', value: list.filter(i => i.status === 'approved').length, icon: 'verified', iconBg: 'bg-emerald-50 text-emerald-700' },
    { label: 'Ditolak', value: list.filter(i => i.status === 'rejected').length, icon: 'cancel', iconBg: 'bg-rose-50 text-rose-700' },
  ];
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

const handleSubmit = async (id) => {
  await permohonanStore.submitPermohonan(id);
};

// ApexCharts Options
const areaChartSeries = ref([
  { name: 'Pengajuan Dokumen', data: [10, 15, 8, 20, 12, 25] }
]);

const areaChartOptions = ref({
  chart: { type: 'area', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
  colors: ['#1E3A8A'],
  stroke: { curve: 'smooth', width: 3 },
  fill: { type: 'gradient', gradient: { opacityFrom: 0.45, opacityTo: 0.05 } },
  dataLabels: { enabled: false },
  xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'] },
});

const donutChartSeries = computed(() => {
  const list = permohonanList.value;
  return [
    list.filter(i => i.status === 'draft').length || 1,
    list.filter(i => i.status === 'submitted').length || 1,
    list.filter(i => i.status === 'revision').length || 1,
    list.filter(i => i.status === 'approved').length || 1,
    list.filter(i => i.status === 'rejected').length || 1,
  ];
});

const donutChartOptions = ref({
  chart: { type: 'donut', fontFamily: 'Inter, sans-serif' },
  labels: ['Draft', 'Submitted', 'Revision', 'Approved', 'Rejected'],
  colors: ['#64748B', '#F59E0B', '#3B82F6', '#10B981', '#EF4444'],
  legend: { position: 'bottom' },
});
</script>
