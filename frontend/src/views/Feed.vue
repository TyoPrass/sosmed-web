<template>
  <div class="feed-wrapper">
    <div v-if="loading" style="text-align: center; padding: 2rem;">
      Loading posts...
    </div>
    
    <div v-else-if="posts.length === 0" style="text-align: center; padding: 2rem;">
      <p>No posts yet. Be the first to post!</p>
    </div>

    <div v-else class="post-card" v-for="post in posts" :key="post._id || post.id">
      <!-- Post Header -->
      <div class="post-header">
        <div class="post-user-info">
          <div class="post-avatar">
             <img v-if="post.user?.avatar_path" :src="post.user.avatar_path" alt="Avatar" />
             <span v-else>{{ post.user?.username.charAt(0).toUpperCase() }}</span>
          </div>
          <div class="post-meta">
            <span class="post-username">{{ post.user?.username }}</span>
            <span class="post-time">• {{ formatTime(post.created_at) }}</span>
            <div class="post-location">Seattle</div>
          </div>
        </div>
        <button class="btn-icon">
          <img src="../assets/icon/option.png" alt="Options" style="width: 16px; opacity: 0.6;" />
        </button>
      </div>

      <!-- Post Image -->
      <div class="post-image">
        <!-- Jika API mengembalikan URL valid, gunakan image_path, kalau kosong pakai dummy smile -->
        <img :src="getImageUrl(post.image_path)" alt="Post Image" />
      </div>

      <!-- Post Actions -->
      <div class="post-actions">
        <div class="actions-left">
          <button class="btn-icon action-btn" @click="toggleLike(post)">
            <svg 
              viewBox="0 0 24 24" 
              width="24" 
              height="24" 
              :fill="post.is_liked ? '#ed4956' : 'none'" 
              :stroke="post.is_liked ? '#ed4956' : '#000'" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
              :style="{ transform: post.is_liked ? 'scale(1.1)' : 'scale(1)', transition: 'transform 0.15s ease-in-out' }"
            >
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
          </button>
          <button class="btn-icon action-btn" @click="toggleCommentInput(post)">
            <img src="../assets/icon/comment.png" alt="Comment" />
          </button>
          <button class="btn-icon action-btn">
            <img src="../assets/icon/send.png" alt="Share" />
          </button>
        </div>
        <div class="actions-right">
          <button class="btn-icon action-btn" @click="toggleSave(post)">
            <svg 
              viewBox="0 0 24 24" 
              width="24" 
              height="24" 
              :fill="post.is_saved ? '#000' : 'none'" 
              stroke="#000" 
              stroke-width="2" 
              stroke-linecap="round" 
              stroke-linejoin="round"
              :style="{ transform: post.is_saved ? 'scale(1.1)' : 'scale(1)', transition: 'transform 0.15s ease-in-out' }"
            >
              <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Post Content -->
      <div class="post-content">
        <div class="post-likes">{{ post.likes_count || 0 }} likes</div>
        <div class="post-caption">
          <span class="post-username">{{ post.user?.username }}</span>
          {{ post.caption }}
        </div>
        
        <div class="post-comments-link" v-if="(post.comments_count || 0) > 0" @click="openModal(post)" style="cursor: pointer;">
          View all {{ post.comments_count }} comments
        </div>
        <div class="post-add-comment" @click="openModal(post)" style="cursor: pointer;">
          <div class="comment-avatar">
             <span style="font-size: 10px;">Me</span>
          </div>
          <div style="color: var(--text-light); font-size: 0.9rem; padding-left: 0.5rem;">Add a comment...</div>
        </div>
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
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../utils/axios';
import PostDetailModal from '../components/PostDetailModal.vue';

// Default image incase post image is not present
import defaultImage from '../assets/images/smile.png';

const BACKEND_URL = 'http://127.0.0.1:8000';

const posts = ref<any[]>([]);
const loading = ref(false);
const isModalOpen = ref(false);
const selectedPost = ref<any>(null);

const getImageUrl = (imagePath: string | null) => {
  if (!imagePath) return defaultImage;
  // If already a full URL, replace wrong host with correct one
  if (imagePath.startsWith('http')) {
    return imagePath.replace(/^https?:\/\/[^/]+/, BACKEND_URL);
  }
  // Relative path (new format)
  return `${BACKEND_URL}/${imagePath}`;
};

const fetchPosts = async () => {
  loading.value = true;
  try {
    const response = await api.get('/posts');
    // Assuming backend returns an array of posts or { data: [...] }
    posts.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching posts:', error);
  } finally {
    loading.value = false;
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
  } catch (e) {
    // revert on failure
    post.is_saved = !post.is_saved;
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

const toggleCommentInput = (post: any) => {
  openModal(post);
};

const submitComment = async (post: any) => {
  if (!post.new_comment || post.new_comment.trim() === '') return;
  await submitCommentFromModal(post, post.new_comment);
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

const formatTime = (dateString: string) => {
  if (!dateString) return 'Just now';
  const date = new Date(dateString);
  const now = new Date();
  const diffHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));
  if (diffHours < 24 && diffHours > 0) return `${diffHours}h`;
  if (diffHours === 0) return 'Just now';
  return `${Math.floor(diffHours / 24)}d`;
};

onMounted(() => {
  fetchPosts();
  window.addEventListener('post-created', fetchPosts);
});

onUnmounted(() => {
  window.removeEventListener('post-created', fetchPosts);
});
</script>

