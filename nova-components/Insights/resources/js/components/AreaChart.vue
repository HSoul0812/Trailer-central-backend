<template>
    <card class="p-10">
        <div class="flex insight-filters">
            <div class="flex w-1/6">
            </div>
            <div class="stay-right flex">
                <date-range-picker
                    control-container-class="date-range-picker-control select-box ml-auto text-sm appearance-none bg-40 pl-2 pr-6"
                    ref="picker"
                    v-show="filters.datePicker.show"
                    :opens="left"
                    v-model="filters.datePicker.dateRange"
                    class="flex mr-4"
                    @update="refresh()">
                    <template v-slot:input="picker" style="min-width: 350px;">
                        {{ dateFormat(picker.startDate) }} - {{ dateFormat(picker.endDate) }}
                    </template>
                </date-range-picker>
                <select @change="refresh()" v-model="filters.period.selected" v-show="filters.period.show"
                        class="flex-auto select-box text-sm appearance-none bg-40 pl-2 pr-6 mr-4
                               active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline">
                    <option v-for="filter in filters.period.list" v-bind:value="filter.value" :key="filter.key">
                        {{ filter.text }}
                    </option>
                </select>
                <div class="flex-auto mr-4">
                    <multi-select :options="filters.category.list"
                                  :selected-options="filters.category.selected"
                                  placeholder="Select a category"
                                  class="subset-list"
                                  @select="onSelectCategory"
                                  v-show="filters.category.show">
                    </multi-select>
                </div>
                <div class="flex-auto">
                    <multi-select :options="filters.subset.list"
                                  :selected-options="filters.subset.selected"
                                  v-bind:placeholder="filters.subset.placeholder"
                                  class="subset-list"
                                  @select="onSelectSubset"
                                  v-show="filters.subset.show">
                    </multi-select>
                </div>
            </div>
        </div>
        <h4 class="chart-js-dashboard-title">{{ chartTitle }}</h4>
        <line-chart :chart-data="dataCollection" :options="options"></line-chart>
    </card>
</template>

<style>
.date-range-picker-control {
    height: 2.7em;
    padding-top: 12px;
    padding-right: 28px;
    cursor: pointer;
    color: black;
    width: 210px;
}

.subset-list .menu.visible, .subset-list {
    background-color: var(--40) !important;
}
</style>
<script>

import { MultiSelect } from 'vue-search-select'
import LineChart from '../area-chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels';
import DateRangePicker from 'vue2-daterange-picker'
import moment from 'moment/dist/moment'
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
import 'vue-search-select/dist/VueSearchSelect.css'

Chart.plugins.unregister(ChartDataLabels);

export default {
    components: {
        LineChart,
        DateRangePicker,
        MultiSelect
    },
    data() {
        const filterPeriodDefault = 'per_week';
        const filterPeriodList = [
            {text: 'Per day', value: 'per_day'},
            {text: 'Per week', value: 'per_week'},
            {text: 'Per month', value: 'per_month' },
            {text: 'Per quarter', value: 'per_quarter'},
            {text: 'Per year', value: 'per_year'}
        ];

        const defaultStartDate = moment().startOf('year');
        const defaultDate = moment().endOf('year');

        this.card.options = this.card.options !== undefined ? this.card.options : {};

        this.card.options.endpoint = this.card.options.endpoint !== undefined ?
            this.card.options.endpoint :
            document.URL.replace('admin/dashboards', 'nova-vendor/insight-filters');

        this.card.filters.period = this.card.filters.period !== undefined ? this.card.filters.period : {
            show: true,
            list: filterPeriodList,
            default: filterPeriodDefault,
            selected: filterPeriodDefault
        };

        this.card.filters.subset = this.card.filters.subset !== undefined ? this.card.filters.subset : {
            show: false,
            list: [],
            default: null,
            selected: [],
            placeholder: ''
        };

        this.card.filters.category = this.card.filters.category !== undefined ? this.card.filters.category : {
            show: false,
            list: [],
            default: null,
            selected: []
        };

        this.card.options.xAxis = this.card.options.xAxis !== undefined ? this.card.options.xAxis : {categories: []};

        this.card.filters.datePicker = this.card.filters.datePicker !== undefined ? this.card.filters.datePicker : {
            show: false,
            dateRange: {startDate: defaultStartDate, endDate: defaultDate}
        };

        return {
            dataCollection: {},
            filters: {
                datePicker: {
                    show: this.card.filters.datePicker.show !== undefined ? this.card.filters.datePicker.show : true,
                    dateRange: {
                        startDate: this.card.filters.datePicker.dateRange.startDate !== undefined ? this.card.filters.datePicker.dateRange.startDate : defaultStartDate,
                        endDate: this.card.filters.datePicker.dateRange.endDate !== undefined ? this.card.filters.datePicker.dateRange.endDate : defaultDate,
                    },
                },
                period: {
                    show: this.card.filters.period.show !== undefined ? this.card.filters.period.show : true,
                    list: this.card.filters.period.list !== undefined ? this.card.filters.period.list : filterPeriodList,
                    default: this.card.filters.period.default !== undefined ? this.card.filters.period.default : filterPeriodDefault,
                    selected: this.card.filters.period.selected !== undefined ? this.card.filters.period.selected : filterPeriodDefault,
                },
                subset: {
                    show: this.card.filters.subset.show !== undefined ? this.card.filters.subset.show : true,
                    list: this.card.filters.subset.list !== undefined ? this.card.filters.subset.list : [],
                    default: this.card.filters.subset.default !== undefined ? this.card.filters.subset.default : null,
                    selected: this.card.filters.subset.selected !== undefined ? this.card.filters.subset.selected : [],
                    placeholder: this.card.filters.subset.placeholder !== undefined ? this.card.filters.subset.placeholder : ''
                },
                category: {
                    show: this.card.filters.category.show !== undefined ? this.card.filters.category.show : true,
                    list: this.card.filters.category.list !== undefined ? this.card.filters.category.list : [],
                    default: this.card.filters.category.default !== undefined ? this.card.filters.category.default : null,
                    selected: this.card.filters.category.selected !== undefined ? this.card.filters.category.selected : []
                }
            },
            chartTooltips: this.card.options.tooltips !== undefined ? this.card.options.tooltips : undefined,
            chartPlugins: this.card.options.plugins !== undefined ? this.card.options.plugins : false,
            chartLayout: this.card.options.layout !== undefined ? this.card.options.layout : {
                padding: {
                    left: 20,
                    right: 20,
                    top: 0,
                    bottom: 10
                }
            },
            chartLegend: this.card.options.legend !== undefined ? this.card.options.legend : {
                display: true,
                position: 'left',
                labels: {
                    fontColor: '#7c858e',
                    fontFamily: "'Nunito'"
                }
            },
        }
    },
    computed: {
        chartTitle() {
            return this.card.title !== undefined ? this.card.title : 'A valuable chart';
        }
    },
    props: [
        'card'
    ],
    mounted() {
        this.fillData(this.card.options.xAxis.categories, this.card.series);
    },
    methods: {
        dateFormat (datetime) {
            return moment(datetime).format('YYYY-MM-DD')
        },
        onSelectCategory(items) {
            this.filters.category.selected = items
            this.refresh();
        },
        onSelectSubset(items) {
            this.filters.subset.selected = items
            this.refresh();
        },
        refresh() {
            Nova.request().post(this.card.options.endpoint, {
                period: this.filters.period.selected,
                subset: this.filters.subset.selected.map((item) => item.value),
                category: this.filters.category.selected.map((item) => item.value),
                from: this.dateFormat(this.filters.datePicker.dateRange.startDate),
                to: this.dateFormat(this.filters.datePicker.dateRange.endDate)
            }).then(({data}) => {
                const chartData = data[0];

                if (chartData) {
                    this.fillData(chartData.options.xAxis.categories, chartData.series);
                }
            }).catch((error) => console.warn(error));
        },
        fillData(labels, datasets) {
            this.options = {
                layout: this.chartLayout,
                legend: this.chartLegend,
                scales: {
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            fontSize: 10,
                            callback: function (num, index, values) {
                                if (num >= 1000000000) {
                                    return (num / 1000000000).toFixed(1).replace(/\.0$/, '') + 'G';
                                }
                                if (num >= 1000000) {
                                    return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
                                }
                                if (num >= 1000) {
                                    return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
                                }
                                return num;
                            }
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            lineHeight: 0.8,
                            fontSize: 10,
                        }
                    }]
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: this.chartPlugins,
            };

            this.options.tooltips = {};

            this.options.tooltips.callbacks = {
                label: function (tooltipItem, data) {
                    return tooltipItem.yLabel.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }
            }

            if (this.chartTooltips !== undefined) {
                this.options.tooltips = this.chartTooltips;
                const tooltipList = ["custom", "itemSort", "filter"];

                for (let z = 0; z < tooltipList.length; z++) {
                    if (this.options.tooltips[tooltipList[z]] !== undefined) {
                        if (this.options.tooltips[tooltipList[z]].search("function") !== -1) {
                            eval("this.options.tooltips." + tooltipList[z] + " = " + this.options.tooltips[tooltipList[z]]);
                        }
                    }
                }

                if (this.chartTooltips.callbacks !== undefined) {
                    const callbackList = [
                        "beforeTitle",
                        "title",
                        "afterTitle",
                        "beforeBody",
                        "beforeLabel",
                        "label",
                        "labelColor",
                        "labelTextColor",
                        "afterLabel",
                        "afterBody",
                        "beforeFooter",
                        "footer",
                        "afterFooter"
                    ];

                    for (let i = 0; i < callbackList.length; i++) {
                        if (this.options.tooltips.callbacks[callbackList[i]] !== undefined) {
                            if (this.options.tooltips.callbacks[callbackList[i]].search("function") !== -1) {
                                eval("this.options.tooltips.callbacks." + callbackList[i] + " = " + this.options.tooltips.callbacks[callbackList[i]]);
                            }
                        }
                    }
                }
            }

            this.title = this.card.title;
            this.dataCollection = {
                labels: labels,
                datasets: datasets
            };
        },
    },
}
</script>
