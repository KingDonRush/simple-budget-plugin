<?php
/**
 * Front-end (assets, popup, Elementor).
 */

namespace SBP\Public_;

if ( ! defined( 'ABSPATH' ) ) exit;

class Public_ {

    public function init_hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_footer', [ $this, 'render_popup' ] );
        add_action( 'elementor/frontend/widget/before_render', [ $this, 'elementor_add_product_id' ] );
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'sbp-styles',
            \SBP_URL . 'assets/css/sbp-styles.css',
            [],
            \SBP_VERSION
        );

        wp_enqueue_script(
            'sbp-script',
            \SBP_URL . 'assets/js/sbp-script.js',
            [ 'jquery' ],
            \SBP_VERSION,
            true
        );

        $whatsapp_number = get_option( 'sbp_whatsapp_number', '' );

        wp_localize_script( 'sbp-script', 'sbp_ajax', [
            'ajax_url'        => admin_url( 'admin-ajax.php' ),
            'nonce'           => wp_create_nonce( 'sbp_nonce' ),
            'whatsapp_number' => $whatsapp_number,
        ]);

        wp_localize_script( 'sbp-script', 'sbp_i18n_js', [
            'popup_not_found'       => __( 'Erro: Popup do carrinho não foi encontrado na página.', 'simple-budget-plugin-sbp' ),
            'popup_closed'          => __( 'Aviso: Popup já está fechado.', 'simple-budget-plugin-sbp' ),
            'cart_empty'            => __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' ),
            'load_error'            => __( 'Erro ao carregar o carrinho.', 'simple-budget-plugin-sbp' ),
            'product_added'         => __( 'Produto adicionado ao carrinho!', 'simple-budget-plugin-sbp' ),
            'product_exists'        => __( 'Produto já está no carrinho!', 'simple-budget-plugin-sbp' ),
            'product_add_error'     => __( 'Erro ao adicionar o produto ao carrinho.', 'simple-budget-plugin-sbp' ),
            'whatsapp_error'        => __( 'Erro ao gerar a mensagem do WhatsApp.', 'simple-budget-plugin-sbp' ),
            'whatsapp_number_error' => __( 'Erro: Número de WhatsApp não configurado.', 'simple-budget-plugin-sbp' ),
            'whatsapp_intro'        => __( "Olá! Eu quero fazer um orçamento dos seguintes produtos:\n", 'simple-budget-plugin-sbp' ),
            'remove_text'           => __( 'Remover', 'simple-budget-plugin-sbp' ),
            'template_loading'      => __( 'Carregando orçamento...', 'simple-budget-plugin-sbp' ),
            'template_error'        => __( 'Erro ao carregar o template do carrinho.', 'simple-budget-plugin-sbp' ),
            'close_cart'            => __( 'Fechar carrinho', 'simple-budget-plugin-sbp' ),
        ]);
    }

    public function render_popup() { ?>
        <div id="sbp-custom-popup" class="sbp-custom-popup" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?php esc_attr_e( 'Seu Carrinho', 'simple-budget-plugin-sbp' ); ?>">
            <div class="sbp-custom-popup-content" role="document" tabindex="-1">
                <button type="button" class="sbp-close-popup" aria-label="<?php esc_attr_e( 'Fechar carrinho', 'simple-budget-plugin-sbp' ); ?>">&times;</button>

                <div id="sbp-custom-popup-template" class="sbp-custom-popup-template" hidden></div>

                <div id="sbp-custom-popup-fallback" class="sbp-custom-popup-fallback">
                    <h2 id="sbp-custom-popup-title"><?php esc_html_e( 'Seu Carrinho', 'simple-budget-plugin-sbp' ); ?></h2>
                    <div id="sbp-cart-items"></div>
                    <button id="enviar-orcamento-whatsapp" style="display:none;">
                        <?php esc_html_e( 'Enviar Orçamento via WhatsApp', 'simple-budget-plugin-sbp' ); ?>
                    </button>
                </div>
            </div>
        </div>
    <?php }

    public function elementor_add_product_id( $widget ) {
        if ( 'button' !== $widget->get_name() ) return;

        $current_id = get_the_ID();
        $button_id  = $widget->get_settings( 'button_css_id' );

        if ( $current_id && $button_id === 'add-to-cart-button' ) {
            $widget->add_render_attribute( '_wrapper', 'data-product-id', $current_id );
        }
    }
}
