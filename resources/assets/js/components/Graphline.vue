<template>
    <canvas width="730" height="240" ref="canvasline"></canvas>
</template>

<script>
import Chart from 'chart.js';

    export default {
        props: ['values', 'labels', 'valuesextra'],
        methods: {
            render(data)
            {
                this.Chart = new Chart(this.$refs.canvasline.getContext('2d'), {
                    type: 'line',
                    data: {
                    labels: this.labels,
                    datasets: [
                        {
                            label: 'Created',
                            borderColor: "rgb(58,72,92)",
                            data: this.values
                        },
                        {
                            label: 'Completed',
                            backgroundColor: "rgba(151,187,205,0.2)",
                            borderColor: "rgba(60,141,188,0.8)",
                            data: this.valuesextra
                        },
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