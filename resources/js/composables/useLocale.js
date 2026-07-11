import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale as setI18nLocale } from '../i18n/index.js'

/**
 * Composable для управления языком (локализацией)
 * Читает язык из localStorage ('en'/'ru'), переключает через vue-i18n, сохраняет в localStorage
 * @returns {Object} locale, availableLocales, setLocale, toggleLocale
 */
export function useLocale() {
  const { locale: i18nLocale } = useI18n()
  const locale = ref(i18nLocale.value)

  // Доступные локали
  const availableLocales = [
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'ru', name: 'Русский', flag: '🇷🇺' },
  ]

  // Инициализация из localStorage
  const initLocale = () => {
    const saved = localStorage.getItem('locale')
    if (saved === 'en' || saved === 'ru') {
      locale.value = saved
      setI18nLocale(saved)
    } else {
      // По умолчанию 'en'
      locale.value = 'en'
      setI18nLocale('en')
    }
  }

  // Установка локали
  const setLocale = (newLocale) => {
    if (newLocale !== 'en' && newLocale !== 'ru') {
      console.warn(`Locale "${newLocale}" is not supported. Use "en" or "ru".`)
      return
    }
    locale.value = newLocale
    setI18nLocale(newLocale)
    localStorage.setItem('locale', newLocale)
  }

  // Переключение между en и ru
  const toggleLocale = () => {
    const newLocale = locale.value === 'en' ? 'ru' : 'en'
    setLocale(newLocale)
  }

  // Синхронизация с i18n при изменении извне
  watch(i18nLocale, (newVal) => {
    if (newVal !== locale.value) {
      locale.value = newVal
    }
  })

  // Инициализация при первом вызове
  initLocale()

  return {
    locale,
    availableLocales,
    setLocale,
    toggleLocale,
  }
}