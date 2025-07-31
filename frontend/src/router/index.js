import { createRouter, createWebHistory } from 'vue-router'
import Homepage from '../views/HomePage.vue'
import CalculateurPage from '../views/CalculateurPage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: Homepage}, {path: '/calculateur', name: 'calculateur', component: CalculateurPage}
    // component: () => import('../views/HomePage.vue')
  ],
})

export default router





