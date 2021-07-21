@extends('base')

@section('title', 'Customer')


@section('content')

<div class="container">
    <div class="row">
        <div class="col-sm-12 mt-5 mb-5">
            <div class="customer Form">
                    @if (!$customer->gateway_profile_id)
                        <button id="customer-prefilled" data-id="{{ $customer->id }}" class="btn btn-outline-primary mb-3">Add Customer to Payment Gateway</button>
                    @endif
                
                    <h4>Customer Profile - {{ $customer->first_name}} {{ $customer->last_name }}</h4>
                    {{-- <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                        {!! Form::label('email', 'Customer Email* (Must match email in shopify)'); !!}
                        {!! Form::email('email', $customer->email, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                        {!! Form::label('description', 'Description'); !!}
                        {!! Form::text('description', $customer->description, ['class' => 'form-control']); !!}
                    </div> --}}
                    <h5>Email:</h5>
                    <h6>{{ $customer->email }}</h6>
                    <h5>Customer Type</h5>
                    <h6>{{ $customer->customer_type }}</h6>
                    {{-- <div class="form-group {{ $errors->has('customer_type') ? ' has-error' : '' }}">
                        {!! Form::radio('customer_type', 'individual', ($customer->customer_type == 'individual' ? true : false), ['class' => 'form-check-input']); !!}
                        {!! Form::label('customer_type', 'Individual', ['class' => 'form-check-label']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('customer_type') ? ' has-error' : '' }}">
                        {!! Form::radio('customer_type', 'business', ($customer->customer_type == 'business' ? true : false), ['class' => 'form-check-input']); !!}
                        {!! Form::label('customer_type', 'Business', ['class' => 'form-check-label']); !!}
                    </div> --}}
                    <br><br>
                    {!! Form::open(['url' => '/customer/'.$customer->id, 'method' => 'put']) !!}
                    <h4>Account Manager or Sales Rep (internal use only)</h4>
                    <div class="form-group {{ $errors->has('employee_name') ? ' has-error' : '' }}">
                        {!! Form::label('employee_name', 'Name'); !!}
                        {!! Form::text('employee_name', $customer->employee_name, ['class' => 'form-control']); !!}
                    </div>
                    <div class="form-group {{ $errors->has('employee_email') ? ' has-error' : '' }}">
                        {!! Form::label('employee_email', 'Email'); !!}
                        {!! Form::email('employee_email', $customer->employee_email, ['class' => 'form-control']); !!}
                    </div>
                        {!! Form::submit('Update', ['class' => 'btn btn-primary mt-3']) !!}
                    {!! Form::close() !!}
                    <br><br>
                    @if ($customer->gateway_profile_id)
                        <h4>Payments Methods</h4>
                        <br>
                        <button id="newCard" data-id="{{ $customer->id }}" class="btn btn-primary mt-1 mb-5">New Card</button>
                        <br>
                    @endif
                    <div class="row ml mr">
                        @foreach($cards as $card)
                            <div class="col-sm-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-text">Card Number: <strong>{{ $card->card_number }}</strong></h3>
                                        <h3 class="card-text">Card Exp: <strong>{{ $card->card_exp }}</strong></h3>
                                        <h4>{{ $card->first_name }} {{ $card->last_name }}</h4>
                                        <h5 class="card-title">Credit Card - {{ $card->card_type }}</h5>
                                        {!! Form::open(['url' => 'card/' . $card->id . '/delete/', 'method' => 'get', 'class' => 'delete']) !!}
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input class="btn btn-outline-danger" type="submit" value="Delete">
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
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
        var breadcrumb = Button.create(app, {label: 'Customers'});
            breadcrumb.subscribe(Button.Action.CLICK, function() {
            app.dispatch(Redirect.toApp({path: '/customer'}));
        });

        var titleBarOptions = {
            title: 'Customer',
            breadcrumbs: breadcrumb,
        };

        var myTitleBar = TitleBar.create(app, titleBarOptions);

        var redirect = Redirect.create(app);

        // redirect.dispatch(Redirect.Action.APP, '/dashboard');

        $('#newCard').on('click', function (e) {
            var id = $(this).data('id');
            redirect.dispatch(Redirect.Action.APP, '/card/create/customer/' + id);
        });

        $('#customer-prefilled').on('click', function (e) {
            var id = $(this).data('id');
            redirect.dispatch(Redirect.Action.APP, '/customer/create/prefilled/' + id);
        });

        $('.delete').on('submit', function(){
            return confirm('Are you sure?');
        });

    </script>
@endsection