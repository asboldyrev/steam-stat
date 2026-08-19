import { use } from 'echarts/core'
import { BarChart, HeatmapChart, LineChart, PieChart } from 'echarts/charts'
import {
    CalendarComponent,
    GridComponent,
    LegendComponent,
    TooltipComponent,
    VisualMapComponent,
} from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

use([
    CanvasRenderer,
    BarChart,
    LineChart,
    PieChart,
    HeatmapChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    CalendarComponent,
    VisualMapComponent,
])
