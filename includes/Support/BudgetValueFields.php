<?php
/**
 * Option-backed budget value field catalog.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetValueFields {

    const OPTION = 'sbp_budget_value_fields';
    const NAMESPACE_LABEL = '__SBP';
    const MAX_FIELDS = 40;

    public static function types() {
        return [
            'number' => __( 'Number', 'simple-budget-plugin-sbp' ),
            'text'   => __( 'Text', 'simple-budget-plugin-sbp' ),
            'select' => __( 'Select', 'simple-budget-plugin-sbp' ),
        ];
    }

    public static function default_fields() {
        return [
            Pricing::META_PRICE => [
                'key'        => Pricing::META_PRICE,
                'label'      => __( 'Budget price', 'simple-budget-plugin-sbp' ),
                'type'       => 'number',
                'unit'       => '',
                'display'    => false,
                'filterable' => true,
                'options'    => [],
                'system'     => true,
            ],
            Pricing::META_PRICE_MIN => [
                'key'        => Pricing::META_PRICE_MIN,
                'label'      => __( 'Budget price min', 'simple-budget-plugin-sbp' ),
                'type'       => 'number',
                'unit'       => '',
                'display'    => false,
                'filterable' => true,
                'options'    => [],
                'system'     => true,
            ],
            Pricing::META_PRICE_MAX => [
                'key'        => Pricing::META_PRICE_MAX,
                'label'      => __( 'Budget price max', 'simple-budget-plugin-sbp' ),
                'type'       => 'number',
                'unit'       => '',
                'display'    => false,
                'filterable' => true,
                'options'    => [],
                'system'     => true,
            ],
        ];
    }

    public static function custom_fields() {
        $fields = get_option( self::OPTION, [] );
        $indexed = [];

        foreach ( self::sanitize_fields( $fields ) as $field ) {
            $indexed[ $field['key'] ] = $field;
        }

        return $indexed;
    }

    public static function all_fields() {
        return self::default_fields() + self::custom_fields();
    }

    public static function filterable_fields() {
        return array_filter(
            self::all_fields(),
            function ( $field ) {
                return ! empty( $field['filterable'] );
            }
        );
    }

    public static function custom_meta_schema() {
        $schema = [];

        foreach ( self::custom_fields() as $key => $field ) {
            $schema[ $key ] = [
                'type'              => 'number' === $field['type'] ? 'number' : 'string',
                'sanitize_callback' => function ( $value ) use ( $field ) {
                    return BudgetValueFields::sanitize_value( $value, $field );
                },
            ];
        }

        return $schema;
    }

    public static function sanitize_fields( $fields ) {
        $fields = is_array( $fields ) ? $fields : [];
        $sanitized = [];
        $reserved = array_fill_keys( array_keys( self::default_fields() ), true );

        foreach ( $fields as $field ) {
            if ( count( $sanitized ) >= self::MAX_FIELDS || ! is_array( $field ) ) {
                continue;
            }

            $field = self::sanitize_field( $field );

            if ( '' === $field['key'] || isset( $reserved[ $field['key'] ] ) || isset( $sanitized[ $field['key'] ] ) ) {
                continue;
            }

            $sanitized[ $field['key'] ] = $field;
        }

        return array_values( $sanitized );
    }

    public static function update_custom_fields( array $fields ) {
        return update_option( self::OPTION, self::sanitize_fields( $fields ), false );
    }

    public static function upsert_custom_field( array $field ) {
        $field = self::sanitize_field( $field );

        if ( '' === $field['key'] || isset( self::default_fields()[ $field['key'] ] ) ) {
            return false;
        }

        $fields = self::custom_fields();
        $fields[ $field['key'] ] = $field;

        return self::update_custom_fields( $fields );
    }

    public static function delete_custom_field( $key ) {
        $key = self::sanitize_meta_key( $key, '' );
        $fields = self::custom_fields();

        if ( ! isset( $fields[ $key ] ) ) {
            return false;
        }

        unset( $fields[ $key ] );
        return self::update_custom_fields( $fields );
    }

    public static function sanitize_value( $value, array $field ) {
        if ( 'number' === $field['type'] ) {
            return Pricing::sanitize_decimal( $value );
        }

        if ( 'select' === $field['type'] ) {
            $value = sanitize_text_field( $value );
            return array_key_exists( $value, self::option_map( $field['options'] ) ) ? $value : '';
        }

        return self::limit_text( sanitize_text_field( $value ), 120 );
    }

    public static function format_value( $value, array $field ) {
        $value = self::sanitize_value( $value, $field );

        if ( '' === $value ) {
            return '';
        }

        if ( 'select' === $field['type'] ) {
            $options = self::option_map( $field['options'] );
            $value = $options[ $value ] ?? $value;
        }

        return '' !== $field['unit'] ? $value . ' ' . $field['unit'] : $value;
    }

    private static function sanitize_field( array $field ) {
        $label = self::limit_text( sanitize_text_field( $field['label'] ?? '' ), 80 );
        $key = self::sanitize_meta_key( $field['key'] ?? '', $label );
        $type = sanitize_key( $field['type'] ?? 'number' );

        if ( ! array_key_exists( $type, self::types() ) ) {
            $type = 'number';
        }

        return [
            'key'        => $key,
            'label'      => '' !== $label ? $label : $key,
            'type'       => $type,
            'unit'       => self::limit_text( sanitize_text_field( $field['unit'] ?? '' ), 32 ),
            'display'    => self::to_bool( $field['display'] ?? false ),
            'filterable' => self::to_bool( $field['filterable'] ?? true ),
            'options'    => self::sanitize_options( $field['options'] ?? [] ),
            'system'     => false,
        ];
    }

    private static function sanitize_meta_key( $key, $label ) {
        $key = sanitize_key( (string) $key );

        if ( '' === $key && '' !== $label ) {
            $key = sanitize_key( $label );
        }

        if ( '' === $key ) {
            return '';
        }

        return 0 === strpos( $key, '_sbp_' ) ? $key : '_sbp_' . $key;
    }

    private static function parse_options( $raw ) {
        if ( is_array( $raw ) ) {
            return $raw;
        }

        $rows = preg_split( '/\r\n|\r|\n/', (string) $raw );
        $options = [];

        foreach ( $rows as $row ) {
            $row = trim( $row );

            if ( '' === $row ) {
                continue;
            }

            $parts = array_map( 'trim', explode( '|', $row, 2 ) );
            $value = $parts[0];
            $label = $parts[1] ?? $parts[0];
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    private static function sanitize_options( $options ) {
        $options = is_array( $options ) ? $options : self::parse_options( $options );
        $sanitized = [];

        foreach ( $options as $option ) {
            if ( count( $sanitized ) >= 30 || ! is_array( $option ) ) {
                continue;
            }

            $value = sanitize_key( $option['value'] ?? '' );
            $label = self::limit_text( sanitize_text_field( $option['label'] ?? $value ), 80 );

            if ( '' === $value || isset( $sanitized[ $value ] ) ) {
                continue;
            }

            $sanitized[ $value ] = [
                'value' => $value,
                'label' => '' !== $label ? $label : $value,
            ];
        }

        return array_values( $sanitized );
    }

    private static function option_map( array $options ) {
        $map = [];

        foreach ( self::sanitize_options( $options ) as $option ) {
            $map[ $option['value'] ] = $option['label'];
        }

        return $map;
    }

    private static function to_bool( $value ) {
        return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
    }

    private static function limit_text( $value, $length ) {
        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $value, 0, $length );
        }

        return substr( $value, 0, $length );
    }
}
