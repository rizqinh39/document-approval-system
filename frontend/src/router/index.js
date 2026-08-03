import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import PemohonDashboard from '../views/PemohonDashboard.vue';
import PenilaiDashboard from '../views/PenilaiDashboard.vue';
import PermohonanForm from '../views/PermohonanForm.vue';
import PermohonanDetail from '../views/PermohonanDetail.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
  },
  {
    path: '/',
    redirect: (to) => {
      const auth = useAuthStore();
      if (!auth.token) return '/login';
      return auth.userRole === 'penilai' ? '/penilai' : '/dashboard';
    },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: PemohonDashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/penilai',
    name: 'penilai-dashboard',
    component: PenilaiDashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/permohonan/create',
    name: 'permohonan-create',
    component: PermohonanForm,
    meta: { requiresAuth: true },
  },
  {
    path: '/permohonan/:id/edit',
    name: 'permohonan-edit',
    component: PermohonanForm,
    meta: { requiresAuth: true },
  },
  {
    path: '/permohonan/:id',
    name: 'permohonan-detail',
    component: PermohonanDetail,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    next({ name: 'login' });
  } else if (to.name === 'login' && auth.isAuthenticated) {
    const target = auth.userRole === 'penilai' ? '/penilai' : '/dashboard';
    next(target);
  } else {
    next();
  }
});

export default router;
