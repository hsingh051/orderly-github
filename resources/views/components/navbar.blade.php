<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">
  <img src="{{ asset('/img/orderly-logo-mark.png') }}" alt="orderly logo mark" width="30" height="30">
  </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNavDropdown">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a id="orders" class="nav-link" href="#">Orders <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a id="customers" class="nav-link" href="#">Customers</a>
      </li>
      <li class="nav-item">
        <a id="support" class="nav-link" target="_blank" href="https://orderlypay.freshdesk.com/support/home">Support</a>
      </li>
    </ul>
  </div>
</nav>

@section('scripts')
    @parent
    
    <script type="text/javascript">
        $(document).ready(function () {

          console.log('hello');

            $('#customers').on('click', function () {
                console.log('customer');
                redirect.dispatch(Redirect.Action.APP, '/customer/');
            });

            $('#orders').on('click', function () {
                console.log('order ');
                redirect.dispatch(Redirect.Action.APP, '/order/');
            });

            $('#support').on('click', function () {
                console.log('support');
                redirect.dispatch(Redirect.Action.APP, '/support/');
            });

            $(".navbar-nav .nav-link").each(function() {
                console.log($(this).attr('href'));
                if ((window.location.pathname.indexOf($(this).attr('href'))) > -1) {
                    $(this).parent().addClass('active');
                }
            });
        });
    </script>
@endsection