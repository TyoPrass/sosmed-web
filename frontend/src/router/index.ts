import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import AuthLayout from '../components/AuthLayout.vue';
import MainLayout from '../components/MainLayout.vue';
import Feed from '../views/Feed.vue';
import Profile from '../views/Profile.vue';
import Settings from '../views/Settings.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: MainLayout,
      children: [
        {
          path: '',
          name: 'feed',
          component: Feed
        },
        {
          path: 'profile/:id?',
          name: 'profile',
          component: Profile
        },
        {
          path: 'settings',
          name: 'settings',
          component: Settings
        }
      ]
    },
    {
      path: '/auth',
      component: AuthLayout,
      children: [
        {
          path: 'login',
          name: 'login',
          component: Login
        },
        {
          path: 'register',
          name: 'register',
          component: Register
        }
      ]
    }
  ]
});

// Navigation guard sederhana
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  
  if (to.name === 'login' || to.name === 'register') {
    if (token) {
      next({ name: 'feed' });
    } else {
      next();
    }
  } else {
    if (!token && to.name !== 'login' && to.name !== 'register') {
      next({ name: 'login' });
    } else {
      next();
    }
  }
});

export default router;
