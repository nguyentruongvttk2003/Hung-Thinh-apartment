import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

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
      path: '/dashboard',
      name: 'Dashboard',
      component: () => import('@/views/Dashboard.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/apartments',
      name: 'Apartments',
      component: () => import('@/views/Apartments.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/apartments/:id',
      name: 'ApartmentDetail',
      component: () => import('@/views/ApartmentDetail.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/users',
      name: 'Users',
      component: () => import('@/views/Users.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/notifications',
      name: 'Notifications',
      component: () => import('@/views/Notifications.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/feedbacks',
      name: 'Feedbacks',
      component: () => import('@/views/Feedbacks.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/invoices',
      name: 'Invoices',
      component: () => import('@/views/Invoices.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/payments',
      name: 'Payments',
      component: () => import('@/views/Payments.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/devices',
      name: 'Devices',
      component: () => import('@/views/Devices.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/maintenances',
      name: 'Maintenances',
      component: () => import('@/views/Maintenances.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/events',
      name: 'Events',
      component: () => import('@/views/Events.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/votes',
      name: 'Votes',
      component: () => import('@/views/Votes.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/profile',
      name: 'Profile',
      component: () => import('@/views/Profile.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: () => import('@/views/NotFound.vue')
    }
  ]
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  console.log('Router guard:', {
    to: to.path,
    from: from.path,
    isAuthenticated: authStore.isAuthenticated,
    hasToken: !!authStore.token,
    hasUser: !!authStore.user,
    requiresAuth: to.meta.requiresAuth,
    requiresGuest: to.meta.requiresGuest
  })
  
  // Initialize auth if needed
  if (!authStore.user && authStore.token) {
    console.log('Initializing auth...')
    authStore.initializeAuth()
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    console.log('Route requires auth but user not authenticated, redirecting to login')
    next('/login')
    return
  }

  // Check if route requires guest (not authenticated)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    console.log('Route requires guest but user authenticated, redirecting to dashboard')
    next('/dashboard')
    return
  }

  // Check if route requires admin role
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    console.log('Route requires admin but user is not admin, redirecting to dashboard')
    next('/dashboard')
    return
  }

  console.log('Navigation allowed to:', to.path)
  next()
})

export default router 