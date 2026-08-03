<template>
  <div class="max-w-5xl mx-auto space-y-6">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <router-link
          to="/dashboard"
          class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors shadow-2xs"
        >
          <span class="material-symbols-outlined text-lg">arrow_back</span>
        </router-link>

        <div>
          <span class="text-xs font-mono text-blue-900 font-bold block">{{ detail.nomor_permohonan || 'REQ-LOADING' }}</span>
          <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ detail.judul_project || 'Memuat Data...' }}</h1>
        </div>
      </div>

      <div class="flex items-center gap-3" v-if="detail.status">
        <StatusBadge :status="detail.status" />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-16 bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs">
      Memuat detail permohonan dari server...
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Detail & Files -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Project Description Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-3">
          <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-900 text-lg">description</span>
            Deskripsi Latar Belakang Project
          </h3>
          <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ detail.deskripsi || 'Tidak ada deskripsi.' }}</p>
        </div>

        <!-- Attached Documents Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
          <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-900 text-lg">attachment</span>
            Dokumen Lampiran Kelayakan
          </h3>

          <div v-if="!detail.documents || detail.documents.length === 0" class="text-center py-8 text-slate-400 text-xs">
            Belum ada berkas dokumen yang diunggah.
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="doc in detail.documents"
              :key="doc.id"
              class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-colors"
            >
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-500 text-2xl">picture_as_pdf</span>
                <div>
                  <p class="text-xs font-semibold text-slate-900">{{ doc.original_name || doc.name }}</p>
                  <p class="text-[10px] text-slate-500">{{ formatFileSize(doc.file_size) }} • {{ doc.mime_type || 'PDF/DOCX' }}</p>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="downloadDocument(doc)"
                  class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold transition-colors flex items-center gap-1"
                >
                  <span class="material-symbols-outlined text-sm">download</span>
                  Unduh
                </button>
                <button
                  v-if="['draft', 'revision'].includes(detail.status)"
                  @click="deleteDoc(doc.id)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                  title="Hapus Berkas"
                >
                  <span class="material-symbols-outlined text-base">delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Audit History Stepper Sidebar -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
        <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-900 text-lg">history</span>
          Riwayat Audit & Penilaian
        </h3>

        <TimelineStepper :logs="formattedLogs" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { usePermohonanStore } from '../stores/permohonan';
import StatusBadge from '../components/StatusBadge.vue';
import TimelineStepper from '../components/TimelineStepper.vue';

const route = useRoute();
const permohonanStore = usePermohonanStore();

const detail = ref({});
const loading = ref(true);

onMounted(async () => {
  loading.value = true;
  try {
    const fetched = await permohonanStore.fetchDetail(route.params.id);
    detail.value = fetched || {};
  } catch (e) {
    detail.value = permohonanStore.currentDetail || {};
  } finally {
    loading.value = false;
  }
});

const formatFileSize = (bytes) => {
  if (!bytes) return 'File';
  if (typeof bytes === 'string') return bytes;
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const formattedLogs = computed(() => {
  const logs = detail.value?.logs || [];
  return logs.map(l => ({
    id: l.id,
    actor_name: l.actor?.name || 'Sistem',
    action: l.action,
    notes: l.notes,
    created_at: new Date(l.created_at).toLocaleString('id-ID'),
  }));
});

const downloadDocument = (doc) => {
  alert(`Mengunduh berkas: ${doc.original_name || doc.name}`);
};

const deleteDoc = async (docId) => {
  if (confirm('Apakah Anda yakin ingin menghapus berkas dokumen ini?')) {
    await permohonanStore.deleteDocument(detail.value.id, docId);
    detail.value = { ...permohonanStore.currentDetail };
  }
};
</script>
