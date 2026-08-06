import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import duration from 'dayjs/plugin/duration'

import 'dayjs/locale/en'
import 'dayjs/locale/ru'

dayjs.extend(relativeTime)
dayjs.extend(duration)

// Начальная локаль до загрузки пользовательских настроек
dayjs.locale('en')

export default dayjs
