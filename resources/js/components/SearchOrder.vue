<template>
  <ais-instant-search
    :search-client="searchClient"
    index-name="orders"
  >
    <!-- Other search components go here -->
    <ais-configure :filters="('shop_id = ' + shop.id)" />
    <ais-search-box
        placeholder="Search Orders by: name, date, id, email, amount, order #, or status"
        submit-title="Find"
        reset-title="Clear"
        :show-loading-indicator="true"
    />
    <ais-stats />
    <h4 class="font-weight-bold mt-3">Results</h4>
    <ais-hits>
        <template
            slot="item"
            slot-scope="{ item }"
        >
            <div v-if="item.shopify_status != draft" v-bind:data-id="item.order_id" class="order-item">
                    <div class="row">
                        <div class="col-7">
                            <div class="row">
                                <div class="col-12">
                                <div class="item-title">ORDER: </div>
                                    <ais-highlight
                                        :hit="item"
                                        attribute="order_name"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="item-title">AMOUNT: </div>
                                    <div class="amount">
                                        $<ais-highlight
                                            :hit="item"
                                            attribute="amount"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <ais-highlight
                                        :hit="item"
                                        attribute="draft_order.id"
                                    />
                                    <div class="item-title">BILLED TO: </div>
                                    <ais-highlight
                                        :hit="item"
                                        attribute="email"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="row">
                                <div class="item-title">PAYMENT: </div>
                                <ais-highlight
                                    :hit="item"
                                    attribute="gateway_status"
                                />
                            </div>
                            <div class="row">
                                <button v-on:click="orderButton(item.order_id)" class="btn btn-sm btn-outline-secondary mt-2">View Order</button>
                            </div>
                            <div class="row"></div>
                        </div>
                    </div>
                    
                    
                
                
                    

                <!-- <button v-on:click="customerButton(item.id)" class="btn btn-outline-primary">View Customer</button> -->
                <!-- <button v-if="item.shopify_customer_id" v-on:click="shopifyButton(item.shopify_customer_id)" class="customer-shopify btn btn-outline-primary">Shopify Link</button> -->
            </div>
        </template>
    </ais-hits>
    <ais-pagination></ais-pagination>
  </ais-instant-search>
</template>

<script>
import algoliasearch from 'algoliasearch/lite';

export default {
    data() {
        return {
            searchClient: algoliasearch(
                process.env.MIX_ALGOLIA_APP_ID,
                process.env.MIX_ALGOLIA_SEARCH
            ),
        };
    },
    props: ['shop', 'apiKey', 'shopOrigin'],
    methods: {
        customerButton(Id) {
            console.log('customer pressed ' + Id);
            redirect.dispatch(Redirect.Action.APP, '/customer/' + Id + '/edit');
        },
        orderButton(Id) {
            console.log('button pressed ' + Id);
            redirect.dispatch(Redirect.Action.ADMIN_PATH, '/orders/' + Id);
        }
    }
};
</script>