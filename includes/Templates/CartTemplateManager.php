<?php
/**
 * Elementor template helpers for the Simple Budget cart modal.
 */

namespace SBP\Templates;

if ( ! defined( 'ABSPATH' ) ) exit;

class CartTemplateManager {

    const ROLE_META = '_sbp_template_role';
    const ROLE_CART_MODAL = 'cart_modal';
    const EDITOR_PAGE_TEMPLATE = 'elementor_canvas';

    public static function is_elementor_available() {
        return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' );
    }

    public static function get_templates() {
        if ( ! post_type_exists( 'elementor_library' ) ) {
            return [];
        }

        $templates = get_posts([
            'post_type'      => 'elementor_library',
            'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'   => self::ROLE_META,
                    'value' => self::ROLE_CART_MODAL,
                ],
            ],
        ]);

        foreach ( $templates as $template ) {
            self::normalize_editor_surface( $template->ID );
        }

        return $templates;
    }

    public static function get_template_options( $include_empty = true ) {
        $options = [];

        if ( $include_empty ) {
            $options[''] = __( 'Legacy popup fallback', 'simple-budget-plugin-sbp' );
        }

        foreach ( self::get_templates() as $template ) {
            $label = get_the_title( $template );

            if ( 'publish' !== $template->post_status ) {
                $status = get_post_status_object( $template->post_status );
                $label .= ' (' . ( $status ? $status->label : $template->post_status ) . ')';
            }

            $options[ (string) $template->ID ] = $label;
        }

        return $options;
    }

    public static function create_cart_modal_template( $title = '' ) {
        if ( ! self::is_elementor_available() ) {
            return new \WP_Error(
                'sbp_elementor_unavailable',
                __( 'Elementor must be active to create cart templates.', 'simple-budget-plugin-sbp' )
            );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            return new \WP_Error(
                'sbp_template_permission',
                __( 'You do not have permission to create Elementor templates.', 'simple-budget-plugin-sbp' )
            );
        }

        $title = sanitize_text_field( $title );
        if ( '' === $title ) {
            $title = __( 'Simple Budget Cart Modal', 'simple-budget-plugin-sbp' );
        }

        $document = \Elementor\Plugin::$instance->documents->create(
            self::get_supported_document_type(),
            [
                'post_title'  => $title,
                'post_status' => 'publish',
            ],
            [
                self::ROLE_META => self::ROLE_CART_MODAL,
            ]
        );

        if ( is_wp_error( $document ) ) {
            return $document;
        }

        if ( ! $document || ! method_exists( $document, 'save' ) ) {
            return new \WP_Error(
                'sbp_template_create_failed',
                __( 'Could not create the Elementor template.', 'simple-budget-plugin-sbp' )
            );
        }

        $document->save([
            'elements' => self::get_starter_elements(),
            'settings' => [
                'template' => self::EDITOR_PAGE_TEMPLATE,
            ],
        ]);

        $template_id = absint( $document->get_main_id() );
        update_post_meta( $template_id, self::ROLE_META, self::ROLE_CART_MODAL );
        self::normalize_editor_surface( $template_id );

        return $template_id;
    }

    public static function get_edit_url( $template_id ) {
        $template_id = absint( $template_id );

        if ( self::is_elementor_available() ) {
            $document = \Elementor\Plugin::$instance->documents->get( $template_id );

            if ( $document && method_exists( $document, 'get_edit_url' ) ) {
                return $document->get_edit_url();
            }
        }

        return add_query_arg(
            [
                'post'   => $template_id,
                'action' => 'elementor',
            ],
            admin_url( 'post.php' )
        );
    }

    public static function is_cart_template( $template_id ) {
        $template_id = absint( $template_id );

        return $template_id
            && 'elementor_library' === get_post_type( $template_id )
            && self::ROLE_CART_MODAL === get_post_meta( $template_id, self::ROLE_META, true );
    }

    public static function render_template( $template_id ) {
        $template_id = absint( $template_id );

        if ( ! self::is_cart_template( $template_id ) || ! self::is_elementor_available() ) {
            return '';
        }

        return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
    }

    private static function get_supported_document_type() {
        $documents = \Elementor\Plugin::$instance->documents;

        if ( $documents->get_document_type( 'page', false ) ) {
            return 'page';
        }

        return 'page';
    }

    private static function normalize_editor_surface( $template_id ) {
        $template_id = absint( $template_id );

        if ( ! $template_id || 'elementor_library' !== get_post_type( $template_id ) ) {
            return;
        }

        update_post_meta( $template_id, '_elementor_template_type', 'page' );
        update_post_meta( $template_id, '_wp_page_template', self::EDITOR_PAGE_TEMPLATE );

        $page_settings = get_post_meta( $template_id, '_elementor_page_settings', true );
        $page_settings = is_array( $page_settings ) ? $page_settings : [];
        $page_settings['template'] = self::EDITOR_PAGE_TEMPLATE;

        update_post_meta( $template_id, '_elementor_page_settings', $page_settings );
    }

    private static function get_starter_elements() {
        return [
            [
                'id'       => self::element_id( 'shell' ),
                'elType'   => 'container',
                'isInner'  => false,
                'settings' => [
                    'container_type'        => 'flex',
                    'content_width'         => 'boxed',
                    'flex_direction'        => 'column',
                    'gap'                   => [
                        'size' => 18,
                        'unit' => 'px',
                    ],
                    'background_background' => 'classic',
                    'background_color'      => '#ffffff',
                    'padding'               => [
                        'top'      => 28,
                        'right'    => 28,
                        'bottom'   => 28,
                        'left'     => 28,
                        'unit'     => 'px',
                        'isLinked' => true,
                    ],
                    'border_radius'         => [
                        'top'      => 18,
                        'right'    => 18,
                        'bottom'   => 18,
                        'left'     => 18,
                        'unit'     => 'px',
                        'isLinked' => true,
                    ],
                    'html_tag'              => 'section',
                ],
                'elements' => [
                    [
                        'id'         => self::element_id( 'title' ),
                        'elType'     => 'widget',
                        'isInner'    => false,
                        'widgetType' => 'heading',
                        'settings'   => [
                            'title'       => __( 'Your Budget', 'simple-budget-plugin-sbp' ),
                            'header_size' => 'h2',
                            'title_color' => '#111827',
                        ],
                        'elements'   => [],
                    ],
                    [
                        'id'         => self::element_id( 'list' ),
                        'elType'     => 'widget',
                        'isInner'    => false,
                        'widgetType' => 'sbp-budget-list',
                        'settings'   => [
                            'empty_message' => __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' ),
                            'show_image'    => 'yes',
                            'show_remove'   => 'yes',
                            'remove_text'   => __( 'Remover', 'simple-budget-plugin-sbp' ),
                            'show_submit'   => 'yes',
                            'submit_text'   => __( 'Enviar orçamento via WhatsApp', 'simple-budget-plugin-sbp' ),
                        ],
                        'elements'   => [],
                    ],
                    [
                        'id'         => self::element_id( 'close' ),
                        'elType'     => 'widget',
                        'isInner'    => false,
                        'widgetType' => 'sbp-budget-button',
                        'settings'   => [
                            'action'        => 'close_cart',
                            'text'          => __( 'Close', 'simple-budget-plugin-sbp' ),
                            'button_type'   => '',
                            'size'          => 'sm',
                            'selected_icon' => [
                                'value'   => 'fas fa-times',
                                'library' => 'fa-solid',
                            ],
                        ],
                        'elements'   => [],
                    ],
                ],
            ],
        ];
    }

    private static function element_id( $seed ) {
        return substr( md5( uniqid( $seed, true ) ), 0, 7 );
    }
}
