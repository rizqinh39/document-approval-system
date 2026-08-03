<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider border shadow-2xs',
      badgeStyles[status] || 'bg-slate-100 text-slate-700 border-slate-200'
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full', dotStyles[status] || 'bg-slate-400']"></span>
    {{ statusLabel }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
});

const badgeStyles = {
  draft: 'bg-slate-100 text-slate-700 border-slate-200',
  submitted: 'bg-amber-50 text-amber-800 border-amber-200',
  revision: 'bg-blue-50 text-blue-800 border-blue-200',
  approved: 'bg-emerald-50 text-emerald-800 border-emerald-200',
  rejected: 'bg-rose-50 text-rose-800 border-rose-200',
};

const dotStyles = {
  draft: 'bg-slate-500',
  submitted: 'bg-amber-500 animate-pulse',
  revision: 'bg-blue-500',
  approved: 'bg-emerald-500',
  rejected: 'bg-rose-500',
};

const statusLabel = computed(() => {
  const labels = {
    draft: 'Draft',
    submitted: 'Diajukan',
    revision: 'Perlu Revisi',
    approved: 'Disetujui',
    rejected: 'Ditolak',
  };
  return labels[props.status] || props.status;
});
</script>
