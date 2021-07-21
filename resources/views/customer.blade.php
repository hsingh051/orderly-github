@extends('base')

@section('title', 'Customers')


@section('content')

    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-9">
                <div class="customer-cards">
                    <div id="app">
                        <search v-bind:shop="{{ json_encode($shop) }}" api-key="{{ env('SHOPIFY_API_KEY', '') }}" shop-origin="{{ ShopifyApp::shop()->shopify_domain }}" />
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <button id="customer-create" class="btn btn-outline-primary">Create Customer Profile</button>
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