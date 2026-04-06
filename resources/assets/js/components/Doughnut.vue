<template>
    <canvas width="750" height="400" ref="canvaschart"></canvas>
</template>

<script>
import Chart from 'chart.js';

    export default {
        props: ['statistics', 'closed', 'open'],
        methods: {
            render(data)
            {
                this.Chart = new Chart(this.$refs.canvaschart.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                    labels: this.statistics.keys,
                    datasets: [
                        {
                            backgroundColor: ["rgba(93, 115, 226, 1)", "rgba(93, 115, 226, 0.8)", "rgba(93, 115, 226, 0.6)", "rgba(93, 115, 226, 0.4)"],
                            data: this.statistics.counts
                        }
                   
                    ]
                    },
                    options: {
                        responsive: true,
                    },
                });
            },
          },
            mounted() {
                this.render();
            },
        };
</script>