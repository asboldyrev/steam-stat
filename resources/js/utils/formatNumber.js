import i18n from '@/i18n/index.js'

/**
 * Форматирует число по текущей локали с использованием Intl.NumberFormat.
 * @param {number|string|null|undefined} value - Число для форматирования. Может быть строкой, null, undefined.
 * @param {Object} options - Опции форматирования (стиль, мин/макс дробные знаки и т.д.) и локаль.
 * @param {string} [options.locale] - Явная локаль (например, 'en', 'ru'). Если не указана, используется текущая локаль из vue-i18n.
 * @param {string} [options.style='decimal'] - Стиль форматирования: 'decimal', 'currency', 'percent', 'unit'.
 * @param {string} [options.currency] - Код валюты (например, 'USD', 'RUB'), требуется при style='currency'.
 * @param {number} [options.minimumFractionDigits] - Минимальное количество дробных знаков.
 * @param {number} [options.maximumFractionDigits] - Максимальное количество дробных знаков.
 * @param {string} [options.unit] - Единица измерения (например, 'kilometer', 'percent'), требуется при style='unit'.
 * @param {string} [options.unitDisplay='short'] - Отображение единицы: 'short', 'long', 'narrow'.
 * @param {boolean} [options.useGrouping=true] - Использовать группировку разрядов (например, 1,000).
 * @param {string} [options.fallback='—'] - Строка, возвращаемая при некорректном значении (null, undefined, NaN).
 * @returns {string} Отформатированная строка или fallback.
 */
export function formatNumber(value, options = {}) {
  // Обработка некорректных значений
  if (value === null || value === undefined || Number.isNaN(Number(value))) {
    return options.fallback ?? '—'
  }

  const num = Number(value)
  if (!Number.isFinite(num)) {
    return options.fallback ?? '—'
  }

  // Определяем локаль: переданная явно или текущая из i18n
  const locale = options.locale ?? i18n.global.locale.value

  // Извлекаем опции для Intl.NumberFormat, исключая наши кастомные поля
  const { locale: _, fallback, ...intlOptions } = options

  try {
    const formatter = new Intl.NumberFormat(locale, intlOptions)
    return formatter.format(num)
  } catch (error) {
    console.warn('Error formatting number:', error)
    // В случае ошибки возвращаем число как строку с минимальным форматированием
    return num.toString()
  }
}

/**
 * Глобальная функция для использования вне Vue компонентов (например, в консоли или скриптах).
 * Регистрируется в window при инициализации приложения.
 */
export default formatNumber