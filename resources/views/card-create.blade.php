@extends('base')

@section('title', 'Customer')


@section('content')

<div class="container">
    <div class="row">
        <div class="col-sm-12 mt-5 mb-5">
            <div id="customerId" class="customer Form" data-id="{{ $customerId }}">
                <h4>Add a New Card</h4>
                <br>
                <button id="cancel" class="btn btn-outline-primary" data-id="{{ $customerId }}">Cancel</button>
                <br><br>
                {!! Form::open(['url' => '/card/store/customer/'.$customerId, 'method' => 'post']) !!}
                    <h6>Customer Type</h6>
                    <div class="form-group {{ $errors->has('customer_type') ? ' has-error' : '' }}">
                        {!! Form::radio('customer_type', 'individual', true, ['class' => 'form-check-input']); !!}
                        {!! Form::label('customer_type', 'Individual', ['class' => 'form-check-label']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('customer_type') ? ' has-error' : '' }}">
                        {!! Form::radio('customer_type', 'business', false, ['class' => 'form-check-input']); !!}
                        {!! Form::label('customer_type', 'Business', ['class' => 'form-check-label']); !!}
                    </div>
                    <br><br>
                    <h4>Payment</h4>
                    <div class="form-group {{ $errors->has('card_number') ? ' has-error' : '' }}">
                        {!! Form::label('card_number', 'Card Number'); !!}
                        {!! Form::text('card_number', null, ['placeholder' => 'XXXX-XXXX-XXXX-XXXX', 'class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('card_exp_month') ? ' has-error' : '' }}">
                        {!! Form::label('card_exp_month', 'Card Expiration Month'); !!}
                        {!! Form::text('card_exp_month', null, ['placeholder' => 'MM', 'class' => 'form-control']); !!}
                    </div>
                     <div class="form-group {{ $errors->has('card_exp_year') ? ' has-error' : '' }}">
                        {!! Form::label('card_exp_year', 'Card Expiration Year'); !!}
                        {!! Form::text('card_exp_year', null, ['placeholder' => 'YYYY', 'class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('card_cvv') ? ' has-error' : '' }}">
                        {!! Form::label('card_cvv', 'Card CVV'); !!}
                        {!! Form::text('card_cvv', null, ['placeholder' => 'xxx', 'class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_first_name') ? ' has-error' : '' }}">
                        {!! Form::label('billing_first_name', 'First Name'); !!}
                        {!! Form::text('billing_first_name', null, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_last_name') ? ' has-error' : '' }}">
                        {!! Form::label('billing_last_name', 'Last Name'); !!}
                        {!! Form::text('billing_last_name', null, ['class' => 'form-control']); !!}
                    </div>
                    <br><br>
                    <h4>Billing Address (required with payment above)</h4>
                    <div class="form-group {{ $errors->has('billing_company') ? ' has-error' : '' }}">
                        {!! Form::label('billing_company', 'Company'); !!}
                        {!! Form::text('billing_company', null, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_address') ? ' has-error' : '' }}">
                        {!! Form::label('billing_address', 'Street Address'); !!}
                        {!! Form::text('billing_address', null, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_city') ? ' has-error' : '' }}">
                        {!! Form::label('billing_city', 'City'); !!}
                        {!! Form::text('billing_city', null, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_state') ? ' has-error' : '' }}">
                        {!! Form::label('billing_state', 'State'); !!}
                        {!! Form::text('billing_state', null, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_zip') ? ' has-error' : '' }}">
                        {!! Form::label('billing_zip', 'Zip'); !!}
                        {!! Form::text('billing_zip', null, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_country') ? ' has-error' : '' }}">
                        {!! Form::label('billing_country', 'Country'); !!}
                        {!! Form::text('billing_country', null, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_phone_number') ? ' has-error' : '' }}">
                        {!! Form::label('billing_phone_number', 'Phone Number'); !!}
                        {!! Form::text('billing_phone_number', null, ['class' => 'form-control']); !!}
                    </div>
                    <br><br>    
                    {!! Form::submit('Save', ['class' => 'btn btn-primary mt-3']) !!}
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

        var $customerId = $('#customerId').data('id');

        // redirect.dispatch(Redirect.Action.APP, '/dashboard');

        $('#cancel').on('click', function (e) {
            var id = $(this).data('id');
            redirect.dispatch(Redirect.Action.APP, '/customer/' + id + '/edit');
            console.log('event ' + e + ' id ' + id);
        });
        

    </script>
@endsection