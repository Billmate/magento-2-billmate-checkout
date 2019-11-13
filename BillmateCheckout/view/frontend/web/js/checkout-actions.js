define([
    "jquery",
], function ($) {
    $.billmateCheckoutActions = function () {
        return {
            init: function (options) {
                bmcathis = this;
                var settings = $.extend({
                    request_url: '' /*setup in block */
                }, options);
                this.config = settings;
                this.listenIncreaseQty();
                this.listenDecreaseQty();
                this.listenDeleteItem();
                this.listenSwitchShipping();
                this.applyCouponeCode();
                this.addComment();
                return this;
            },
            addComment: function () {
                $(document).on('keyup', '#order-comments', function(){
                    event.preventDefault();
                    var param = {
                        comment: document.getElementById('order-comments').value
                    };
                    console.log(param);
                    $.ajax({
                        url: comment_url,
                        data: param,
                        type: "POST",
                        dataType: 'json'
                    }).done(function (data) {
                    }).fail(function (jqXHR, status, error) {
                    });
                });
            },
            listenIncreaseQty: function () {
                $(document).on('click', '.inc', function () {
                    bmcathis.switchActionButtons(true);
                    var param = {
                        field1: "ajax",
                        field2: "inc",
                        field3: this.id
                    };
                    bmcathis.sendRequest(param);
                });
            },
            listenDecreaseQty: function () {
                $(document).on('click', '.sub', function () {
                    bmcathis.switchActionButtons(true);
                    var param = {
                        field1: "ajax",
                        field2: "sub",
                        field3: this.id
                    };
                    bmcathis.sendRequest(param);
                });
            },
            listenDeleteItem: function () {
                $(document).on('click', '.del', function () {
                    var param = {
                        field1: "ajax",
                        field2: "del",
                        field3: this.id
                    };
                    bmcathis.sendRequest(param);
                });
            },
            listenSwitchShipping: function () {
                $(document).on('click', '.radio', function () {
                    var param = {
                        field1: "ajax",
                        field2: "radio",
                        field3: this.id
                    };
                    bmcathis.sendRequest(param);
                });
            },
            applyCouponeCode: function () {
                $(document).on('click', '.codeButton', function () {
                    var param = {
                        field1: "ajax",
                        field2: "submit",
                        field3: document.getElementById("code").value
                    };
                    bmcathis.sendRequest(param);
                });
            },
            sendRequest: function (requestData) {
                BillmateIframe.lock();
                $.ajax({
                    showLoader: true,
                    url: bmcathis.config.request_url,
                    data: requestData,
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    if (data.error) {
                        location.href = data.redirect_url;
                        return;
                    }
                    BillmateIframe.update();
                    bmcathis.updateHtmlContent(data);
                    bmcathis.switchActionButtons(false);
                    bmcathis.updateCustomerData();
                    BillmateIframe.unlock();
                });
            },
            updateHtmlContent: function (data) {
                document.getElementById('billmate-cart').innerHTML = data.cart;
            },
            updateCustomerData: function () {
                require([
                    'Magento_Customer/js/customer-data'
                ], function (customerData) {
                    var sections = ['cart'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                });
            },
            switchActionButtons: function (state) {
                var exists1 = document.getElementById("button-step1");
                var exists2 = document.getElementById("button-step2");
                var incexists = document.getElementById("bm-inc-btn");
                var subexists = document.getElementById("bm-sub-btn");
                var delexists = document.getElementById("bm-del-btn");
                if (exists1 != null) {
                    exists1.disabled = state;
                }
                if (exists2 != null) {
                    exists2.disabled = state;
                }
                if (incexists != null) {
                    incexists.disabled = state;
                }
                if (subexists != null) {
                    subexists.disabled = state;
                }
                if (delexists != null) {
                    delexists.disabled = state;
                }
            }

        }
    };

    return new $.billmateCheckoutActions();

});