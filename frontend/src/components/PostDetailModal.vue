<template>
  <div class="modal-overlay" v-if="isOpen && post" @click.self="closeModal">
    <button class="close-btn" @click="closeModal">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <div class="modal-container">
      <!-- Left Side: Image -->
      <div class="modal-image-section">
        <img :src="getImageUrl(post.image_path)" alt="Post Image" class="modal-image" />
      </div>

      <!-- Right Side: Details -->
      <div class="modal-details-section">
        
        <!-- Header: Post Author -->
        <div class="modal-header">
          <div class="post-avatar">
            <img v-if="post.user?.avatar_path" :src="post.user.avatar_path" alt="Avatar" />
            <span v-else>{{ post.user?.username?.charAt(0).toUpperCase() || 'U' }}</span>
          </div>
          <span class="post-username">{{ post.user?.username }}</span>
          <span class="post-time" style="margin-left: auto;">• {{ formatTime(post.created_at) }}</span>
          
          <!-- Post Options Menu (3 dots) -->
          <div class="post-options-dropdown" v-if="isPostOwner" style="position: relative; margin-left: 0.5rem;">
            <button class="btn-icon" @click="showOptions = !showOptions">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.6;"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
            </button>
            <div v-if="showOptions" class="dropdown-menu">
              <button @click="startEditing" class="dropdown-item">Edit</button>
              <button @click="deletePost" class="dropdown-item delete">Delete</button>
            </div>
          </div>
        </div>

        <!-- Middle: Comments and Caption (Scrollable) -->
        <div class="modal-comments-area">
          <!-- Caption -->
          <div class="comment-item" v-if="post.caption || isEditing">
            <div class="post-avatar comment-avatar">
              <img v-if="post.user?.avatar_path" :src="post.user.avatar_path" alt="Avatar" />
              <span v-else>{{ post.user?.username?.charAt(0).toUpperCase() || 'U' }}</span>
            </div>
            <div class="comment-content" style="flex: 1;">
              <span class="post-username">{{ post.user?.username }}</span>
              <span v-if="!isEditing">{{ post.caption }}</span>
              <div v-else style="margin-top: 0.5rem;">
                <textarea v-model="editCaptionText" class="edit-textarea" rows="3"></textarea>
                <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                  <button class="post-comment-btn" style="font-size: 0.8rem;" @click="saveEdit">Save</button>
                  <button class="post-comment-btn" style="font-size: 0.8rem; color: var(--text-light);" @click="cancelEdit">Cancel</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Comments List -->
          <div v-if="post.comments && post.comments.length > 0">
            <div class="comment-item" v-for="comment in post.comments" :key="comment._id || comment.id">
              <div class="post-avatar comment-avatar">
                <img v-if="comment.user?.avatar_path" :src="comment.user.avatar_path" alt="Avatar" />
                <span v-else>{{ comment.user?.username?.charAt(0).toUpperCase() || 'U' }}</span>
              </div>
              <div class="comment-content">
                <span class="post-username">{{ comment.user?.username || 'User' }}</span>
                <span>{{ comment.text }}</span>
              </div>
            </div>
          </div>
          <div v-else class="no-comments">
            <p>No comments yet.</p>
            <p style="font-size: 0.8rem; color: var(--text-light);">Start the conversation.</p>
          </div>
        </div>

        <!-- Bottom: Actions & Input -->
        <div class="modal-actions-area">
          <div class="post-actions" style="padding: 0.5rem 0;">
            <div class="actions-left">
              <button class="btn-icon action-btn" @click="$emit('toggle-like', post)">
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
              <button class="btn-icon action-btn" @click="focusInput">
                <img src="../assets/icon/comment.png" alt="Comment" />
              </button>
              <button class="btn-icon action-btn">
                <img src="../assets/icon/send.png" alt="Share" />
              </button>
            </div>
            <div class="actions-right">
              <button class="btn-icon action-btn" @click="$emit('toggle-save', post)">
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
          <div class="post-likes" style="padding-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600;">
            {{ post.likes_count || 0 }} likes
          </div>
          
          <div class="comment-input-wrapper">
            <div class="comment-avatar">
               <span style="font-size: 10px;">Me</span>
            </div>
            <input 
              ref="commentInput"
              type="text" 
              v-model="newComment" 
              @keyup.enter="submitComment" 
              placeholder="Add a comment..." 
              class="comment-input" 
            />
            <button class="post-comment-btn" @click="submitComment" :disabled="!newComment.trim()">Post</button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '../utils/axios';
import defaultImage from '../assets/images/smile.png';

const BACKEND_URL = 'http://127.0.0.1:8000';

const props = defineProps<{
  isOpen: boolean;
  post: any;
}>();

const emit = defineEmits(['close', 'add-comment', 'toggle-like', 'post-updated', 'post-deleted', 'toggle-save']);

const newComment = ref('');
const commentInput = ref<HTMLInputElement | null>(null);

const currentUserId = ref<string | null>(null);
const showOptions = ref(false);
const isEditing = ref(false);
const editCaptionText = ref('');

onMounted(async () => {
  try {
    const res = await api.get('/profile');
    currentUserId.value = res.data._id || res.data.id;
  } catch (e) {}
});

const isPostOwner = computed(() => {
  if (!currentUserId.value || !props.post) return false;
  return props.post.user_id === currentUserId.value || props.post.user?.id === currentUserId.value || props.post.user?._id === currentUserId.value;
});

const closeModal = () => {
  showOptions.value = false;
  isEditing.value = false;
  emit('close');
};

const focusInput = () => {
  if (commentInput.value) {
    commentInput.value.focus();
  }
};

const startEditing = () => {
  editCaptionText.value = props.post.caption || '';
  isEditing.value = true;
  showOptions.value = false;
};

const cancelEdit = () => {
  isEditing.value = false;
};

const saveEdit = async () => {
  if (!props.post) return;
  try {
    const postId = props.post._id || props.post.id;
    const res = await api.put(`/posts/${postId}`, { caption: editCaptionText.value });
    props.post.caption = res.data.post.caption;
    isEditing.value = false;
    emit('post-updated', props.post);
  } catch (error) {
    console.error('Failed to update post', error);
  }
};

const deletePost = async () => {
  if (!props.post || !confirm('Are you sure you want to delete this post?')) return;
  try {
    const postId = props.post._id || props.post.id;
    await api.delete(`/posts/${postId}`);
    showOptions.value = false;
    emit('post-deleted', postId);
    closeModal();
  } catch (error) {
    console.error('Failed to delete post', error);
  }
};

const submitComment = () => {
  if (!newComment.value.trim()) return;
  emit('add-comment', props.post, newComment.value.trim());
  newComment.value = '';
};

const getImageUrl = (imagePath: string | null) => {
  if (!imagePath) return defaultImage;
  if (imagePath.startsWith('http')) {
    return imagePath.replace(/^https?:\/\/[^/]+/, BACKEND_URL);
  }
  return `${BACKEND_URL}/${imagePath}`;
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
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background: var(--white);
  border-radius: 4px;
  width: 95%;
  max-width: 950px;
  height: 85vh;
  display: flex;
  flex-direction: row;
  overflow: hidden;
  position: relative;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.close-btn {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  background: none;
  border: none;
  cursor: pointer;
  color: white;
  z-index: 1010;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.close-btn:hover {
  opacity: 1;
}

.modal-image-section {
  flex: 1.5;
  background-color: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.modal-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.modal-details-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--white);
  max-width: 350px;
}

.modal-header {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid var(--border);
}

.post-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
  overflow: hidden;
  font-weight: bold;
  color: var(--text-dark);
}

.post-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.post-username {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--text-dark);
}

.post-time {
  font-size: 0.8rem;
  color: var(--text-light);
}

.modal-comments-area {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.comment-item {
  display: flex;
  align-items: flex-start;
}

.comment-avatar {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
}

.comment-content {
  font-size: 0.875rem;
  line-height: 1.4;
  word-break: break-word;
}

.comment-content .post-username {
  margin-right: 0.4rem;
}

.no-comments {
  text-align: center;
  margin-top: 2rem;
  color: var(--text-dark);
  font-weight: 600;
}

.modal-actions-area {
  padding: 0 1rem;
  border-top: 1px solid var(--border);
}

.post-actions {
  display: flex;
  justify-content: space-between;
}

.actions-left {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  background: none;
  border: none;
  padding: 0.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon img {
  width: 24px;
  height: 24px;
}

.comment-input-wrapper {
  display: flex;
  align-items: center;
  padding: 1rem 0;
  border-top: 1px solid var(--border);
}

.comment-input {
  flex: 1;
  border: none;
  outline: none;
  padding: 0 0.5rem;
  font-size: 0.9rem;
}

.comment-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: var(--text-dark);
}

.post-comment-btn {
  background: none;
  border: none;
  color: var(--primary);
  font-weight: 600;
  cursor: pointer;
}

.post-comment-btn:disabled {
  opacity: 0.5;
  cursor: default;
}

/* Post Options Dropdown */
.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  min-width: 120px;
  z-index: 50;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  margin-top: 0.25rem;
}

.dropdown-item {
  padding: 0.75rem 1rem;
  text-align: left;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  color: var(--text-dark);
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item.delete {
  color: #ed4956;
  font-weight: 600;
}

.edit-textarea {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 0.25rem;
  padding: 0.5rem;
  font-size: 0.85rem;
  resize: vertical;
  outline: none;
}
.edit-textarea:focus {
  border-color: var(--primary);
}

@media (max-width: 768px) {
  .modal-container {
    flex-direction: column;
    height: 90vh;
  }
  
  .modal-image-section {
    flex: none;
    height: 40%;
  }

  .modal-details-section {
    flex: 1;
    max-width: 100%;
  }
}
</style>
