<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import type { Notification } from '@/types/echo';
import axios from 'axios';
import { onClickOutside } from '@vueuse/core';
// import type { User } from '@/types/echo';

const notifications = ref<Notification[]>([]); // notifications of the user
const unreadCount = ref(0); // unread count of messages
const currentUserId = ref<number | null>(null); // current user id
const loading = ref(true);
const isOpen = ref(false);
const notificationRef = ref(null);

// load notifications
const loadNotifications = async () => {
    try {
        const response = await axios.get('/api/notifications');
        notifications.value = response.data;
        unreadCount.value = notifications.value.filter((n: Notification) => !n.read_at).length;
    }catch (error) {
        console.error('فشل تحميل الاشعارات', error);
    } finally {
        loading.value = false;
    }
};

// mark as read
const markAsRead = async (notification: Notification) => {
    try {
        await axios.post(`/api/notifications/${notification.id}/read`);
        notification.read_at = new Date().toISOString();
    } catch (error) {
        console.error('فشل وضع الاشعار كمقروء', error);
    }
};

const toggleNotifications = () => {
    isOpen.value = !isOpen.value;
};

onMounted(async () => {
    try {
        const response = await axios.get('/user');
        currentUserId.value = response.data.id;
    } catch (error) {
        console.error('Failed to get current user:', error);
    }
    await loadNotifications();

    if (window.Echo && currentUserId.value) {
        const channelName = `private-notifications.${currentUserId.value}`;
        console.log('Subscribing to private notification channel:', channelName);
        
        const channel = window.Echo.private(channelName);

        // listen to new notification and update the notifications list
        channel.listen('NewNotification', (data: Notification) => {
            console.log('Received new notification:', data);
            notifications.value.unshift(data);
            unreadCount.value++;
        });

        // Add error handling
        channel.error((error: any) => {
            console.error('Error in notification channel:', error);
        });

        // Add subscription success handler
        channel.subscribed(() => {
            console.log('Successfully subscribed to private notification channel:', channelName);
        });
    }
});

onBeforeUnmount(() => {
    if (window.Echo && currentUserId.value) {
        const channelName = `private-notifications.${currentUserId.value}`;
        console.log('Unsubscribing from private notification channel:', channelName);
        window.Echo.leave(channelName);
    }
});

onClickOutside(notificationRef, () => {
    isOpen.value = false;
});
</script>

<template>
    <div class="notifications" ref="notificationRef">
        <!-- Add toggle button -->
        <button @click="toggleNotifications" class="notification-toggle">
            <span class="notification-icon"></span>
            <div class="notification-badge" v-if="unreadCount > 0">
                {{ unreadCount }}
            </div>
        </button>

        <!-- Add dropdown -->
        <div v-if="isOpen" class="notification-dropdown">
            <div class="notification-list">
                <div v-for="notification in notifications"
                    :key="notification.id"
                    :class="['notification-item', { unread: !notification.read_at}]"
                    @click="markAsRead(notification)">

                    <!-- notification content -->
                    <div class="notification-content">
                        {{ notification.data.message }}
                    </div>

                    <!-- notification time -->
                    <div class="notification-time">
                        {{ new Date(notification.created_at).toLocaleTimeString() }}
                    </div>
            
                </div>
            </div>

            <div v-if="!loading && notifications.length === 0" class="empty-state">
                No notifications
            </div>

            <div v-if="loading" class="loading-state">
                Loading notifications...
            </div>
        </div>
    </div>
</template>

<style scoped>
.notifications {
    position: relative;
}

.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: red;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.notification-item.unread {
    background: #f0f7ff;
}

.notification-toggle {
    position: relative;
    padding: 8px;
    border: none;
    background: none;
    cursor: pointer;
}

.notification-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: white;
    border: 1px solid #eee;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000;
}

.notification-icon {
    font-size: 20px;
}
</style>
