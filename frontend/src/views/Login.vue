<template>
  <div>
    <div class="auth-form-header">
      <!-- icon -->
      <div style="display: flex; justify-content: center; margin-bottom: 1rem;">
        <img src="../assets/images/InstaApp.png" alt="InstaApp Logo" style="height: 48px; object-fit: contain;" />
      </div>
      <h1>Welcome back</h1>
      <p>Please enter your details to sign in.</p>
    </div>

    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="form-input-container">
          <input type="email" class="form-input" v-model="email" placeholder="name@company.com" required style="padding-left: 1rem;" />
        </div>
      </div>

      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <label class="form-label" style="margin: 0;">Password</label>
          <a href="#" style="font-size: 0.75rem; font-weight: 600; color: var(--primary); text-decoration: none;">Forgot password?</a>
        </div>
        <div class="form-input-container" style="margin-top: 0.5rem;">
          <input :type="showPassword ? 'text' : 'password'" class="form-input" v-model="password" placeholder="••••••••" required style="padding-left: 1rem;" />
          <div @click="showPassword = !showPassword" style="position: absolute; right: 1rem; cursor: pointer; font-size: 0.75rem; font-weight: 600; color: var(--primary);">
            {{ showPassword ? 'Hide' : 'Show' }}
          </div>
        </div>
      </div>

      <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
        <input type="checkbox" id="remember" style="accent-color: var(--primary);" />
        <label for="remember" style="font-size: 0.875rem; color: var(--text-light); user-select: none;">Keep me logged in</label>
      </div>

      <p v-if="errorMsg" style="color: red; font-size: 0.875rem; margin-bottom: 1rem; text-align: center;">{{ errorMsg }}</p>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1.5rem;" :disabled="loading">
        {{ loading ? 'Signing In...' : 'Sign In' }} <span v-if="!loading" style="margin-left: 0.5rem;">→</span>
      </button>
      
      <div style="text-align: center; font-size: 0.875rem; color: var(--text-light);">
        Don't have an account? 
        <router-link to="/auth/register" style="color: var(--primary); font-weight: 600; text-decoration: none;">Register here</router-link>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../utils/axios';

const router = useRouter();
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const errorMsg = ref('');

const handleLogin = async () => {
  loading.value = true;
  errorMsg.value = '';
  try {
    const response = await api.post('/auth/login', {
      email: email.value,
      password: password.value
    });
    localStorage.setItem('token', response.data.access_token);
    router.push('/');
  } catch (error: any) {
    errorMsg.value = error.response?.data?.error || 'Login failed. Please check your credentials.';
  } finally {
    loading.value = false;
  }
};
</script>
