import { ref, computed } from 'vue'

const STORAGE_KEY = 'steamstat-theme'
const THEMES = { LIGHT: 'light', DARK: 'dark' }

// Глобальное состояние темы (singleton)
const theme = ref(THEMES.LIGHT)

function applyThemeToDom(value) {
  const html = document.documentElement
  if (value === THEMES.DARK) {
    html.classList.add('dark')
  } else {
    html.classList.remove('dark')
  }
  localStorage.setItem(STORAGE_KEY, value)
}

// Инициализация при импорте модуля
const saved = localStorage.getItem(STORAGE_KEY)
if (saved === THEMES.DARK || saved === THEMES.LIGHT) {
  theme.value = saved
} else {
  theme.value = window.matchMedia('(prefers-color-scheme: dark)').matches ? THEMES.DARK : THEMES.LIGHT
}
applyThemeToDom(theme.value)

export function useTheme() {
  const isDark = computed(() => theme.value === THEMES.DARK)

  const toggleTheme = () => {
    theme.value = theme.value === THEMES.LIGHT ? THEMES.DARK : THEMES.LIGHT
    applyThemeToDom(theme.value)
  }

  const setTheme = (newTheme) => {
    if (newTheme === THEMES.LIGHT || newTheme === THEMES.DARK) {
      theme.value = newTheme
      applyThemeToDom(theme.value)
    }
  }

  return {
    theme: computed(() => theme.value),
    isDark,
    toggleTheme,
    setTheme,
    THEMES,
  }
}