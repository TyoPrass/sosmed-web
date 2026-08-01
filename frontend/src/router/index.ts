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
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'feed',
          component: Feed
        },
        {
          path: 'profile',
          name: 'my-profile',
          component: Profile
        },
        {
          path: 'profile/:id',
          name: 'user-profile',
          component: Profile
        },
        {
          path: 'notifications',
          name: 'notifications',
          component: () => import('../views/Notifications.vue')
        },
        {
          path: 'search',
          name: 'search',
          component: () => import('../views/Explore.vue')
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
  const tokenString = localStorage.getItem('token');
  const hasToken = tokenString && tokenString !== 'undefined' && tokenString !== 'null';
  
  if (to.name === 'login' || to.name === 'register') {
    if (hasToken) {
      next({ name: 'feed' });
    } else {
      next();
    }
  } else {
    if (!hasToken && to.name !== 'login' && to.name !== 'register') {
      next({ name: 'login' });
    } else {
      next();
    }
  }
});

export default router;
