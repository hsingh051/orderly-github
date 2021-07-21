@extends('base')

@section('title', 'Settings')


@section('content')

<div class="container">
    <div class="row">
        <div class="col-sm-12 mt-5">
            <div class="settings Form">
                {!! Form::open(['url' => '/settings/update/' . $setting->id, 'method' => 'post']) !!}
                    <div class="form-group">
                        <h6>Gatway Setup</h6>
                        {!! Form::radio('gateway', 'authorize', true, ['class' => 'form-check-input']); !!}
                        {!! Form::label('gateway', 'Authorize.net', ['class' => 'form-check-label']); !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('authorize_payment_api_login_id', 'Authorize.net Payment API Login ID*'); !!}
                        {!! Form::text('authorize_payment_api_login_id', $setting->authorize_payment_api_login_id, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('authorize_payment_transaction_key', 'Authorize.net Payment Transaction Key*'); !!}
                        {!! Form::text('authorize_payment_transaction_key', $setting->authorize_payment_transaction_key, ['class' => 'form-control']); !!}
                    </div>
                    {{-- <div class="form-group">
                        {!! Form::label('authorize_key', 'Authorize.net Key*'); !!}
                        {!! Form::text('authorize_key', $setting->authorize_key, ['class' => 'form-control']); !!}
                    </div> --}}
                    {!! Form::submit('Update Settings', ['class' => 'btn btn-primary mt-3']) !!}
                {!! Form::close() !!}
                </div>
            </div>
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
            title: 'Settings',
            breadcrumbs: breadcrumb,
        };

        var myTitleBar = TitleBar.create(app, titleBarOptions);

        var redirect = Redirect.create(app);

        // redirect.dispatch(Redirect.Action.APP, '/dashboard');
        

    </script>
@endsection