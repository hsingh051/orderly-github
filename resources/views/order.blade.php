@extends('base')

@section('title', 'Orders')


@section('content')

    <div class=" mb-5">
        <div class="row">
            <div class="col-md-9">
                <h4 class="font-weight-bold">Search</h4>
                <div class="order-cards">
                    <div id="app">
                        <SearchOrder v-bind:shop="{{ json_encode($shop) }}" api-key="{{ env('SHOPIFY_API_KEY', '') }}" shop-origin="{{ ShopifyApp::shop()->shopify_domain }}" />
                    </div>
                </div>
            </div>
            <div class="sidebar orders-sidebar col-md-3">
                <div class="sticky-top sticky-offset">
                    <div class="orders">
                        <div class="row">
                            <div class="col-12">
                                <div class="today-grabber">
                                    <div class="title text-center">Today's Sales</div>
                                    <div class="value text-center">${{ $salesToday }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="today-orders">
                                    <div class="title text-center">
                                        Orders
                                    </div>
                                    <div class="value text-center">
                                        {{ $ordersCount }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="today-pending">
                                    <div class="title text-center">
                                        Pending
                                    </div>
                                    <div class="value text-center">
                                        ${{ $pendingToday }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (count($ordersToday) > 0)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h4 class="font-weight-bold">Orders Today</h4>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-12">
                                    <div class="today-list">
                                        @foreach ($ordersToday as $order)
                                            <div class="list-item">
                                                <div class="row">
                                                    <div class="col-8">
                                                        <div class="name">
                                                            {{ $order->email }}
                                                        </div>
                                                        <div class="amount">
                                                            ${{ $order->amount }}
                                                        </div>
                                                        <div class="time">
                                                            {{ $order->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <!-- <div class="credit-card">**3049</div> -->
                                                        <div class="status paid mt-4">{{ $order->gateway_status }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (count($ordersPending) > 0)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h4 class="font-weight-bold">Pending Orders</h4>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-12">
                                    <div class="today-list">
                                        @foreach ($ordersPending as $order)
                                            <div class="list-item">
                                                <div class="row">
                                                    <div class="col-8">
                                                        <div class="name">
                                                            {{ $order->email }}
                                                        </div>
                                                        <div class="amount">
                                                            ${{ $order->amount }}
                                                        </div>
                                                        <div class="time">
                                                            {{ $order->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <!-- <div class="credit-card">**3049</div> -->
                                                        <div class="status paid mt-4">{{ $order->gateway_status }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (count($ordersFraud) > 0)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h4 class="font-weight-bold">Fraud Detected</h4>
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-12">
                                    <div class="today-list">
                                        @foreach ($ordersFraud as $order)
                                            <div class="list-item">
                                                <div class="row">
                                                    <div class="col-8">
                                                        <div class="name">
                                                            {{ $order->email }}
                                                        </div>
                                                        <div class="amount">
                                                            ${{ $order->amount }}
                                                        </div>
                                                        <div class="time">
                                                            {{ $order->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <!-- <div class="credit-card">**3049</div> -->
                                                        <div class="status paid mt-4">{{ $order->gateway_status }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>   
@endsection

@section('scripts')
    @parent
    
    <script type="text/javascript">
        $(document).ready(function () {
            $('#customer-create').on('click', function () {
                console.log('customer-create');
                redirect.dispatch(Redirect.Action.APP, '/customer/create');
            });

            $('.customer-edit').on('click', function () {
                var id = $(this).data('id');
                console.log('customer edit ' + id);
                if (id) {
                    redirect.dispatch(Redirect.Action.APP, '/customer/' + id + '/edit');
                }
            });

        });
    </script>
@endsection