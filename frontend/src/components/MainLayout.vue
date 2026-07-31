<template>
  <div class="main-wrapper">
    <!-- Left Sidebar -->
    <aside class="sidebar-left">
      <div class="sidebar-brand">
        <img src="../assets/images/InstaApp.png" alt="InstaApp Logo" style="height: 32px; object-fit: contain; margin-right: 0.5rem;" />
        <span style="font-weight: bold; font-size: 1.25rem; color: var(--primary);">InstaApp</span>
      </div>
      
      <nav class="sidebar-nav">
        <router-link to="/" class="nav-item active">
          <img src="../assets/icon/home_new.png" alt="Home" class="nav-icon" />
          <span>Home</span>
        </router-link>
        <a href="#" class="nav-item">
          <img src="../assets/icon/search.png" alt="Search" class="nav-icon" />
          <span>Search</span>
        </a>
        <a href="#" class="nav-item" @click.prevent="isCreatePostOpen = true">
          <img src="../assets/icon/post.png" alt="Create Post" class="nav-icon" />
          <span>Create Post</span>
        </a>
        <a href="#" class="nav-item">
          <img src="../assets/icon/notification.png" alt="Notifications" class="nav-icon" />
          <span>Notifications</span>
        </a>
        <router-link to="/profile" class="nav-item">
          <img src="../assets/icon/Profil.png" alt="Profile" class="nav-icon" />
          <span>Profile</span>
        </router-link>
      </nav>

      <div class="sidebar-bottom">
        <a href="#" @click.prevent="handleLogout" class="nav-item">
          <img src="../assets/icon/logout.png" alt="Logout" class="nav-icon" />
          <span>Logout</span>
        </a>
      </div>
    </aside>

    <!-- Center Feed Content -->
    <main class="feed-container">
      <router-view></router-view>
    </main>

    <!-- Right Sidebar (Profile & Suggestions) -->
    <aside class="sidebar-right">
      <div class="profile-snippet" v-if="profile">
        <div class="snippet-avatar">
          <img v-if="profile.avatar_path" :src="profile.avatar_path" alt="Avatar" />
          <span v-else>{{ profile.username.charAt(0).toUpperCase() }}</span>
        </div>
        <div class="snippet-info">
          <span class="snippet-name">{{ profile.username }}</span>
          <span class="snippet-username">@{{ profile.username }}</span>
        </div>
        <button class="btn-text" style="color: var(--primary);">Switch</button>
      </div>
      
      <div class="suggestions-header">
        <span class="suggestions-title">Suggestions for you</span>
        <button class="btn-text">See All</button>
      </div>

      <div class="suggestion-item">
        <div class="snippet-avatar" style="width: 32px; height: 32px;">
           <span style="font-size: 14px;">S</span>
        </div>
        <div class="snippet-info">
          <span class="snippet-name" style="font-size: 0.8rem;">social_expert</span>
          <span class="snippet-username" style="font-size: 0.75rem;">Followed by...</span>
        </div>
        <button class="btn-text" style="color: var(--primary); font-size: 0.75rem;">Follow</button>
      </div>

      <footer class="right-footer">
        About • Help • Press • API • Jobs • Privacy • Terms <br>
        © 2024 INSTAAPP FROM ME
      </footer>
    </aside>

    <!-- Create Post Modal -->
    <CreatePostModal 
      :isOpen="isCreatePostOpen" 
      :profile="profile" 
      @close="isCreatePostOpen = false" 
      @post-created="handlePostCreated" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../utils/axios';
import CreatePostModal from './CreatePostModal.vue';

const router = useRouter();
const profile = ref<any>(null);
const isCreatePostOpen = ref(false);

const handlePostCreated = () => {
  // Dispatch a custom event to notify Feed.vue to refresh
  window.dispatchEvent(new Event('post-created'));
};

const handleLogout = async () => {
  try {
    await api.post('/logout');
  } catch (e) {
    // ignore error
  } finally {
    localStorage.removeItem('token');
    router.push('/auth/login');
  }
};

const fetchProfile = async () => {
  try {
    const response = await api.get('/profile');
    profile.value = response.data;
  } catch (error) {
    console.error('Failed to fetch profile', error);
  }
};

onMounted(() => {
  fetchProfile();
});
</script>
