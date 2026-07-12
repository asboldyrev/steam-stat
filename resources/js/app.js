import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'
import i18n from '@/i18n/index.js'
import { formatNumber } from '@/utils/formatNumber.js'
import App from '@/App.vue'
import '../css/app.css'

// Pages
import DashboardPage from '@/pages/DashboardPage.vue'
import LibraryPage from '@/pages/LibraryPage.vue'
import GameDetailPage from '@/pages/GameDetailPage.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: DashboardPage },
        { path: '/library', component: LibraryPage },
        { path: '/game/:id', component: GameDetailPage },
    ],
})

const pinia = createPinia()
const app = createApp(App)

// Регистрация глобальной функции форматирования чисел
app.config.globalProperties.$formatNumber = formatNumber

// Экспорт для использования вне Vue компонентов (например, в консоли или скриптах)
if (typeof window !== 'undefined') {
    window.formatNumber = formatNumber
}

app.use(i18n)
app.use(router)
app.use(pinia)
app.mount('#app')
