<template>
  <div class="explore-page">
    <!-- Header with Search Bar -->
    <div class="explore-header">
      <div class="search-input-container">
        <div class="search-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </div>
        <input 
          type="text" 
          v-model="searchQuery" 
          @input="handleInput"
          placeholder="Search" 
          class="search-input-dark" 
        />
        <button v-if="searchQuery" class="clear-btn-dark" @click="clearSearch">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <circle cx="12" cy="12" r="10" opacity="0.3"></circle>
            <path d="M15 9l-6 6M9 9l6 6" stroke="#000" stroke-width="2" stroke-linecap="round"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Content Area -->
    <div class="explore-content">
      <!-- Search Results -->
      <div v-if="searchQuery" class="search-results-section">
        <div v-if="loadingUsers" class="loading-state">Searching...</div>
        <div v-else-if="userResults.length === 0" class="empty-state">No results found.</div>
        <div v-else class="user-list">
          <div 
            v-for="user in userResults" 
            :key="user._id || user.id" 
            class="user-item"
            @click="goToProfile(user._id || user.id)"
          >
            <div class="user-avatar">
              <img v-if="user.avatar_path" :src="getImageUrl(user.avatar_path)" alt="Avatar" />
              <span v-else>{{ user.username.charAt(0).toUpperCase() }}</span>
            </div>
            <div class="user-info">
              <span class="user-name">{{ user.username }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Post Grid (Explore Feed) -->
      <div v-else class="post-grid-section">
        <div v-if="loadingPosts" class="loading-state">Loading posts...</div>
        <div v-else-if="posts.length === 0" class="empty-state">No posts yet.</div>
        <div v-else class="post-grid">
          <div 
            v-for="post in posts" 
            :key="post._id || post.id" 
            class="grid-item"
            @click="openPostDetail(post)"
          >
            <img v-if="post.image_path" :src="getImageUrl(post.image_path)" alt="Post image" />
            <div v-else class="text-post">
              <p>{{ post.content }}</p>
            </div>
            
            <div class="grid-overlay">
              <span>❤️ {{ post.likes_count || 0 }}</span>
              <span>💬 {{ post.comments_count || 0 }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <PostDetailModal 
      :isOpen="isPostDetailOpen" 
      :post="selectedPost"
      :profile="currentUserProfile"
      @close="closePostDetail"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../utils/axios';
import PostDetailModal from '../components/PostDetailModal.vue';

const router = useRouter();

const searchQuery = ref('');
const userResults = ref<any[]>([]);
const loadingUsers = ref(false);
const posts = ref<any[]>([]);
const loadingPosts = ref(true);
const currentUserProfile = ref<any>(null);

const isPostDetailOpen = ref(false);
const selectedPost = ref<any>(null);

let searchTimeout: any = null;
const BACKEND_URL = 'http://127.0.0.1:8000';

const getImageUrl = (imagePath: string | null) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) return imagePath.replace(/^https?:\/\/[^/]+/, BACKEND_URL);
  return `${BACKEND_URL}/${imagePath}`;
};

const fetchPosts = async () => {
  try {
    loadingPosts.value = true;
    const response = await api.get('/posts');
    posts.value = response.data;
  } catch (error) {
    console.error('Failed to fetch posts', error);
  } finally {
    loadingPosts.value = false;
  }
};

const fetchCurrentUser = async () => {
  try {
    const response = await api.get('/profile');
    currentUserProfile.value = response.data;
  } catch (error) {
    console.error('Failed to fetch current user', error);
  }
};

const handleInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  
  if (!searchQuery.value.trim()) {
    userResults.value = [];
    return;
  }

  loadingUsers.value = true;
  searchTimeout = setTimeout(async () => {
    try {
      const response = await api.get(`/search?q=${searchQuery.value}`);
      userResults.value = response.data;
    } catch (e) {
      console.error(e);
    } finally {
      loadingUsers.value = false;
    }
  }, 500);
};

const clearSearch = () => {
  searchQuery.value = '';
  userResults.value = [];
};

const goToProfile = (id: string) => {
  router.push(`/profile/${id}`);
};

const openPostDetail = (post: any) => {
  selectedPost.value = post;
  isPostDetailOpen.value = true;
};

const closePostDetail = () => {
  isPostDetailOpen.value = false;
  selectedPost.value = null;
};

onMounted(() => {
  fetchPosts();
  fetchCurrentUser();
});
</script>

<style scoped>
.explore-page {
  background-color: var(--white, #fff);
  min-height: 100vh;
  color: var(--text-dark, #262626);
  display: flex;
  flex-direction: column;
}

.explore-header {
  display: flex;
  justify-content: center;
  padding: 1rem;
  border-bottom: 1px solid var(--border, #dbdbdb);
  position: sticky;
  top: 0;
  background-color: var(--white, #fff);
  z-index: 10;
}

.search-input-container {
  position: relative;
  width: 100%;
  max-width: 600px;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: #8e8e8e;
  display: flex;
  align-items: center;
}
.search-icon svg {
  width: 18px;
  height: 18px;
}

.search-input-dark {
  width: 100%;
  background-color: var(--background-mute, #efefef);
  border: none;
  border-radius: 8px;
  color: var(--text-dark, #262626);
  padding: 12px 40px;
  font-size: 1rem;
  outline: none;
}
.search-input-dark::placeholder {
  color: #8e8e8e;
}

.clear-btn-dark {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #8e8e8e;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}
.clear-btn-dark svg {
  width: 18px;
  height: 18px;
}

.explore-content {
  flex: 1;
  padding: 1rem;
  max-width: 935px;
  margin: 0 auto;
  width: 100%;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 2rem;
  color: #8e8e8e;
}

/* User List (Search Results) */
.user-list {
  display: flex;
  flex-direction: column;
  max-width: 600px;
  margin: 0 auto;
}

.user-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
  border-radius: 8px;
}
.user-item:hover {
  background-color: var(--background-soft, #fafafa);
}

.user-avatar {
  border-radius: 50%;
  background-color: var(--primary, #0095f6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  overflow: hidden;
  margin-right: 1rem;
  width: 50px;
  height: 50px;
  flex-shrink: 0;
}
.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.user-info {
  display: flex;
  flex-direction: column;
}
.user-name {
  font-weight: 600;
  color: var(--text-dark, #262626);
  font-size: 1rem;
}

/* Post Grid */
.post-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

.grid-item {
  position: relative;
  aspect-ratio: 1 / 1;
  background-color: #f0f0f0;
  cursor: pointer;
  overflow: hidden;
}

.grid-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.text-post {
  padding: 1rem;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
  color: var(--text-dark, #262626);
}

.text-post p {
  margin: 0;
  font-size: 0.9rem;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.grid-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  opacity: 0;
  transition: opacity 0.2s;
  font-weight: bold;
}

.grid-item:hover .grid-overlay {
  opacity: 1;
}

@media (max-width: 768px) {
  .explore-content {
    padding: 0;
  }
}
</style>
