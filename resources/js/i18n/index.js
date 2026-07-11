import { createI18n } from 'vue-i18n'
import enMessages from './locales/en.json'
import ruMessages from './locales/ru.json'

// Определяем доступные локали
const messages = {
  en: enMessages,
  ru: ruMessages,
}

// Получаем сохранённую локаль из localStorage или используем 'en' по умолчанию
const savedLocale = localStorage.getItem('locale')
const defaultLocale = savedLocale && messages[savedLocale] ? savedLocale : 'en'

// Создаём экземпляр i18n
const i18n = createI18n({
  legacy: false, // использовать Composition API
  locale: defaultLocale,
  fallbackLocale: 'en',
  messages,
  globalInjection: true,
  datetimeFormats: {
    en: {
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
        hour: 'numeric',
        minute: 'numeric',
      },
    },
    ru: {
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      },
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
        hour: 'numeric',
        minute: 'numeric',
      },
    },
  },
  numberFormats: {
    en: {
      currency: {
        style: 'currency',
        currency: 'USD',
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      },
      percent: {
        style: 'percent',
        minimumFractionDigits: 0,
        maximumFractionDigits: 1,
      },
    },
    ru: {
      currency: {
        style: 'currency',
        currency: 'RUB',
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
      },
      percent: {
        style: 'percent',
        minimumFractionDigits: 0,
        maximumFractionDigits: 1,
      },
    },
  },
})

// Экспортируем экземпляр i18n
export default i18n

// Вспомогательная функция для изменения локали с сохранением в localStorage
export function setLocale(locale) {
  if (!messages[locale]) {
    console.warn(`Locale "${locale}" is not supported. Falling back to "en".`)
    locale = 'en'
  }
  i18n.global.locale.value = locale
  localStorage.setItem('locale', locale)
  document.documentElement.lang = locale
}

// Инициализация языка документа
document.documentElement.lang = defaultLocale