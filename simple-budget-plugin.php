<?php
/*
Plugin Name: Simple Budget Plugin
Description: Plugin para orçamentos personalizados em sites com Elementor e CPTs.
Version: 1.0.0
Author: Guilherme Silva
Text Domain: simple-budget-plugin-sbp
Domain Path: /languages
Requires at least: 6.0
Requires PHP: 7.4
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// ===========================
// Constantes úteis
// ===========================
define( 'SBP_VERSION', '1.0.0' );
define( 'SBP_FILE', __FILE__ );
define( 'SBP_PATH', plugin_dir_path( __FILE__ ) );
define( 'SBP_URL',  plugin_dir_url( __FILE__ ) );

// ===========================
// Load Text Domain (Traduções)
// ===========================
function sbp_load_textdomain() {
    load_plugin_textdomain(
        'simple-budget-plugin-sbp',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
}
add_action( 'plugins_loaded', 'sbp_load_textdomain' );

// ======================================
// Enfileirar scripts JS e CSS (Frontend)
// ======================================
function sbp_enqueue_scripts() {
    wp_enqueue_script(
        'sbp-script',
        SBP_URL . 'assets/js/sbp-script.js',
        array( 'jquery' ),
        SBP_VERSION,
        true
    );

    wp_enqueue_style(
        'sbp-styles',
        SBP_URL . 'assets/css/sbp-styles.css',
        array(),
        SBP_VERSION
    );

    $whatsapp_number = get_option( 'sbp_whatsapp_number', '' );

    // Dados AJAX
    wp_localize_script( 'sbp-script', 'sbp_ajax', array(
        'ajax_url'        => admin_url( 'admin-ajax.php' ),
        'nonce'           => wp_create_nonce( 'sbp_nonce' ),
        'whatsapp_number' => $whatsapp_number,
    ) );

    // Traduções para uso no JavaScript (mantido como sbp_i18n_js para compatibilidade com seu JS)
    wp_localize_script( 'sbp-script', 'sbp_i18n_js', array(
        'popup_not_found'        => __( 'Erro: Popup do carrinho não foi encontrado na página.', 'simple-budget-plugin-sbp' ),
        'popup_closed'           => __( 'Aviso: Popup já está fechado.', 'simple-budget-plugin-sbp' ),
        'cart_empty'             => __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' ),
        'load_error'             => __( 'Erro ao carregar o carrinho.', 'simple-budget-plugin-sbp' ),
        'product_added'          => __( 'Produto adicionado ao carrinho!', 'simple-budget-plugin-sbp' ),
        'product_exists'         => __( 'Produto já está no carrinho!', 'simple-budget-plugin-sbp' ),
        'product_add_error'      => __( 'Erro ao adicionar o produto ao carrinho.', 'simple-budget-plugin-sbp' ),
        'whatsapp_error'         => __( 'Erro ao gerar a mensagem do WhatsApp.', 'simple-budget-plugin-sbp' ),
        'whatsapp_number_error'  => __( 'Erro: Número de WhatsApp não configurado.', 'simple-budget-plugin-sbp' ),
        'whatsapp_intro'         => __( "Olá! Eu quero fazer um orçamento dos seguintes produtos:\n", 'simple-budget-plugin-sbp' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'sbp_enqueue_scripts' );

// =======================================
// AJAX - Obter produtos no carrinho
// =======================================
function sbp_get_cart_products() {
    if ( ! check_ajax_referer( 'sbp_nonce', 'nonce', false ) ) {
        wp_send_json_error( __( 'Erro de segurança: nonce inválido ou expirado.', 'simple-budget-plugin-sbp' ), 403 );
    }

    $product_ids = isset( $_POST['product_ids'] ) ? array_map( 'intval', (array) $_POST['product_ids'] ) : array();
    if ( empty( $product_ids ) ) {
        wp_send_json_error( __( 'Carrinho vazio ou dados inválidos.', 'simple-budget-plugin-sbp' ) );
    }

    // Se a opção estiver vazia -> aceita QUALQUER post type
    $post_types_opt = get_option( 'sbp_product_post_types', array() );
    $post_type_arg  = ( ! empty( $post_types_opt ) && is_array( $post_types_opt ) ) ? $post_types_opt : 'any';

    $query = new WP_Query( array(
        'post_type'      => $post_type_arg,
        'post__in'       => $product_ids,
        'orderby'        => 'post__in', // mantém a ordem do carrinho
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    if ( ! $query->have_posts() ) {
        wp_send_json_error( __( 'Nenhum item encontrado.', 'simple-budget-plugin-sbp' ) );
    }

    ob_start();
    while ( $query->have_posts() ) {
        $query->the_post();
        $product_id = get_the_ID(); ?>
        <div class="sbp-cart-item">
            <?php if ( has_post_thumbnail() ) { ?>
                <img src="<?php echo esc_url( get_the_post_thumbnail_url() ); ?>" alt="<?php the_title_attribute(); ?>">
            <?php } ?>
            <h4><?php the_title(); ?></h4>
            <button class="sbp-remove-from-cart" data-product-id="<?php echo esc_attr( $product_id ); ?>">
                <?php _e( 'Remover', 'simple-budget-plugin-sbp' ); ?>
            </button>
        </div>
    <?php }
    wp_reset_postdata();

    wp_send_json_success( ob_get_clean() );
}
add_action( 'wp_ajax_sbp_get_cart_products', 'sbp_get_cart_products' );
add_action( 'wp_ajax_nopriv_sbp_get_cart_products', 'sbp_get_cart_products' );

// =======================================
// Integração com Elementor (usa CSS ID)
// =======================================
function sbp_add_product_id_to_button( $widget ) {
    if ( 'button' !== $widget->get_name() ) {
        return;
    }

    $current_id = get_the_ID();
    // No Elementor, o campo é "CSS ID", acessível por 'button_css_id'
    $button_id  = $widget->get_settings( 'button_css_id' );

    if ( $current_id && $button_id === 'add-to-cart-button' ) {
        $widget->add_render_attribute( '_wrapper', 'data-product-id', $current_id );
    }
}
add_action( 'elementor/frontend/widget/before_render', 'sbp_add_product_id_to_button' );

// =======================================
// Shortcodes
// =======================================
require_once __DIR__ . '/includes/class-sbp-shortcodes.php';

// =======================================
// Popup do Carrinho (Frontend)
// =======================================
function sbp_add_popup_html() { ?>
    <div id="sbp-custom-popup" class="sbp-custom-popup">
        <div class="sbp-custom-popup-content">
            <span class="sbp-close-popup">&times;</span>
            <h2><?php _e( 'Seu Carrinho', 'simple-budget-plugin-sbp' ); ?></h2>
            <div id="sbp-cart-items"></div>
            <button id="enviar-orcamento-whatsapp" style="display:none;">
                <?php _e( 'Enviar Orçamento via WhatsApp', 'simple-budget-plugin-sbp' ); ?>
            </button>
        </div>
    </div>
<?php }
add_action( 'wp_footer', 'sbp_add_popup_html' );

// =======================================
// Admin Menu
// =======================================
function sbp_add_admin_menu() {
    add_menu_page(
        __( 'Configurações sbp', 'simple-budget-plugin-sbp' ),
        __( 'Configurações sbp', 'simple-budget-plugin-sbp' ),
        'manage_options',
        'sbp-settings',
        'sbp_settings_page',
        'dashicons-cart'
    );
}
add_action( 'admin_menu', 'sbp_add_admin_menu' );

// =======================================
// Página de Configurações
// =======================================
function sbp_settings_page() { ?>
    <div class="wrap">
        <h1><?php _e( 'Configurações do Simple Budget Plugin', 'simple-budget-plugin-sbp' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'sbp_settings_group' );
            do_settings_sections( 'sbp-settings' );
            submit_button();
            ?>
        </form>
    </div>
<?php }

// =======================================
// Registrar Configurações
// =======================================
function sbp_register_settings() {
    register_setting( 'sbp_settings_group', 'sbp_whatsapp_number', array(
        'type'              => 'string',
        'sanitize_callback' => 'sbp_sanitize_phone_number',
        'default'           => '',
    ) );

    // Vazio = aceita qualquer post type
    register_setting( 'sbp_settings_group', 'sbp_product_post_types', array(
        'type'              => 'array',
        'sanitize_callback' => 'sbp_sanitize_post_types',
        'default'           => array(),
    ) );

    add_settings_section(
        'sbp_main_section',
        __( 'Configurações do WhatsApp', 'simple-budget-plugin-sbp' ),
        null,
        'sbp-settings'
    );

    add_settings_field(
        'sbp_whatsapp_number',
        __( 'Número de WhatsApp', 'simple-budget-plugin-sbp' ),
        'sbp_render_whatsapp_number_field',
        'sbp-settings',
        'sbp_main_section'
    );

    add_settings_field(
        'sbp_product_post_types',
        __( 'Post Types dos Produtos', 'simple-budget-plugin-sbp' ),
        'sbp_render_product_post_types_field',
        'sbp-settings',
        'sbp_main_section'
    );
}
add_action( 'admin_init', 'sbp_register_settings' );

// =======================================
// Sanitização
// =======================================
function sbp_sanitize_phone_number( $phone ) {
    $phone = preg_replace( '/[^0-9]/', '', (string) $phone );
    if ( strlen( $phone ) < 10 ) {
        return '';
    }
    return $phone;
}

function sbp_sanitize_post_types( $post_types ) {
    $post_types = array_map( 'sanitize_key', (array) $post_types );
    $post_types = array_filter( $post_types ); // remove vazios
    return array_values( $post_types );
}

// =======================================
// Campos do Admin
// =======================================
function sbp_render_whatsapp_number_field() {
    $whatsapp_number = get_option( 'sbp_whatsapp_number', '' );
    ?>
    <input type="text" name="sbp_whatsapp_number" value="<?php echo esc_attr( $whatsapp_number ); ?>" class="regular-text" />
    <p class="description">
        <?php _e( 'Insira o número de WhatsApp (apenas dígitos, com DDD). Ex.: 11999998888', 'simple-budget-plugin-sbp' ); ?>
    </p>
    <?php
}

function sbp_render_product_post_types_field() {
    $post_types = get_option( 'sbp_product_post_types', array() );
    if ( ! is_array( $post_types ) ) {
        $post_types = array();
    }
    // Renderiza pelo menos um campo vazio
    if ( empty( $post_types ) ) {
        $post_types = array( '' );
    }
    ?>
    <div id="post-types-wrapper">
        <?php foreach ( $post_types as $post_type ) { ?>
            <div class="post-type-entry">
                <input type="text" name="sbp_product_post_types[]" value="<?php echo esc_attr( $post_type ); ?>" class="regular-text" />
            </div>
        <?php } ?>
    </div>
    <p class="description">
        <?php _e( 'Deixe em branco para aceitar qualquer post type. Para restringir, informe os slugs (um por linha).', 'simple-budget-plugin-sbp' ); ?>
    </p>
    <?php
}

// =======================================
// AJAX - Adicionar / Remover / Enviar WhatsApp
// =======================================
function sbp_add_to_cart() {
    if ( ! check_ajax_referer( 'sbp_nonce', 'nonce', false ) ) {
        wp_send_json_error( __( 'Falha de segurança.', 'simple-budget-plugin-sbp' ), 403 );
    }

    $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
    if ( ! $product_id ) {
        wp_send_json_error( __( 'Produto inválido.', 'simple-budget-plugin-sbp' ) );
    }

    $allowed_types = get_option( 'sbp_product_post_types', array() );
    $type_ok = empty( $allowed_types ) ? true : in_array( get_post_type( $product_id ), $allowed_types, true );

    if ( ! $type_ok ) {
        wp_send_json_error( __( 'Produto inválido.', 'simple-budget-plugin-sbp' ) );
    }

    wp_send_json_success( __( 'Produto adicionado ao carrinho.', 'simple-budget-plugin-sbp' ) );
}
add_action( 'wp_ajax_sbp_add_to_cart', 'sbp_add_to_cart' );
add_action( 'wp_ajax_nopriv_sbp_add_to_cart', 'sbp_add_to_cart' );

function sbp_remove_from_cart() {
    if ( ! check_ajax_referer( 'sbp_nonce', 'nonce', false ) ) {
        wp_send_json_error( __( 'Falha de segurança.', 'simple-budget-plugin-sbp' ), 403 );
    }

    $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
    if ( ! $product_id ) {
        wp_send_json_error( __( 'Produto inválido.', 'simple-budget-plugin-sbp' ) );
    }

    $allowed_types = get_option( 'sbp_product_post_types', array() );
    $type_ok = empty( $allowed_types ) ? true : in_array( get_post_type( $product_id ), $allowed_types, true );

    if ( ! $type_ok ) {
        wp_send_json_error( __( 'Produto inválido.', 'simple-budget-plugin-sbp' ) );
    }

    wp_send_json_success( __( 'Produto removido do carrinho.', 'simple-budget-plugin-sbp' ) );
}
add_action( 'wp_ajax_sbp_remove_from_cart', 'sbp_remove_from_cart' );
add_action( 'wp_ajax_nopriv_sbp_remove_from_cart', 'sbp_remove_from_cart' );

function sbp_send_whatsapp_message() {
    if ( ! check_ajax_referer( 'sbp_nonce', 'nonce', false ) ) {
        wp_send_json_error( __( 'Falha de segurança.', 'simple-budget-plugin-sbp' ), 403 );
    }

    $cart = isset( $_POST['cart'] ) ? (array) $_POST['cart'] : array();

    if ( empty( $cart ) ) {
        wp_send_json_error( __( 'Carrinho está vazio.', 'simple-budget-plugin-sbp' ) );
    }

    $message = __( "Olá! Eu gostaria de fazer um orçamento dos seguintes produtos:\n", 'simple-budget-plugin-sbp' );
    foreach ( $cart as $index => $product_id ) {
        $product_title = get_the_title( intval( $product_id ) );
        if ( $product_title ) {
            $message .= ( $index + 1 ) . ' - ' . $product_title . "\n";
        }
    }

    $whatsapp_number = get_option( 'sbp_whatsapp_number', '' );
    if ( empty( $whatsapp_number ) ) {
        wp_send_json_error( __( 'Número de WhatsApp não configurado.', 'simple-budget-plugin-sbp' ) );
    }

    $whatsapp_number = '+55' . preg_replace( '/[^0-9]/', '', $whatsapp_number );
    $url = 'https://wa.me/' . rawurlencode( $whatsapp_number ) . '?text=' . rawurlencode( $message );

    wp_send_json_success( array( 'url' => $url ) );
}
add_action( 'wp_ajax_sbp_send_whatsapp_message', 'sbp_send_whatsapp_message' );
add_action( 'wp_ajax_nopriv_sbp_send_whatsapp_message', 'sbp_send_whatsapp_message' );

// =======================================
// AJAX - Obter títulos dos produtos
// =======================================
function sbp_get_product_titles() {
    if ( ! check_ajax_referer( 'sbp_nonce', 'nonce', false ) ) {
        wp_send_json_error( __( 'Erro de segurança: nonce inválido ou expirado.', 'simple-budget-plugin-sbp' ), 403 );
    }

    $product_ids = isset( $_POST['product_ids'] ) ? array_map( 'intval', (array) $_POST['product_ids'] ) : array();
    if ( empty( $product_ids ) ) {
        wp_send_json_error( __( 'IDs de produtos não enviados.', 'simple-budget-plugin-sbp' ) );
    }

    // Se a opção estiver vazia -> aceita QUALQUER post type
    $post_types_opt = get_option( 'sbp_product_post_types', array() );
    $allowed_types  = ( ! empty( $post_types_opt ) && is_array( $post_types_opt ) ) ? $post_types_opt : null;

    $product_titles = array();
    foreach ( $product_ids as $product_id ) {
        $product = get_post( $product_id );
        if ( $product && get_post_status( $product ) === 'publish' ) {
            if ( is_array( $allowed_types ) && ! in_array( $product->post_type, $allowed_types, true ) ) {
                continue;
            }
            $product_titles[] = $product->post_title;
        }
    }

    if ( empty( $product_titles ) ) {
        wp_send_json_error( __( 'Nenhum título encontrado.', 'simple-budget-plugin-sbp' ) );
    }

    wp_send_json_success( $product_titles );
}
add_action( 'wp_ajax_sbp_get_product_titles', 'sbp_get_product_titles' );
add_action( 'wp_ajax_nopriv_sbp_get_product_titles', 'sbp_get_product_titles' );
