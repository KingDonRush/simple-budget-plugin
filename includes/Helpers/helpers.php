<?php
/**
 * Funções auxiliares globais do Simple Budget Plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ========================================================
 * 🔹 Sanitização e Validação
 * ========================================================
 */

/**
 * Sanitiza um número de telefone (apenas dígitos).
 *
 * @param string $phone
 * @return string Número limpo ou vazio se inválido.
 */
function sbp_sanitize_phone( $phone ) {
    $phone = preg_replace( '/[^0-9]/', '', (string) $phone );
    return ( strlen( $phone ) >= 10 ) ? $phone : '';
}

/**
 * Sanitiza uma lista de post types.
 *
 * @param array|string $post_types
 * @return array
 */
function sbp_sanitize_post_types( $post_types ) {
    $post_types = (array) $post_types;
    $post_types = array_map( 'sanitize_key', $post_types );
    $post_types = array_filter( $post_types );
    return array_values( $post_types );
}

/**
 * Verifica se o nonce do plugin é válido (para AJAX).
 *
 * @param string $nonce
 * @return bool
 */
function sbp_verify_nonce( $nonce ) {
    return check_ajax_referer( 'sbp_nonce', 'nonce', false );
}

/**
 * ========================================================
 * 🔹 Opções e Configurações
 * ========================================================
 */

/**
 * Retorna o número de WhatsApp configurado.
 *
 * @return string
 */
function sbp_get_whatsapp_number() {
    $number = get_option( 'sbp_whatsapp_number', '' );
    $number = sbp_sanitize_phone( $number );
    return $number;
}

/**
 * Retorna os post types aceitos pelo plugin.
 *
 * @return array
 */
function sbp_get_allowed_post_types() {
    $types = get_option( 'sbp_product_post_types', [] );
    return is_array( $types ) ? $types : [];
}

/**
 * Verifica se um post ID é válido e permitido.
 *
 * @param int $product_id
 * @return bool
 */
function sbp_is_valid_product( $product_id ) {
    $product = get_post( $product_id );
    if ( ! $product || 'publish' !== get_post_status( $product ) ) {
        return false;
    }

    $allowed_types = sbp_get_allowed_post_types();
    if ( ! empty( $allowed_types ) && ! in_array( $product->post_type, $allowed_types, true ) ) {
        return false;
    }

    return true;
}

/**
 * ========================================================
 * 🔹 Utilidades Gerais
 * ========================================================
 */

/**
 * Retorna uma mensagem de depuração segura (para log futuro).
 *
 * @param string $message
 * @return void
 */
function sbp_debug_log( $message ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
        error_log( '[SBP] ' . $message );
    }
}

/**
 * Escapa e retorna uma string segura para saída HTML.
 *
 * @param string $text
 * @return string
 */
function sbp_esc( $text ) {
    return esc_html( $text ?? '' );
}

/**
 * Escapa e imprime uma string segura.
 *
 * @param string $text
 * @return void
 */
function sbp_echo( $text ) {
    echo sbp_esc( $text );
}
