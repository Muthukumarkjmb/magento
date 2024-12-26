define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Payment/js/model/credit-card-validation/credit-card-data',
    'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Ui/js/lib/validation/validator'
], function ($, Component, creditCardData, cardNumberValidator, messageList, $t, validator) {
    'use strict';
    
    var creditCustomerName = '';
    var creditCardType = '';
    var creditCardExpYear = '';
    var creditCardExpMonth = '';
    var creditCardNumber = '';
    var creditCardVerificationNumber = '';

    return Component.extend({
        defaults: {
            template: 'Burstonline_Epipayment/payment/epipayment',
            creditCustomerName: '',
            creditCardType: '',
            creditCardExpYear: '',
            creditCardExpMonth: '',
            creditCardNumber: '',
            creditCardVerificationNumber: '',
            selectedCardType: null,
            cardTypeImageUrl: '/media/burstonline/cards/default.png'
        },
        initObservable: function () {
            this._super()
                .observe([
                    'creditCustomerName',
                    'creditCardType',
                    'creditCardExpYear',
                    'creditCardExpMonth',
                    'creditCardNumber',
                    'creditCardVerificationNumber',
                    'selectedCardType',
                    'cardTypeImageUrl'
                ]);
            return this;
        },

        initialize: function() {
            var self = this;
            this._super();

            this.creditCardNumber.subscribe(this.updateCardType.bind(this));

            //Set expiration year to credit card data object
            this.creditCardExpYear.subscribe(function(value) {
                creditCardData.expirationYear = value;
            });

            //Set expiration month to credit card data object
            this.creditCardExpMonth.subscribe(function(value) {
                creditCardData.expirationMonth = value;
            });

            //Set cvv code to credit card data object
            this.creditCardVerificationNumber.subscribe(function(value) {
                creditCardData.cvvCode = value;
            });
        },
        
        updateCardType: function(value) {
            var cardType = this.getCardType(value);
            this.creditCardType(cardType);
            this.updateCardTypeImage(cardType);
        },

        getCardType: function(number) {
            var re = {
                'visa': /^4[0-9]{12}(?:[0-9]{3})?$/,
                'mastercard': /^5[1-5][0-9]{14}$/,
                'amex': /^3[47][0-9]{13}$/,
                'discover': /^6(?:011|5[0-9]{2})[0-9]{12}$/,
                'diners': /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
                'jcb': /^(?:2131|1800|35\d{3})\d{11}$/
            };

            for (var key in re) {
                if (re[key].test(number)) {
                    console.log("Card Type :: ", key);
                    return key;
                }
            }
            
            return 'default';
        },
        
        updateCardTypeImage: function(cardType) {
            var imageUrl = '';
            switch (cardType) {
                case 'visa':
                    imageUrl = '/media/burstonline/cards/visa.png';
                    break;
                case 'mastercard':
                    imageUrl = '/media/burstonline/cards/mastercard.png';
                    break;
                case 'amex':
                    imageUrl = '/media/burstonline/cards/amex.png';
                    break;
                case 'discover':
                    imageUrl = '/media/burstonline/cards/discover.png';
                    break;
                case 'diners':
                    imageUrl = '/media/burstonline/cards/diners.png';
                    break;
                case 'jcb':
                    imageUrl = '/media/burstonline/cards/jcb.png';
                    break;
                case 'default':
                    imageUrl = '/media/burstonline/cards/default.png';
                default:
                    imageUrl = '/media/burstonline/cards/default.png';
            }
            this.cardTypeImageUrl(imageUrl);  // Update the observable with the new image URL
        },

        isActive: function () {
            return true;
        },

        getCcAvailableTypes: function() {
            return window.checkoutConfig.payment.epipayment.availableTypes['epipayment'];
        },

        getCcMonths: function() {
            return window.checkoutConfig.payment.epipayment.months['epipayment'];
        },

        getCcYears: function() {
            return window.checkoutConfig.payment.epipayment.years['epipayment'];
        },

        hasVerification: function() {
            return window.checkoutConfig.payment.epipayment.hasVerification['epipayment'];
        },

        getCcAvailableTypesValues: function() {
            return _.map(this.getCcAvailableTypes(), function(value, key) {
                return {
                    'value': key,
                    'type': value
                }
            });
        },
        getCcMonthsValues: function() {
            return _.map(this.getCcMonths(), function(value, key) {
                return {
                    'value': key,
                    'month': value
                }
            });
        },
        getCcYearsValues: function() {
            return _.map(this.getCcYears(), function(value, key) {
                return {
                    'value': key,
                    'year': value
                }
            });
        },
        validate: function () {
            var form = '#pay';
            $(form).validation();
            var isValid = true;

            // CC customer name
            var customer_name = creditCustomerName = $(".customer_name").val();
            if(customer_name == '')
            {
                $(".customer_name").css("border","1px solid red");
                isValid = false;
            }

            // CC card validation
            var cc_number = creditCardNumber = $(".cc_number").val();
            if(cc_number == ''  ||  (/[^0-9-]+/.test(cc_number)) || cc_number.length != 16)
            {
                $(".cc_number").css("border","1px solid red");
                isValid = false;
            }

            // CC Expiry Check
            var cc_exp_year = creditCardExpYear = $(".cc_exp_year").val();
            var cc_exp_month = creditCardExpMonth = $(".cc_exp_month").val();
            var current_month = new Date().getMonth() + 1; // card valid for entire expiry month
            var current_year = new Date().getFullYear();
            if (cc_exp_year < current_year || (cc_exp_year == current_year && cc_exp_month < current_month)) {
                $(".cc_exp_year").css("border","1px solid red");
                $(".cc_exp_month").css("border","1px solid red");
                isValid = false;
            }

            // CC cvv validation
            var cc_cid = creditCardVerificationNumber = $(".cc_cid").val();
            if(cc_cid == ''  ||  (/[^0-9-]+/.test(cc_cid)) || cc_cid.length != 3)
            {
                $(".cc_cid").css("border","1px solid red");
                isValid = false;
            }
            creditCardType = $(".cc_type").val();
            //var isValid = $(form).validation('isValid');
            console.log('Form validation status:', isValid); //return false;
            return isValid;
        },
        placeOrder: function (data, event) { 
            if (this.validate() && this.isPlaceOrderActionAllowed()) { console.log("Coming");
            var thisSuper = this._super();
            console.log("Form validated. Proceeding with data:");
            console.log("Customer Name:", creditCustomerName);
            console.log("Card Type:", creditCardType);
            console.log("Card Number:",creditCardNumber);
            console.log("Expiration Month:", creditCardExpMonth);
            console.log("Expiration Year:", creditCardExpYear);
            console.log("CVV:", creditCardVerificationNumber);
                this.additionalData = {
                    cc_customer_name: creditCustomerName,
                    cc_type: this.creditCardType(),
                    cc_number: creditCardNumber,
                    cc_exp_month: creditCardExpMonth,
                    cc_exp_year: creditCardExpYear,
                    cc_cid: creditCardVerificationNumber
                };
        
                // Send data to session via AJAX
                $.ajax({
                    url: '/epipayment/index/savepaymentdata',
                    type: 'POST',
                    data: this.additionalData,
                    success: function(response) {
                        if (response.success) {
                            // Continue with placing order
                            return thisSuper;
                        } else {
                            messageList.addErrorMessage({ message: $t('Unable to save payment data.') });
                        }
                    }.bind(this),
                    error: function() {
                        messageList.addErrorMessage({ message: $t('Unable to save payment data.') });
                    }
                });
        
                //return false; // Prevent default action until AJAX is completed
            } else {
                messageList.addErrorMessage({ message: $t('Please fill all required fields correctly.') });
            }
        }
        
    });
});
