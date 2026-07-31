<template>
  <div class="auth-wrapper">


    <main class="auth-container">
      <div class="auth-card">
        <div class="auth-illustration">
          <img src="../assets/images/smile.png" alt="Connect with your world" />
          <h2>Connect with your world.</h2>
          <p>Join the vibrant community of professionals and creators.</p>
        </div>
        <div class="auth-form-container">
          <slot></slot>
          <router-view></router-view>
        </div>
      </div>
    </main>
    
    <footer class="auth-footer">
      © 2026 InstaApp. Precision & Connectivity.
    </footer>
  </div>
</template>

<script setup lang="ts">
// AuthLayout acts as a wrapper for Login and Register views
</script>
