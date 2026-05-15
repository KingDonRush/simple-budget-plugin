<?php
/**
 * Painel administrativo (settings page).
 */

namespace SBP\Admin;

use SBP\Templates\CartTemplateManager;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {

    public function init_hooks() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_post_sbp_create_cart_template', [ $this, 'handle_create_cart_template' ] );
    }

    public function register_menu() {
        add_menu_page(
            __( 'Configurações SBP', 'simple-budget-plugin-sbp' ),
            __( 'Simple Budget', 'simple-budget-plugin-sbp' ),
            'manage_options',
            'sbp-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-cart'
        );

        add_submenu_page(
            'sbp-settings',
            __( 'Budget Templates', 'simple-budget-plugin-sbp' ),
            __( 'Templates', 'simple-budget-plugin-sbp' ),
            'manage_options',
            'sbp-templates',
            [ $this, 'render_templates_page' ]
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

    public function render_templates_page() {
        $templates = CartTemplateManager::get_templates();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Simple Budget Templates', 'simple-budget-plugin-sbp' ); ?></h1>

            <?php if ( isset( $_GET['sbp_error'] ) ) : ?>
                <div class="notice notice-error">
                    <p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['sbp_error'] ) ) ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( ! CartTemplateManager::is_elementor_available() ) : ?>
                <div class="notice notice-warning">
                    <p><?php esc_html_e( 'Elementor must be active to create and edit Simple Budget templates.', 'simple-budget-plugin-sbp' ); ?></p>
                </div>
            <?php endif; ?>

            <p>
                <?php esc_html_e( 'Create Elementor templates for the budget modal, then select one in a Budget Button configured as Open cart.', 'simple-budget-plugin-sbp' ); ?>
            </p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin: 18px 0 24px;">
                <?php wp_nonce_field( 'sbp_create_cart_template' ); ?>
                <input type="hidden" name="action" value="sbp_create_cart_template" />
                <label for="sbp_template_title" class="screen-reader-text">
                    <?php esc_html_e( 'Template title', 'simple-budget-plugin-sbp' ); ?>
                </label>
                <input
                    id="sbp_template_title"
                    type="text"
                    name="template_title"
                    class="regular-text"
                    placeholder="<?php echo esc_attr__( 'Simple Budget Cart Modal', 'simple-budget-plugin-sbp' ); ?>"
                />
                <?php submit_button( __( 'Create cart template', 'simple-budget-plugin-sbp' ), 'primary', 'submit', false ); ?>
            </form>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Template', 'simple-budget-plugin-sbp' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'simple-budget-plugin-sbp' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'simple-budget-plugin-sbp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $templates ) ) : ?>
                        <tr>
                            <td colspan="3"><?php esc_html_e( 'No Simple Budget cart templates found yet.', 'simple-budget-plugin-sbp' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $templates as $template ) : ?>
                            <?php $status = get_post_status_object( $template->post_status ); ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( get_the_title( $template ) ); ?></strong>
                                    <br />
                                    <code><?php echo esc_html( '#' . $template->ID ); ?></code>
                                </td>
                                <td><?php echo esc_html( $status ? $status->label : $template->post_status ); ?></td>
                                <td>
                                    <a class="button button-primary" href="<?php echo esc_url( CartTemplateManager::get_edit_url( $template->ID ) ); ?>">
                                        <?php esc_html_e( 'Edit in Elementor', 'simple-budget-plugin-sbp' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php }

    public function handle_create_cart_template() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to create Simple Budget templates.', 'simple-budget-plugin-sbp' ) );
        }

        check_admin_referer( 'sbp_create_cart_template' );

        $title = isset( $_POST['template_title'] ) ? sanitize_text_field( wp_unslash( $_POST['template_title'] ) ) : '';
        $template_id = CartTemplateManager::create_cart_modal_template( $title );

        if ( is_wp_error( $template_id ) ) {
            wp_safe_redirect(
                add_query_arg(
                    [
                        'page'      => 'sbp-templates',
                        'sbp_error' => $template_id->get_error_message(),
                    ],
                    admin_url( 'admin.php' )
                )
            );
            exit;
        }

        wp_safe_redirect( CartTemplateManager::get_edit_url( $template_id ) );
        exit;
    }

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
