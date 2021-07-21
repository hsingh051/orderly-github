<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <title>Shopify App — Login</title>

        <style>
            html, body { padding: 0; margin: 0; }
            body {
                font-family: "ProximaNovaLight", "Helvetica Neue", Helvetica, Arial, sans-serif;
                background-color: #f2f7fa;
            }
            h1 {
                font-weight: 300;
                font-size: 40px;
                margin-bottom: 10px;
            }
            .subhead {
                font-size: 17px;
                line-height: 32px;
                font-weight: 300;
                color: #969A9C;
            }
            .subhead.error {
                color: #f4645f;
            }
            .shop-input {
                width: 300px;
                height: 50px;
                padding: 10px;
                border: 1px solid #cccccc;
                color: #575757;
                background-color: #ffffff;
                box-sizing: border-box;
                border-radius: 4px 0 0 4px;
                font-size: 18px;
                float: left;
            }
            .shop-input:focus {
                outline: none;
            }
            .shop-submit {
                color: #ffffff;
                background-color: #4CB948;
                width: 100px;
                height: 50px;
                padding: 10px 20px 10px 20px;
                box-sizing: border-box;
                border: none;
                font-size: 18px;
                cursor: pointer;
                border-radius: 0 4px 4px 0;
                float: right;
                transition: all 0.3s;
            }
            .shop-submit:hover {
                background-color: #7ABF4A;
                transition: all 0.3s;
            }
            form {
                display: block;
            }
            .container {
                text-align: center;
                margin-top: 100px;
                padding: 20px;
            }
            .container__form {
                width: 400px;
                margin: auto;
            }
            .shopify-plus-logo {
                width: 100px;
                transform: translateY(30%);
                /* margin-top: -50%; */
            }
            .orderly-logo {
                width: 200px;
            }
            .toggle {
                align-items: center;
                display: flex;
                justify-content: center;
                transform: scale(0.3);
            }

            .label-toggle {
                background: white;
                border-radius: 12px;
                box-shadow: 0px 50px 20px 0 rgba(0,0,0,0.1);
                display: flex;
                height: 50px;
                padding: 8px;
                position: relative;
                transform: scale(2);
                transition: transform 300ms ease, box-shadow 300ms ease;
                width: 116px;
            }

            .input-toggle {
                display: none;
            }

            .label-toggle:after {
                animation: move-left 400ms;
                background: #E2E2E2 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='27' height='27' viewBox='0 0 24 24'%3E%3Cpath stroke='#E2E2E2' fill='#E2E2E2' stroke-linecap='round' d='M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z'/%3E%3C/svg%3E") no-repeat center;
                border-radius: 12px;
                content: '';
                height: 50px;
                left: 8px;
                outline: none;
                position: absolute;
                transition: background 100ms linear;
                width: 50px;
            }

            .label-toggle:active {
                box-shadow: 0px 10px 20px 0 rgba(0,0,0,0.2);
                transform: scale(1.8);
            }

            .input-toggle:checked + .label-toggle:after {
                animation: move-right 400ms;
                background: #C49F47 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='27' height='27' viewBox='0 0 24 24'%3E%3Cpath stroke='white' fill='white' stroke-linecap='round' d='M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z'/%3E%3C/svg%3E") no-repeat center;
                left: 72px;
            }

            /* Animation */

            @keyframes move-right {
            0% {
                left: 8px;
            }
            75% {
                left: 78px;
            }
            100% {
                left: 72px;
            }
            }

            @keyframes move-left {
            0% {
                left: 72px;
            }
            75% {
                left: 2px;
            }
            100% {
                left: 8px;
            }
            }
        </style>
    </head>
    <body>
        <main class="container" role="main">
            <header>
                <img class="orderly-logo" src="{{asset('/img/orderly-logo.png')}}"
                
                @if (session()->has('error'))
                    <p class="subheadError">{{ session('error') }}</p>
                @endif
            </header>

            <div class="container__form">
                <form class="form-horizontal" method="POST" action="{{ route('authenticate') }}">
                    {{ csrf_field() }}
                    <p class="subhead">
                        <label for="shop">Toggle for <img class="shopify-plus-logo" src="{{asset('/img/shopify-plus.png')}}" alt="Shopify Plus"> Plans Only</label>
                    </p>
                     <div class="toggle">
                        <input class="input-toggle" id="plan" name="plan" value="enterprise" type="checkbox">
                        <label class="label-toggle" for="plan"></label>
                    </div>
                    <p class="subhead">
                    <label for="shop">Enter your Shopify domain to login.</label>
                </p>
                    <div class="form-group">
                        <input class="shop-input" type="text" name="shop" id="shop" placeholder="example.myshopify.com" value="{{ isset($shopDomain) ? $shopDomain : '' }}" autofocus="true">
                        <button class="shop-submit" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>
