<template>
  <div class="settings-page">
    <h2>Settings</h2>
    <p class="subtitle">Manage your account settings and preferences.</p>

    <div class="settings-layout">
      <!-- Settings Sidebar -->
      <div class="settings-nav">
        <a href="#" class="nav-item active">
          <img src="../assets/icon/Profil.png" alt="Edit Profile" />
          Edit Profile
        </a>
        <a href="#" class="nav-item">
          <img src="../assets/icon/option.png" alt="Change Password" />
          Change Password
        </a>
        <a href="#" class="nav-item">
           <img src="../assets/icon/save.png" alt="Privacy" />
           Privacy
        </a>
        <a href="#" class="nav-item">
           <img src="../assets/icon/notification.png" alt="Notifications" />
           Notifications
        </a>
        <a href="#" class="nav-item logout-btn" @click.prevent="handleLogout">
           <img src="../assets/icon/logout.png" alt="Logout" />
           Logout
        </a>
      </div>

      <!-- Settings Content -->
      <div class="settings-content">
        <div class="profile-pic-edit">
          <div class="pic-preview">
            <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar Preview" />
            <span v-else-if="profile?.username">{{ profile.username.charAt(0).toUpperCase() }}</span>
          </div>
          <div class="pic-actions">
            <h3>Profile picture</h3>
            <p>JPG, GIF or PNG. Max size of 5MB.</p>
            <input type="file" ref="fileInput" @change="handleFileChange" accept="image/*" style="display: none;" />
            <button class="btn btn-outline" @click="fileInput?.click()" type="button">Change photo</button>
          </div>
        </div>

        <form @submit.prevent="saveSettings" class="settings-form">
          <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-input" v-model="form.username" required />
            <small>This is your public display name.</small>
          </div>

          <div class="form-group">
            <label>Email address</label>
            <input type="email" class="form-input" v-model="form.email" />
          </div>

          <div class="form-group">
            <label>Bio</label>
            <textarea class="form-input" v-model="form.bio" rows="4" placeholder="Tell us a little bit about yourself..." maxlength="150"></textarea>
            <small style="text-align: right; display: block; width: 100%;">{{ form.bio.length }} / 150</small>
          </div>

          <div class="form-actions">
            <router-link to="/profile" class="btn btn-outline" style="text-decoration: none;">Cancel</router-link>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              {{ loading ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../utils/axios';

const router = useRouter();
const loading = ref(false);
const profile = ref<any>(null);

const form = ref({
  username: '',
  email: '',
  bio: ''
});

const avatarFile = ref<File | null>(null);
const avatarPreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const fetchProfile = async () => {
  try {
    const res = await api.get('/profile');
    profile.value = res.data;
    form.value.username = res.data.username || '';
    form.value.email = res.data.email || '';
    form.value.bio = res.data.bio || '';
    if (res.data.avatar_path) {
      avatarPreview.value = res.data.avatar_path;
    }
  } catch (error) {
    console.error('Failed to fetch profile', error);
  }
};

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
      console.warn("File is too large! Maximum 5MB allowed.");
      return;
    }
    avatarFile.value = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const saveSettings = async () => {
  loading.value = true;
  try {
    const formData = new FormData();
    formData.append('_method', 'PUT'); // Laravel requirement for multipart PUT requests
    if (form.value.username !== profile.value.username) formData.append('username', form.value.username);
    if (form.value.email !== profile.value.email && form.value.email) formData.append('email', form.value.email);
    if (form.value.bio !== profile.value.bio) formData.append('bio', form.value.bio);
    if (avatarFile.value) formData.append('avatar', avatarFile.value);

    // If nothing changed and no file, skip
    if (Array.from(formData.keys()).length === 1) {
       router.push('/profile');
       return;
    }

    // Since we use FormData, we need to make a POST request with _method=PUT to trick Laravel
    await api.post('/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    router.push('/profile');
  } catch (error: any) {
    console.error(error);
  } finally {
    loading.value = false;
  }
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

onMounted(() => {
  fetchProfile();
});
</script>

<style scoped>
.settings-page {
  padding: 1rem 0;
}

.settings-page h2 {
  font-size: 1.5rem;
  margin-bottom: 0.25rem;
}

.subtitle {
  color: var(--text-light);
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

.settings-layout {
  display: flex;
  gap: 2rem;
}

.settings-nav {
  width: 200px;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  color: var(--text-dark);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
}

.nav-item img {
  width: 18px;
  opacity: 0.7;
}

.nav-item.active {
  background-color: var(--primary);
  color: var(--white);
}

.nav-item.active img {
  filter: brightness(0) invert(1);
  opacity: 1;
}

.nav-item.logout-btn {
  color: #e11d48;
  margin-top: 1rem;
}

.nav-item.logout-btn:hover {
  background-color: #ffe4e6;
}

.settings-content {
  flex: 1;
  background: var(--white);
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.profile-pic-edit {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2.5rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--border);
}

.pic-preview {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: bold;
  overflow: hidden;
}

.pic-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.pic-actions h3 {
  margin: 0;
  font-size: 1rem;
}

.pic-actions p {
  margin: 0.25rem 0 1rem;
  font-size: 0.75rem;
  color: var(--text-light);
}

.settings-form .form-group {
  margin-bottom: 1.5rem;
}

.settings-form label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.settings-form small {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--text-light);
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  font-family: inherit;
  font-size: 0.9rem;
  outline: none;
}

.form-input:focus {
  border-color: var(--primary);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}

@media (max-width: 768px) {
  .settings-layout {
    flex-direction: column;
    gap: 1.5rem;
  }
  .settings-nav {
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
  }
  .nav-item {
    flex: 1 1 45%;
    justify-content: center;
  }
  .nav-item.logout-btn {
    margin-top: 0;
  }
  .settings-content {
    padding: 1.5rem;
  }
  .profile-pic-edit {
    flex-direction: column;
    text-align: center;
  }
  .form-actions {
    flex-direction: column;
  }
  .form-actions .btn {
    width: 100%;
    text-align: center;
  }
}
</style>
