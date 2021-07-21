@extends('base')

@section('title', 'Dashboard')


@section('content')

        <div class="row">
            <div class="col-12 col-lg-9">
                <div class="row">
                    <div class="col-4">
                        <h4 class="font-weight-bold">Charge Card on File</h4>
                    </div>
                    <!-- BUG IS BEING CAUSED SO TURNED OFF FOR NOW
                    <div class="col-8 mb-3 text-right">
                        {{-- <a id="newCard" data-id="{{ $customer->id }}" class="btn btn-outline-primary new-card">Add a New Card</a> --}}
                    </div>
                    -->
                </div>
                <div class="row">
                    @foreach($cards as $card)
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="credit-card card">
                                <div class="card-body {{ $card->cardType }}">
                                    <div class="row">
                                        <div class="col-10 col-sm-8">
                                            <h5 class="name">{{ $card->cardFirst }} {{ $card->cardLast }}</h5>
                                        </div>
                                        <div class="col-2 col-sm-4">
                                            <div class="logo">
                                                @if ($card->cardType = 'Visa')
                                                    <img src="{{ asset('/img/visa-logo.svg') }}" alt="Visa">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row card-strip">
                                        <div class="col-12 col-sm-7">
                                            <div class="field-container">
                                                <div class="field-label">Credit Card Number</div>
                                                <div class="field-text">{{ $card->cardNumber }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-5">
                                            <div class="field-container">
                                                <div class="field-label">Exp</div>
                                                <div class="field-text">{{ $card->cardExp }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-sm-7">
                                            <div class="card-company">{{ $card->cardCompany }}</div>
                                            <address class="billing">
                                                {{ $card->cardAddress }}<br>
                                                {{ $card->cardCity }}, {{ $card->cardState }} {{ $card->cardZip }}<br>
                                            </address>
                                        </div>
                                        <div class="col-5">
                                            <div class="paynowcountdownContainer">
                                                <b>Please Wait <span class="paynowcountdown">20</span> seconds</b>
                                                <p>Tip: Please check order on right.If incorrect, wait for our servers to update and refresh page.<p>
                                            </div>
                                            <div class="paynowbutton d-none">
                                                {!! Form::open(['url' => 'order/' . $order->id . '/customer/' .  $profile['cust_id'] . '/card/' . $card->paymentProfileId .  '/amount/' . $order->amount]) !!}
                                                    @csrf
                                                    {!! Form::submit('PAY NOW', ['class' => 'btn btn-pay']) !!}
                                                {!! Form::close() !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="sidebar col-12 col-lg-3">
                <div class="sticky-top sticky-offset">
                    <div class="row">
                        <div class="col-12">
                            <h3>Order</h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="choose-credit-card">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="order-total">
                                            <div class="title text-center">Total</div>
                                            <div class="value text-center">${{ $order->amount }}</div>
                                        </div>
                                    </div>
                                </div>
                                @if ($draftOrder->applied_discount)
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="order-items">
                                                <div class="title text-center">
                                                    Applied Discount
                                                </div>
                                                <div class="value text-center">
                                                    Amount: ${{ $draftOrder->applied_discount->amount }}
                                                </div>
                                                <p class="text-center text-sm">Description: {{ $draftOrder->applied_discount->description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-12">
                                        <div class="order-items">
                                            <div class="title text-center">
                                                Line Items
                                            </div>
                                            <div class="value text-center">
                                                {{ count($draftOrder->line_items) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5 class="font-weight-bold">Details</h5>
                                        <div class="details">
                                             @foreach($draftOrder->line_items as $item)
                                                <div class="detail-item">
                                                    <div class="row">
                                                        <div class="col-5">
                                                            <div class="title">{{ $item->title }}</div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="quantity"><strong>Qty:</strong> {{ $item->quantity }}</div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="price">${{ $item->price }}</div>
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
                </div>
            </div>
        </div>

        <div class="row justify-content-center d-none">
            <div class="text-center w-100">
                <h3>Charging Credit Card. Do Not Leave or Refresh Page.</h3>
            </div>
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

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
        var breadcrumb = Button.create(app, {label: 'Customers'});
            breadcrumb.subscribe(Button.Action.CLICK, function() {
            app.dispatch(Redirect.toApp({path: '/customer'}));
        });

        var titleBarOptions = {
            title: 'Charge Card',
            breadcrumbs: breadcrumb,
        };

        var myTitleBar = TitleBar.create(app, titleBarOptions);

        var redirect = Redirect.create(app);

        // redirect.dispatch(Redirect.Action.APP, '/dashboard');

        $('#newCard').on('click', function (e) {
            var id = $(this).data('id');
            redirect.dispatch(Redirect.Action.APP, '/card/create/customer/' + id);
        });

        $('.btn-pay').on('submit', function(){
            return confirm('Are you sure?');
        });


        var sec = 20;
        var myTimer = $('.paynowcountdown');
        var myBtn = $('.paynowbutton');
        var $paynowbutton = $('.paynowbutton');
        var $paynowcountdown = $('.paynowcountdown');
        var $paynowcountdownContainer = $('.paynowcountdownContainer');


        function makeTimer() {

            if (sec < 10) {
                myTimer.html("0" + sec);
            } else {
                myTimer.html(sec);
            }
            if (sec <= 0) {
                $paynowcountdownContainer.addClass('d-none');
                $paynowbutton.removeClass('d-none');
                
                return;
            }
            sec -= 1;
            console.log('timer ' + sec)
        }


        setInterval(() => {
            makeTimer();
        }, 1000);

        });

    </script>
@endsection