<?php
/**
 * Post meta rendering and persistence for custom budget values.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetValueMeta {

    public static function render_fields( array $values ) {
        $fields = BudgetValueFields::custom_fields();

        if ( empty( $fields ) ) {
            return;
        }
        ?>
        <hr />
        <p><strong><?php esc_html_e( 'Budget values (__SBP)', 'simple-budget-plugin-sbp' ); ?></strong></p>
        <?php foreach ( $fields as $key => $field ) : ?>
            <?php self::render_field( $key, $field, $values[ $key ] ?? '' ); ?>
        <?php endforeach; ?>
        <?php
    }

    public static function save_for_post( $post_id, $raw_values ) {
        $raw_values = is_array( $raw_values ) ? $raw_values : [];

        foreach ( BudgetValueFields::custom_fields() as $key => $field ) {
            $value = BudgetValueFields::sanitize_value( $raw_values[ $key ] ?? '', $field );

            if ( '' === $value ) {
                delete_post_meta( $post_id, $key );
                continue;
            }

            update_post_meta( $post_id, $key, $value );
        }
    }

    public static function values_for_post( $post_id ) {
        $values = [];

        foreach ( BudgetValueFields::custom_fields() as $key => $field ) {
            $value = BudgetValueFields::sanitize_value( get_post_meta( $post_id, $key, true ), $field );

            if ( '' !== $value ) {
                $values[ $key ] = $value;
            }
        }

        return $values;
    }

    public static function sanitize_values_payload( $raw ) {
        $raw = is_array( $raw ) ? $raw : [];
        $values = [];

        foreach ( BudgetValueFields::custom_fields() as $key => $field ) {
            $value = BudgetValueFields::sanitize_value( $raw[ $key ] ?? '', $field );

            if ( '' !== $value ) {
                $values[ $key ] = $value;
            }
        }

        return $values;
    }

    public static function display_values( array $values ) {
        $display = [];

        foreach ( BudgetValueFields::custom_fields() as $key => $field ) {
            if ( empty( $field['display'] ) || ! isset( $values[ $key ] ) ) {
                continue;
            }

            $value = BudgetValueFields::format_value( $values[ $key ], $field );

            if ( '' !== $value ) {
                $display[] = $field['label'] . ': ' . $value;
            }
        }

        return $display;
    }

    private static function render_field( $key, array $field, $value ) {
        $field_id = 'sbp-value-' . sanitize_html_class( $key );
        ?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>">
                <strong><?php echo esc_html( $field['label'] ); ?></strong>
            </label>
            <?php if ( 'select' === $field['type'] ) : ?>
                <?php self::render_select( $field_id, $key, $field, $value ); ?>
            <?php else : ?>
                <input
                    id="<?php echo esc_attr( $field_id ); ?>"
                    type="<?php echo 'number' === $field['type'] ? 'number' : 'text'; ?>"
                    <?php echo 'number' === $field['type'] ? 'min="0" step="0.01"' : 'maxlength="120"'; ?>
                    name="sbp_pricing_values[<?php echo esc_attr( $key ); ?>]"
                    class="widefat"
                    value="<?php echo esc_attr( $value ); ?>"
                />
            <?php endif; ?>
            <?php if ( '' !== $field['unit'] ) : ?>
                <span class="description"><?php echo esc_html( $field['unit'] ); ?></span>
            <?php endif; ?>
        </p>
        <?php
    }

    private static function render_select( $field_id, $key, array $field, $value ) {
        ?>
        <select id="<?php echo esc_attr( $field_id ); ?>" name="sbp_pricing_values[<?php echo esc_attr( $key ); ?>]" class="widefat">
            <option value=""><?php esc_html_e( 'Not set', 'simple-budget-plugin-sbp' ); ?></option>
            <?php foreach ( $field['options'] as $option ) : ?>
                <option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $value, $option['value'] ); ?>>
                    <?php echo esc_html( $option['label'] ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}
