@extends('base')

@section('title', 'Dashboard')


@section('content')

@if(count($orders) == 0)
<div class="container mt-5">
    <h3>Instructions:</h3>
    <br>
    <h6>
        @if ($setting)
            1) Settings saved! <button id="settings-edit" class="btn btn-outline-success btn-sm">Edit Gateway</button><br>
        @else
            1) Setup your payment gateway here -> <button id="settings" class="btn btn-outline-primary btn-sm">Setup Gateway</button><br>
        @endif
        2) Add a customer profile to your payment gateway as needed - <button id="customer" class="btn btn-outline-primary btn-sm">Add Customer</button> <br>
        3) Make a draft order <br>
        4) Select "More Actions" Dropdown at the top of the draft order page<br>
        5) Click "Orderly - Charge Card On File"
    </h6>
    </div>   
@else
    <table class="table table-striped">
        <thead>
            <tr>
                {{-- <th scope="col">#ID</th> --}}
                <th scope="col">#</th>
                <th scope="col">Date Created</th>
                <th scope="col">From Now</th>
                <th scope="col">Shopify Status</th>
                <th scope="col">Gateway Status</th>
                <th scope="col">Gateway Transaction ID</th>
                <th scope="col">Customer Email</th>
                <th scope="col">Amount</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $orderItem)
                <tr class="table-row go-to-order" data-id="{{ $orderItem->order_id }}">
                    {{-- <th scope="row">{{ $orderItem->id }}</th> --}}
                <th scope="row">@if (isset($orderItem->order_id)) Order {{ $orderItem->order_name }} @else Draft {{ $orderItem->draft_name }} @endif</th>
                    <td>{{ Carbon\Carbon::parse($orderItem->created_at)->toDayDateTimeString() }}</td>
                    <td>{{ Carbon\Carbon::parse($orderItem->created_at)->diffForHumans() }}</td>
                    <td>{{ $orderItem->shopify_status }}</td>
                    <td>{{ $orderItem->gateway_status }}</td>
                    <td>{{ $orderItem->gateway_transaction_id }}</td>
                    <td>{{ $orderItem->email }}</td>
                    <td>${{ $orderItem->amount }}</td>
                    <td>
                        @if ($orderItem->gateway_status != 'settledSuccessfully')
                            {!! Form::open(['url' => 'order/' . $orderItem->id . '/status/', 'method' => 'get']) !!}
                                {!! Form::submit('Check Status', ['class' => 'btn btn-primary', 'onClick' => 'event.stopPropagation();']) !!}
                            {!! Form::close() !!}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
    {{ $orders->links() }}
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
            var ButtonGroup = actions.ButtonGroup;
            var createApp = AppBridge.default;
            var app = createApp({
                apiKey: '{{ env('SHOPIFY_API_KEY', '') }}',
                shopOrigin: '{{ ShopifyApp::shop()->shopify_domain }}',
            });
            var Redirect = actions.Redirect;
            var breadcrumb = Button.create(app, {label: 'Customers'});
                breadcrumb.subscribe(Button.Action.CLICK, function() {
                app.dispatch(Redirect.toApp({path: '/customer'}));
            });
            
            // var customers = Button.create(app, {label: 'Customers'});
            // var settings = Button.create(app, {label: 'Settings'});
            
            // var moreActions = ButtonGroup.create(app, {
            //     label: 'More actions',
            //     buttons: [customers, settings],
            // });


            var titleBarOptions = {
                title: 'Dashboard',
                breadcrumbs: breadcrumb,
                // buttons: {
                //     secondary: [moreActions],
                // },
            };


            var myTitleBar = TitleBar.create(app, titleBarOptions);

            var redirect = Redirect.create(app);

            redirect.dispatch(Redirect.Action.APP, '/dashboard');

            
            $('#settings').on('click', function () {
                redirect.dispatch(Redirect.Action.APP, '/settings/');
            });

            $('#settings-edit').on('click', function () {
                redirect.dispatch(Redirect.Action.APP, '/settings/edit');
            });

            $('#customer').on('click', function () {
                redirect.dispatch(Redirect.Action.APP, '/customer/create');
            });

            $('.go-to-order').on('click', function () {
                var id = $(this).data('id');

                redirect.dispatch(Redirect.Action.ADMIN_PATH, '/orders/' + id);
            });
            
            
            
        });

    </script>
@endsection