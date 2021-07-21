@extends('base')

@section('title', 'Dashboard')


@section('content')
    <div class="container">
        <div class="row">
            <div class="text-center mt-5">
                <h5>Whoops... Something went wrong.</h5>
                {{-- @if ($errorCode == 'E00041' ) --}}
                    <h4>Try creating a customer profile by clicking the button below.</h4>
                    <h6>{{ $errorText }}</h6>
                    <h5>{{ $errorCode }}</h5>
                {{-- @endif --}}
                @if ($path)
                    <button onclick="back()" class="btn btn-primary">Back To Draft</button>
                @endif
                @if (!$customer->gateway_profile_id)
                    <button id="customer-prefilled" data-id="{{ $customer->id }}" class="btn btn-outline-primary">Add Customer to Payment Gateway</button>
                @endif
                @if ($customer->gateway_profile_id)
                    <button onclick="customerCreate()" class="btn btn-outline-primary">Create Customer Profile</button>
                @endif
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    @parent
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
            title: 'Error',
            breadcrumbs: breadcrumb,
        };

        var myTitleBar = TitleBar.create(app, titleBarOptions);

        var redirect = Redirect.create(app);
        
        function back(params) {
            redirect.dispatch(Redirect.Action.ADMIN_PATH, '{{$uri}}');
        }

        function customerCreate() {
            redirect.dispatch(Redirect.Action.APP, '/customer/create');
        }

        $('#customer-prefilled').on('click', function (e) {
            var id = $(this).data('id');
            redirect.dispatch(Redirect.Action.APP, '/customer/create/prefilled/' + id);
        });

    </script>
@endsection