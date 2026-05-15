<?php
/**
 * Endpoints AJAX.
 */

namespace SBP\Ajax;

use SBP\Support\CartRenderer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {

    public function init_hooks() {
        add_action( 'wp_ajax_sbp_get_cart_products', [ $this, 'get_cart_products' ] );
        add_action( 'wp_ajax_nopriv_sbp_get_cart_products', [ $this, 'get_cart_products' ] );

        add_action( 'wp_ajax_sbp_get_product_titles', [ $this, 'get_product_titles' ] );
        add_action( 'wp_ajax_nopriv_sbp_get_product_titles', [ $this, 'get_product_titles' ] );

        add_action( 'wp_ajax_sbp_add_to_cart', [ $this, 'add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_sbp_add_to_cart', [ $this, 'add_to_cart' ] );

        add_action( 'wp_ajax_sbp_remove_from_cart', [ $this, 'remove_from_cart' ] );
        add_action( 'wp_ajax_nopriv_sbp_remove_from_cart', [ $this, 'remove_from_cart' ] );

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

        $display = isset( $_POST['display'] ) ? (array) wp_unslash( $_POST['display'] ) : [];
        $html    = CartRenderer::render_items( $product_ids, $display );

        if ( '' === $html ) {
            wp_send_json_error( __( 'Nenhum item encontrado.', 'simple-budget-plugin-sbp' ) );
        }

        wp_send_json_success( $html );
    }

    public function get_product_titles() {
        $this->verify_nonce();

        $product_ids = isset( $_POST['product_ids'] ) ? CartRenderer::normalize_product_ids( wp_unslash( $_POST['product_ids'] ) ) : [];
        if ( empty( $product_ids ) ) {
            wp_send_json_error( __( 'IDs de produtos não enviados.', 'simple-budget-plugin-sbp' ) );
        }

        $allowed_types = get_option( 'sbp_product_post_types', [] );
        $allowed_types = ! empty( $allowed_types ) ? $allowed_types : null;

        $titles = [];
        foreach ( $product_ids as $id ) {
            $post = get_post( $id );
            if ( $post && get_post_status( $post ) === 'publish' ) {
                if ( is_array( $allowed_types ) && ! in_array( $post->post_type, $allowed_types, true ) ) {
                    continue;
                }
                $titles[] = $post->post_title;
            }
        }

        if ( empty( $titles ) ) {
            wp_send_json_error( __( 'Nenhum título encontrado.', 'simple-budget-plugin-sbp' ) );
        }

        wp_send_json_success( $titles );
    }

    public function add_to_cart() {
        $this->verify_nonce();

        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        if ( ! $product_id ) {
            wp_send_json_error( __( 'Produto inválido.', 'simple-budget-plugin-sbp' ) );
        }

        $allowed_types = get_option( 'sbp_product_post_types', [] );
        $is_allowed    = empty( $allowed_types ) || in_array( get_post_type( $product_id ), $allowed_types, true );

        if ( ! $is_allowed ) {
            wp_send_json_error( __( 'Produto inválido.', 'simple-budget-plugin-sbp' ) );
        }

        wp_send_json_success( __( 'Produto adicionado ao carrinho.', 'simple-budget-plugin-sbp' ) );
    }

    public function remove_from_cart() {
        $this->verify_nonce();

        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        if ( ! $product_id ) {
            wp_send_json_error( __( 'Produto inválido.', 'simple-budget-plugin-sbp' ) );
        }

        $allowed_types = get_option( 'sbp_product_post_types', [] );
        $is_allowed    = empty( $allowed_types ) || in_array( get_post_type( $product_id ), $allowed_types, true );

        if ( ! $is_allowed ) {
            wp_send_json_error( __( 'Produto inválido.', 'simple-budget-plugin-sbp' ) );
        }

        wp_send_json_success( __( 'Produto removido do carrinho.', 'simple-budget-plugin-sbp' ) );
    }

    public function send_whatsapp_message() {
        $this->verify_nonce();

        $cart = isset( $_POST['cart'] ) ? CartRenderer::normalize_product_ids( wp_unslash( $_POST['cart'] ) ) : [];
        if ( empty( $cart ) ) {
            wp_send_json_error( __( 'Carrinho está vazio.', 'simple-budget-plugin-sbp' ) );
        }

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
                $message .= $line_number . ' - ' . $title . "\n";
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
