<template>
  <el-container class="app-layout">
    <!-- Sidebar -->
    <el-aside width="250px" class="sidebar">
      <div class="logo">
        <h2>Chung cư Hưng Thịnh</h2>
      </div>
      
      <el-menu
        :default-active="$route.path"
        class="sidebar-menu"
        router
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#409EFF"
      >
        <el-menu-item index="/dashboard">
          <el-icon><DataBoard /></el-icon>
          <span>Dashboard</span>
        </el-menu-item>

        <el-menu-item index="/apartments">
          <el-icon><House /></el-icon>
          <span>Quản lý căn hộ</span>
        </el-menu-item>

        <el-menu-item v-if="authStore.isAdmin" index="/users">
          <el-icon><User /></el-icon>
          <span>Quản lý người dùng</span>
        </el-menu-item>

        <el-menu-item index="/notifications">
          <el-icon><Bell /></el-icon>
          <span>Thông báo</span>
        </el-menu-item>

        <el-menu-item index="/feedbacks">
          <el-icon><ChatDotRound /></el-icon>
          <span>Phản ánh</span>
        </el-menu-item>

        <el-menu-item index="/invoices">
          <el-icon><Document /></el-icon>
          <span>Hóa đơn</span>
        </el-menu-item>

        <el-menu-item index="/payments">
          <el-icon><Money /></el-icon>
          <span>Thanh toán</span>
        </el-menu-item>

        <el-menu-item index="/devices">
          <el-icon><Setting /></el-icon>
          <span>Thiết bị</span>
        </el-menu-item>

        <el-menu-item index="/maintenances">
          <el-icon><Tools /></el-icon>
          <span>Bảo trì</span>
        </el-menu-item>

        <el-menu-item index="/events">
          <el-icon><Calendar /></el-icon>
          <span>Sự kiện</span>
        </el-menu-item>

        <el-menu-item index="/votes">
          <el-icon><Select /></el-icon>
          <span>Biểu quyết</span>
        </el-menu-item>
      </el-menu>
    </el-aside>

    <!-- Main content -->
    <el-container>
      <!-- Header -->
      <el-header class="header">
        <div class="header-left">
          <el-breadcrumb separator="/">
            <el-breadcrumb-item v-for="item in breadcrumbs" :key="item.path" :to="item.path">
              {{ item.name }}
            </el-breadcrumb-item>
          </el-breadcrumb>
        </div>
        
        <div class="header-right">
          <el-dropdown @command="handleCommand">
            <span class="user-dropdown">
              <el-avatar :size="32" :src="authStore.user?.avatar">
                {{ authStore.user?.name?.charAt(0) }}
              </el-avatar>
              <span class="username">{{ authStore.user?.name }}</span>
              <el-icon><ArrowDown /></el-icon>
            </span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="profile">Hồ sơ</el-dropdown-item>
                <el-dropdown-item command="logout" divided>Đăng xuất</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </el-header>

      <!-- Main content area -->
      <el-main class="main-content">
        <router-view />
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  DataBoard,
  House,
  User,
  Bell,
  ChatDotRound,
  Document,
  Money,
  Setting,
  Tools,
  Calendar,
  Select,
  ArrowDown
} from '@element-plus/icons-vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const breadcrumbs = computed(() => {
  const paths = route.path.split('/').filter(Boolean)
  const breadcrumbs = [{ path: '/', name: 'Trang chủ' }]
  
  let currentPath = ''
  paths.forEach((path, index) => {
    currentPath += `/${path}`
    const name = getBreadcrumbName(path, index)
    breadcrumbs.push({ path: currentPath, name })
  })
  
  return breadcrumbs
})

function getBreadcrumbName(path: string, index: number): string {
  const nameMap: Record<string, string> = {
    dashboard: 'Dashboard',
    apartments: 'Căn hộ',
    users: 'Người dùng',
    notifications: 'Thông báo',
    feedbacks: 'Phản ánh',
    invoices: 'Hóa đơn',
    payments: 'Thanh toán',
    devices: 'Thiết bị',
    maintenances: 'Bảo trì',
    events: 'Sự kiện',
    votes: 'Biểu quyết',
    profile: 'Hồ sơ'
  }
  
  return nameMap[path] || path
}

async function handleCommand(command: string) {
  if (command === 'profile') {
    router.push('/profile')
  } else if (command === 'logout') {
    await authStore.logout()
    router.push('/login')
  }
}
</script>

<style scoped>
.app-layout {
  height: 100vh;
}

.sidebar {
  background-color: #304156;
  color: #bfcbd9;
}

.logo {
  padding: 20px;
  text-align: center;
  border-bottom: 1px solid #435266;
}

.logo h2 {
  color: #fff;
  margin: 0;
  font-size: 18px;
}

.sidebar-menu {
  border: none;
}

.header {
  background-color: #fff;
  border-bottom: 1px solid #e4e7ed;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
}

.header-left {
  flex: 1;
}

.header-right {
  display: flex;
  align-items: center;
}

.user-dropdown {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.user-dropdown:hover {
  background-color: #f5f7fa;
}

.username {
  margin: 0 8px;
  color: #606266;
}

.main-content {
  background-color: #f5f5f5;
  padding: 20px;
}
</style> 