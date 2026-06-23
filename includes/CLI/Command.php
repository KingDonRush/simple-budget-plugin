<?php
/**
 * WP-CLI surface for Simple Budget Plugin settings.
 */

namespace SBP\CLI;

use SBP\Admin\Admin;
use SBP\Support\BudgetValueFields;

if ( ! defined( 'ABSPATH' ) ) exit;

class Command {

    public function init_hooks() {
        if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
            return;
        }

        \WP_CLI::add_command( 'sbp', $this );
        \WP_CLI::add_command( 'sbp value-fields', [ $this, 'value_fields' ] );
    }

    /**
     * Manage Simple Budget settings shown in wp-admin.
     *
     * ## EXAMPLES
     *
     *     wp sbp settings list --format=json
     *     wp sbp settings get post_types
     *     wp sbp settings set post_types page,produto
     */
    public function settings( $args, $assoc_args ) {
        $action = $args[0] ?? 'list';

        if ( 'list' === $action ) {
            $this->format_items(
                $assoc_args,
                [
                    [
                        'key'   => 'whatsapp_number',
                        'value' => get_option( 'sbp_whatsapp_number', '' ),
                    ],
                    [
                        'key'   => 'post_types',
                        'value' => implode( ',', (array) get_option( 'sbp_product_post_types', [] ) ),
                    ],
                ],
                [ 'key', 'value' ]
            );
            return;
        }

        $key = sanitize_key( $args[1] ?? '' );

        if ( ! in_array( $key, [ 'whatsapp_number', 'post_types' ], true ) ) {
            \WP_CLI::error( 'Use one of: whatsapp_number, post_types.' );
        }

        if ( 'get' === $action ) {
            $value = 'post_types' === $key
                ? array_values( (array) get_option( 'sbp_product_post_types', [] ) )
                : get_option( 'sbp_whatsapp_number', '' );

            $this->line_value( $value, $assoc_args );
            return;
        }

        if ( 'set' !== $action ) {
            \WP_CLI::error( 'Use one of: list, get, set.' );
        }

        $value = $args[2] ?? ( $assoc_args['value'] ?? '' );

        if ( 'post_types' === $key ) {
            update_option( 'sbp_product_post_types', ( new Admin() )->sanitize_post_types( explode( ',', (string) $value ) ), false );
        } else {
            update_option( 'sbp_whatsapp_number', ( new Admin() )->sanitize_phone_number( $value ), false );
        }

        \WP_CLI::success( 'Simple Budget setting updated.' );
    }

    /**
     * Manage __SBP budget value fields.
     *
     * ## EXAMPLES
     *
     *     wp sbp value-fields list
     *     wp sbp value-fields add --key=_sbp_area --label=Area --type=number --unit=m2 --display=1 --filterable=1
     *     wp sbp value-fields delete _sbp_area
     */
    public function value_fields( $args, $assoc_args ) {
        $action = $args[0] ?? 'list';

        if ( 'list' === $action ) {
            $this->format_items( $assoc_args, $this->budget_value_field_rows(), [ 'key', 'label', 'type', 'unit', 'display', 'filterable', 'system' ] );
            return;
        }

        if ( 'add' === $action ) {
            $field = $this->budget_value_field_from_args( $assoc_args );

            if ( '' === $field['key'] || '' === $field['label'] ) {
                \WP_CLI::error( 'Both --key and --label are required.' );
            }

            if ( ! BudgetValueFields::upsert_custom_field( $field ) ) {
                \WP_CLI::error( 'Could not store the budget value field.' );
            }

            \WP_CLI::success( 'Budget value field stored.' );
            return;
        }

        if ( 'delete' === $action ) {
            $key = $args[1] ?? '';

            if ( ! BudgetValueFields::delete_custom_field( $key ) ) {
                \WP_CLI::error( 'Budget value field not found or cannot be deleted.' );
            }

            \WP_CLI::success( 'Budget value field deleted.' );
            return;
        }

        if ( 'reset' === $action ) {
            BudgetValueFields::update_custom_fields( [] );
            \WP_CLI::success( 'Custom budget value fields reset.' );
            return;
        }

        \WP_CLI::error( 'Use one of: list, add, delete, reset.' );
    }

    private function budget_value_field_from_args( array $assoc_args ) {
        return [
            'key'        => $assoc_args['key'] ?? '',
            'label'      => $assoc_args['label'] ?? '',
            'type'       => $assoc_args['type'] ?? 'number',
            'unit'       => $assoc_args['unit'] ?? '',
            'display'    => $assoc_args['display'] ?? true,
            'filterable' => $assoc_args['filterable'] ?? true,
            'options'    => $assoc_args['options'] ?? '',
        ];
    }

    private function budget_value_field_rows() {
        return array_values(
            array_map(
                function ( $field ) {
                    return [
                        'key'        => $field['key'],
                        'label'      => $field['label'],
                        'type'       => $field['type'],
                        'unit'       => $field['unit'],
                        'display'    => $field['display'] ? 'yes' : 'no',
                        'filterable' => $field['filterable'] ? 'yes' : 'no',
                        'system'     => ! empty( $field['system'] ) ? 'yes' : 'no',
                    ];
                },
                BudgetValueFields::all_fields()
            )
        );
    }

    private function format_items( array $assoc_args, array $items, array $fields ) {
        $format = $assoc_args['format'] ?? 'table';
        \WP_CLI\Utils\format_items( $format, $items, $fields );
    }

    private function line_value( $value, array $assoc_args ) {
        if ( 'json' === ( $assoc_args['format'] ?? '' ) ) {
            \WP_CLI::line( wp_json_encode( $value ) );
            return;
        }

        if ( is_array( $value ) ) {
            \WP_CLI::line( implode( ',', $value ) );
            return;
        }

        \WP_CLI::line( (string) $value );
    }
}
