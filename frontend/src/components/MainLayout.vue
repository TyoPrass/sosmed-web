<template>
  <div class="main-wrapper">
    <!-- Left Sidebar -->
    <aside class="sidebar-left">
      <div class="sidebar-brand">
        <img src="../assets/images/InstaApp.png" alt="InstaApp Logo" style="height: 32px; object-fit: contain; margin-right: 0.5rem;" />
        <span style="font-weight: bold; font-size: 1.25rem; color: var(--primary);">InstaApp</span>
      </div>
      
      <nav class="sidebar-nav">
        <router-link to="/" class="nav-item" exact-active-class="active">
          <img src="../assets/icon/home_new.png" alt="Home" class="nav-icon" />
          <span>Home</span>
        </router-link>
        <router-link to="/search" class="nav-item" active-class="active">
          <img src="../assets/icon/search.png" alt="Search" class="nav-icon" />
          <span>Search</span>
        </router-link>
        <a href="#" class="nav-item" @click.prevent="isCreatePostOpen = true">
          <img src="../assets/icon/post.png" alt="Create Post" class="nav-icon" />
          <span>Create Post</span>
        </a>
        <router-link to="/notifications" class="nav-item" active-class="active">
          <img src="../assets/icon/notification.png" alt="Notifications" class="nav-icon" />
          <span>Notifications</span>
        </router-link>
        <router-link to="/profile" class="nav-item" active-class="active">
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
        <span class="suggestions-title">Trending Topics</span>
      </div>

      <div class="suggestion-item" style="cursor: pointer;">
        <div class="snippet-info">
          <span class="snippet-name" style="font-size: 0.85rem; font-weight: bold;">#InstaApp</span>
          <span class="snippet-username" style="font-size: 0.75rem;">15.4k posts</span>
        </div>
      </div>
      
      <div class="suggestion-item" style="cursor: pointer;">
        <div class="snippet-info">
          <span class="snippet-name" style="font-size: 0.85rem; font-weight: bold;">#WebDevelopment</span>
          <span class="snippet-username" style="font-size: 0.75rem;">8.2k posts</span>
        </div>
      </div>

      <div class="suggestion-item" style="cursor: pointer;">
        <div class="snippet-info">
          <span class="snippet-name" style="font-size: 0.85rem; font-weight: bold;">#Photography</span>
          <span class="snippet-username" style="font-size: 0.75rem;">5.1k posts</span>
        </div>
      </div>

      <footer class="right-footer">
        About • Help • Press • API • Jobs • Privacy • Terms <br>
        © 2026 INSTAAPP FROM ME
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
