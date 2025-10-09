<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Segurança

class sbp_Shortcodes {

    public function __construct() {
        // Registra o shortcode no WordPress
        add_shortcode( 'sbp_cart', array( $this, 'sbp_cart_shortcode' ) );
    }

    // Função do shortcode que exibe o carrinho
    public function sbp_cart_shortcode() {
        // Localizar strings de tradução para o JavaScript
        $i18n = array(
            'load_error' => __( 'Erro ao carregar o carrinho.', 'simple-budget-plugin-sbp' ),
            'empty_cart' => __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' ),
        );

        // Passar traduções para o JS (garantindo que estejam disponíveis)
        wp_localize_script( 'sbp-script', 'sbp_i18n', $i18n );

        ob_start();
        ?>
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
                                    quantities: quantitiesObj
                                },
                                success: function(response) {
                                    if (response.success) {
                                        $('#sbp-cart-content').html(response.data);
                                    } else {
                                        $('#sbp-cart-content').html('<p>' + sbp_i18n.load_error + '</p>');
                                    }
                                },
                                error: function() {
                                    $('#sbp-cart-content').html('<p>' + sbp_i18n.load_error + '</p>');
                                }
                            });
                        } else {
                            $('#sbp-cart-content').html('<p>' + sbp_i18n.empty_cart + '</p>');
                        }
                    } else {
                        $('#sbp-cart-content').html('<p>' + sbp_i18n.empty_cart + '</p>');
                    }
                }

                displayCartProducts();

                // Evento para alterar a quantidade
                $(document).on('change', '.sbp-quantity', function() {
                    var productId = $(this).data('product-id').toString();
                    var quantity = parseInt($(this).val());
                    var cartQuantities = localStorage.getItem('sbp_cart_quantities') ? JSON.parse(localStorage.getItem('sbp_cart_quantities')) : {};

                    if (quantity > 0) {
                        cartQuantities[productId] = quantity;
                    } else {
                        delete cartQuantities[productId];
                    }

                    localStorage.setItem('sbp_cart_quantities', JSON.stringify(cartQuantities));
                });

                // Evento para remover produto do carrinho
                $(document).on('click', '.sbp-remove-from-cart', function() {
                    var productId = $(this).data('product-id').toString();
                    var cart = localStorage.getItem('sbp_cart') ? JSON.parse(localStorage.getItem('sbp_cart')) : [];
                    var cartQuantities = localStorage.getItem('sbp_cart_quantities') ? JSON.parse(localStorage.getItem('sbp_cart_quantities')) : {};

                    var index = cart.indexOf(productId);
                    if (index > -1) {
                        cart.splice(index, 1);
                        localStorage.setItem('sbp_cart', JSON.stringify(cart));

                        delete cartQuantities[productId];
                        localStorage.setItem('sbp_cart_quantities', JSON.stringify(cartQuantities));

                        displayCartProducts();
                    }
                });

            });
        </script>
        <?php
        return ob_get_clean();
    }
}

new sbp_Shortcodes();
