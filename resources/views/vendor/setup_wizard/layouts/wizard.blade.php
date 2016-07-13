<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ trans('setup_wizard::views.default_title') }} &raquo; @yield('page.title')</title>

    <!-- Styles (using Bootstrap as default) -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/setup_wizard/css/' . config('setup_wizard.theme') . '.css') }}">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-push-1 col-lg-8 col-lg-push-2">
            {!! Form::open([
                'route' => ['setup_wizard.submit', $currentStep->getSlug()],
                'files' => true,
            ]) !!}

            <div class="sw-wizard">
                <div class="sw-step-header">
                    @yield('wizard.header')
                </div>

                <div class="sw-breadcrumb">
                    @yield('wizard.breadcrumb')
                </div>

                <div class="sw-description">
                    @yield('wizard.description')
                </div>

                <div class="sw-errors">
                    @yield('wizard.errors')
                </div>

                <div class="sw-step-form">
                    @yield('wizard.form')
                </div>

                <div class="sw-navigation">
                    @yield('wizard.navigation')
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>
</body>
</html>