<template>
  <div class="not-found-page">
    <div class="not-found-content">
      <div class="error-code">404</div>
      <h1>Trang không tồn tại</h1>
      <p>Xin lỗi, trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.</p>
      <div class="actions">
        <el-button type="primary" @click="goHome">Về trang chủ</el-button>
        <el-button @click="goBack">Quay lại</el-button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const goHome = () => {
  router.push('/')
}

const goBack = () => {
  router.go(-1)
}
</script>

<style scoped>
.not-found-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.not-found-content {
  text-align: center;
  padding: 40px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.error-code {
  font-size: 120px;
  font-weight: bold;
  color: #409eff;
  margin-bottom: 20px;
  line-height: 1;
}

.not-found-content h1 {
  font-size: 32px;
  color: #303133;
  margin-bottom: 16px;
}

.not-found-content p {
  font-size: 16px;
  color: #606266;
  margin-bottom: 32px;
  max-width: 400px;
}

.actions {
  display: flex;
  gap: 16px;
  justify-content: center;
}
</style> 