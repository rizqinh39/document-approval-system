<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-6 relative animate-in fade-in zoom-in duration-200">
      <!-- Modal Header -->
      <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <div>
          <h3 class="font-bold text-slate-900 text-lg flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-900">gavel</span>
            Form Penilaian Dokumen
          </h3>
          <p class="text-xs text-slate-500 font-mono mt-0.5">{{ permohonan?.nomor_permohonan }}</p>
        </div>

        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Project Info Card -->
      <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-1">
        <p class="text-xs font-bold text-slate-900">{{ permohonan?.judul_project }}</p>
        <p class="text-xs text-slate-600 line-clamp-2">{{ permohonan?.deskripsi }}</p>
        <p class="text-[11px] text-slate-500 pt-1">Pemohon: <span class="font-semibold text-slate-700">{{ permohonan?.pemohon_name }}</span></p>
      </div>

      <!-- Decision Radio Options -->
      <div class="space-y-3">
        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
          Pilih Keputusan Penilaian <span class="text-rose-500">*</span>
        </label>

        <div class="grid grid-cols-3 gap-3">
          <!-- Approve Radio Option -->
          <label
            :class="[
              'flex flex-col items-center p-3 rounded-xl border-2 cursor-pointer transition-all text-center',
              selectedAction === 'approve' ? 'border-emerald-600 bg-emerald-50 text-emerald-900 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-700'
            ]"
          >
            <input type="radio" v-model="selectedAction" value="approve" class="sr-only" />
            <span class="material-symbols-outlined text-xl mb-1 text-emerald-600">verified</span>
            <span class="text-xs">Setujui</span>
          </label>

          <!-- Revision Radio Option -->
          <label
            :class="[
              'flex flex-col items-center p-3 rounded-xl border-2 cursor-pointer transition-all text-center',
              selectedAction === 'revision' ? 'border-blue-600 bg-blue-50 text-blue-900 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-700'
            ]"
          >
            <input type="radio" v-model="selectedAction" value="revision" class="sr-only" />
            <span class="material-symbols-outlined text-xl mb-1 text-blue-600">history_edu</span>
            <span class="text-xs">Revisi</span>
          </label>

          <!-- Reject Radio Option -->
          <label
            :class="[
              'flex flex-col items-center p-3 rounded-xl border-2 cursor-pointer transition-all text-center',
              selectedAction === 'reject' ? 'border-rose-600 bg-rose-50 text-rose-900 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-700'
            ]"
          >
            <input type="radio" v-model="selectedAction" value="reject" class="sr-only" />
            <span class="material-symbols-outlined text-xl mb-1 text-rose-600">cancel</span>
            <span class="text-xs">Tolak</span>
          </label>
        </div>
      </div>

      <!-- Mandatory Notes Textarea -->
      <div class="space-y-2">
        <label class="block text-xs font-bold text-slate-800">
          Catatan & Alasan Evaluasi Penilai
          <span v-if="['revision', 'reject'].includes(selectedAction)" class="text-rose-500">* (Wajib diisi)</span>
        </label>
        <textarea
          v-model="notes"
          rows="3"
          placeholder="Tuliskan catatan evaluasi, poin revisi yang harus diperbaiki, atau alasan penolakan..."
          class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
        ></textarea>
      </div>

      <!-- Modal Footer -->
      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
        >
          Batal
        </button>
        <button
          @click="submitReview"
          :disabled="!isValid"
          class="px-5 py-2 text-xs font-semibold bg-purple-900 hover:bg-purple-800 disabled:opacity-50 text-white rounded-xl shadow-md transition-colors"
        >
          Simpan Keputusan
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  isOpen: Boolean,
  permohonan: Object,
});

const emit = defineEmits(['close', 'submit-review']);

const selectedAction = ref('approve');
const notes = ref('');

const isValid = computed(() => {
  if (['revision', 'reject'].includes(selectedAction.value)) {
    return notes.value.trim().length > 0;
  }
  return true;
});

const submitReview = () => {
  if (!isValid.value) return;

  emit('submit-review', {
    id: props.permohonan.id,
    action: selectedAction.value,
    notes: notes.value,
  });

  notes.value = '';
  selectedAction.value = 'approve';
};
</script>
