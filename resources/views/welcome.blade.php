<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
                padding-bottom: 500px;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                @extends('shopify-app::layouts.default')

                @section('content')
                    <div class="flex-center">
                        <p>You are: {{ ShopifyApp::shop()->shopify_domain }}</p>
                    </div>
                @endsection

                @section('scripts')
                    @parent

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
                        var breadcrumb = Button.create(app, {label: 'My breadcrumb'});
                            breadcrumb.subscribe(Button.Action.CLICK, function() {
                            app.dispatch(Redirect.toApp({path: '/breadcrumb-link'}));
                        });

                        var titleBarOptions = {
                            title: 'My page title',
                            breadcrumbs: breadcrumb,
                        };

                        var myTitleBar = TitleBar.create(app, titleBarOptions);

                        var redirect = Redirect.create(app);

                        redirect.dispatch(Redirect.Action.APP, '/dashboard');

                    </script>
                @endsection
            </div>
        </div>
    </body>
</html>
