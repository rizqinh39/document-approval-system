<template>
  <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
    <div
      v-for="(log, idx) in logs"
      :key="log.id || idx"
      class="relative group"
    >
      <!-- Timeline Node Dot -->
      <div class="absolute -left-6 top-1 w-4 h-4 rounded-full bg-blue-900 border-2 border-white shadow-xs group-hover:scale-125 transition-transform"></div>

      <div class="space-y-1">
        <div class="flex items-center justify-between">
          <p class="text-xs font-bold text-slate-900">{{ log.action }}</p>
          <span class="text-[10px] text-slate-400 font-mono">{{ log.created_at }}</span>
        </div>

        <p class="text-[11px] font-semibold text-blue-900">{{ log.actor_name || 'System Actor' }}</p>
        <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/60 font-sans">
          {{ log.notes || 'Tidak ada catatan.' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  logs: {
    type: Array,
    default: () => [],
  },
});
</script>
