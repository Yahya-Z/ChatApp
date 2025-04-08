<script setup lang="ts">
import { ref, onMounted } from 'vue';
import messageList from '../components/messageList.vue';
import messageInput from '@/components/messageInput.vue';
// import userSearch from '../components/userSearch.vue';
import UserList from '../components/UserList.vue';
import axios from 'axios';
import NotificationList from '../components/NotificationList.vue';
// import { router } from '@inertiajs/vue3';
// import type { PusherMessage } from '@/types/echo';


interface User {
    id: number;
    name: string;
    email: string;
}

const users = ref<User[]>([]);
const loading = ref(true);
const selectedUser = ref<User | null>(null);
const apiError = ref<string | null>(null);
const isAuthenticated = ref(true); // Assume the user is authenticated initially
const currentUser = ref<User | null>(null);
const messageListRef = ref<InstanceType<typeof messageList> | null>(null);

// Check authentication
const checkAuthentication = async () => {
  try {
    console.log('التحقق من حالة المصادقة...');
    const response = await axios.get('/auth/check');
    console.log('حالة المصادقة:', response.data);
    
    // Check if the user is authenticated
    if (response.data.authenticated) {
      isAuthenticated.value = true;
      currentUser.value = response.data.user;
      console.log('المستخدم مصادق عليه:', currentUser.value);
    }
    
    // If the user is not authenticated 
    else {
      isAuthenticated.value = false;
      apiError.value = 'يرجى تسجيل الدخول للوصول إلى المحادثات';
      console.warn('المستخدم غير مصادق عليه');
    }
  }
  
  // Handle authentication errors
  catch (error) {
    console.error('خطأ في التحقق من المصادقة:', error);
    isAuthenticated.value = false;
  }
};

// Fetch all users
const fetchUsers = async () => {
  loading.value = true;
  apiError.value = null;
  
  // Check authentication first
  if (!isAuthenticated.value) {
    apiError.value = 'يرجى تسجيل الدخول للوصول إلى قائمة المستخدمين';
    loading.value = false;
    return;
  }
  
  // Fetch users
  try {
    console.log('محاولة استرجاع المستخدمين من /api/users');
    
    // Use axios withCredentials to maintain the session and cookies
    const response = await axios.get('/api/users', { 
      withCredentials: true 
    });
    
    console.log('استجابة API:', response);
    
    // Check if the response contains users
    if (response?.data?.users) {
      users.value = response.data.users;
      console.log(`تم استرجاع ${users.value.length} مستخدمين`);
    }

    // If the response contains an array
    else if (Array.isArray(response?.data)) {
      // In case of a direct array return
      users.value = response.data;
      console.log(`تم استرجاع ${users.value.length} مستخدمين (تنسيق مصفوفة)`);
    }
    
    // If the response contains an error
    else {
      console.error('تنسيق استجابة غير متوقع. البيانات المستلمة:', response.data);
      apiError.value = 'تنسيق استجابة غير متوقع من الخادم';
    }
  }
  
  // Handle errors
  catch (error: any) {
    console.error('خطأ في استرجاع المستخدمين:', error);
    
    // If the response contains a 401 error
    if (error.response && error.response.status === 401) {
      apiError.value = 'غير مصرح لك بالوصول. يرجى تسجيل الدخول.';
      isAuthenticated.value = false;
    }
    
    // If the response contains a 404 error
    else if (error.response && error.response.status === 404) {
      apiError.value = 'المسار غير موجود. تحقق من إعدادات API.';
    }
    
    // If the response contains an error
    else {
      apiError.value = 'فشل في الاتصال بالخادم. يرجى المحاولة مرة أخرى';
    }
  } finally {
    loading.value = false;
  }
};

// Select the user to chat with
const selectUser = (user: User) => {
  console.log('تم اختيار المستخدم للدردشة:', user.name);
  selectedUser.value = user;
};

// Retry or login
const retryOrLogin = () => {
  if (isAuthenticated.value) {
    fetchUsers();
  }
  
  // If the user is not authenticated
  else {
    // Redirect the user to the login page
    window.location.href = '/login';
  }
};

onMounted(async () => {
  console.log('تهيئة مكون chatLayout');
  await checkAuthentication();
  if (isAuthenticated.value) {
    await fetchUsers();
  }
});
</script>

<template>
  <div class="flex h-full">
    <!-- Left Sidebar: User List -->
    <div class="w-1/4 border-r flex flex-col">
      <div v-if="apiError" class="p-4 bg-red-100 text-red-700 text-center">
        <p>{{ apiError }}</p>
        <button @click="retryOrLogin" class="mt-2 bg-red-500 text-white px-4 py-1 rounded-md">
          {{ isAuthenticated ? 'إعادة المحاولة' : 'تسجيل الدخول' }}
        </button>
      </div>
      
      <UserList 
        :users="users" 
        class="flex-1 overflow-y-auto"
        @select-user="selectUser"
      />
      
      <div v-if="loading" class="p-4 text-center">جارٍ تحميل المستخدمين...</div>
      </div>

      <!-- notification list -->
      <NotificationList class="mt-4" />

    <!-- Right Side: Chat Window -->
    <div class="flex-1 flex flex-col">
      <!-- Chat Header -->
      <div class="border-b bg-white p-4">
        <h2 class="text-lg font-semibold">
          {{ selectedUser ? `محادثة مع ${selectedUser.name}` : 'اختر مستخدماً للدردشة' }}
        </h2>
      </div>

      <!-- Chat Content -->
      <div class="flex-1 flex flex-col">
        <div v-if="!selectedUser" class="flex-1 flex items-center justify-center text-gray-500">
          الرجاء اختيار مستخدم من القائمة للبدء في الدردشة
        </div>

        <!-- Chat Window -->
        <template v-else>
          <messageList 
            ref="messageListRef"
            class="flex-1" 
            :receiver-id="selectedUser.id.toString()"
          />
          <messageInput 
            :receiver-id="selectedUser.id.toString()"
            class="border-t bg-white"
            @message-sent="(message) => messageListRef?.addMessage(message)"
          />
        </template>
      </div>
    </div>
  </div>
</template>