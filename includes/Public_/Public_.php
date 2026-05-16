<?php
/**
 * Front-end (assets, popup, Elementor).
 */

namespace SBP\Public_;

use SBP\Support\CartRenderer;
use SBP\Support\RuntimeAssets;

if ( ! defined( 'ABSPATH' ) ) exit;

class Public_ {

    public function init_hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_footer', [ $this, 'render_popup' ] );
        add_filter( 'body_class', [ $this, 'add_cart_template_editor_preview_body_class' ] );
    }

    public function enqueue_assets() {
        wp_register_style(
            'sbp-styles',
            \SBP_URL . 'assets/css/sbp-styles.css',
            [],
            \SBP_VERSION
        );

        wp_register_script(
            'sbp-script',
            \SBP_URL . 'assets/js/sbp-script.js',
            [ 'jquery' ],
            \SBP_VERSION,
            true
        );

        wp_localize_script( 'sbp-script', 'sbp_ajax', [
            'ajax_url'          => admin_url( 'admin-ajax.php' ),
            'nonce'             => wp_create_nonce( 'sbp_nonce' ),
            'max_cart_items'    => CartRenderer::MAX_CART_ITEMS,
            'max_item_quantity' => CartRenderer::MAX_ITEM_QUANTITY,
        ]);

        wp_localize_script( 'sbp-script', 'sbp_i18n_js', [
            'popup_not_found'       => __( 'Erro: Popup do carrinho não foi encontrado na página.', 'simple-budget-plugin-sbp' ),
            'cart_empty'            => __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' ),
            'load_error'            => __( 'Erro ao carregar o carrinho.', 'simple-budget-plugin-sbp' ),
            'whatsapp_error'        => __( 'Erro ao gerar a mensagem do WhatsApp.', 'simple-budget-plugin-sbp' ),
            'remove_text'           => __( 'Remover', 'simple-budget-plugin-sbp' ),
            'quantity_label'        => __( 'Quantidade', 'simple-budget-plugin-sbp' ),
            'template_loading'      => __( 'Carregando orçamento...', 'simple-budget-plugin-sbp' ),
            'template_setup_title'  => __( 'Create a cart template', 'simple-budget-plugin-sbp' ),
            'template_setup_text'   => __( 'This Budget Button needs a Simple Budget template. Create one in Simple Budget > Templates, edit it with Elementor, then select it in the button settings.', 'simple-budget-plugin-sbp' ),
        ]);
    }

    public function render_popup() {
        if ( ! RuntimeAssets::is_popup_required() ) {
            return;
        }

        $templates_url = admin_url( 'admin.php?page=sbp-templates' );
        ?>
        <div id="sbp-custom-popup" class="sbp-custom-popup" role="dialog" aria-modal="true" aria-hidden="true" data-sbp-shell="modal" data-sbp-animation="fade_scale" data-sbp-close-overlay="yes" data-sbp-close-escape="yes" data-sbp-show-close="yes" aria-label="<?php esc_attr_e( 'Seu Carrinho', 'simple-budget-plugin-sbp' ); ?>">
            <div class="sbp-custom-popup-content" role="document" tabindex="-1">
                <button type="button" class="sbp-close-popup" aria-label="<?php esc_attr_e( 'Fechar carrinho', 'simple-budget-plugin-sbp' ); ?>">&times;</button>

                <div id="sbp-custom-popup-template" class="sbp-custom-popup-template" hidden></div>

                <div id="sbp-custom-popup-fallback" class="sbp-custom-popup-fallback">
                    <div class="sbp-template-setup" role="status">
                        <h2 id="sbp-custom-popup-title"><?php esc_html_e( 'Create a cart template', 'simple-budget-plugin-sbp' ); ?></h2>
                        <p><?php esc_html_e( 'This Budget Button needs a Simple Budget template. Create one in Simple Budget > Templates, edit it with Elementor, then select it in the button settings.', 'simple-budget-plugin-sbp' ); ?></p>
                        <?php if ( current_user_can( 'manage_options' ) ) : ?>
                            <a class="button button-primary sbp-template-setup__button" href="<?php echo esc_url( $templates_url ); ?>">
                                <?php esc_html_e( 'Open Simple Budget Templates', 'simple-budget-plugin-sbp' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php }

    public function add_cart_template_editor_preview_body_class( $classes ) {
        if ( ! is_singular( 'elementor_library' ) ) {
            return $classes;
        }

        $post_id = get_queried_object_id();

        if ( $post_id && 'cart_modal' === get_post_meta( $post_id, '_sbp_template_role', true ) ) {
            $classes[] = 'sbp-cart-template-editor-preview';
        }

        return $classes;
    }
}
