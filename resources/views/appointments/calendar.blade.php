<!DOCTYPE HTML>
<html>
<head>
  <title>DaybydayCRM</title>
  <link rel="stylesheet" href="{{ mix('css/vendor.css') }}" type="text/css">
  <link rel="stylesheet" href="{{ mix('css/app.css') }}" type="text/css">
  <link rel="stylesheet" href="{{ URL::asset('css/font-awesome.min.css') }}" type="text/css">
  <link rel="stylesheet" href="{{ URL::asset('css/picker.classic.css') }}" type="text/css">
  <link rel="stylesheet" href="{{ mix('css/bootstrap-select.min.css') }}">
  <link rel="shortcut icon" href="{{{ asset('images/favicon.png') }}}">
</head>
<body>
<div id="wrapper">
  <calendar></calendar>
</div>
<script>
  var DayByDay = {
    csrfToken: "{{csrf_token()}}",
    baseUrl: "{{url('/')}}"
  }
  window.trans = <?php
  // copy all translations from /resources/lang/CURRENT_LOCALE/* to global JS variable
  try {
      $filename = File::get(resource_path() . '/lang/' . App::getLocale() . '.json');
  } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
      return;
  }
  $trans = [];
  $entries = json_decode($filename, true);
  foreach ($entries as $k => $v) {
      $trans[$k] = trans($v);
  }
  $trans[$filename] = trans($filename);
  echo json_encode($trans);
  ?>;
</script>
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script type="text/javascript" src="{{ mix('js/app.js') }}"></script>

</body>
</html>
