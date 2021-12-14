<template>
    <card class="p-10">
        <div class="flex insight-filters">
            <div class="flex w-1/2">
            </div>
            <div class="stay-right flex w-1/2">
                <date-range-picker
                    control-container-class="date-range-picker-control select-box-sm ml-auto h-6 text-xs appearance-none bg-40 pl-2 pr-6"
                    ref="picker"
                    v-show="filters.datePicker.show"
                    :opens="left"
                    v-model="filters.datePicker.dateRange"
                    @update="refresh()">
                    <template v-slot:input="picker" style="min-width: 350px;">
                        {{ dateFormat(picker.startDate) }} - {{ dateFormat(picker.endDate) }}
                    </template>
                </date-range-picker>
                <select @change="refresh()" v-model="filters.period.selected" v-show="filters.period.show"
                        class="flex-auto select-box-sm ml-auto w-24 h-6 text-xs appearance-none bg-40 pl-2 pr-6
                               active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline">
                    <option v-for="filter in filters.period.list" v-bind:value="filter.value" :key="filter.key">
                        {{ filter.text }}
                    </option>
                </select>
                <div class="flex-auto manufacturer-list">
                    <model-select :options="filters.subset.list"
                                  v-model="filters.subset.selected"
                                  @input="refresh"
                                  v-show="filters.subset.show">
                    </model-select>
                </div>
            </div>
        </div>
        <h4 class="chart-js-dashboard-title">{{ chartTitle }}</h4>
        <line-chart :chart-data="dataCollection" :options="options"></line-chart>
    </card>
</template>

<style>
.insight-filters .vue-daterange-picker{
    -webkit-box-flex: 1;
    -ms-flex: auto;
    flex: auto;
    display: block;
}
.date-range-picker-control{
  padding-right: 25px;
  margin-right: 5px;
  padding-top: 5px;
  cursor: pointer;
  width: 180px;
}
.manufacturer-list {
    margin-left: 3px;
}

.manufacturer-list .ui.dropdown,
.manufacturer-list .ui.dropdown .menu > .item,
.manufacturer-list .ui.search.dropdown > .text,
.manufacturer-list .ui.search.selection.dropdown > input.search {
    font-size: 12px;
}

.manufacturer-list {
    min-width: 14rem;
}

.manufacturer-list .ui.search.selection.dropdown > input.search,
.manufacturer-list .ui.selection.dropdown {
    padding: 0 0.5em 0 1.5em;
    height: 1.5rem;
}

.manufacturer-list .ui.search.dropdown > .text {
    top: 5px
}

.manufacturer-list .ui.fluid.dropdown > .dropdown.icon {
    padding: 0.5em;
}
</style>
<script>

import { ModelSelect } from 'vue-search-select'
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
        ModelSelect
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
            document.URL.replace('admin', 'nova-api');

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
            selected: ''
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
                    selected: this.card.filters.subset.selected !== undefined ? this.card.filters.subset.selected : null
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
        refresh() {
            Nova.request().get(this.card.options.endpoint, {
                params: {
                    period: this.filters.period.selected,
                    subset: this.filters.subset.selected,
                    from: this.dateFormat(this.filters.datePicker.dateRange.startDate),
                    to: this.dateFormat(this.filters.datePicker.dateRange.endDate)
                },
            }).then(({data}) => {
                const chartData = data.cards.filter((card) => card.component === 'area-chart')[0];

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
