<script setup lang="ts">
import { ref, defineEmits } from 'vue';
import axios from 'axios';

const props = defineProps<{
  receiverId: number | string
}>();

// إضافة emit لتنبيه الأب بإضافة رسالة جديدة
const emit = defineEmits<{
  (e: 'message-sent', message: any): void;
}>();

const message = ref('');
const loading = ref(false);
const error = ref<string | null>(null);

const sendMessage = async () => {
  if (!message.value.trim()) return;

  loading.value = true;
  error.value = null;
  
  try {
    console.log('إرسال رسالة إلى المستخدم:', props.receiverId);
    
    // إضافة ويذكريدنشل للحفاظ على الجلسة
    const response = await axios.post('/messages', {
      message: message.value,
      receiver_id: props.receiverId
    }, {
      withCredentials: true
    });
    
    console.log('تمت إضافة الرسالة بنجاح:', response.data);
    
    // إصدار حدث بالرسالة الجديدة ليتم إضافتها مباشرة للعرض
    if (response.data.message) {
      emit('message-sent', response.data.message);
    }
    
    message.value = '';
  } catch (err: any) {
    console.error('فشل في إرسال الرسالة:', err);
    
    if (err.response) {
      // التعامل مع أخطاء الاستجابة
      if (err.response.status === 404) {
        error.value = 'المسار غير موجود. تحقق من إعدادات API.';
      } else if (err.response.status === 401) {
        error.value = 'غير مصرح لك بالوصول. يرجى تسجيل الدخول.';
      } else if (err.response.status === 422) {
        error.value = 'بيانات غير صالحة. تحقق من محتوى الرسالة.';
      } else {
        error.value = `خطأ في الخادم: ${err.response.status}`;
      }
    } else if (err.request) {
      // الطلب تم إرساله لكن لم يتم استلام استجابة
      error.value = 'لم يتم استلام استجابة من الخادم. تحقق من اتصالك بالإنترنت.';
    } else {
      // حدث خطأ آخر
      error.value = 'حدث خطأ غير متوقع. حاول مرة أخرى.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="message-input mt-4">
    <div v-if="error" class="mb-2 p-2 bg-red-100 text-red-700 rounded-md text-sm">
      {{ error }}
    </div>
    <form @submit.prevent="sendMessage" class="flex gap-2">
      <input
        v-model="message"
        type="text"
        id="message-input"
        name="message"
        placeholder="اكتب رسالتك هنا..."
        class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none"
        :disabled="loading"
        :class="{ 'border-red-500': error }"
      />
      <button
        type="submit"
        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none disabled:opacity-50"
        :disabled="loading"
      >
        {{ loading ? 'جارٍ الإرسال...' : 'إرسال' }}
      </button>
    </form>
  </div>
</template>

<style scoped>
.message-input {
  padding: 20px;
  border-top: 1px solid #e5e7eb;
}
</style>