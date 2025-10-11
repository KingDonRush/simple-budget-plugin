<?php
/**
 * Painel administrativo (settings page).
 */

namespace SBP\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {

    public function init_hooks() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_menu() {
        add_menu_page(
            __( 'Configurações SBP', 'simple-budget-plugin-sbp' ),
            __( 'Configurações SBP', 'simple-budget-plugin-sbp' ),
            'manage_options',
            'sbp-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-cart'
        );
    }

    public function render_settings_page() { ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Configurações do Simple Budget Plugin', 'simple-budget-plugin-sbp' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'sbp_settings_group' );
                do_settings_sections( 'sbp-settings' );
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    public function register_settings() {
        register_setting( 'sbp_settings_group', 'sbp_whatsapp_number', [
            'type'              => 'string',
            'sanitize_callback' => [ $this, 'sanitize_phone_number' ],
            'default'           => '',
        ] );

        register_setting( 'sbp_settings_group', 'sbp_product_post_types', [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_post_types' ],
            'default'           => [],
        ] );

        add_settings_section(
            'sbp_main_section',
            __( 'Configurações do WhatsApp', 'simple-budget-plugin-sbp' ),
            null,
            'sbp-settings'
        );

        add_settings_field(
            'sbp_whatsapp_number',
            __( 'Número de WhatsApp', 'simple-budget-plugin-sbp' ),
            [ $this, 'render_whatsapp_field' ],
            'sbp-settings',
            'sbp_main_section'
        );

        add_settings_field(
            'sbp_product_post_types',
            __( 'Tipos de Conteúdo', 'simple-budget-plugin-sbp' ),
            [ $this, 'render_post_types_field' ],
            'sbp-settings',
            'sbp_main_section'
        );
    }

    public function render_whatsapp_field() {
        $number = get_option( 'sbp_whatsapp_number', '' ); ?>
        <input type="text" name="sbp_whatsapp_number" value="<?php echo esc_attr( $number ); ?>" class="regular-text" />
        <p class="description">
            <?php esc_html_e( 'Insira o número de WhatsApp (apenas dígitos, com DDD). Ex.: 11999998888', 'simple-budget-plugin-sbp' ); ?>
        </p>
    <?php }

    public function render_post_types_field() {
        $post_types = get_option( 'sbp_product_post_types', [] );
        if ( ! is_array( $post_types ) || empty( $post_types ) ) {
            $post_types = [ '' ];
        } ?>
        <div id="sbp-post-types-wrapper">
            <?php foreach ( $post_types as $index => $type ) : ?>
                <div class="sbp-post-type-entry" style="margin-bottom:8px;">
                    <input type="text" name="sbp_product_post_types[]" value="<?php echo esc_attr( $type ); ?>" class="regular-text" />
                    <?php if ( $index === 0 ) : ?>
                        <button type="button" class="button add-post-type">+</button>
                    <?php else : ?>
                        <button type="button" class="button remove-post-type">-</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="description">
            <?php esc_html_e( 'Deixe em branco para aceitar qualquer post type. Para restringir, informe os slugs (um por linha).', 'simple-budget-plugin-sbp' ); ?>
        </p>
        <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-post-type')) {
                const wrapper = document.getElementById('sbp-post-types-wrapper');
                const div = document.createElement('div');
                div.classList.add('sbp-post-type-entry');
                div.style.marginBottom = '8px';
                div.innerHTML =
                    '<input type="text" name="sbp_product_post_types[]" class="regular-text" /> ' +
                    '<button type="button" class="button remove-post-type">-</button>';
                wrapper.appendChild(div);
            }
            if (e.target.classList.contains('remove-post-type')) {
                e.target.closest('.sbp-post-type-entry').remove();
            }
        });
        </script>
    <?php }

    public function sanitize_phone_number( $value ) {
        $value = preg_replace( '/[^0-9]/', '', (string) $value );
        return ( strlen( $value ) >= 10 ) ? $value : '';
    }

    public function sanitize_post_types( $types ) {
        $types = array_map( 'sanitize_key', (array) $types );
        return array_values( array_filter( $types ) );
    }
}
