<?php
/**
 * Shortcodes do plugin.
 */

namespace SBP\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcodes {

    public function init_hooks() {
        add_shortcode( 'sbp_cart', [ $this, 'render_cart_shortcode' ] );
    }

    public function render_cart_shortcode() {
        $i18n = [
            'load_error' => __( 'Erro ao carregar o carrinho.', 'simple-budget-plugin-sbp' ),
            'empty_cart' => __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' ),
        ];
        wp_localize_script( 'sbp-script', 'sbp_i18n', $i18n );

        ob_start(); ?>
        <div id="sbp-cart-content"></div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function displayCartProducts() {
                    var cart = localStorage.getItem('sbp_cart');
                    var quantities = localStorage.getItem('sbp_cart_quantities');

                    if (cart) {
                        var productIds = JSON.parse(cart);
                        var quantitiesObj = quantities ? JSON.parse(quantities) : {};

                        if (productIds.length > 0) {
                            $.ajax({
                                url: sbp_ajax.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'sbp_get_cart_products',
                                    nonce: sbp_ajax.nonce,
                                    product_ids: productIds,
                                    quantities: quantitiesObj,
                                    display: {
                                        show_quantity: 'yes',
                                        quantity_label: '<?php echo esc_js( __( 'Quantidade', 'simple-budget-plugin-sbp' ) ); ?>'
                                    }
                                },
                                success: function(response) {
                                    if (response.success) {
                                        $('#sbp-cart-content').html(response.data);
                                    } else {
                                        $('#sbp-cart-content').html('<p>' + (window.sbp_i18n?.load_error || 'Erro ao carregar o carrinho.') + '</p>');
                                    }
                                },
                                error: function() {
                                    $('#sbp-cart-content').html('<p>' + (window.sbp_i18n?.load_error || 'Erro ao carregar o carrinho.') + '</p>');
                                }
                            });
                        } else {
                            $('#sbp-cart-content').html('<p>' + (window.sbp_i18n?.empty_cart || 'Seu carrinho está vazio.') + '</p>');
                        }
                    } else {
                        $('#sbp-cart-content').html('<p>' + (window.sbp_i18n?.empty_cart || 'Seu carrinho está vazio.') + '</p>');
                    }
                }

                displayCartProducts();

                $(document).on('change', '.sbp-quantity', function() {
                    var productId = $(this).data('product-id').toString();
                    var quantity = parseInt($(this).val());
                    var cartQuantities = localStorage.getItem('sbp_cart_quantities')
                        ? JSON.parse(localStorage.getItem('sbp_cart_quantities'))
                        : {};

                    if (quantity > 0) {
                        cartQuantities[productId] = quantity;
                    } else {
                        delete cartQuantities[productId];
                    }

                    localStorage.setItem('sbp_cart_quantities', JSON.stringify(cartQuantities));
                    $(document).trigger('sbp:cart-updated');
                });

                $(document).on('click', '.sbp-remove-from-cart', function() {
                    var productId = $(this).data('product-id').toString();
                    var cart = localStorage.getItem('sbp_cart') ? JSON.parse(localStorage.getItem('sbp_cart')) : [];
                    var cartQuantities = localStorage.getItem('sbp_cart_quantities')
                        ? JSON.parse(localStorage.getItem('sbp_cart_quantities'))
                        : {};

                    var index = cart.indexOf(productId);
                    if (index > -1) {
                        cart.splice(index, 1);
                        localStorage.setItem('sbp_cart', JSON.stringify(cart));
                        delete cartQuantities[productId];
                        localStorage.setItem('sbp_cart_quantities', JSON.stringify(cartQuantities));
                        $(document).trigger('sbp:cart-updated');
                        displayCartProducts();
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
