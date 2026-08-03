import { defineStore } from 'pinia';
import axios from 'axios';

export const usePermohonanStore = defineStore('permohonan', {
  state: () => ({
    items: [],
    penilaiQueue: [],
    currentDetail: null,
    summary: null,
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
    },
  }),
  actions: {
    async fetchPermohonan(page = 1, search = '') {
      this.loading = true;
      try {
        const response = await axios.get(`/api/v1/permohonan?page=${page}&search=${search}`);
        const result = response.data.data;
        this.items = result.data || result;
        if (result.current_page) {
          this.pagination = {
            current_page: result.current_page,
            last_page: result.last_page,
            total: result.total,
          };
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mengambil data permohonan';
      } finally {
        this.loading = false;
      }
    },

    async fetchPenilaiQueue(page = 1) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/v1/penilaian/queue?page=${page}`);
        const result = response.data.data;
        this.penilaiQueue = result.data || result;
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mengambil antrean penilai';
      } finally {
        this.loading = false;
      }
    },

    async fetchDetail(id) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/v1/permohonan/${id}`);
        this.currentDetail = response.data.data;
        return this.currentDetail;
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mengambil detail permohonan';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async createDraft(judul_project, deskripsi) {
      this.loading = true;
      try {
        const response = await axios.post('/api/v1/permohonan', {
          judul_project,
          deskripsi,
        });
        const created = response.data.data;
        this.items.unshift(created);
        return created;
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal membuat draft permohonan';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async submitPermohonan(id) {
      this.loading = true;
      try {
        const response = await axios.post(`/api/v1/permohonan/${id}/submit`);
        const updated = response.data.data;
        const index = this.items.findIndex(item => item.id === id);
        if (index !== -1) {
          this.items[index] = updated;
        }
        if (this.currentDetail?.id === id) {
          this.currentDetail = updated;
        }
        return updated;
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mengirim permohonan';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async uploadDocument(id, file) {
      const formData = new FormData();
      formData.append('file', file);
      try {
        const response = await axios.post(`/api/v1/permohonan/${id}/upload`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (this.currentDetail && this.currentDetail.id === id) {
          if (!this.currentDetail.documents) this.currentDetail.documents = [];
          this.currentDetail.documents.push(response.data.data);
        }
        return response.data.data;
      } catch (err) {
        throw err;
      }
    },

    async deleteDocument(id, docId) {
      try {
        await axios.delete(`/api/v1/permohonan/${id}/documents/${docId}`);
        if (this.currentDetail && this.currentDetail.id === id) {
          this.currentDetail.documents = this.currentDetail.documents.filter(d => d.id !== docId);
        }
      } catch (err) {
        throw err;
      }
    },

    async reviewPermohonan(id, action, notes) {
      this.loading = true;
      try {
        const response = await axios.post(`/api/v1/penilaian/${id}/review`, {
          action,
          notes,
        });
        const updated = response.data.data;
        this.penilaiQueue = this.penilaiQueue.filter(item => item.id !== id);
        return updated;
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal menyimpan keputusan penilaian';
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
