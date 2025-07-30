import { createRouter, createWebHistory } from 'vue-router'
import Homepage from '../views/HomePage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: Homepage}
    // component: () => import('../views/HomePage.vue')
  ],
})

export default router





