<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

interface Props {
    series: number[]
    colors: string[]
    labels: string[]
    centerValue: number
}

const props = defineProps<Props>()

const chartOptions = computed(() => ({
    chart: {
        type: 'donut',
        fontFamily: 'inherit',
    },
    dataLabels: {
        enabled: true,
    },
    plotOptions: {
        pie: {
            donut: {
                size: '50%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        showAlways: true,
                        label: 'Commandes',
                        formatter: () => props.centerValue,
                    },
                },
            },
        },
    },
    colors: props.colors,
    labels: props.labels,
}))
</script>

<template>
    <VueApexCharts
        type="donut"
        :options="chartOptions"
        :series="series"
    />
</template>
