import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// تكوين axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
// window.Pusher = require('pusher-js');
// window.Pusher.logToConsole = true;


// إضافة معالج لأخطاء axios
axios.interceptors.response.use(
  response => response,
  error => {
    console.error('خطأ في طلب الشبكة:', error.message);
    console.error('حالة الاستجابة:', error.response ? error.response.status : 'لا توجد استجابة');
    console.error('بيانات الاستجابة:', error.response ? error.response.data : 'لا توجد بيانات');
    return Promise.reject(error);
  }
);

// تهيئة Pusher و Echo
console.log('بدء تهيئة Pusher و Echo...');
console.log('التحقق من CSRF token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

try {
  // تعيين Pusher في النافذة العالمية
  window.Pusher = Pusher;

  // تفعيل وضع debug للـ Pusher
  Pusher.logToConsole = true;
  
  // قراءة إعدادات Pusher من متغيرات البيئة
  const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
  const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;
  const pusherAppId = import.meta.env.VITE_PUSHER_APP_ID;
  
  console.log('Environment Variables:', {
    VITE_PUSHER_APP_KEY: pusherKey,
    VITE_PUSHER_APP_CLUSTER: pusherCluster,
    VITE_PUSHER_APP_ID: pusherAppId
  });
  
  if (!pusherKey) {
    throw new Error('VITE_PUSHER_APP_KEY is missing');
  }
  if (!pusherCluster) {
    throw new Error('VITE_PUSHER_APP_CLUSTER is missing');
  }
  if (!pusherAppId) {
    throw new Error('VITE_PUSHER_APP_ID is missing');
  }
  
  // تهيئة كائن Echo
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    appId: import.meta.env.VITE_PUSHER_APP_ID,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest',
      },
    },
    enabledTransports: ['ws', 'wss'],
    forceTLS: true,
    authTransport: 'ajax',
    authorizer: (channel, options) => {
      return {
        authorize: (socketId, callback) => {
          axios.post('/broadcasting/auth', {
            socket_id: socketId,
            channel_name: channel.name
          }, {
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
              'X-Requested-With': 'XMLHttpRequest',
            },
            withCredentials: true
          })
          .then(response => {
            console.log('Authorization response:', response.data);
            callback(false, response.data);
          })
          .catch(error => {
            console.error('Authorization error:', error);
            callback(true, error);
          });
        }
      };
    }
  });
  
  // إضافة معالجات أحداث للكشف عن المشاكل
  window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Pusher متصل بنجاح!');
  });

  window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.error('Pusher انقطع الاتصال!');
  });

  window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('خطأ في اتصال Pusher:', err);
  });
  
  // استماع لأحداث التصحيح من Pusher
  window.Echo.connector.pusher.connection.bind('message', (message) => {
    console.log('رسالة من Pusher:', message);
  });
  
  window.Echo.connector.pusher.connection.bind('subscription_succeeded', (data) => {
    console.log('تم الاشتراك بنجاح في قناة:', data);
  });

  window.Echo.connector.pusher.connection.bind('subscription_error', (data) => {
    console.error('خطأ في الاشتراك في القناة:', data);
  });
  
  console.log('تم تهيئة Echo بنجاح!');
} catch (error) {
  console.error('خطأ في تهيئة Echo:', error);
}