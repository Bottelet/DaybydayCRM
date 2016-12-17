<template>
    <canvas width="730" v-el:canvasline></canvas>
</template>

<script>
import Chart from 'chart.js';

    export default Vue.extend({
        props: ['values', 'labels', 'valuesextra'],
        render(data)
        {
        this.Chart = new Chart(this.$els.canvasline.getContext('2d'), {
            type: 'line',
            data: {
            labels: this.labels,
            datasets: [
                {
                    label: 'Created',
                    fillColor: "rgba(151,187,205,0.2)",
                    strokeColor: "rgb(58,72,92)",
                    pointColor: "rgba(58,72,92,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220,220,220,1)",
                    data: this.values
                },
                {
                    label: 'Completed',
                    fillColor: "rgba(151,187,205,0.2)",
                    strokeColor: "rgba(60,141,188,0.8)",
                    pointColor: "#3b8bba",
                    pointStrokeColor: "rgba(60,141,188,1)",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(60,141,188,1)",
                    data: this.valuesextra
                },
            ]
            }
        });
        },
        mounted() {

            console.log('chart: ' + this.Chart);
        }
    });
</script>
<!--
<template>
    <button v-if="verb" class="btn btn-default" @click.prevent="update()">{{ verb }}</button>
</template>




<script>
    import {moment} from './moment.js';
    
    export default {
        data() {
            return {
                // if verb is null in the template, the user is probably not logged in..
                verb: null
            }
        },
        props: {
            topicSlug: null
        },
        methods: {
            moment() {
               return moment(); 
            }, 
            up date() {
                return this.$http.post('/forum/topics/' + this.topicSlug + '/subscription').then((response) => {
                    this.getStatus();
                });
            },
            getStatus() {
                return this.$http.get('/forum/topics/' + this.topicSlug + '/subscription/status').then((response) => {
                    if (response !== null) {
                        if (response.body === '1') {
                            // subscribed
                            this.verb = 'Unsubscribe';
                        } else {
                            // not subscribed
                            this.verb = 'Subscribe';
                        }
                    } else {
                        // no row in database matching criteria
                        this.verb = 'Subscribe';
                    }
                });
            }
        },
        mounted() {
            this.getStatus();
        }
    }
</script>
-->