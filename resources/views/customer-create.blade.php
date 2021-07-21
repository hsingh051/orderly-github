@extends('base')

@section('title', 'Customer')


@section('content')

<div class="container">
    <div class="row">
        <div class="col-sm-12 mt-5 mb-5">
            <div class="customer Form">
                {!! Form::open(['url' => '/customer', 'method' => 'post']) !!}
                    <h4>Create a Customer Profile</h4>
                    <div class="form-group {{ $errors->has('createshopifycustomer') ? ' has-error' : '' }}">
                        {!! Form::checkbox('createshopifycustomer', 'Create Shopify Customer', false, ['class' => 'form-check-input']); !!}
                        {!! Form::label('createshopifycustomer', 'Create Shopify Customer', ['class' => 'form-check-label']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                        {!! Form::label('email', 'Customer Email*'); !!}
                        {!! Form::email('email', old('email'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                        {!! Form::label('description', 'Description (optional)'); !!}
                        {!! Form::text('description', old('description'), ['class' => 'form-control']); !!}
                    </div>
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
                    <h4>Account Manager</h4>
                    <div class="form-group {{ $errors->has('employee_name') ? ' has-error' : '' }}">
                        {!! Form::label('employee_name', 'Name (optional)'); !!}
                        {!! Form::text('employee_name', old('employee_name'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('employee_email') ? ' has-error' : '' }}">
                        {!! Form::label('employee_email', 'Email (optional)'); !!}
                        {!! Form::email('employee_email', old('employee_email'), ['class' => 'form-control']); !!}
                    </div>
                    <br><br>
                    <h4>Payment</h4>
                    <div class="form-group {{ $errors->has('card_number') ? ' has-error' : '' }}">
                        {!! Form::label('card_number', 'Card Number'); !!}
                        {!! Form::text('card_number', old('card_number'), ['placeholder' => 'XXXX-XXXX-XXXX-XXXX', 'class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('card_exp_month') ? ' has-error' : '' }}">
                        {!! Form::label('card_exp_month', 'Card Expiration Month'); !!}
                        {!! Form::text('card_exp_month', old('card_exp_month'), ['placeholder' => 'MM', 'class' => 'form-control']); !!}
                    </div>
                     <div class="form-group {{ $errors->has('card_exp_year') ? ' has-error' : '' }}">
                        {!! Form::label('card_exp_year', 'Card Expiration Year'); !!}
                        {!! Form::text('card_exp_year', old('card_exp_year'), ['placeholder' => 'YYYY', 'class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('card_cvv') ? ' has-error' : '' }}">
                        {!! Form::label('card_cvv', 'Card CVV'); !!}
                        {!! Form::text('card_cvv', old('card_cvv'), ['placeholder' => 'xxx', 'class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_first_name') ? ' has-error' : '' }}">
                        {!! Form::label('billing_first_name', 'First Name'); !!}
                        {!! Form::text('billing_first_name', old('billing_first_name'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_last_name') ? ' has-error' : '' }}">
                        {!! Form::label('billing_last_name', 'Last Name'); !!}
                        {!! Form::text('billing_last_name', old('billing_last_name'), ['class' => 'form-control']); !!}
                    </div>
                    <br><br>
                    <h4>Billing Address (required with payment above)</h4>
                    <div class="form-group {{ $errors->has('billing_company') ? ' has-error' : '' }}">
                        {!! Form::label('billing_company', 'Company (optional)'); !!}
                        {!! Form::text('billing_company', old('billing_company'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_address') ? ' has-error' : '' }}">
                        {!! Form::label('billing_address', 'Street Address'); !!}
                        {!! Form::text('billing_address', old('billing_address'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_city') ? ' has-error' : '' }}">
                        {!! Form::label('billing_city', 'City'); !!}
                        {!! Form::text('billing_city', old('billing_city'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_state') ? ' has-error' : '' }}">
                        {!! Form::label('billing_state', 'State'); !!}
                        {!! Form::text('billing_state', old('billing_state'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_zip') ? ' has-error' : '' }}">
                        {!! Form::label('billing_zip', 'Zip'); !!}
                        {!! Form::text('billing_zip', old('billing_zip'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_country') ? ' has-error' : '' }}">
                        {!! Form::label('billing_country', 'Country'); !!}
                        {!! Form::text('billing_country', old('billing_country'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('billing_phone_number') ? ' has-error' : '' }}">
                        {!! Form::label('billing_phone_number', 'Phone Number (optional)'); !!}
                        {!! Form::text('billing_phone_number', old('billing_phone_number'), ['class' => 'form-control']); !!}
                    </div>
                    {{-- <div class="form-group {{ $errors->has('billing_fax_number') ? ' has-error' : '' }}">
                        {!! Form::label('billing_fax_number', 'Fax Number'); !!}
                        {!! Form::text('billing_fax_number', old('billing_fax_number'), ['class' => 'form-control']); !!}
                    </div> --}}
                    <br><br>    
                    <h4>Shipping Address</h4>
                    <div class="form-group {{ $errors->has('same_as_billing') ? ' has-error' : '' }}">
                        {!! Form::radio('same_as_billing', 'true', false, ['class' => 'form-check-input']); !!}
                        {!! Form::label('same_as_billing', 'Shipping is the same as billing', ['class' => 'form-check-label']); !!}
                    </div>
                    <br>
                    <div class="form-group {{ $errors->has('shipping_first_name') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_first_name', 'First Name'); !!}
                        {!! Form::text('shipping_first_name', old('shipping_first_name'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('shipping_last_name') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_last_name', 'Last Name'); !!}
                        {!! Form::text('shipping_last_name', old('shipping_last_name'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('shipping_company') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_company', 'Company'); !!}
                        {!! Form::text('shipping_company', old('shipping_company'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('shipping_address') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_address', 'Street Address'); !!}
                        {!! Form::text('shipping_address', old('shipping_address'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('shipping_city') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_city', 'City'); !!}
                        {!! Form::text('shipping_city', old('shipping_city'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('shipping_state') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_state', 'State'); !!}
                        {!! Form::text('shipping_state', old('shipping_state'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('shipping_zip') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_zip', 'Zip'); !!}
                        {!! Form::text('shipping_zip', old('shipping_zip'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('shipping_country') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_country', 'Country'); !!}
                        {!! Form::text('shipping_country', old('shipping_country'), ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('shipping_phone_number') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_phone_number', 'Phone Number (optional)'); !!}
                        {!! Form::text('shipping_phone_number', old('shipping_phone_number'), ['class' => 'form-control']); !!}
                    </div>
                    {{-- <div class="form-group {{ $errors->has('shipping_fax_number') ? ' has-error' : '' }}">
                        {!! Form::label('shipping_fax_number', 'Fax Number'); !!}
                        {!! Form::text('shipping_fax_number', old('shipping_fax_number'), ['class' => 'form-control']); !!}
                    </div> --}}
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
            title: 'Customer',
            breadcrumbs: breadcrumb,
        };

        var myTitleBar = TitleBar.create(app, titleBarOptions);

        var redirect = Redirect.create(app);

        // redirect.dispatch(Redirect.Action.APP, '/dashboard');
        

    </script>
@endsection