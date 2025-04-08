<script setup lang="ts">
import { ref, onMounted, watch, nextTick, onBeforeUnmount } from 'vue';
import axios from 'axios';
import type { PusherMessage } from '@/types/echo';

const props = defineProps<{
  receiverId: number | string
}>();

const messages = ref<PusherMessage[]>([]);
const loading = ref(true);
const currentUserId = ref<number | null>(null);
const messageContainer = ref<HTMLElement | null>(null);
const shouldAutoScroll = ref(true);

// Store the current channel subscription
const currentChannel = ref<any>(null);

// Scroll to bottom
const scrollToBottom = async () => {
  if (!shouldAutoScroll.value) return;
  
  await nextTick();
  if (messageContainer.value) {
    messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
  }
};

//////////////////////////////////
//////////// Watchers ////////////
//////////////////////////////////

// Watch for messages changes
watch(() => messages.value.length, () => {
  scrollToBottom();
});

// Watch for receiver changes
watch(() => props.receiverId, async () => {
  shouldAutoScroll.value = true;
  await loadMessages();
  scrollToBottom();
});

// Handle manual scrolling
const handleScroll = () => {
  if (messageContainer.value) {
    const { scrollTop, scrollHeight, clientHeight } = messageContainer.value;
    const isAtBottom = Math.abs(scrollHeight - scrollTop - clientHeight) < 50;
    shouldAutoScroll.value = isAtBottom;
  }
};

// Load messages
const loadMessages = async () => {
  try {
    const response = await axios.get(`/messages/${props.receiverId}`);
    if (response && response.data) {
      messages.value = response.data;
    } else {
      console.error('استجابة غير متوقعة عند تحميل الرسائل');
      messages.value = [];
    }
  } catch (error) {
    console.error('فشل في تحميل الرسائل:', error);
    messages.value = [];
  } finally {
    loading.value = false;
  }
};

// Check if the message is sent by the current user
const isCurrentUser = (message: PusherMessage) => {
  return message.sender.id === currentUserId.value;
};

// Add a new message
const addMessage = (NewMessage: PusherMessage) => {
  console.log('Adding new message:', NewMessage);
  messages.value.push(NewMessage);
  scrollToBottom();
};

// Mount the component
onMounted(async () => {
  // Add a listener for scrolling
  if (messageContainer.value) {
    messageContainer.value.addEventListener('scroll', handleScroll);
  }

  // Get the current user id
  try {
    const response = await axios.get('/user');
    if (response && response.data) {
      currentUserId.value = response.data.id;
      console.log('Current user ID:', currentUserId.value);
    }
  } catch (error) {
    console.error('Failed to get current user:', error);
  }

  await loadMessages();
  scrollToBottom();

  window.Echo.connector.pusher.connection.bind('state_change', (states: { current: string, previous: string }) => {
  console.log('Pusher connection state change:', states);
});

window.Echo.connector.pusher.connection.bind('connected', () => {
  console.log('Pusher connected');
});

window.Echo.connector.pusher.connection.bind('error', (err: Error) => {
  console.error('Pusher connection error:', err);
});


  // Setup Echo for new messages
  if (window.Echo) {
    subscribeToChannel();
  } else {
    console.error('Echo is not initialized');
  }
});

// Function to subscribe to a channel
const subscribeToChannel = () => {
  if (!window.Echo) {
    console.error('Echo is not initialized');
    return;
  }

  if (!currentUserId.value) {
    console.error('Current user ID is not set');
    return;
  }

  // Create a unique channel name for this conversation
  const channelName = `chat.${Math.min(currentUserId.value, Number(props.receiverId))}.${Math.max(currentUserId.value, Number(props.receiverId))}`;
  console.log('Attempting to subscribe to public channel:', {
    channelName,
    currentUserId: currentUserId.value,
    receiverId: props.receiverId
  });
  
  try {
    // Leave the current channel if it exists
    if (currentChannel.value) {
      console.log('Leaving current channel:', currentChannel.value.name);
      window.Echo.leave(currentChannel.value.name);
    }

    // Subscribe to the new public channel
    const channel = window.Echo.channel(channelName);
    currentChannel.value = channel;
    
    // Listen for events from the server
    channel.listen('.NewMessage', (data: PusherMessage) => {
      console.log('Received new message:', {
        data,
        channel: channelName,
        currentUserId: currentUserId.value
      });
      
      // Check if the message is a duplicate
      const isDuplicate = messages.value.some(msg => 
        msg.id === data.id || 
        (msg.message === data.message && 
         msg.sender.id === data.sender.id)
      );
      
      // Add the message to the list if it's not a duplicate
      if (!isDuplicate) {
        console.log('Adding new message to list:', data);
        messages.value.push(data);
        scrollToBottom();
      } else {
        console.log('Duplicate message detected, skipping:', data);
      }
    });
    
    // Add error handling for subscription
    channel.error((error: any) => {
      console.error('Error in channel subscription:', {
        error,
        channel: channelName,
        currentUserId: currentUserId.value
      });
    });
    
    // Add subscription success handler
    channel.subscribed(() => {
      console.log('Successfully subscribed to public channel:', {
        channel: channelName,
        currentUserId: currentUserId.value
      });
    });
    
  } catch (error) {
    console.error('Error while subscribing to channel:', {
      error,
      channel: channelName,
      currentUserId: currentUserId.value
    });
  }
};

// Watch for receiver changes to update the channel subscription
watch(() => props.receiverId, async (newReceiverId) => {
  console.log('Receiver changed, updating channel subscription');
  shouldAutoScroll.value = true;
  await loadMessages();
  scrollToBottom();
  
  // Update channel subscription
  subscribeToChannel();
});

// Unsubscribe from the channel when component is unmounted
onBeforeUnmount(() => {
  if (messageContainer.value) {
    messageContainer.value.removeEventListener('scroll', handleScroll);
  }
  
  if (window.Echo && currentChannel.value) {
    console.log(`Unsubscribing from public conversation channel: ${currentChannel.value.name}`);
    window.Echo.leave(currentChannel.value.name);
  }
});

defineExpose({
  addMessage
});
</script>

<template>
  <!-- Message list container -->
  <div class="flex flex-col h-full">
    
    <!-- Messages list -->
    <div class="message-list flex-1 overflow-y-auto" ref="messageContainer">

      <!-- Loading state -->
      <div v-if="loading" class="text-center p-4">Loading messages...</div>

      <!-- Empty state -->
      <div v-else-if="messages.length === 0" class="text-center text-gray-500 p-4">
        لا توجد اي رسائل. ابدأ المحادثة الآن!
      </div>

      <!-- Messages list -->
      <ul v-else class="space-y-4 p-4">
        <li v-for="(message, index) in messages" :key="index" :class="['flex',isCurrentUser(message) ? 'justify-end' : 'justify-start']">
          
          <!-- Message bubble -->
          <div
            :class="[
              'max-w-[70%] rounded-lg p-3',
              isCurrentUser(message)
                ? 'bg-blue-500 text-white'
                : 'bg-gray-200 text-gray-800'
            ]">

            <!-- Sender name -->
            <div class="text-sm font-semibold mb-1">
              {{ message.sender.name }}
            </div>

            <!-- Message content -->
            <div class="message-content">
              {{ message.message }}
            </div>

            <!-- Message status and timestamp -->
            <div class="flex justify-between items-center mt-1">
              <div class="text-xs opacity-75">
                {{ new Date(message.created_at).toLocaleTimeString() }}
              </div>
              <div v-if="isCurrentUser(message)" class="text-xs ml-2">
                <span v-if="message.status === 'sent'" class="text-gray-400">✓</span>
                <span v-else-if="message.status === 'delivered'" class="text-gray-400">✓✓</span>
                <span v-else-if="message.status === 'read'" class="text-gray-400">✓✓</span>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.message-list {
  height: calc(100vh - 200px);
  scroll-behavior: smooth;
}

.message-content {
  word-break: break-word;
}
</style>