<?php
/**
 * Endpoints AJAX.
 */

namespace SBP\Ajax;

use SBP\Support\CartRenderer;
use SBP\Templates\CartTemplateManager;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {

    public function init_hooks() {
        add_action( 'wp_ajax_sbp_get_cart_products', [ $this, 'get_cart_products' ] );
        add_action( 'wp_ajax_nopriv_sbp_get_cart_products', [ $this, 'get_cart_products' ] );

        add_action( 'wp_ajax_sbp_render_cart_template', [ $this, 'render_cart_template' ] );
        add_action( 'wp_ajax_nopriv_sbp_render_cart_template', [ $this, 'render_cart_template' ] );

        add_action( 'wp_ajax_sbp_send_whatsapp_message', [ $this, 'send_whatsapp_message' ] );
        add_action( 'wp_ajax_nopriv_sbp_send_whatsapp_message', [ $this, 'send_whatsapp_message' ] );
    }

    private function verify_nonce() {
        if ( ! check_ajax_referer( 'sbp_nonce', 'nonce', false ) ) {
            wp_send_json_error( __( 'Erro de segurança: nonce inválido ou expirado.', 'simple-budget-plugin-sbp' ), 403 );
        }
    }

    public function get_cart_products() {
        $this->verify_nonce();

        $product_ids = isset( $_POST['product_ids'] ) ? CartRenderer::normalize_product_ids( wp_unslash( $_POST['product_ids'] ) ) : [];
        if ( empty( $product_ids ) ) {
            wp_send_json_error( __( 'Carrinho vazio ou dados inválidos.', 'simple-budget-plugin-sbp' ) );
        }

        $display    = isset( $_POST['display'] ) ? (array) wp_unslash( $_POST['display'] ) : [];
        $quantities = isset( $_POST['quantities'] ) ? CartRenderer::normalize_quantities( wp_unslash( $_POST['quantities'] ) ) : [];
        $html       = CartRenderer::render_items( $product_ids, $display, $quantities );

        if ( '' === $html ) {
            wp_send_json_error( __( 'Nenhum item encontrado.', 'simple-budget-plugin-sbp' ) );
        }

        wp_send_json_success( $html );
    }

    public function render_cart_template() {
        $this->verify_nonce();

        $template_id = isset( $_POST['template_id'] ) ? absint( $_POST['template_id'] ) : 0;

        if ( ! $template_id ) {
            wp_send_json_error( __( 'Template não informado.', 'simple-budget-plugin-sbp' ) );
        }

        $html = CartTemplateManager::render_template( $template_id );

        if ( '' === trim( $html ) ) {
            wp_send_json_error( __( 'Template não encontrado ou inválido.', 'simple-budget-plugin-sbp' ) );
        }

        wp_send_json_success([
            'template_id' => $template_id,
            'html'        => $html,
        ]);
    }

    public function send_whatsapp_message() {
        $this->verify_nonce();

        $cart = isset( $_POST['cart'] ) ? CartRenderer::normalize_product_ids( wp_unslash( $_POST['cart'] ) ) : [];
        if ( empty( $cart ) ) {
            wp_send_json_error( __( 'Carrinho está vazio.', 'simple-budget-plugin-sbp' ) );
        }

        $quantities = isset( $_POST['quantities'] ) ? CartRenderer::normalize_quantities( wp_unslash( $_POST['quantities'] ) ) : [];
        $message = __( "Olá! Eu gostaria de fazer um orçamento dos seguintes produtos:\n", 'simple-budget-plugin-sbp' );
        $allowed_types = get_option( 'sbp_product_post_types', [] );
        $line_number = 1;

        foreach ( $cart as $id ) {
            $post = get_post( $id );

            if ( ! $post || 'publish' !== get_post_status( $post ) ) {
                continue;
            }

            if ( is_array( $allowed_types ) && ! empty( $allowed_types ) && ! in_array( $post->post_type, $allowed_types, true ) ) {
                continue;
            }

            $title = get_the_title( $id );
            if ( $title ) {
                $quantity = max( 1, absint( $quantities[ (string) $id ] ?? 1 ) );
                $message .= $line_number . ' - ' . $title . ' - ' . sprintf(
                    /* translators: %d: item quantity. */
                    __( 'Qtd: %d', 'simple-budget-plugin-sbp' ),
                    $quantity
                ) . "\n";
                $line_number++;
            }
        }

        if ( 1 === $line_number ) {
            wp_send_json_error( __( 'Nenhum título encontrado.', 'simple-budget-plugin-sbp' ) );
        }

        $whatsapp_number = get_option( 'sbp_whatsapp_number', '' );
        if ( empty( $whatsapp_number ) ) {
            wp_send_json_error( __( 'Número de WhatsApp não configurado.', 'simple-budget-plugin-sbp' ) );
        }

        $whatsapp_number = '+55' . preg_replace( '/[^0-9]/', '', $whatsapp_number );
        $url = 'https://wa.me/' . rawurlencode( $whatsapp_number ) . '?text=' . rawurlencode( $message );

        wp_send_json_success( [ 'url' => $url ] );
    }
}
