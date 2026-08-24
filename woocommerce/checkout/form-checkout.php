<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

</div></div>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <div class="studio94-checkout-layout">
        
        <div class="studio94-checkout-main">
            <?php if ( $checkout->get_checkout_fields() ) : ?>
                <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
                
                <div class="col2-set" id="customer_details">
                    <div class="col-1">
                        <?php do_action( 'woocommerce_checkout_billing' ); ?>
                    </div>
                    <div class="col-2">
                        <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                    </div>
                </div>

                <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
            <?php endif; ?>
            
            <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
        </div>

        <div class="studio94-checkout-sidebar">
            <h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
            
            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

            <div id="order_review" class="woocommerce-checkout-review-order">
                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
            </div>

            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
        </div>
        
    </div>

</form>

<div class="page-body-container" style="display: none;"><div class="page-content">

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<script>
jQuery(function($) {
    
    if ($('#ship-to-different-address-checkbox').length) {
        $('#ship-to-different-address-checkbox').prop('checked', false).trigger('change');
    }

    function safeAppend($el, $parent) {
        if ($el.length && $el.parent().get(0) !== $parent.get(0)) {
            $el.appendTo($parent);
        }
    }

    function enforceColumns() {
        let isMobile = window.innerWidth <= 768;
        let $billWrap = $('.woocommerce-billing-fields__field-wrapper');
        let $shipWrap = $('.woocommerce-shipping-fields__field-wrapper');

        if ($billWrap.length && $billWrap.find('.s94-col-left').length === 0) {
            $billWrap.prepend('<div class="s94-col-left"></div><div class="s94-col-right"></div>');
        }
        if ($shipWrap.length && $shipWrap.find('.s94-col-left').length === 0) {
            $shipWrap.prepend('<div class="s94-col-left"></div><div class="s94-col-right"></div>');
        }

        let $bLeft = $billWrap.find('.s94-col-left');
        let $bRight = $billWrap.find('.s94-col-right');
        
        safeAppend($('#billing_first_name_field'), $bLeft);
        safeAppend($('#billing_last_name_field'), $bLeft);
        safeAppend($('#billing_email_field'), $bLeft);
        safeAppend($('#billing_phone_field'), $bLeft);

        safeAppend($('#billing_country_field'), $bRight);
        safeAppend($('#billing_address_1_field'), $bRight);
        safeAppend($('#billing_address_2_field'), $bRight);
        safeAppend($('#billing_city_field'), $bRight);
        safeAppend($('#billing_postcode_field'), $bRight);

        let $sLeft = $shipWrap.find('.s94-col-left');
        let $sRight = $shipWrap.find('.s94-col-right');
        
        safeAppend($('#shipping_first_name_field'), $sLeft);
        safeAppend($('#shipping_last_name_field'), $sLeft);
        safeAppend($('#shipping_phone_field'), $sLeft);

        if (!isMobile) {
            safeAppend($('#order_comments_field'), $sLeft);
        }

        safeAppend($('#shipping_country_field'), $sRight);
        safeAppend($('#shipping_address_1_field'), $sRight);
        safeAppend($('#shipping_address_2_field'), $sRight);
        safeAppend($('#shipping_city_field'), $sRight);
        safeAppend($('#shipping_postcode_field'), $sRight);

        if (isMobile) {
            safeAppend($('#order_comments_field'), $sRight);
        }
    }

    function styliseCheckoutUI() {
        enforceColumns();

        const textReplacements = {
            'Billing details': 'Billing details',
            'Ship to a different address?': 'Ship to a different address',
            'First name': 'First name',
            'Last name': 'Last name',
            'Email address': 'Email address',
            'Street address': 'Street address',
            'Town / City': 'Town / City',
            'Order notes': 'Order notes',
            '(optional)': '(Optional)'
        };
        
        $('.woocommerce-checkout h3, .woocommerce-checkout label, .woocommerce-checkout span.optional, .woocommerce-checkout abbr').each(function() {
            $(this).contents().filter(function() {
                return this.nodeType === 3; 
            }).each(function() {
                let text = this.nodeValue;
                let updated = false;
                for (let [oldStr, newStr] of Object.entries(textReplacements)) {
                    if (text.includes(oldStr)) {
                        text = text.replace(oldStr, newStr);
                        updated = true;
                    }
                }
                if (updated) this.nodeValue = text;
            });
        });

        let $tcText = $('.woocommerce-terms-and-conditions-checkbox-text');
        let $reqStar = $('.woocommerce-terms-and-conditions-wrapper abbr.required');
        if ($tcText.length && $reqStar.length && !$tcText.find('abbr.required').length) {
            $reqStar.detach().appendTo($tcText);
            $tcText.css({ 'display': 'inline' });
        }

        if (!$('.custom-sidebar-coupon').length) {
            $('.woocommerce-checkout-review-order-table').after(`
                <div class="custom-sidebar-coupon">
                    <input type="text" id="s94_fake_coupon" placeholder="Coupon code" />
                    <button type="button" id="s94_apply_coupon" class="btn">Apply</button>
                </div>
            `);
        }
        
        $('.woocommerce-checkout-review-order-table .cart-discount').each(function() {
            let $th = $(this).find('th');
            let $td = $(this).find('td');
            
            if ($th.find('.coupon-pill').length) return;

            let $removeBtn = $td.find('.woocommerce-remove-coupon').detach();
            
            let code = $th.text().replace(/Coupon:?/gi, '').replace('×', '').trim();
            $th.html('Coupon <span class="coupon-pill">' + code + '</span>');
            
            if ($removeBtn.length) {
                $removeBtn.text('×').appendTo($th);
                $th.css({'display': 'flex', 'align-items': 'center'});
                let amountHtml = $td.find('.amount').prop('outerHTML');
                $td.html('-' + amountHtml);
            }
        });

        $('.wc_payment_method label').each(function() {
            let html = $(this).html();
            html = html.replace(/Debit &amp; Credit Cards/g, 'Cards').replace(/Card /g, 'Cards ');
            $(this).html(html);
        });

        if(!$('#ship-to-different-address label .s94-toggle').length) {
            $('#ship-to-different-address label').append('<div class="s94-toggle"></div>');
        }
        if(!$('#terms').siblings('.s94-toggle').length) {
            $('.woocommerce-terms-and-conditions-wrapper .checkbox').append('<div class="s94-toggle"></div>');
        }
    }

    styliseCheckoutUI();

    setInterval(enforceColumns, 200);

    $(window).on('resize', function() {
        enforceColumns();
    });

    $(document.body).on('updated_checkout', function() {
        styliseCheckoutUI();
    });

    $(document).on('click', '#s94_apply_coupon', function(e) {
        e.preventDefault();
        let code = $('#s94_fake_coupon').val();
        if (code.trim() !== '') {
            $('#coupon_code').val(code);
            $('button[name="apply_coupon"]').trigger('click');
        }
    });

});
</script>