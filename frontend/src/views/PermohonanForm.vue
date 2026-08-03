<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-900">post_add</span>
          Buat Pengajuan Permohonan Baru
        </h1>
        <p class="text-xs text-slate-500">Lengkapi formulir dan unggah berkas kelayakan untuk dikirimkan ke tim penilai</p>
      </div>

      <router-link
        to="/dashboard"
        class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors"
      >
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Kembali ke Dashboard
      </router-link>
    </div>

    <!-- Alert Feedback -->
    <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-2xl flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-lg text-emerald-600">check_circle</span>
        <span>{{ successMessage }}</span>
      </div>
    </div>

    <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-lg text-rose-600">error</span>
        <span>{{ errorMessage }}</span>
      </div>
    </div>

    <!-- Form Container Card -->
    <form @submit.prevent="handleSubmit(true)" class="bg-white rounded-2xl p-8 border border-slate-200 shadow-xs space-y-6">
      <!-- Section 1: Informasi Permohonan -->
      <div class="space-y-4">
        <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-900 text-lg">info</span>
          Informasi Utama Project
        </h3>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-800">
            Judul Project / Permohonan <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="form.judul_project"
            type="text"
            required
            placeholder="Contoh: Dokumen Kelayakan AMDAL Pembangunan Fasilitas Kesehatan"
            class="w-full px-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent"
          />
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-800">
            Deskripsi Latar Belakang & Spesifikasi Project <span class="text-rose-500">*</span>
          </label>
          <textarea
            v-model="form.deskripsi"
            rows="4"
            required
            placeholder="Jelaskan secara rinci cakupan proyek, maksud pengajuan, serta spesifikasi kelayakan..."
            class="w-full px-4 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent"
          ></textarea>
        </div>
      </div>

      <!-- Section 2: Upload File Lampiran -->
      <div class="space-y-4">
        <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-900 text-lg">cloud_upload</span>
          Unggah Berkas Dokumen Lampiran
        </h3>

        <FileDropzone
          @file-selected="handleFileSelected"
          @file-removed="handleFileRemoved"
        />
      </div>

      <!-- Actions Footer -->
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
        <button
          type="button"
          @click="handleSubmit(false)"
          :disabled="isSubmitting"
          class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 disabled:opacity-50 transition-colors"
        >
          Simpan Sebagai Draft
        </button>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="px-6 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-800 disabled:opacity-50 text-white font-semibold text-xs shadow-md transition-all inline-flex items-center gap-2"
        >
          <span class="material-symbols-outlined text-sm">send</span>
          {{ isSubmitting ? 'Memproses...' : 'Kirim Untuk Penilaian' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePermohonanStore } from '../stores/permohonan';
import FileDropzone from '../components/FileDropzone.vue';

const router = useRouter();
const permohonanStore = usePermohonanStore();

const form = ref({
  judul_project: '',
  deskripsi: '',
  file: null,
});

const isSubmitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const handleFileSelected = (file) => {
  form.value.file = file;
};

const handleFileRemoved = () => {
  form.value.file = null;
};

const handleSubmit = async (shouldSubmit = false) => {
  if (!form.value.judul_project.trim() || !form.value.deskripsi.trim()) {
    errorMessage.value = 'Judul project dan deskripsi wajib diisi.';
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const created = await permohonanStore.createDraft(form.value.judul_project, form.value.deskripsi);

    if (form.value.file && created?.id) {
      await permohonanStore.uploadDocument(created.id, form.value.file);
    }

    if (shouldSubmit && created?.id) {
      await permohonanStore.submitPermohonan(created.id);
    }

    successMessage.value = shouldSubmit
      ? 'Permohonan berhasil dikirim ke antrean tim penilai!'
      : 'Draft permohonan berhasil disimpan!';

    setTimeout(() => {
      router.push('/dashboard');
    }, 1200);
  } catch (err) {
    errorMessage.value = permohonanStore.error || 'Gagal memproses permohonan.';
  } finally {
    isSubmitting.value = false;
  }
};
</script>
