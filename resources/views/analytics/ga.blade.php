@extends('layouts.master')
@section('heading')
<h1>Google Analytics</h1>
@stop

@section('content')

<script>
(function(w,d,s,g,js,fs){
  g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(f){this.q.push(f);}};
  js=d.createElement(s);fs=d.getElementsByTagName(s)[0];
  js.src='https://apis.google.com/js/platform.js';
  fs.parentNode.insertBefore(js,fs);js.onload=function(){g.load('analytics');};
}(window,document,'script'));
</script>
<div class="row">

<div id="embed-api-auth-container"></div>
<div id="chart-3-container"></div>
<div id="chart-1-container"></div>
<div id="chart-2-container"></div>
<div id="medium-chart-container"></div>
<div id="main-chart-container"></div>

<div id="view-selector-container"></div>
</div>
<script>

gapi.analytics.ready(function() {

  /**
   * Authorize the user immediately if the user has already granted access.
   * If no access has been created, render an authorize button inside the
   * element with the ID "embed-api-auth-container".
   */
  gapi.analytics.auth.authorize({
    container: 'embed-api-auth-container',
    clientid: '638322839963-a0m0rl8cm9ldstdjgu1l4a9jk7d3lm8j.apps.googleusercontent.com'
  });


  /**
   * Create a ViewSelector for the first view to be rendered inside of an
   * element with the id "view-selector-1-container".
   */
  var viewSelector = new gapi.analytics.ViewSelector({
    container: 'view-selector-container'
  });



  // Render both view selectors to the page.
  viewSelector.execute();


  /**
   * Create the first DataChart for top countries over the past 30 days.
   * It will be rendered inside an element with the id "chart-1-container".
   */
  var dataChart1 = new gapi.analytics.googleCharts.DataChart({
    query: {
      metrics: 'ga:sessions',
      dimensions: 'ga:country',
      'start-date': '30daysAgo',
      'end-date': 'yesterday',
      'max-results': 6,
      sort: '-ga:sessions'
    },
    chart: {
      container: 'chart-1-container',
      type: 'PIE',
      options: {
        width: '100%',
        pieHole: 4/9
      }
    }
  });


  /**
   * Create the second DataChart for top countries over the past 30 days.
   * It will be rendered inside an element with the id "chart-2-container".
   */
  var dataChart2 = new gapi.analytics.googleCharts.DataChart({
    query: {
      metrics: 'ga:sessions',
      dimensions: 'ga:country',
      'start-date': '30daysAgo',
      'end-date': 'yesterday',
      'max-results': 6,
      sort: '-ga:sessions'
    },
    chart: {
      container: 'chart-2-container',
      type: 'PIE',
      options: {
        width: '40%',
        pieHole: 4/9
      }
    }
  });

    var dataChart3 = new gapi.analytics.googleCharts.DataChart({
    query: {
      metrics: 'ga:sessions',
      dimensions: 'ga:date',
      'start-date': '20daysAgo',
      'end-date': 'yesterday'
    },
    chart: {
      container: 'chart-3-container',
      type: 'LINE',
      options: {
        width: '40%'
      }
    }
  });

  var mainChart = new gapi.analytics.googleCharts.DataChart({
    query: {
      'dimensions': 'ga:country',
      'metrics': 'ga:sessions',
      'sort': '-ga:sessions',
      'max-results': '3'
    },
    chart: {
      type: 'TABLE',
      container: 'main-chart-container',
      options: {
        width: '30%'
      }
    }
  });
  var mediumChart = new gapi.analytics.googleCharts.DataChart({
    query: {
      'dimensions': 'ga:medium',
      'metrics': 'ga:sessions',
      'sort': '-ga:sessions',
      'max-results': '3'
    },
    chart: {
      type: 'TABLE',
      container: 'medium-chart-container',
      options: {
        width: '40%'
      }
    }
  });
  /**
   * Update the first dataChart when the first view selecter is changed.
   */
  viewSelector.on('change', function(ids) {
    dataChart1.set({query: {ids: ids}}).execute();
    dataChart2.set({query: {ids: ids}}).execute();
    dataChart3.set({query: {ids: ids}}).execute();
    mainChart.set({query: {ids: ids}}).execute();
    mediumChart.set({query: {ids: ids}}).execute();
  });

  window.addEventListener('resize', function() {
    linechart.execute();
});

  /**
   * Update the second dataChart when the second view selecter is changed.
   */


});
</script>

@stop
  