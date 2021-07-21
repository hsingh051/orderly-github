@extends('base')

@section('title', 'Customers')


@section('content')

    <div id="customer" data-id="{{ $customer->id }}"></div>
    <h1>Card Saved! Redirecting Automatically</h1>
    
@endsection

@section('scripts')
    @parent
    <script src="https://unpkg.com/@shopify/app-bridge"></script>
    <script type="text/javascript">
        $(document).ready(function () {
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

            var myTitleBar = TitleBar.create(app, titleBarOptions);

            var redirect = Redirect.create(app);
            var $customerId = $('#customer').data('id');
            redirect.dispatch(Redirect.Action.APP, '/customer/' + $customerId + '/edit');
        });
    </script>
@endsection