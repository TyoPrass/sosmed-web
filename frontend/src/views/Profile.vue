<template>
  <div class="profile-page">
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading profile...</p>
    </div>
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
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <h2>{{ profile.username }}</h2>
              <router-link to="/settings" class="btn-icon" v-if="isCurrentUser" title="Edit Profile">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-dark); opacity: 0.7;">
                  <circle cx="12" cy="12" r="3"></circle>
                  <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
              </router-link>
              <button 
                v-else-if="profile && profile.username"
                @click="toggleFollow" 
                class="btn" 
                :class="profile.is_following ? 'btn-outline' : 'btn-primary'" 
                style="padding: 0.25rem 1rem; font-size: 0.85rem; height: 32px; border-radius: 6px;"
              >
                {{ profile.is_following ? 'Following' : 'Follow' }}
              </button>
            </div>
            <p>@{{ profile.username }}</p>
          </div>
        </div>
      </div>

      <!-- Profile Stats & Actions -->
      <div class="profile-actions">
        <div class="profile-bio">
          <p>{{ profile.bio || 'No bio yet.' }}</p>
        </div>
      </div>

      <div class="profile-stats">
        <div class="stat-item">
          <strong>{{ posts.length }}</strong> POSTS
        </div>
        <div class="stat-item">
          <strong>{{ profile.followers_count || 0 }}</strong> FOLLOWERS
        </div>
        <div class="stat-item">
          <strong>{{ profile.following_count || 0 }}</strong> FOLLOWING
        </div>
      </div>

      <!-- Tabs -->
      <div class="profile-tabs">
        <div class="tab" :class="{ active: activeTab === 'POSTS' }" @click="setActiveTab('POSTS')">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> POSTS
        </div>
        <div class="tab" :class="{ active: activeTab === 'SAVED' }" @click="setActiveTab('SAVED')" v-if="isCurrentUser">
          <img src="../assets/icon/save.png" alt="Saved" class="tab-icon"/> SAVED
        </div>
      </div>

      <!-- Posts Grid -->
      <div class="posts-grid" v-if="posts.length > 0">
        <div class="post-item" v-for="post in posts" :key="post._id || post.id" @click="openModal(post)">
          <img v-if="post.image_path" :src="getImageUrl(post.image_path)" alt="Post image" />
          <div v-else class="post-text-only">
             <p>{{ post.caption ? post.caption.substring(0, 80) : '' }}</p>
          </div>
          <!-- Hover Overlay -->
          <div class="post-overlay">
            <div class="overlay-stat">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
              <span>{{ post.likes_count || 0 }}</span>
            </div>
            <div class="overlay-stat">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white" stroke="none"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
              <span>{{ post.comments_count || 0 }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="empty-posts">
        <div class="empty-icon">
          <svg v-if="activeTab === 'POSTS'" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          <svg v-if="activeTab === 'SAVED'" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
        </div>
        <h3 v-if="activeTab === 'POSTS'">No Posts Yet</h3>
        <h3 v-if="activeTab === 'SAVED'">No Saved Posts</h3>
        <p v-if="activeTab === 'POSTS'">When you share posts, they will appear on your profile.</p>
        <p v-if="activeTab === 'SAVED'">Only you can see what you've saved.</p>
      </div>
    </div>
    
    <!-- Post Detail Modal -->
    <PostDetailModal 
      :isOpen="isModalOpen" 
      :post="selectedPost" 
      @close="closeModal" 
      @toggle-like="toggleLike" 
      @toggle-save="toggleSave"
      @add-comment="submitCommentFromModal" 
      @post-deleted="handlePostDeleted"
      @post-updated="handlePostUpdated"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '../utils/axios';
import PostDetailModal from '../components/PostDetailModal.vue';

const route = useRoute();
const profile = ref<any>(null);
const posts = ref<any[]>([]);
const loading = ref(true);
const currentUserId = ref<string | null>(null);
const isModalOpen = ref(false);
const selectedPost = ref<any>(null);
const activeTab = ref('POSTS');

const BACKEND_URL = 'http://127.0.0.1:8000';

const isCurrentUser = computed(() => {
  if (!currentUserId.value || !profile.value) return false;
  const pId = profile.value._id || profile.value.id;
  return currentUserId.value === pId;
});

const getImageUrl = (imagePath: string | null) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) {
    return imagePath.replace(/^https?:\/\/[^/]+/, BACKEND_URL);
  }
  return `${BACKEND_URL}/${imagePath}`;
};

const fetchPosts = async () => {
  if (activeTab.value === 'SAVED') {
    try {
      const savedRes = await api.get('/saved-posts');
      posts.value = savedRes.data.data || [];
    } catch (e) {
      console.error('Failed to fetch saved posts', e);
    }
  } else {
    const pId = profile.value._id || profile.value.id;
    if (profile.value && pId) {
      try {
        const postsRes = await api.get(`/users/${pId}/posts`);
        const data = postsRes.data;
        posts.value = Array.isArray(data) ? data : (data.data || []);
      } catch (e) {
        console.error('Failed to fetch user posts', e);
      }
    }
  }
};

const setActiveTab = (tab: string) => {
  activeTab.value = tab;
  fetchPosts();
};

const fetchProfile = async () => {
  try {
    const meRes = await api.get('/profile');
    currentUserId.value = meRes.data._id || meRes.data.id;

    const id = route.params.id;
    if (id) {
      const res = await api.get(`/users/${id}`);
      profile.value = res.data;
    } else {
      profile.value = meRes.data;
    }

    await fetchPosts();
  } catch (error) {
    console.error('Failed to fetch profile', error);
  } finally {
    loading.value = false;
  }
};

const openModal = (post: any) => {
  selectedPost.value = post;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  setTimeout(() => {
    selectedPost.value = null;
  }, 200);
};

const handlePostDeleted = (postId: string) => {
  posts.value = posts.value.filter(p => p._id !== postId && p.id !== postId);
};

const handlePostUpdated = (updatedPost: any) => {
  const postId = updatedPost._id || updatedPost.id;
  const index = posts.value.findIndex(p => p._id === postId || p.id === postId);
  if (index !== -1) {
    posts.value[index] = updatedPost;
  }
};

const toggleLike = async (post: any) => {
  post.is_liked = !post.is_liked;
  post.likes_count = (post.likes_count || 0) + (post.is_liked ? 1 : -1);
  try {
    const postId = post._id || post.id;
    await api.post(`/posts/${postId}/like`);
  } catch (e) {
    // revert on failure
    post.is_liked = !post.is_liked;
    post.likes_count = (post.likes_count || 0) + (post.is_liked ? 1 : -1);
  }
};

const toggleSave = async (post: any) => {
  post.is_saved = !post.is_saved;
  try {
    const postId = post._id || post.id;
    await api.post(`/posts/${postId}/save`);
    
    // If we're on the SAVED tab and we unsave, maybe we should remove it from the list?
    if (activeTab.value === 'SAVED' && !post.is_saved) {
       posts.value = posts.value.filter(p => (p._id || p.id) !== postId);
       // close modal if the post was removed
       if (selectedPost.value && (selectedPost.value._id || selectedPost.value.id) === postId) {
          closeModal();
       }
    }
  } catch (e) {
    // revert on failure
    post.is_saved = !post.is_saved;
  }
};

const submitCommentFromModal = async (post: any, text: string) => {
  post.new_comment = '';
  post.comments_count = (post.comments_count || 0) + 1;
  
  if (!post.comments) post.comments = [];
  const tempId = 'temp-' + Date.now();
  post.comments.push({
    _id: tempId,
    text: text,
    user: { username: 'Sending...' }
  });
  
  try {
    const postId = post._id || post.id;
    const response = await api.post(`/posts/${postId}/comments`, { text });
    const index = post.comments.findIndex((c: any) => c._id === tempId || c.id === tempId);
    if (index !== -1) {
      post.comments[index] = response.data.comment;
    }
  } catch (e) {
    post.comments_count = (post.comments_count || 0) - 1;
    post.comments = post.comments.filter((c: any) => c._id !== tempId && c.id !== tempId);
  }
};

const toggleFollow = async () => {
  if (!profile.value) return;
  profile.value.is_following = !profile.value.is_following;
  profile.value.followers_count = (profile.value.followers_count || 0) + (profile.value.is_following ? 1 : -1);
  try {
    const id = profile.value._id || profile.value.id;
    await api.post(`/users/${id}/follow`);
  } catch (error) {
    profile.value.is_following = !profile.value.is_following;
    profile.value.followers_count = (profile.value.followers_count || 0) + (profile.value.is_following ? 1 : -1);
    console.error('Failed to toggle follow status', error);
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

.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-light);
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.profile-banner {
  position: relative;
  margin-bottom: 3rem;
}

.banner-bg {
  height: 180px;
  background: linear-gradient(135deg, #fce7f3 0%, #e0e7ff 50%, #dbeafe 100%);
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
  width: 96px;
  height: 96px;
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
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.avatar-large img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.header-text h2 {
  margin: 0;
  font-size: 1.3rem;
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
  gap: 2.5rem;
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
  font-size: 1.15rem;
  color: var(--text-dark);
}

.profile-tabs {
  display: flex;
  justify-content: center;
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  gap: 2.5rem;
}

.tab {
  padding: 0.85rem 0;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-light);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  position: relative;
  transition: color 0.2s;
}

.tab:hover {
  color: var(--text-dark);
}

.tab.active {
  color: var(--text-dark);
}

.tab.active::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--primary);
  border-radius: 2px 2px 0 0;
}

.tab-icon {
  width: 14px;
  opacity: 0.6;
}
.tab.active .tab-icon {
  opacity: 1;
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
  margin-top: 1rem;
}

.post-item {
  aspect-ratio: 1;
  background-color: #f3f4f6;
  overflow: hidden;
  cursor: pointer;
  position: relative;
}

.post-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.post-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.post-item:hover .post-overlay {
  opacity: 1;
}

.post-item:hover img {
  transform: scale(1.03);
}

.overlay-stats {
  display: flex;
  gap: 1.5rem;
}

.overlay-stat {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: white;
  font-weight: 700;
  font-size: 0.95rem;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.post-text-only {
  padding: 1rem;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  height: 100%;
  background: linear-gradient(135deg, #fce7f3, #ede9fe);
  color: #831843;
  font-weight: 500;
}

.empty-posts {
  grid-column: span 3;
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-light);
  border-top: none;
  margin-top: 2rem;
}

.empty-icon {
  margin-bottom: 1rem;
  color: var(--border);
}

.empty-posts h3 {
  font-size: 1.2rem;
  color: var(--text-dark);
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.empty-posts p {
  font-size: 0.9rem;
  color: var(--text-light);
}

@media (max-width: 768px) {
  .profile-banner {
    margin-bottom: 4rem;
  }
  .banner-bg {
    height: 120px;
  }
  .profile-header-info {
    flex-direction: column;
    align-items: center;
    left: 50%;
    transform: translateX(-50%);
    bottom: -50px;
    text-align: center;
  }
  .header-text {
    align-items: center;
    display: flex;
    flex-direction: column;
  }
  .header-text > div {
    justify-content: center;
  }
  .avatar-large {
    width: 80px;
    height: 80px;
    font-size: 2rem;
  }
  .profile-actions {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 1rem;
  }
  .profile-bio {
    max-width: 100%;
  }
  .profile-stats {
    justify-content: center;
  }
  .posts-grid {
    gap: 2px;
  }
}
</style>
