<template>
  <div class="modal-overlay" v-if="isOpen">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Create new post</h2>
        <button class="close-btn" @click="closeModal">✕</button>
      </div>

      <div class="modal-body">
        <!-- Area Gambar -->
        <div class="media-upload-area" :class="{ 'has-image': imagePreview }">
          <input 
            type="file" 
            ref="fileInput" 
            @change="handleFileChange" 
            accept="image/png, image/jpeg, image/jpg, image/gif" 
            style="display: none" 
          />
          
          <template v-if="!imagePreview">
            <div class="upload-placeholder">
              <!-- Icon image dihapus sesuai instruksi untuk tidak menggunakan SVG dari kode -->
              <h3>Drag photos and videos here</h3>
              <p>SVG, PNG, JPG or GIF (max. 10MB)</p>
              <button class="btn btn-primary" style="margin-top: 1rem;" type="button" @click="fileInput?.click()">
                Select from computer
              </button>
            </div>
          </template>

          <template v-else>
            <img :src="imagePreview" class="image-preview" alt="Preview" />
            <button class="btn-icon remove-img-btn" @click="removeImage">✕</button>
          </template>
        </div>

        <!-- Area Caption -->
        <div class="caption-area">
          <div class="caption-header">
            <div class="snippet-avatar" style="width: 24px; height: 24px; font-size: 10px;">
               <img v-if="profile?.avatar_path" :src="profile.avatar_path" alt="Avatar" />
               <span v-else>{{ profile?.username?.charAt(0).toUpperCase() || 'U' }}</span>
            </div>
            <span class="caption-username">@{{ profile?.username || 'username' }}</span>
          </div>

          <textarea 
            v-model="caption" 
            placeholder="Write a caption..." 
            class="caption-input"
            maxlength="2200"
          ></textarea>

          <div class="caption-footer">
            <span class="char-count">{{ caption.length }}/2200</span>
            <span class="advanced-settings">Advanced settings</span>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline" @click="closeModal" :disabled="loading">Cancel</button>
        <button class="btn btn-primary" @click="submitPost" :disabled="loading || !caption">
          {{ loading ? 'Sharing...' : 'Share' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import api from '../utils/axios';

const props = defineProps<{
  isOpen: boolean;
  profile: any;
}>();

const emit = defineEmits(['close', 'post-created']);

const fileInput = ref<HTMLInputElement | null>(null);
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const caption = ref('');
const loading = ref(false);

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];
    if (!file) return;
    
    if (file.size > 10 * 1024 * 1024) {
      console.warn("File is too large! Maximum 10MB allowed.");
      return;
    }
    imageFile.value = file;
    // Create preview URL
    imagePreview.value = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  imageFile.value = null;
  if (imagePreview.value) {
    URL.revokeObjectURL(imagePreview.value);
    imagePreview.value = null;
  }
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const closeModal = () => {
  removeImage();
  caption.value = '';
  emit('close');
};

const submitPost = async () => {
  if (!caption.value) return;
  loading.value = true;
  try {
    const formData = new FormData();
    formData.append('caption', caption.value);
    if (imageFile.value) {
      formData.append('image', imageFile.value);
    }

    await api.post('/posts', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    closeModal();
    emit('post-created');
  } catch (error: any) {
    console.error(error);
  } finally {
    loading.value = false;
  }
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
  border-radius: 1rem;
  width: 90%;
  max-width: 600px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
}

.modal-header h2 {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: var(--text-light);
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.media-upload-area {
  border: 2px dashed #fca5a5;
  border-radius: 0.5rem;
  background-color: #fff1f2;
  height: 240px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.media-upload-area.has-image {
  border-color: transparent;
  background-color: transparent;
}

.upload-placeholder {
  text-align: center;
  color: #881337;
}

.upload-placeholder h3 {
  font-size: 0.95rem;
  font-weight: 600;
  margin-top: 0.75rem;
  margin-bottom: 0.25rem;
}

.upload-placeholder p {
  font-size: 0.75rem;
  color: #be123c;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.remove-img-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0,0,0,0.5);
  color: white;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  font-size: 12px;
}

.caption-area {
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1rem;
}

.caption-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.caption-username {
  font-weight: 600;
  font-size: 0.85rem;
}

.caption-input {
  width: 100%;
  border: none;
  resize: none;
  min-height: 80px;
  outline: none;
  font-family: inherit;
  font-size: 0.9rem;
}

.caption-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--text-light);
}

.advanced-settings {
  color: var(--primary);
  font-weight: 600;
  cursor: pointer;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border);
  background-color: var(--bg-color);
}

.btn-outline {
  background: var(--white);
  border: 1px solid var(--border);
  color: var(--text-dark);
}

.btn-outline:hover {
  background: #f3f4f6;
}
</style>
