<?php
/**
 * Presentation contract for aggregate budget results.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetSummary {

    public static function context( array $result ) {
        $currency = $result['currency'] ?: Pricing::DEFAULT_CURRENCY;
        $subtotal = self::money( $result['subtotal_min'] ?? null, $currency );
        $adjustment = self::money( $result['adjustment_min'] ?? null, $currency );
        $range = self::format_total( $result, $currency );

        return [
            'subtotal'        => $subtotal,
            'adjustment'      => $adjustment,
            'estimated_range' => $range,
            'status'          => self::status_text( $result ),
            'preview'         => false,
        ];
    }

    public static function render_builtin( array $result, array $labels = [] ) {
        $context = self::context( $result );
        $labels = wp_parse_args(
            $labels,
            [
                'subtotal' => __( 'Subtotal', 'simple-budget-plugin-sbp' ),
                'adjustment' => $result['adjustment']['label'] ?? __( 'Adjustment', 'simple-budget-plugin-sbp' ),
                'estimated_range' => __( 'Estimated range', 'simple-budget-plugin-sbp' ),
            ]
        );

        ob_start();
        ?>
        <div class="sbp-budget-summary sbp-budget-summary--<?php echo esc_attr( $result['status'] ?? 'partial' ); ?>">
            <?php self::render_row( $labels['subtotal'], $context['subtotal'], 'subtotal' ); ?>
            <?php if ( 'none' !== ( $result['adjustment']['type'] ?? 'none' ) ) : ?>
                <?php self::render_row( $labels['adjustment'], $context['adjustment'], 'adjustment' ); ?>
            <?php endif; ?>
            <?php self::render_row( $labels['estimated_range'], $context['estimated_range'], 'estimated-range' ); ?>
            <?php if ( '' !== $context['status'] ) : ?>
                <p class="sbp-budget-summary__status"><?php echo esc_html( $context['status'] ); ?></p>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    private static function render_row( $label, $value, $modifier ) {
        if ( '' === $value ) {
            return;
        }
        ?>
        <div class="sbp-budget-summary__row sbp-budget-summary__row--<?php echo esc_attr( $modifier ); ?>">
            <span class="sbp-budget-summary__label"><?php echo esc_html( $label ); ?></span>
            <strong class="sbp-budget-summary__value"><?php echo esc_html( $value ); ?></strong>
        </div>
        <?php
    }

    private static function format_total( array $result, $currency ) {
        $min = $result['total_min'] ?? null;
        $max = $result['total_max'] ?? null;

        if ( null === $min ) {
            return '';
        }

        $min_display = self::money( $min, $currency );

        if ( null === $max ) {
            return sprintf(
                /* translators: %s: formatted price or estimate. */
                __( 'From %s', 'simple-budget-plugin-sbp' ),
                $min_display
            );
        }

        if ( $min === $max ) {
            return $min_display;
        }

        return $min_display . ' – ' . self::money( $max, $currency );
    }

    private static function status_text( array $result ) {
        $status = sanitize_key( $result['status'] ?? 'partial' );

        if ( 'mixed_currency' === $status ) {
            return __( 'Selected items use different currencies, so a combined estimate is unavailable.', 'simple-budget-plugin-sbp' );
        }

        if ( 'partial' === $status ) {
            return __( 'Some selected items require manual pricing; the displayed amount is only the priced portion.', 'simple-budget-plugin-sbp' );
        }

        if ( 'open' === $status ) {
            return __( 'Starting estimate; the final upper limit depends on project details.', 'simple-budget-plugin-sbp' );
        }

        if ( 'range' === $status ) {
            return __( 'Estimated range based on the selected scope.', 'simple-budget-plugin-sbp' );
        }

        return __( 'Estimated total based on the selected scope.', 'simple-budget-plugin-sbp' );
    }

    private static function money( $cents, $currency ) {
        if ( null === $cents ) {
            return '';
        }

        return Pricing::format_money( ( (int) $cents ) / 100, $currency );
    }
}
