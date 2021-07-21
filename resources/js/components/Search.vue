<template>
  <ais-instant-search
    :search-client="searchClient"
    index-name="customers"
  >
    <!-- Other search components go here -->
    <ais-configure :filters="'shop_id = ' + shop.id" />
    <ais-search-box
        placeholder="Find a customer by: Name, Email, Type, Rep, or Shopify ID"
        submit-title="Find"
        reset-title="Clear"
        :show-loading-indicator="true"
    />
    <ais-stats />
    <ais-hits>
        <template
            slot="item"
            slot-scope="{ item }"
        >
            <div v-bind:data-id="item.employee_email" class="customer-edit">
                <h4 class="">
                    <span>Name: </span>    
                    <ais-highlight
                        :hit="item"
                        attribute="first_name"
                    />
                    <ais-highlight
                        :hit="item"
                        attribute="last_name"
                    />
                </h4>
                <h5 class="text-muted">
                    <span>Email: </span>
                    <ais-highlight
                        :hit="item"
                        attribute="email"
                    />
                </h5>
                <p class="card-text">
                    <span v-if="item.employee_name != 'NULL'">Rep Name: </span>
                    <ais-highlight
                        :hit="item"
                        attribute="employee_name"
                        v-if="item.employee_name != 'NULL'"
                    />
                    <span v-if="item.employee_email != 'NULL' & item.employee_name != 'NULL'">| Rep Email: </span>
                    <ais-highlight
                        :hit="item"
                        attribute="employee_email"
                        v-if="item.employee_email != 'NULL'"
                    />
                </p>
                <button v-on:click="customerButton(item.id)" class="btn btn-sm btn-outline-primary">View Customer</button>
                <button v-if="item.shopify_customer_id" v-on:click="shopifyButton(item.shopify_customer_id)" class="customer-shopify btn btn-sm btn-outline-primary">Shopify Link</button>
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
        shopifyButton(Id) {
            console.log('button pressed ' + Id);
            redirect.dispatch(Redirect.Action.ADMIN_PATH, '/customers/' + Id);
        }
    }
};
</script>