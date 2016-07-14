    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
        $(".sidebar-brand").toggleClass("shownone");
    });
        
        $(document).ready( function() {
$('.dropdown-toggle').dropdown();
});
        
$('.dropdown.keep-open').on({
    "shown.bs.dropdown": function() { this.closable = false; },
    "click":             function() { this.closable = true; },
    "hide.bs.dropdown":  function() { return this.closable; }
});


    $("#collapse1").click(function() {
        
        $(".box-body1").toggleClass("hide");
    });

$(function () {
     $('.panel-collapse').collapse('show');
     $("#collapse1").click(function(){
     $("#toggler1").toggleClass("fa fa-minus fa fa-plus") 
    });
    $("#collapse2").click(function(){
     $("#toggler2").toggleClass("fa fa-minus fa fa-plus") 
    });
   $("#collapse3").click(function(){
     $("#toggler3").toggleClass("fa fa-minus fa fa-plus") 
    });
    $("#collapse4").click(function(){
     $("#toggler4").toggleClass("fa fa-minus fa fa-plus") 
    });
    var active = false;
  
    $('#collapse-init').click(function () {
        if (active) {
            active = false;
            $('.panel-collapse').collapse('show');
            $('.panel-title').attr('data-toggle', '');
            $(this).text('Hide all');
        } else {
            active = true;
            $('.panel-collapse').collapse('hide');
            $('.panel-title').attr('data-toggle', 'collapse');
            $(this).text('Show All');
        }
    });
    
    $('#accordion').on('show.bs.collapse', function () {
        if (active) $('#accordion .in').collapse('hide');
    });

});

Vue.component('graphbar', {
            template:  
            `
            <canvas width="500" v-el:canvasbar></canvas>
            `,
            props: ['values', 'labels'],

            data(){
        		return {legend : ''};
            },

         ready() {
        var data = {
          labels: this.labels,
          
          datasets: [
            {
              fillColor: "rgba(0,138,230,1)",
              strokeColor: "rgba(0,148,230,1)",
              pointColor: "rgba(220,220,220,1)",
              pointStrokeColor: "#fff",
              pointHighlightFill: "#fff",
              pointHighlightStroke: "rgba(220,220,220,1)",
              data: this.values
            },
            
          ]
        };


		const chart = new Chart(
			this.$els.canvasbar.getContext('2d')
			).Bar(data);


		this.legend = (chart.generateLegend());
    }
        });

Vue.component('graphline', {
            template:  
            `
            <canvas width="730" v-el:canvasline></canvas>
            `,
            props: ['values', 'labels', 'valuesextra'],

            data(){
        		return {legend : ''};
            },

         ready() {
        var data = {
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
        };
       options = {
    responsive: true,
    maintainAspectRatio: false,
  };

		const chart = new Chart(
			this.$els.canvasline.getContext('2d')
			).Line(data);


		this.legend = (chart.generateLegend());
    }
        });

var graph = new Vue({
    el: 'body',
        data: {
        views: ['graphbar', 'graphline']
    },
});

$('.notification-warning').delay(5000).fadeOut(2500);
$('.notification-success').delay(5000).fadeOut(2500);

$('.search-select')
  .dropdown({
    direction: 'upward'
  })
;