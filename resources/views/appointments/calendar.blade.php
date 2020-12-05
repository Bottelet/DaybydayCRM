<!DOCTYPE HTML>
<html>
<head>
  <title>DaybydayCRM</title>
  <link rel="stylesheet" href="{{ asset(elixir('css/vendor.css')) }}">
  <link rel="stylesheet" href="{{ asset(elixir('css/app.css')) }}">
  <link href="{{ URL::asset('css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ URL::asset('css/picker.classic.css') }}" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="{{ asset(elixir('css/bootstrap-select.min.css')) }}">
</head>
<body>
<div id="wrapper">
  <calendar></calendar>
</div>
<script>
  window.trans = <?= translations() ?>;
</script>
<script src="/js/manifest.js"></script>
<script src="/js/vendor.js"></script>
<script type="text/javascript" src="{{ URL::asset('js/app.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/picker.js') }}"></script>

</body>
</html>
