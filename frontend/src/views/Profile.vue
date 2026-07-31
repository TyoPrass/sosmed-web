<template>
  <div class="profile-page">
    <div v-if="loading" style="text-align: center; padding: 2rem;">Loading...</div>
    <div v-else-if="profile">
      <!-- Profile Header / Banner -->
      <div class="profile-banner">
        <div class="banner-bg"></div>
        <div class="profile-header-info">
          <div class="avatar-large">
            <img v-if="profile.avatar_path" :src="profile.avatar_path" alt="Avatar" />
            <span v-else>{{ profile.username?.charAt(0).toUpperCase() || 'U' }}</span>
          </div>
          <div class="header-text">
            <h2>{{ profile.username }}</h2>
            <p>@{{ profile.username }}</p>
          </div>
        </div>
      </div>

      <!-- Profile Stats & Actions -->
      <div class="profile-actions">
        <div class="profile-bio">
          <p>{{ profile.bio || 'No bio yet.' }}</p>
        </div>
        <div class="action-buttons">
          <router-link to="/settings" class="btn btn-outline" v-if="isCurrentUser">Edit Profile</router-link>
          <button class="btn btn-outline" v-if="isCurrentUser">
            <img src="../assets/icon/option.png" alt="Settings" style="width: 20px;" />
          </button>
        </div>
      </div>

      <div class="profile-stats">
        <div class="stat-item">
          <strong>{{ posts.length }}</strong> POSTS
        </div>
        <div class="stat-item">
          <strong>12.5k</strong> FOLLOWERS
        </div>
        <div class="stat-item">
          <strong>842</strong> FOLLOWING
        </div>
      </div>

      <!-- Tabs -->
      <div class="profile-tabs">
        <div class="tab active"><img src="../assets/icon/post.png" alt="Posts" class="tab-icon"/> Posts</div>
        <div class="tab"><img src="../assets/icon/save.png" alt="Saved" class="tab-icon"/> Saved</div>
        <div class="tab"><img src="../assets/icon/option.png" alt="Tagged" class="tab-icon"/> Tagged</div>
      </div>

      <!-- Posts Grid -->
      <div class="posts-grid">
        <div class="post-item" v-for="post in posts" :key="post._id">
          <!-- Try to show image, fallback to a placeholder if it's text only or no image -->
          <img v-if="post.image_path" :src="post.image_path" alt="Post image" />
          <div v-else class="post-text-only">
             <p>{{ post.caption.substring(0, 50) }}...</p>
          </div>
        </div>
        <div v-if="posts.length === 0" style="grid-column: span 3; text-align: center; padding: 2rem; color: var(--text-light);">
          No posts yet.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '../utils/axios';

const route = useRoute();
const profile = ref<any>(null);
const posts = ref<any[]>([]);
const loading = ref(true);
const currentUserId = ref<string | null>(null);

const isCurrentUser = computed(() => {
  if (!currentUserId.value || !profile.value) return false;
  return currentUserId.value === profile.value._id;
});

const fetchProfile = async () => {
  try {
    // get current user id first to check if we are viewing our own profile
    const meRes = await api.get('/profile');
    currentUserId.value = meRes.data._id;

    const id = route.params.id;
    if (id) {
      const res = await api.get(`/users/${id}`);
      profile.value = res.data;
    } else {
      profile.value = meRes.data;
    }

    if (profile.value && profile.value._id) {
      const postsRes = await api.get(`/posts?user_id=${profile.value._id}`);
      posts.value = postsRes.data;
    }
  } catch (error) {
    console.error('Failed to fetch profile', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchProfile();
});
</script>

<style scoped>
.profile-page {
  display: flex;
  flex-direction: column;
}

.profile-banner {
  position: relative;
  margin-bottom: 3rem;
}

.banner-bg {
  height: 180px;
  background: linear-gradient(135deg, #fce7f3 0%, #e0e7ff 100%);
  border-radius: 1rem;
}

.profile-header-info {
  position: absolute;
  bottom: -40px;
  left: 2rem;
  display: flex;
  align-items: flex-end;
  gap: 1rem;
}

.avatar-large {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  border: 4px solid var(--white);
  background-color: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  font-weight: bold;
  overflow: hidden;
}

.avatar-large img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.header-text h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
}

.header-text p {
  margin: 0;
  color: var(--text-light);
  font-size: 0.875rem;
}

.profile-actions {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 0 1rem;
  margin-bottom: 1.5rem;
}

.profile-bio {
  max-width: 60%;
  font-size: 0.95rem;
  line-height: 1.5;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.profile-stats {
  display: flex;
  gap: 2rem;
  padding: 0 1rem;
  margin-bottom: 2rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  font-size: 0.75rem;
  color: var(--text-light);
  letter-spacing: 0.05em;
}

.stat-item strong {
  font-size: 1.1rem;
  color: var(--text-dark);
}

.profile-tabs {
  display: flex;
  justify-content: center;
  border-top: 1px solid var(--border);
  gap: 2rem;
}

.tab {
  padding: 1rem 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-light);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tab.active {
  color: var(--text-dark);
  border-top: 1px solid var(--text-dark);
}

.tab-icon {
  width: 16px;
  opacity: 0.6;
}
.tab.active .tab-icon {
  opacity: 1;
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.post-item {
  aspect-ratio: 1;
  background-color: #f3f4f6;
  border-radius: 0.5rem;
  overflow: hidden;
  cursor: pointer;
}

.post-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.post-item:hover img {
  transform: scale(1.05);
}

.post-text-only {
  padding: 1rem;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  height: 100%;
  background-color: #fce7f3;
  color: #831843;
}
</style>
