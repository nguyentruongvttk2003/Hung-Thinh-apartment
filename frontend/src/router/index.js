import { createRouter, createWebHistory } from 'vue-router'
import AppLayout from '@/components/Layout/AppLayout.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/Login.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/',
      component: AppLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: 'dashboard',
          name: 'Dashboard',
          component: () => import('@/views/Dashboard.vue')
        },
        {
          path: 'apartments',
          name: 'Apartments',
          component: () => import('@/views/Apartments.vue')
        },
        {
          path: 'users',
          name: 'Users',
          component: () => import('@/views/Users.vue')
        },
        {
          path: 'notifications',
          name: 'Notifications',
          component: () => import('@/views/Notifications.vue')
        },
        {
          path: 'feedbacks',
          name: 'Feedbacks',
          component: () => import('@/views/Feedbacks.vue')
        },
        {
          path: 'invoices',
          name: 'Invoices',
          component: () => import('@/views/Invoices.vue')
        },
        {
          path: 'payments',
          name: 'Payments',
          component: () => import('@/views/Payments.vue')
        },
        {
          path: 'devices',
          name: 'Devices',
          component: () => import('@/views/Devices.vue')
        },
        {
          path: 'maintenances',
          name: 'Maintenances',
          component: () => import('@/views/Maintenances.vue')
        },
        {
          path: 'events',
          name: 'Events',
          component: () => import('@/views/Events.vue')
        },
        {
          path: 'votes',
          name: 'Votes',
          component: () => import('@/views/Votes.vue')
        }
      ]
    }
  ]
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('auth_token')
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router