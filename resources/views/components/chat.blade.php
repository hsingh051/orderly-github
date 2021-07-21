<script>
    function initFreshChat() {
        window.fcWidget.init({
        token: "9785e8cf-ada9-4378-93d3-97aad48e4615",
        host: "https://wchat.freshchat.com"
        });
    }
    function initialize(i,t){var e;i.getElementById(t)?initFreshChat():((e=i.createElement("script")).id=t,e.async=!0,e.src="https://wchat.freshchat.com/js/widget.js",e.onload=initFreshChat,i.head.appendChild(e))}function initiateCall(){initialize(document,"freshchat-js-sdk")}window.addEventListener?window.addEventListener("load",initiateCall,!1):window.attachEvent("load",initiateCall,!1);
</script>
