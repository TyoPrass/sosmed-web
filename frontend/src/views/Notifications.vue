<template>
  <div class="notifications-page">
    <div class="notifications-header">
      <h2>Notifications</h2>
    </div>

    <div v-if="loading" style="text-align: center; padding: 2rem;">
      Loading notifications...
    </div>

    <div v-else-if="notifications.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
      </div>
      <h3>No Notifications</h3>
      <p>When someone likes or comments on your posts, you'll see it here.</p>
    </div>

    <div v-else class="notifications-list">
      <div 
        v-for="notif in notifications" 
        :key="notif._id" 
        class="notification-item" 
        :class="{ 'unread': !notif.is_read }"
      >
        <div class="notif-avatar">
          <img v-if="notif.actor?.avatar_path" :src="notif.actor.avatar_path" alt="Avatar" />
          <span v-else>{{ notif.actor?.username?.charAt(0).toUpperCase() || 'U' }}</span>
        </div>
        
        <div class="notif-content">
          <span class="notif-username">{{ notif.actor?.username }}</span>
          <span v-if="notif.type === 'like'"> liked your post.</span>
          <span v-else-if="notif.type === 'comment'"> commented: "{{ notif.message }}"</span>
          <span class="notif-time">{{ formatTime(notif.created_at) }}</span>
        </div>

        <div class="notif-post-preview" v-if="notif.post">
          <img v-if="notif.post.image_path" :src="getImageUrl(notif.post.image_path)" alt="Post" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../utils/axios';

const BACKEND_URL = 'http://127.0.0.1:8000';

const notifications = ref<any[]>([]);
const loading = ref(true);

const fetchNotifications = async () => {
  try {
    const res = await api.get('/notifications');
    notifications.value = res.data.data;
  } catch (error) {
    console.error('Error fetching notifications', error);
  } finally {
    loading.value = false;
  }
};

const markAsRead = async () => {
  try {
    await api.post('/notifications/read');
  } catch (error) {
    console.error('Error marking notifications as read', error);
  }
};

const getImageUrl = (imagePath: string | null) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) {
    return imagePath.replace(/^https?:\/\/[^/]+/, BACKEND_URL);
  }
  return `${BACKEND_URL}/${imagePath}`;
};

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diffHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));
  if (diffHours < 24 && diffHours > 0) return `${diffHours}h`;
  if (diffHours === 0) {
    const diffMins = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));
    return diffMins > 0 ? `${diffMins}m` : 'Just now';
  }
  const diffDays = Math.floor(diffHours / 24);
  return `${diffDays}d`;
};

onMounted(() => {
  fetchNotifications();
});

onUnmounted(() => {
  markAsRead();
});
</script>

<style scoped>
.notifications-page {
  background: var(--white);
  border-radius: 1rem;
  border: 1px solid var(--border);
  min-height: 80vh;
}

.notifications-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
}

.notifications-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
}

.notifications-list {
  display: flex;
  flex-direction: column;
}

.notification-item {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: #f9fafb;
}

.notification-item.unread {
  background-color: #f0fdf4; /* Light green tint for unread */
}

.notif-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background-color: #e5e7eb;
  margin-right: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  overflow: hidden;
  flex-shrink: 0;
}

.notif-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.notif-content {
  flex: 1;
  font-size: 0.95rem;
  line-height: 1.4;
  color: var(--text-dark);
}

.notif-username {
  font-weight: 600;
}

.notif-time {
  color: var(--text-light);
  font-size: 0.85rem;
  margin-left: 0.25rem;
}

.notif-post-preview {
  width: 44px;
  height: 44px;
  margin-left: 1rem;
  flex-shrink: 0;
  border-radius: 4px;
  overflow: hidden;
  background-color: #e5e7eb;
}

.notif-post-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
  color: var(--text-light);
}

.empty-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 2px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  color: var(--text-dark);
  margin-bottom: 0.5rem;
}
</style>
