"use strict";

$(function () {

    const handelTapPaymentProcess = () => {
        let request = $.ajax({
            url: `https://dalstore.me/tap/handelPayment`,
            type: "post",
            dataType: "JSON",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content")
            }
        });


        request.done((data) => {
            let paymentConfig = {
                containerID: "tapElement",
                gateway: {
                    publicKey: data.publicKey,
                    notifications: "standard",
                    backgroundImg: {
                        url: "imgURL",
                        opacity: "0.5",
                    },
                    labels: {
                        cardNumber: "Card Number",
                        expirationDate: "MM/YY",
                        cvv: "CVV",
                        cardHolder: "Name on Card",
                        actionButton: "Pay",
                    },
                    style: {
                        base: {
                            color: "#535353",
                            lineHeight: "18px",
                            fontFamily: "sans-serif",
                            fontSmoothing: "antialiased",
                            fontSize: "16px",
                            "::placeholder": {
                                color: "rgba(0, 0, 0, 0.26)",
                                fontSize: "15px",
                            },
                        },
                        invalid: {
                            color: "red",
                            iconColor: "#fa755a ",
                        },
                    },
                },
                customer: {
                    first_name: data.email,
                    email: data.email,
                    phone: {
                        country_code: data.phoneCode,
                        number: data.phoneNumber
                    }
                },
                order: {
                    amount: data.amount,
                    currency: data.currency,
                },
                transaction: {
                    mode: "charge",
                    charge: {
                        threeDSecure: true,
                        receipt: {
                            email: false,
                            sms: true,
                        },
                        reference: {
                            transaction: new Date().getTime().toString(),
                            order: data.orderId
                        },
                        redirect: data.successRedirect,
                        post: data.webhook,
                    },
                },
            };

            loadTapPay(paymentConfig);
        });



    }

    handelTapPaymentProcess();

    /*================================================================================= */
    function loadTapPay(tapConfig) {

        const link = document.createElement('link');
        link.href = 'https://goSellJSLib.b-cdn.net/v2.0.0/css/gosell.css';
        link.rel = 'stylesheet';

        document.getElementsByTagName('head')[0].appendChild(link);
        /***************************************************************/
        const tapLibScript = document.createElement('script');
        tapLibScript.setAttribute("src", "https://goSellJSLib.b-cdn.net/v2.0.0/js/gosell.js");
        document.getElementById("tapBox").appendChild(tapLibScript);
        /***************************************************************/
        tapLibScript.onload = () => {
            goSell.config(tapConfig);
            goSell.openLightBox();
        }

    }
    /*================================================================================= */

    /*================================================================================= */


});
