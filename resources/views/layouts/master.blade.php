<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daybyday CRM</title>
    <link href="{{ URL::asset('css/jasny-bootstrap.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/bootstrap-select.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/dropzone.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/jquery.atwho.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/fonts/flaticon.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/bootstrap-tour-standalone.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('css/picker.classic.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://unpkg.com/vis-timeline@7.3.4/styles/vis-timeline-graph2d.min.css">
    <link href="https://unpkg.com/ionicons@4.5.5/dist/css/ionicons.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/assets/sass/vendor.scss', 'resources/assets/sass/app.scss'])
    @endif
    <link href="{{ URL::asset('css/summernote.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{{ asset('images/favicon.png') }}}">
    <script>
        var DayByDay =  {
            csrfToken: "{{csrf_token()}}",
            stripeKey: "{{config('services.stripe.key')}}",
            baseUrl: "{{url('/')}}"
        }
    </script>
    <?php if (isDemo()) { ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-152899919-3"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-152899919-3');
        </script>
    <?php } ?>
    <script src="https://js.stripe.com/v3/"></script>
    @stack('style')
</head>
<body>

<div id="wrapper">
{{--
    Vue mounts directly onto #wrapper (see resources/assets/js/app.js) using
    #wrapper's existing markup as an in-DOM template, since there's no
    explicit render()/template option. Vue 2's compiler requires a template
    to have exactly one root element, but #wrapper's actual children (navbar
    include, sidebar nav, page-content-wrapper div) are siblings — so this
    single wrapping div exists purely to satisfy that single-root requirement
    without changing anything about the page's visible structure or CSS.
--}}
<div>
@include('layouts._navbar')
<!-- /#sidebar-wrapper -->
    <!-- Sidebar menu -->

    <nav id="myNavmenu" class="navmenu navmenu-default navmenu-fixed-left offcanvas-sm" role="navigation">
        <div class="list-group panel" id="MainMenu">
            <p class=" list-group-item siderbar-top" title=""><img src="{{url('images/daybyday-logo-white.png')}}" alt="" style="width: 100%; margin: 1em 0;"></p>
            <a href="{{route('dashboard')}}" class=" list-group-item" data-parent="#MainMenu"><i
                        class="fa fa-home sidebar-icon"></i><span id="menu-txt">{{ __('Dashboard') }} </span></a>
            <a href="{{route('users.show', \Auth::user()->external_id)}}" class=" list-group-item"
               data-parent="#MainMenu"><i
                        class="fa fa-user sidebar-icon"></i><span id="menu-txt">{{ __('Profile') }}</span> </a>
            <a href="{{ route('clients.index')}}" class=" list-group-item" data-toggle="collapse" data-target="#clients" data-parent="#MainMenu"><i
                        class="fa fa-user-secret sidebar-icon"></i><span id="menu-txt">{{ __('Clients') }}</span>
                <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
            <div class="collapse {{Request::is('clients*') ? 'in' : ''}}" id="clients">

                <a href="{{ route('clients.index')}}" class="list-group-item childlist"> <i
                            class="bullet-point"><span></span></i> {{ __('All Clients') }}</a>
                @if(Entrust::can('client-create'))
                    <a href="{{ route('clients.create')}}" id="newClient"
                       class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('New Client') }}</a>
                @endif
            </div>
            <a href="{{ route('projects.index')}}" class="list-group-item" data-toggle="collapse" data-target="#projects" data-parent="#MainMenu"><i
                        class="fa fa-briefcase sidebar-icon "></i><span id="menu-txt">{{ __('Projects') }}</span>
                <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
            <div class="collapse {{Request::is('projects*') ? 'in' : ''}}" id="projects">
                <a href="{{ route('projects.index')}}" class="list-group-item childlist"> <i
                            class="bullet-point"><span></span></i> {{ __('All Projects') }}</a>
                @if(Entrust::can('project-create'))
                    <a href="{{ route('projects.create')}}" id="newProject"  class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('New Project') }}</a>
                @endif
            </div>
            <a href="{{ route('tasks.index')}}" class="list-group-item" data-toggle="collapse" data-target="#tasks" data-parent="#MainMenu"><i
                        class="fa fa-tasks sidebar-icon "></i><span id="menu-txt">{{ __('Tasks') }}</span>
                <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
            <div class="collapse {{Request::is('tasks*') ? 'in' : ''}}" id="tasks">
                <a href="{{ route('tasks.index')}}" class="list-group-item childlist"> <i
                            class="bullet-point"><span></span></i> {{ __('All Tasks') }}</a>
                @if(Entrust::can('task-create'))
                    <a href="{{ route('tasks.create')}}" id="newTask" class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('New Task') }}</a>
                @endif
            </div>

            <a href="{{ route('users.index')}}" class=" list-group-item" data-toggle="collapse" data-target="#user" data-parent="#MainMenu"><i
                        class="fa fa-users sidebar-icon"></i><span id="menu-txt">{{ __('Users') }}</span>
                <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
            <div class="collapse {{Request::is('users*') ? 'in' : ''}}" id="user">
                <a href="{{ route('users.index')}}" class="list-group-item childlist"> <i
                            class="bullet-point"><span></span></i> {{ __('All Users') }}</a>
                @if(Entrust::can('user-create'))
                    <a href="{{ route('users.create')}}"
                       class="list-group-item childlist"> <i class="bullet-point"><span></span></i> {{ __('New User') }}
                    </a>
                @endif
            </div>

            <a href="{{ route('leads.index')}}" class=" list-group-item" data-toggle="collapse" data-target="#leads" data-parent="#MainMenu"><i
                        class="fa fa-hourglass-2 sidebar-icon"></i><span id="menu-txt">{{ __('Leads') }}</span>
                <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
            <div class="collapse {{Request::is('leads*') ? 'in' : ''}}" id="leads">
            <a href="{{ route('leads.index')}}" class="list-group-item childlist"> <i
                            class="bullet-point"><span></span></i> {{ __('All Leads') }}</a>
                @if(Entrust::can('lead-create'))
                    <a href="{{ route('leads.create')}}"
                       class="list-group-item childlist"> <i class="bullet-point"><span></span></i> {{ __('New Lead') }}
                    </a>
                @endif
            </div>
            <a href="{{ route('invoices.overdue')}}" class=" list-group-item" data-toggle="collapse" data-target="#sales" data-parent="#MainMenu"><i
                class="fa fa-dollar sidebar-icon"></i><span id="menu-txt">{{ __('Sales') }}</span>
                <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
            <div class="collapse {{Request::is('invoices*') || Request::is('products*') ? 'in' : ''}}" id="sales">
            <a href="{{ route('invoices.overdue')}}" class="list-group-item childlist">
                <i class="bullet-point"><span></span></i> {{ __('Overdue') }}
            </a>
            <a href="{{ route('products.index')}}" class="list-group-item childlist">
                <i class="bullet-point"><span></span></i> {{ __('Products') }}
            </a>
            </div>
            {{--@if(Entrust::can('calendar-view'))
                <a href="{{ route('appointments.calendar')}}" class="list-group-item" data-toggle="collapse" data-target="#appointments" data-parent="#MainMenu"><i
                            class="fa fa-calendar sidebar-icon"></i><span id="menu-txt">{{ __('Appointments') }}</span>
                    <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
                <div class="collapse {{Request::is('appointments*') ? 'in' : ''}}" id="appointments">
                    <a href="{{ route('appointments.calendar')}}" target="_blank"
                       class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('Calendar') }}</a>
                </div>
            @endif--}}
            <a href="{{ route('absence.index')}}" class=" list-group-item" data-toggle="collapse" data-target="#hr" data-parent="#MainMenu"><i
                        class="fa fa-handshake-o sidebar-icon"></i><span id="menu-txt">{{ __('HR') }}</span>
                <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
            <div class="collapse {{Request::is('absence*') || Request::is('departments*') ? 'in' : ''}}" id="hr">
                @if(Entrust::can('absence-view'))
                    <a href="{{ route('absence.index')}}"
                       class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('Absence overview') }}</a>
                @endif
                @if(Entrust::can('absence-manage'))
                    <a href="{{ route('absence.create', ['management' => 'true'])}}"
                       class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('Register absence') }}</a>
                @endif
                <a href="{{ route('departments.index')}}"
                   class="list-group-item childlist"> <i
                            class="bullet-point"><span></span></i> {{ __('Departments') }}</a>
            </div>

            @if(Entrust::hasRole('administrator') || Entrust::hasRole('owner'))
                <a href="{{ route('settings.index')}}" class=" list-group-item" data-toggle="collapse" data-target="#settings" data-parent="#MainMenu"><i
                            class="fa fa-cog sidebar-icon"></i><span id="menu-txt">{{ __('Settings') }}</span>
                    <i class="icon ion-md-arrow-dropup arrow-side sidebar-arrow"></i></a>
                <div class="collapse {{Request::is('settings*') || Request::is('roles*') || Request::is('integrations*') ? 'in' : ''}}" id="settings">
                    <a href="{{ route('settings.index')}}"
                       class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('Overall Settings') }}</a>

                    <a href="{{ route('roles.index')}}"
                       class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('Role & Permissions Management') }}</a>
                    <a href="{{ route('integrations.index')}}"
                       class="list-group-item childlist"> <i
                                class="bullet-point"><span></span></i> {{ __('Integrations') }}</a>
                </div>
            @endif
        </div>
    </nav>


<!-- Page Content -->
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if(Session::has('flash_message_warning'))
                        <div class="alert alert-warning alert-dismissible flash-message" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('Close') }}"><span aria-hidden="true">&times;</span></button>
                            {{ Session::get('flash_message_warning') }}
                        </div>
                    @endif
                    @if(Session::has('flash_message'))
                        <div class="alert alert-success alert-dismissible flash-message" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('Close') }}"><span aria-hidden="true">&times;</span></button>
                            {{ Session::get('flash_message') }}
                        </div>
                    @endif
                    <div class="row" style="margin-bottom: 20px; margin-top: 20px;">
                        <div class="col-md-9">
                            <h1 class="global-heading" style="margin: 0;">@yield('heading')</h1>
                        </div>
                        <div class="col-md-3">
                            @yield('actions')
                        </div>
                    </div>
                    <div class="row" style="display: none;">
                        @yield('alerts')
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- /#page-content-wrapper -->
</div>
</div>
{{--
    jQuery MUST load as a classic (non-module) blocking script before all jQuery plugins.
    All classic jQuery plugins attach to the same window.jQuery instance, and then @vite
    loads the ES modules (which are deferred). This ensures page inline code uses the
    classic jQuery with all plugins available.
--}}
<script src="{{ URL::asset('js/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/bootstrap.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/bootstrap-select.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery.caret.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jasny-bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/picker.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery.atwho.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/bootstrap-tour-standalone.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/dropzone.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/summernote.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery-ui-sortable.min.js') }}"></script>
{{--
    The Vite build externalizes "jquery" (rollupOptions.external) so it shares
    the one classic, global jQuery instance loaded above rather than bundling
    a second copy — but that only works via rollupOptions.output.globals for
    UMD/IIFE output, not the ES module format @vite emits. This import map
    resolves the bare "jquery" specifier in the built module to a shim that
    re-exports the global.
--}}
<script type="importmap">
    { "imports": { "jquery": "{{ URL::asset('js/jquery-esm-shim.js') }}" } }
</script>
@vite(['resources/assets/js/app.js'])
<script>
    $(document).ready(function () {
        // Auto-dismiss flashed success/warning alerts after 5s, matching the
        // previous toast's duration — manual close via the button still works.
        setTimeout(function () {
            $('.flash-message').fadeOut(400, function () { $(this).remove(); });
        }, 5000);

        // Moved from layouts/_navbar.blade.php — that markup is inside the
        // Vue-mounted #wrapper subtree, and Vue's compiler drops <script>
        // tags found in its own in-DOM template.
        var menuToggle = document.getElementById('menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var wrapper = document.getElementById('wrapper');
                if (wrapper) wrapper.classList.toggle('myNavmenu-icons');
            });
        }
    });
</script>
@if(App::getLocale() === "dk")
<script>
    $(document).ready(function () {
        $.extend( $.fn.pickadate.defaults, {
            monthsFull: [ 'januar', 'februar', 'marts', 'april', 'maj', 'juni', 'juli', 'august', 'september', 'oktober', 'november', 'december' ],
            monthsShort: [ 'jan', 'feb', 'mar', 'apr', 'maj', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec' ],
            weekdaysFull: [ 'søndag', 'mandag', 'tirsdag', 'onsdag', 'torsdag', 'fredag', 'lørdag' ],
            weekdaysShort: [ 'søn', 'man', 'tir', 'ons', 'tor', 'fre', 'lør' ],
            today: 'i dag',
            clear: 'slet',
            close: 'luk',
            firstDay: 1,
            format: 'd. mmmm yyyy',
            formatSubmit: 'yyyy/mm/dd'
        });
    });
</script>
@endif
@stack('scripts')
<script>
    window.trans = <?php
    // copy all translations from /resources/lang/CURRENT_LOCALE/* to global JS variable
    try {
        $filename = File::get(resource_path() . '/lang/' . App::getLocale() . '.json');
        $trans    = [];
        $entries  = json_decode($filename, true);
        foreach ($entries as $k => $v) {
            $trans[$k] = trans($v);
        }
        $trans[$filename] = trans($filename);
        echo json_encode($trans);
    } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
        echo '{}';
    }
    ?>;
</script>
<script>
    $(document).ready(function() {
        // Ensure dropdown is available on the jQuery instance used by the page
        if (!$.fn.dropdown && window.jQuery && window.jQuery.fn.dropdown) {
            $.fn.dropdown = window.jQuery.fn.dropdown;
        }

        if (!$.fn.dropdown) {
            $.fn.dropdown = function() {
                console.warn('dropdown() was called but is not defined');
                return this;
            };
        }
    });
</script>
</body>

</html>
