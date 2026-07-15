<?php
/**
 * Budget item pricing contract.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class Pricing {

    const META_MODE = '_sbp_price_mode';
    const META_PRICE = '_sbp_price';
    const META_PRICE_MIN = '_sbp_price_min';
    const META_PRICE_MAX = '_sbp_price_max';
    const META_CURRENCY = '_sbp_price_currency';
    const META_UNIT = '_sbp_price_unit';
    const META_LABEL = '_sbp_price_label';

    const DEFAULT_CURRENCY = 'BRL';
    const NONCE_ACTION = 'sbp_save_pricing';
    const NONCE_NAME = 'sbp_pricing_nonce';

    public function init_hooks() {
        add_action( 'init', [ $this, 'register_meta' ], 20 );
        add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta_box' ] );
        add_filter( 'eit_toolkit_field_catalog_entries', [ PricingEitCatalog::class, 'add_catalog_entries' ] );
        add_filter( 'eit_toolkit_public_meta_fields_for_post_type', [ PricingEitCatalog::class, 'add_public_meta_fields' ], 10, 2 );
    }

    public static function modes() {
        return [
            'hidden' => __( 'Hidden', 'simple-budget-plugin-sbp' ),
            'fixed'  => __( 'Fixed price', 'simple-budget-plugin-sbp' ),
            'from'   => __( 'Starting price', 'simple-budget-plugin-sbp' ),
            'range'  => __( 'Price range', 'simple-budget-plugin-sbp' ),
        ];
    }

    public function register_meta() {
        foreach ( self::supported_post_types() as $post_type ) {
            foreach ( self::meta_schema() + BudgetValueFields::custom_meta_schema() as $key => $schema ) {
                register_post_meta(
                    $post_type,
                    $key,
                    [
                        'type'              => $schema['type'],
                        'single'            => true,
                        'show_in_rest'      => true,
                        'sanitize_callback' => $schema['sanitize_callback'],
                        'auth_callback'     => function () {
                            return current_user_can( 'edit_posts' );
                        },
                    ]
                );
            }
        }
    }

    public function register_meta_boxes() {
        foreach ( self::supported_post_types() as $post_type ) {
            add_meta_box(
                'sbp-pricing',
                __( 'Simple Budget Pricing', 'simple-budget-plugin-sbp' ),
                [ $this, 'render_meta_box' ],
                $post_type,
                'side',
                'default'
            );
        }
    }

    public function render_meta_box( $post ) {
        $pricing = self::for_post( $post->ID );
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
        PricingMetaBox::render( $pricing );
    }

    public function save_meta_box( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! in_array( get_post_type( $post_id ), self::supported_post_types(), true ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
            return;
        }

        $raw = isset( $_POST['sbp_pricing'] ) && is_array( $_POST['sbp_pricing'] ) ? wp_unslash( $_POST['sbp_pricing'] ) : [];
        $pricing = self::sanitize_payload( $raw );

        update_post_meta( $post_id, self::META_MODE, $pricing['mode'] );
        update_post_meta( $post_id, self::META_PRICE, $pricing['price'] );
        update_post_meta( $post_id, self::META_PRICE_MIN, $pricing['price_min'] );
        update_post_meta( $post_id, self::META_PRICE_MAX, $pricing['price_max'] );
        update_post_meta( $post_id, self::META_CURRENCY, $pricing['currency'] );
        update_post_meta( $post_id, self::META_UNIT, $pricing['unit'] );
        update_post_meta( $post_id, self::META_LABEL, $pricing['label'] );

        $raw_values = isset( $_POST['sbp_pricing_values'] ) && is_array( $_POST['sbp_pricing_values'] ) ? wp_unslash( $_POST['sbp_pricing_values'] ) : [];
        BudgetValueMeta::save_for_post( $post_id, $raw_values );
    }

    public static function for_post( $post_id ) {
        $post_id = absint( $post_id );

        if ( ! $post_id ) {
            return self::blank();
        }

        return self::sanitize_payload(
            [
                'mode'      => get_post_meta( $post_id, self::META_MODE, true ),
                'price'     => get_post_meta( $post_id, self::META_PRICE, true ),
                'price_min' => get_post_meta( $post_id, self::META_PRICE_MIN, true ),
                'price_max' => get_post_meta( $post_id, self::META_PRICE_MAX, true ),
                'currency'  => get_post_meta( $post_id, self::META_CURRENCY, true ),
                'unit'      => get_post_meta( $post_id, self::META_UNIT, true ),
                'label'     => get_post_meta( $post_id, self::META_LABEL, true ),
                'values'    => BudgetValueMeta::values_for_post( $post_id ),
            ]
        );
    }

    public static function display_for_post( $post_id ) {
        return self::display_from_data( self::for_post( $post_id ) );
    }

    public static function display_from_data( $pricing ) {
        $pricing = self::sanitize_payload( is_array( $pricing ) ? $pricing : [] );
        $display = '';

        if ( 'fixed' === $pricing['mode'] && '' !== $pricing['price'] ) {
            $display = self::format_money( $pricing['price'], $pricing['currency'] );
        } elseif ( 'from' === $pricing['mode'] && '' !== $pricing['price'] ) {
            $display = sprintf(
                /* translators: %s: formatted price or estimate. */
                __( 'From %s', 'simple-budget-plugin-sbp' ),
                self::format_money( $pricing['price'], $pricing['currency'] )
            );
        } elseif ( 'range' === $pricing['mode'] ) {
            $display = self::format_range( $pricing );
        }

        if ( '' !== $display ) {
            if ( '' !== $pricing['label'] ) {
                $display = $pricing['label'] . ': ' . $display;
            }

            if ( '' !== $pricing['unit'] ) {
                $display .= ' / ' . $pricing['unit'];
            }
        }

        $values = BudgetValueMeta::display_values( $pricing['values'] );

        if ( empty( $values ) ) {
            return $display;
        }

        if ( '' === $display ) {
            return implode( '; ', $values );
        }

        return $display . '; ' . implode( '; ', $values );
    }

    public static function preview() {
        return self::sanitize_payload(
            [
                'mode'     => 'range',
                'price_min' => '1200',
                'price_max' => '2800',
                'currency' => self::DEFAULT_CURRENCY,
            ]
        );
    }

    public static function supported_post_types() {
        $configured = get_option( 'sbp_product_post_types', [] );
        $post_types = array_map( 'sanitize_key', (array) $configured );
        $post_types = array_filter( array_unique( $post_types ) );

        if ( empty( $post_types ) ) {
            $post_types = get_post_types( [ 'public' => true ], 'names' );
        }

        $post_types = array_diff( $post_types, [ 'attachment', 'elementor_library' ] );

        return array_values(
            array_filter(
                $post_types,
                function ( $post_type ) {
                    return post_type_exists( $post_type );
                }
            )
        );
    }

    private static function blank() {
        return [
            'mode'      => 'hidden',
            'price'     => '',
            'price_min' => '',
            'price_max' => '',
            'currency'  => self::DEFAULT_CURRENCY,
            'unit'      => '',
            'label'     => '',
            'values'    => [],
        ];
    }

    private static function sanitize_payload( array $raw ) {
        $payload = self::blank();
        $mode = sanitize_key( $raw['mode'] ?? $payload['mode'] );

        $payload['mode'] = array_key_exists( $mode, self::modes() ) ? $mode : 'hidden';
        $payload['price'] = self::sanitize_decimal( $raw['price'] ?? '' );
        $payload['price_min'] = self::sanitize_decimal( $raw['price_min'] ?? '' );
        $payload['price_max'] = self::sanitize_decimal( $raw['price_max'] ?? '' );
        $payload['currency'] = self::sanitize_currency( $raw['currency'] ?? self::DEFAULT_CURRENCY );
        $payload['unit'] = self::limit_text( sanitize_text_field( $raw['unit'] ?? '' ), 32 );
        $payload['label'] = self::limit_text( sanitize_text_field( $raw['label'] ?? '' ), 80 );
        $payload['values'] = BudgetValueMeta::sanitize_values_payload( $raw['values'] ?? [] );

        return $payload;
    }

    private static function meta_schema() {
        return [
            self::META_MODE => [
                'type'              => 'string',
                'sanitize_callback' => function ( $value ) {
                    $mode = sanitize_key( $value );
                    return array_key_exists( $mode, self::modes() ) ? $mode : 'hidden';
                },
            ],
            self::META_PRICE => [
                'type'              => 'number',
                'sanitize_callback' => [ self::class, 'sanitize_decimal' ],
            ],
            self::META_PRICE_MIN => [
                'type'              => 'number',
                'sanitize_callback' => [ self::class, 'sanitize_decimal' ],
            ],
            self::META_PRICE_MAX => [
                'type'              => 'number',
                'sanitize_callback' => [ self::class, 'sanitize_decimal' ],
            ],
            self::META_CURRENCY => [
                'type'              => 'string',
                'sanitize_callback' => [ self::class, 'sanitize_currency' ],
            ],
            self::META_UNIT => [
                'type'              => 'string',
                'sanitize_callback' => function ( $value ) {
                    return self::limit_text( sanitize_text_field( $value ), 32 );
                },
            ],
            self::META_LABEL => [
                'type'              => 'string',
                'sanitize_callback' => function ( $value ) {
                    return self::limit_text( sanitize_text_field( $value ), 80 );
                },
            ],
        ];
    }

    private static function format_range( array $pricing ) {
        $min = '' !== $pricing['price_min'] ? self::format_money( $pricing['price_min'], $pricing['currency'] ) : '';
        $max = '' !== $pricing['price_max'] ? self::format_money( $pricing['price_max'], $pricing['currency'] ) : '';

        if ( $min && $max ) {
            return $min . ' - ' . $max;
        }

        if ( $min ) {
            return sprintf(
                /* translators: %s: formatted price or estimate. */
                __( 'From %s', 'simple-budget-plugin-sbp' ),
                $min
            );
        }

        if ( $max ) {
            return sprintf(
                /* translators: %s: formatted item price. */
                __( 'Up to %s', 'simple-budget-plugin-sbp' ),
                $max
            );
        }

        return '';
    }

    public static function format_money( $value, $currency ) {
        $number = (float) $value;
        $currency = self::sanitize_currency( $currency );
        $formatted = number_format( $number, 2, ',', '.' );

        if ( 'BRL' === $currency ) {
            return 'R$ ' . $formatted;
        }

        if ( 'USD' === $currency ) {
            return '$' . number_format( $number, 2, '.', ',' );
        }

        if ( 'EUR' === $currency ) {
            return '€ ' . $formatted;
        }

        return $currency . ' ' . $formatted;
    }

    public static function sanitize_decimal( $value ) {
        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        $value = str_replace( ',', '.', preg_replace( '/[^0-9,.\-]/', '', $value ) );

        if ( ! is_numeric( $value ) ) {
            return '';
        }

        $number = max( 0, (float) $value );
        $value = number_format( $number, 2, '.', '' );

        return rtrim( rtrim( $value, '0' ), '.' );
    }

    public static function sanitize_currency( $value ) {
        $value = strtoupper( preg_replace( '/[^A-Z]/i', '', (string) $value ) );

        return 3 === strlen( $value ) ? $value : self::DEFAULT_CURRENCY;
    }

    private static function limit_text( $value, $length ) {
        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $value, 0, $length );
        }

        return substr( $value, 0, $length );
    }
}
