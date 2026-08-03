<template>
  <div class="w-full space-y-3">
    <!-- Dropzone Area -->
    <div
      v-if="!selectedFile"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      :class="[
        'border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-3',
        isDragging ? 'border-blue-600 bg-blue-50/50 scale-[0.99]' : 'border-slate-300 hover:border-blue-500 hover:bg-slate-50'
      ]"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        class="hidden"
        accept=".pdf,.doc,.docx"
        @change="handleFileInput"
      />

      <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-900 flex items-center justify-center shadow-2xs">
        <span class="material-symbols-outlined text-2xl">cloud_upload</span>
      </div>

      <div>
        <p class="text-xs font-bold text-slate-800">
          Tarik & Lepas file ke sini, atau <span class="text-blue-900 underline">Cari File</span>
        </p>
        <p class="text-[11px] text-slate-400 mt-1">Format PDF atau DOCX (Maksimal 10MB)</p>
      </div>
    </div>

    <!-- Selected File Preview Card -->
    <div
      v-else
      class="bg-blue-50/70 border border-blue-200 rounded-2xl p-4 flex items-center justify-between shadow-2xs"
    >
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-blue-900 text-white flex items-center justify-center shadow-2xs">
          <span class="material-symbols-outlined text-xl">description</span>
        </div>
        <div>
          <p class="text-xs font-bold text-slate-900 line-clamp-1">{{ selectedFile.name }}</p>
          <p class="text-[11px] text-slate-500">{{ formatFileSize(selectedFile.size) }} • Siap Diunggah</p>
        </div>
      </div>

      <button
        type="button"
        @click="removeFile"
        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors"
        title="Hapus berkas"
      >
        <span class="material-symbols-outlined text-lg">delete</span>
      </button>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="text-xs font-medium text-rose-600 bg-rose-50 border border-rose-200 p-3 rounded-xl flex items-center gap-2">
      <span class="material-symbols-outlined text-base">error</span>
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const emit = defineEmits(['file-selected', 'file-removed']);
const fileInput = ref(null);
const isDragging = ref(false);
const selectedFile = ref(null);
const error = ref('');

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const validateFile = (file) => {
  error.value = '';
  if (!file) return false;

  const validExtensions = ['pdf', 'doc', 'docx'];
  const ext = file.name.split('.').pop().toLowerCase();

  if (!validExtensions.includes(ext)) {
    error.value = 'Format berkas tidak valid. Hanya menerima file PDF atau DOCX.';
    return false;
  }

  if (file.size > 10 * 1024 * 1024) {
    error.value = 'Ukuran berkas melebihi batas 10MB.';
    return false;
  }

  return true;
};

const handleDrop = (e) => {
  isDragging.value = false;
  const file = e.dataTransfer?.files[0];
  if (validateFile(file)) {
    selectedFile.value = file;
    emit('file-selected', file);
  }
};

const handleFileInput = (e) => {
  const file = e.target.files[0];
  if (validateFile(file)) {
    selectedFile.value = file;
    emit('file-selected', file);
  }
};

const removeFile = () => {
  selectedFile.value = null;
  error.value = '';
  if (fileInput.value) fileInput.value.value = '';
  emit('file-removed');
};
</script>
