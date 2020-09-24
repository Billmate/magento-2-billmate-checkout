//åäö
define([
	'jquery'
], function ($, billmateajax) {
    window.method = null;
	window.address_selected = null;
	window.latestScroll = null;
	BillmateIframe = new function(){
		var self = this;
		var childWindow = null;
	    this.updateAddress = function (data) {
			$.ajax({
				url : UPDATE_ADDRESS_URL,
				data: data,
				type: 'POST',
				success: function(response){
					document.getElementById('billmate-cart').innerHTML = response.cart;
					console.log("checkout");
					console.log(response);
					if (typeof response.orderMessage !== "undefined") {
                        document.getElementById('order-comments').value = response.orderMessage
                    }
				}
			});
		};
		this.unlock = function() {
			setTimeout(
				function() {
                    self.checkoutPostMessage('unlock')
				}, 1000);
		};
        this.lock = function() {
            this.checkoutPostMessage('lock');
        };
        this.update = function() {
            this.checkoutPostMessage('update');
        };
        this.checkoutPostMessage = function(message) {
            var checkout = document.getElementById('checkout');
            if (checkout != null) {
                var win = checkout.contentWindow;
                win.postMessage(message,'*');
            }
        }
		this.createOrder = function(data) {
			if (data && data.status == "Step2Loaded") {
				$.ajax({
					url : CREATE_ORDER_URL,
					data: data,
					type: 'POST',
					success: function(response){
						location.href=response;
					}
				});
			}
		};
		this.initListeners = function () {
			window.addEventListener("message",self.handleEvent);
		};
		this.handleEvent = function(event){
				try {
					var json = JSON.parse(event.data);
				} catch (e) {
					return;
				}
				self.childWindow = json.source;
				switch (json.event) {
                    case 'go_to':
                        location.href = json.data;
                        break;
				    case 'address_selected':
						self.updateAddress(json.data);
						if(window.method == null || window.method == json.data.method) {
							$('#checkoutdiv').removeClass('loading');
						}
						break;
					case 'checkout_success':
							self.createOrder(json.data);
						break;
					case 'content_height':
						$("iframe#checkout").css('height',json.data);
						break;
					case 'content_scroll_position':
						window.latestScroll = $(document).find( "#checkout" ).offset().top + json.data;
						break;
					case 'checkout_loaded':
						$('#checkoutdiv').removeClass('loading');
						break;
					default:
						break;

				}
		};
	};
	var b_iframe = BillmateIframe;
	b_iframe.initListeners();
	b_iframe.update();
});