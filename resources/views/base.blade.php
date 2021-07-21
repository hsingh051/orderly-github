<!DOCTYPE html>
<html>
    <head>
        <title>Orderly - @yield('title')</title>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
        <script src="https://unpkg.com/@shopify/app-bridge"></script>
        <script type="text/javascript">
            var AppBridge = window['app-bridge'];
            var actions = AppBridge.actions;
            var TitleBar = actions.TitleBar;
            var Button = actions.Button;
            var createApp = AppBridge.default;
            
            var app = createApp({
                apiKey: '{{ env('SHOPIFY_API_KEY', '') }}',
                shopOrigin: '{{ ShopifyApp::shop()->shopify_domain }}'
            });

            var Redirect = actions.Redirect;
            var breadcrumb = Button.create(app, {label: 'Dashboard'});
                breadcrumb.subscribe(Button.Action.CLICK, function() {
                app.dispatch(Redirect.toApp({path: '/dashboard'}));
            });

            var titleBarOptions = {
                title: 'Customers',
                breadcrumbs: breadcrumb,
            };

            // var myTitleBar = TitleBar.create(app, titleBarOptions);

            var redirect = Redirect.create(app);
        </script>
        <meta name="csrf-token" content="{{ csrf_token() }}">

    </head>
    <body>
        <div id="app">
            @include('components.navbar')
            @include('components.flash')
            <div class="container-fluid page">
                @yield('content')
            </div>
        </div>
        <script src="{{ mix('/js/app.js') }}"></script>
        @yield('scripts')
        @include('components.chat')
    </body>
</html>