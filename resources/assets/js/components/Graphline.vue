<template>
    <canvas width="730" height="240" ref="canvasline"></canvas>
</template>

<script>
import Chart from 'chart.js';

    export default {
        props: ['datasheet'],
        data() {
            return {
                tasksMonthly: [],
                leadsMonthly: [],
                labels: []
            };
        },
        methods: {
            getMonthlyTasks() {
                for(const key in this.datasheet) {
                    this.tasksMonthly.push(this.datasheet[key].monthly.tasks);
                }
            },
            getMonthlyLeads() {
                for(const key in this.datasheet) {
                    this.leadsMonthly.push(this.datasheet[key].monthly.leads);
                }
            },
            getLabels() {
                for(const key in this.datasheet) {
                    this.labels.push(key);
                }
            },
            render(data)
            {
                this.getMonthlyTasks();
                this.getMonthlyLeads();
                this.getLabels();
                this.Chart = new Chart(this.$refs.canvasline.getContext('2d'), {
                    type: 'line',
                    data: {
                    labels: this.labels,
                    datasets: [
                        {
                            label: 'Tasks',
                            borderColor: "#242939",
                            data: this.tasksMonthly,
                            fill: false,
                            pointStyle: 'rectRounded',
                            pointRadius: 4,
                            pointHoverRadius: 5,
                        },
                        {
                            label: 'Leads',
                            borderColor: "#337ab7",
                            data: this.leadsMonthly,
                            fill: false,
                            pointStyle: 'rectRounded',
                            pointRadius: 4,
                            pointHoverRadius: 5,
                        },
                    ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false
                                }
                            }],
                            yAxes: [{
                                gridLines: {
                                    display:true,
                                    color: "rgba(139,143,146,0.42)",
                                    borderDash: [1, 6],
                                }
                            }]
                        }
                    },
                });
            },
          },
            mounted() {
                this.render();
            },
        };
</script>
