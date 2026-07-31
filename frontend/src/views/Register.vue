<template>
  <div>
    <div class="auth-form-header">
      <div style="display: flex; justify-content: center; margin-bottom: 1rem;">
        <img src="../assets/images/InstaApp.png" alt="InstaApp Logo" style="height: 48px; object-fit: contain;" />
      </div>
      <h1>Create an account</h1>
      <p>Please enter your details to register.</p>
    </div>

    <form @submit.prevent="handleRegister">
      <div class="form-group">
        <label class="form-label">Username</label>
        <div class="form-input-container">
          <input type="text" class="form-input" v-model="username" placeholder="johndoe" required style="padding-left: 1rem;" />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="form-input-container">
          <input type="email" class="form-input" v-model="email" placeholder="name@company.com" required style="padding-left: 1rem;" />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="form-input-container">
          <input :type="showPassword ? 'text' : 'password'" class="form-input" v-model="password" placeholder="••••••••" required style="padding-left: 1rem;" />
          <div @click="showPassword = !showPassword" style="position: absolute; right: 1rem; cursor: pointer; font-size: 0.75rem; font-weight: 600; color: var(--primary);">
            {{ showPassword ? 'Hide' : 'Show' }}
          </div>
        </div>
      </div>

      <p v-if="errorMsg" style="color: red; font-size: 0.875rem; margin-bottom: 1rem; text-align: center;">{{ errorMsg }}</p>
      <div v-if="successMsg" style="background-color: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1rem; text-align: center; font-size: 0.875rem; font-weight: 500;">
        {{ successMsg }}
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1.5rem;" :disabled="loading">
        {{ loading ? 'Registering...' : 'Sign Up' }} <span v-if="!loading" style="margin-left: 0.5rem;">→</span>
      </button>
      
      <div style="text-align: center; font-size: 0.875rem; color: var(--text-light);">
        Already have an account? 
        <router-link to="/auth/login" style="color: var(--primary); font-weight: 600; text-decoration: none;">Log in</router-link>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../utils/axios';

const router = useRouter();
const username = ref('');
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

const handleRegister = async () => {
  loading.value = true;
  errorMsg.value = '';
  try {
    await api.post('/auth/register', {
      username: username.value,
      email: email.value,
      password: password.value
    });
    successMsg.value = 'Selamat Pendaftaran anda berhasil';
    setTimeout(() => {
      router.push('/auth/login');
    }, 1500);
  } catch (error: any) {
    if (error.response?.data?.errors) {
       const msgs = Object.values(error.response.data.errors).flat();
       errorMsg.value = msgs.join(', ');
    } else {
       errorMsg.value = error.response?.data?.message || 'Registration failed. Please try again.';
    }
  } finally {
    loading.value = false;
  }
};
</script>
