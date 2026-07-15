<?php
/**
 * Role-aware Elementor template management for Simple Budget surfaces.
 */

namespace SBP\Templates;

if ( ! defined( 'ABSPATH' ) ) exit;

class TemplateManager {

    const ROLE_META = '_sbp_template_role';
    const ROLE_CART_MODAL = 'cart_modal';
    const ROLE_BUDGET_ITEM = 'budget_item';
    const ROLE_BUDGET_SUMMARY = 'budget_summary';
    const EDITOR_PAGE_TEMPLATE = 'elementor_canvas';

    private static $render_stack = [];

    public static function roles() {
        return [
            self::ROLE_CART_MODAL => [
                'label'         => __( 'Cart Templates', 'simple-budget-plugin-sbp' ),
                'singular'      => __( 'cart template', 'simple-budget-plugin-sbp' ),
                'default_title' => __( 'Simple Budget Cart Modal', 'simple-budget-plugin-sbp' ),
                'description'   => __( 'The complete modal or drawer opened by a Budget Button.', 'simple-budget-plugin-sbp' ),
            ],
            self::ROLE_BUDGET_ITEM => [
                'label'         => __( 'Budget Item Templates', 'simple-budget-plugin-sbp' ),
                'singular'      => __( 'budget item template', 'simple-budget-plugin-sbp' ),
                'default_title' => __( 'Simple Budget Item', 'simple-budget-plugin-sbp' ),
                'description'   => __( 'Repeated once for each selected item inside a Budget Listing.', 'simple-budget-plugin-sbp' ),
            ],
            self::ROLE_BUDGET_SUMMARY => [
                'label'         => __( 'Budget Summary Templates', 'simple-budget-plugin-sbp' ),
                'singular'      => __( 'budget summary template', 'simple-budget-plugin-sbp' ),
                'default_title' => __( 'Simple Budget Summary', 'simple-budget-plugin-sbp' ),
                'description'   => __( 'Optional subtotal, adjustment, and estimated-range composition.', 'simple-budget-plugin-sbp' ),
            ],
        ];
    }

    public static function is_valid_role( $role ) {
        return array_key_exists( sanitize_key( $role ), self::roles() );
    }

    public static function is_elementor_available() {
        return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' );
    }

    public static function get_templates( $role ) {
        $role = sanitize_key( $role );

        if ( ! self::is_valid_role( $role ) || ! post_type_exists( 'elementor_library' ) ) {
            return [];
        }

        return get_posts([
            'post_type'      => 'elementor_library',
            'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'   => self::ROLE_META,
                    'value' => $role,
                ],
            ],
        ]);
    }

    public static function get_template_options( $role, $include_empty = true ) {
        $options = [];

        if ( $include_empty ) {
            $options[''] = __( 'No template selected', 'simple-budget-plugin-sbp' );
        }

        foreach ( self::get_templates( $role ) as $template ) {
            $label = get_the_title( $template );

            if ( 'publish' !== $template->post_status ) {
                $status = get_post_status_object( $template->post_status );
                $label .= ' (' . ( $status ? $status->label : $template->post_status ) . ')';
            }

            $options[ (string) $template->ID ] = $label;
        }

        return $options;
    }

    public static function create_template( $role, $title = '' ) {
        $role = sanitize_key( $role );

        if ( ! self::is_valid_role( $role ) ) {
            return new \WP_Error(
                'sbp_template_role_invalid',
                __( 'Unknown Simple Budget template type.', 'simple-budget-plugin-sbp' )
            );
        }

        if ( ! self::is_elementor_available() ) {
            return new \WP_Error(
                'sbp_elementor_unavailable',
                __( 'Elementor must be active to create Simple Budget templates.', 'simple-budget-plugin-sbp' )
            );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            return new \WP_Error(
                'sbp_template_permission',
                __( 'You do not have permission to create Elementor templates.', 'simple-budget-plugin-sbp' )
            );
        }

        $title = sanitize_text_field( $title );
        $roles = self::roles();

        if ( '' === $title ) {
            $title = $roles[ $role ]['default_title'];
        }

        $document = \Elementor\Plugin::$instance->documents->create(
            self::get_supported_document_type(),
            [
                'post_title'  => $title,
                'post_status' => 'publish',
            ],
            [
                self::ROLE_META => $role,
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
            'elements' => self::get_starter_elements( $role ),
            'settings' => [
                'template' => self::EDITOR_PAGE_TEMPLATE,
            ],
        ]);

        $template_id = absint( $document->get_main_id() );
        update_post_meta( $template_id, self::ROLE_META, $role );
        self::ensure_editor_surface( $template_id );

        return $template_id;
    }

    public static function get_edit_url( $template_id ) {
        $template_id = absint( $template_id );

        if ( self::is_template( $template_id ) ) {
            self::ensure_editor_surface( $template_id );
        }

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

    public static function get_template_role( $template_id ) {
        $template_id = absint( $template_id );

        if ( ! $template_id || 'elementor_library' !== get_post_type( $template_id ) ) {
            return '';
        }

        $role = sanitize_key( get_post_meta( $template_id, self::ROLE_META, true ) );

        return self::is_valid_role( $role ) ? $role : '';
    }

    public static function is_template( $template_id, $role = '' ) {
        $actual_role = self::get_template_role( $template_id );

        if ( '' === $actual_role ) {
            return false;
        }

        $role = sanitize_key( $role );

        return '' === $role || $actual_role === $role;
    }

    public static function can_render_template( $template_id, $role = '' ) {
        $template_id = absint( $template_id );
        $template = $template_id ? get_post( $template_id ) : null;

        if ( ! $template || ! self::is_template( $template_id, $role ) ) {
            return false;
        }

        if ( 'publish' === $template->post_status ) {
            return true;
        }

        return is_user_logged_in() && current_user_can( 'edit_post', $template_id );
    }

    public static function render_template( $template_id, $role = '', $with_css = true ) {
        $template_id = absint( $template_id );

        if (
            ! self::can_render_template( $template_id, $role )
            || ! self::is_elementor_available()
            || in_array( $template_id, self::$render_stack, true )
        ) {
            return '';
        }

        self::$render_stack[] = $template_id;

        try {
            return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, (bool) $with_css );
        } finally {
            array_pop( self::$render_stack );
        }
    }

    public static function delete_template( $template_id, $role = '' ) {
        $template_id = absint( $template_id );

        if ( ! self::is_template( $template_id, $role ) ) {
            return new \WP_Error(
                'sbp_template_not_found',
                __( 'Simple Budget template not found.', 'simple-budget-plugin-sbp' )
            );
        }

        if ( ! current_user_can( 'delete_post', $template_id ) ) {
            return new \WP_Error(
                'sbp_template_delete_permission',
                __( 'You do not have permission to delete this template.', 'simple-budget-plugin-sbp' )
            );
        }

        $deleted = defined( 'EMPTY_TRASH_DAYS' ) && EMPTY_TRASH_DAYS > 0
            ? wp_trash_post( $template_id )
            : wp_delete_post( $template_id, true );

        if ( ! $deleted ) {
            return new \WP_Error(
                'sbp_template_delete_failed',
                __( 'Could not delete the Simple Budget template.', 'simple-budget-plugin-sbp' )
            );
        }

        return true;
    }

    public static function ensure_editor_surface( $template_id ) {
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

    private static function get_supported_document_type() {
        return 'page';
    }

    private static function get_starter_elements( $role ) {
        if ( self::ROLE_BUDGET_ITEM === $role ) {
            return self::get_item_starter_elements();
        }

        if ( self::ROLE_BUDGET_SUMMARY === $role ) {
            return self::get_summary_starter_elements();
        }

        return self::get_cart_starter_elements();
    }

    private static function get_cart_starter_elements() {
        return [
            self::container(
                'cart-shell',
                [
                    self::heading( 'cart-title', __( 'Your Budget', 'simple-budget-plugin-sbp' ) ),
                    self::widget(
                        'cart-list',
                        'sbp-budget-list',
                        [
                            'empty_message'  => __( 'Your budget is empty.', 'simple-budget-plugin-sbp' ),
                            'show_image'     => 'yes',
                            'show_remove'    => 'yes',
                            'show_quantity'  => 'yes',
                            'quantity_label' => __( 'Quantity', 'simple-budget-plugin-sbp' ),
                            'remove_text'    => __( 'Remove', 'simple-budget-plugin-sbp' ),
                            'remove_position' => 'inline_end',
                        ]
                    ),
                    self::widget(
                        'cart-send',
                        'sbp-budget-button',
                        [
                            'action'              => 'send_whatsapp',
                            'text'                => __( 'Send budget via WhatsApp', 'simple-budget-plugin-sbp' ),
                            'empty_cart_behavior' => 'disable',
                            'size'                => 'sm',
                        ]
                    ),
                    self::widget(
                        'cart-close',
                        'sbp-budget-button',
                        [
                            'action'        => 'close_cart',
                            'text'          => __( 'Close', 'simple-budget-plugin-sbp' ),
                            'size'          => 'sm',
                            'selected_icon' => [
                                'value'   => 'fas fa-times',
                                'library' => 'fa-solid',
                            ],
                        ]
                    ),
                ],
                [
                    'flex_direction'        => 'column',
                    'gap'                   => [ 'size' => 18, 'unit' => 'px' ],
                    'background_background' => 'classic',
                    'background_color'      => '#ffffff',
                    'padding'               => [
                        'top' => 28, 'right' => 28, 'bottom' => 28, 'left' => 28, 'unit' => 'px', 'isLinked' => true,
                    ],
                    'html_tag'              => 'section',
                ]
            ),
        ];
    }

    private static function get_item_starter_elements() {
        return [
            self::container(
                'item-shell',
                [
                    self::widget(
                        'item-image',
                        'image',
                        [
                            'image_size' => 'thumbnail',
                            '__dynamic__' => [
                                'image' => self::dynamic_tag( 'sbp-item-image' ),
                            ],
                        ]
                    ),
                    self::container(
                        'item-copy',
                        [
                            self::heading( 'item-title', '', 'sbp-item-title' ),
                            self::widget(
                                'item-description',
                                'text-editor',
                                [
                                    '__dynamic__' => [
                                        'editor' => self::dynamic_tag( 'sbp-item-description' ),
                                    ],
                                ]
                            ),
                            self::heading( 'item-price', '', 'sbp-item-price' ),
                        ],
                        [
                            'flex_direction' => 'column',
                            'gap'            => [ 'size' => 6, 'unit' => 'px' ],
                        ]
                    ),
                    self::widget( 'item-quantity', 'sbp-budget-quantity', [ 'mode' => 'stepper' ] ),
                    self::widget(
                        'item-remove',
                        'sbp-budget-button',
                        [
                            'action' => 'remove',
                            'text'   => __( 'Remove', 'simple-budget-plugin-sbp' ),
                            'size'   => 'xs',
                        ]
                    ),
                ],
                [
                    'flex_direction'      => 'row',
                    'flex_align_items'    => 'center',
                    'gap'                 => [ 'size' => 16, 'unit' => 'px' ],
                    'padding'             => [
                        'top' => 12, 'right' => 12, 'bottom' => 12, 'left' => 12, 'unit' => 'px', 'isLinked' => true,
                    ],
                ]
            ),
        ];
    }

    private static function get_summary_starter_elements() {
        return [
            self::container(
                'summary-shell',
                [
                    self::summary_row( 'summary-subtotal', __( 'Subtotal', 'simple-budget-plugin-sbp' ), 'sbp-summary-subtotal' ),
                    self::summary_row( 'summary-adjustment', __( 'Adjustment', 'simple-budget-plugin-sbp' ), 'sbp-summary-adjustment' ),
                    self::summary_row( 'summary-range', __( 'Estimated range', 'simple-budget-plugin-sbp' ), 'sbp-summary-estimated-range' ),
                    self::widget(
                        'summary-status',
                        'text-editor',
                        [
                            '__dynamic__' => [
                                'editor' => self::dynamic_tag( 'sbp-summary-status' ),
                            ],
                        ]
                    ),
                ],
                [
                    'flex_direction' => 'column',
                    'gap'            => [ 'size' => 10, 'unit' => 'px' ],
                ]
            ),
        ];
    }

    private static function summary_row( $seed, $label, $tag ) {
        return self::container(
            $seed,
            [
                self::heading( $seed . '-label', $label, '', 'h4' ),
                self::heading( $seed . '-value', '', $tag, 'h4' ),
            ],
            [
                'flex_direction'       => 'row',
                'flex_justify_content' => 'space-between',
                'gap'                  => [ 'size' => 12, 'unit' => 'px' ],
            ]
        );
    }

    private static function container( $seed, array $elements, array $settings = [] ) {
        return [
            'id'       => self::element_id( $seed ),
            'elType'   => 'container',
            'isInner'  => false,
            'settings' => array_merge(
                [
                    'container_type' => 'flex',
                    'content_width'  => 'full',
                ],
                $settings
            ),
            'elements' => $elements,
        ];
    }

    private static function heading( $seed, $title, $dynamic_tag = '', $size = 'h3' ) {
        $settings = [
            'title'       => $title,
            'header_size' => $size,
        ];

        if ( '' !== $dynamic_tag ) {
            $settings['__dynamic__'] = [
                'title' => self::dynamic_tag( $dynamic_tag ),
            ];
        }

        return self::widget( $seed, 'heading', $settings );
    }

    private static function widget( $seed, $widget_type, array $settings ) {
        return [
            'id'         => self::element_id( $seed ),
            'elType'     => 'widget',
            'isInner'    => false,
            'widgetType' => $widget_type,
            'settings'   => $settings,
            'elements'   => [],
        ];
    }

    private static function dynamic_tag( $name ) {
        return '[elementor-tag id="' . self::element_id( $name ) . '" name="' . sanitize_key( $name ) . '" settings="%7B%7D"]';
    }

    private static function element_id( $seed ) {
        return substr( md5( uniqid( $seed, true ) ), 0, 7 );
    }
}
