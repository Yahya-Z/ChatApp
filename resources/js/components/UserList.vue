<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';

interface User {
  id: number;
  name: string;
  email: string;
}

// Define props and receive the users
const props = defineProps<{
  users: User[];
}>();

// Define emits and emit the selected user
const emit = defineEmits<{
  (e: 'select-user', user: User): void;
}>();

// Define the selectUser function and emit the selected user
const selectUser = (user: User) => {
  emit('select-user', user);
};
</script>

<template>

  <!-- User List -->
  <div class="h-full overflow-y-auto p-4">
    <h2 class="mb-4 text-lg font-semibold">المستخدمين</h2>

    <!-- User List Items -->
    <ul>
      <!-- If there are users -->
      <div v-if="users.length > 0">
        <!-- Loop through the users -->
        <li
          v-for="user in users"
          :key="user.id"
          class="mb-2 flex items-center rounded-lg p-2 hover:bg-gray-200 cursor-pointer"
          @click="selectUser(user)"
            >
            <!-- User Avatar -->
            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                <span class="font-bold text-gray-700">{{ user.name.charAt(0) }}</span>
            </div>
            <!-- User Name -->
                <span class="ml-3">{{ user.name }}</span>
            </li>
        </div>
        <!-- If there are no users -->
        <div v-else>
            <p class="text-gray-500">لا يوجد مستخدمين.</p>
        </div>
    </ul>
  </div>
</template>

<style scoped>

/* User List */
.user-list {
  height: 100%;
  overflow-y: auto;
}

/* User List Unordered List */
.user-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

/* User List Items */
.user-list li {
  border-bottom: 1px solid #eee;
  transition: background-color 0.2s;
}

/* User List Items Hover */
.user-list li:hover {
  background-color: #f5f5f5;
}
</style>
