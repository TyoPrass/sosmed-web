<template>
  <div class="feed-wrapper">
    <div v-if="loading" style="text-align: center; padding: 2rem;">
      Loading posts...
    </div>
    
    <div v-else-if="posts.length === 0" style="text-align: center; padding: 2rem;">
      <p>No posts yet. Be the first to post!</p>
    </div>

    <div v-else class="post-card" v-for="post in posts" :key="post._id">
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
          <!-- TODO: SVG fallback for heart if not in icon folder -->
          <button class="btn-icon action-btn" @click="toggleLike(post)">
            <img src="../assets/icon/notification.png" alt="Like" :style="{ filter: post.is_liked ? 'invert(27%) sepia(51%) saturate(2878%) hue-rotate(346deg) brightness(104%) contrast(97%)' : 'none' }" />
          </button>
          <button class="btn-icon action-btn">
            <img src="../assets/icon/comment.png" alt="Comment" />
          </button>
          <button class="btn-icon action-btn">
            <img src="../assets/icon/send.png" alt="Share" />
          </button>
        </div>
        <div class="actions-right">
          <button class="btn-icon action-btn">
            <img src="../assets/icon/save.png" alt="Save" />
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
        
        <div class="post-comments-link" v-if="(post.comments_count || 0) > 0">
          View all {{ post.comments_count }} comments
        </div>
        
        <div class="post-add-comment">
          <div class="comment-avatar">
             <span style="font-size: 10px;">Me</span>
          </div>
          <input type="text" v-model="post.new_comment" @keyup.enter="submitComment(post)" placeholder="Add a comment..." class="comment-input" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../utils/axios';

// Default image incase post image is not present
import defaultImage from '../assets/images/smile.png';

const BACKEND_URL = 'http://127.0.0.1:8000';

const posts = ref<any[]>([]);
const loading = ref(false);

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
    await api.post(`/posts/${post._id}/like`);
  } catch (e) {
    // revert on failure
    post.is_liked = !post.is_liked;
    post.likes_count = (post.likes_count || 0) + (post.is_liked ? 1 : -1);
  }
};

const submitComment = async (post: any) => {
  if (!post.new_comment || post.new_comment.trim() === '') return;
  
  const text = post.new_comment.trim();
  post.new_comment = '';
  post.comments_count = (post.comments_count || 0) + 1;
  
  try {
    await api.post(`/posts/${post._id}/comments`, { text });
  } catch (e) {
    post.comments_count = (post.comments_count || 0) - 1;
    post.new_comment = text;
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
