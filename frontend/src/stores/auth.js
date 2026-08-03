import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('qi_token') || null,
    user: JSON.parse(localStorage.getItem('qi_user')) || null,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    userRole: (state) => {
      if (!state.user) return null;
      if (typeof state.user.role === 'string') return state.user.role;
      if (Array.isArray(state.user.roles) && state.user.roles.length > 0) {
        return state.user.roles[0];
      }
      return 'pemohon';
    },
  },
  actions: {
    initAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      }
    },
    async login(email, password) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/v1/login', { email, password });
        const { token, user } = response.data.data;
        this.setAuthData(token, user);
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal login, periksa email & password';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    async register(name, email, password) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/v1/register', { name, email, password });
        const { token, user } = response.data.data;
        this.setAuthData(token, user);
        return true;
      } catch (err) {
        this.error = err.response?.data?.message || 'Gagal mendaftar akun';
        throw err;
      } finally {
        this.loading = false;
      }
    },
    async logout() {
      try {
        if (this.token) {
          await axios.post('/api/v1/logout');
        }
      } catch (e) {
        console.warn('Logout API error swallowed', e);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('qi_token');
        localStorage.removeItem('qi_user');
        delete axios.defaults.headers.common['Authorization'];
      }
    },
    setAuthData(token, user) {
      this.token = token;
      this.user = user;
      localStorage.setItem('qi_token', token);
      localStorage.setItem('qi_user', JSON.stringify(user));
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
  },
});
