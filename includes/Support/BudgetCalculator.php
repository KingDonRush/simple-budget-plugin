<?php
/**
 * Deterministic aggregate budget calculation using integer cents.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetCalculator {

    public static function calculate( array $items, array $adjustment = [] ) {
        $adjustment = self::normalize_adjustment( $adjustment );
        $subtotal_min = 0;
        $subtotal_max = 0;
        $has_open_max = false;
        $unpriced_count = 0;
        $invalid_count = 0;
        $priced_count = 0;
        $currencies = [];

        foreach ( array_slice( $items, 0, CartRenderer::MAX_CART_ITEMS ) as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $quantity = min( CartRenderer::MAX_ITEM_QUANTITY, max( 1, absint( $item['quantity'] ?? 1 ) ) );
            $bounds = self::price_bounds( $item['pricing'] ?? [] );

            if ( ! empty( $bounds['invalid'] ) ) {
                $invalid_count++;
            }

            if ( null === $bounds['min'] ) {
                $unpriced_count++;
                $has_open_max = true;
                continue;
            }

            $priced_count++;
            $currencies[ $bounds['currency'] ] = true;
            $subtotal_min += $bounds['min'] * $quantity;

            if ( null === $bounds['max'] ) {
                $has_open_max = true;
            } else {
                $subtotal_max += $bounds['max'] * $quantity;
            }
        }

        $currency = 1 === count( $currencies ) ? (string) array_key_first( $currencies ) : '';
        $mixed_currency = count( $currencies ) > 1;

        if ( $mixed_currency || 0 === $priced_count ) {
            return [
                'currency'       => $currency,
                'subtotal_min'   => null,
                'subtotal_max'   => null,
                'adjustment_min' => null,
                'adjustment_max' => null,
                'total_min'      => null,
                'total_max'      => null,
                'status'         => $mixed_currency ? 'mixed_currency' : 'partial',
                'unpriced_count' => $unpriced_count,
                'invalid_count'  => $invalid_count,
                'priced_count'   => $priced_count,
                'adjustment'     => $adjustment,
            ];
        }

        $subtotal_max = $has_open_max ? null : $subtotal_max;
        $adjustment_min = self::adjustment_cents( $subtotal_min, $adjustment );
        $adjustment_max = null === $subtotal_max ? null : self::adjustment_cents( $subtotal_max, $adjustment );
        $total_min = $subtotal_min + $adjustment_min;
        $total_max = null === $subtotal_max ? null : $subtotal_max + $adjustment_max;

        if ( $unpriced_count > 0 || $invalid_count > 0 ) {
            $status = 'partial';
        } elseif ( null === $total_max ) {
            $status = 'open';
        } elseif ( $total_min !== $total_max ) {
            $status = 'range';
        } else {
            $status = 'exact';
        }

        return [
            'currency'       => $currency,
            'subtotal_min'   => $subtotal_min,
            'subtotal_max'   => $subtotal_max,
            'adjustment_min' => $adjustment_min,
            'adjustment_max' => $adjustment_max,
            'total_min'      => $total_min,
            'total_max'      => $total_max,
            'status'         => $status,
            'unpriced_count' => $unpriced_count,
            'invalid_count'  => $invalid_count,
            'priced_count'   => $priced_count,
            'adjustment'     => $adjustment,
        ];
    }

    public static function normalize_adjustment( array $adjustment ) {
        $type = sanitize_key( $adjustment['type'] ?? 'none' );

        if ( ! in_array( $type, [ 'none', 'fixed', 'percentage' ], true ) ) {
            $type = 'none';
        }

        $value = Pricing::sanitize_decimal( $adjustment['value'] ?? '' );

        if ( 'percentage' === $type ) {
            $value = min( 100, max( 0, (float) $value ) );
        }

        return [
            'type'  => $type,
            'value' => 'none' === $type || '' === $value ? 0 : (float) $value,
            'label' => sanitize_text_field( $adjustment['label'] ?? __( 'Adjustment', 'simple-budget-plugin-sbp' ) ),
        ];
    }

    private static function price_bounds( $pricing ) {
        $pricing = is_array( $pricing ) ? $pricing : [];
        $mode = sanitize_key( $pricing['mode'] ?? 'hidden' );
        $currency = Pricing::sanitize_currency( $pricing['currency'] ?? Pricing::DEFAULT_CURRENCY );
        $min = null;
        $max = null;
        $invalid = false;

        if ( 'fixed' === $mode ) {
            $min = self::to_cents( $pricing['price'] ?? '' );
            $max = $min;
        } elseif ( 'from' === $mode ) {
            $min = self::to_cents( $pricing['price'] ?? '' );
            $max = self::to_cents( $pricing['price_max'] ?? '' );
        } elseif ( 'range' === $mode ) {
            $min = self::to_cents( $pricing['price_min'] ?? '' );
            $max = self::to_cents( $pricing['price_max'] ?? '' );
        }

        if ( null !== $min && null !== $max && $max < $min ) {
            $max = null;
            $invalid = true;
        }

        return [
            'min'      => $min,
            'max'      => $max,
            'currency' => $currency,
            'invalid'  => $invalid,
        ];
    }

    private static function adjustment_cents( $base_cents, array $adjustment ) {
        if ( 'fixed' === $adjustment['type'] ) {
            return self::to_cents( $adjustment['value'] ) ?: 0;
        }

        if ( 'percentage' === $adjustment['type'] ) {
            return (int) round( $base_cents * $adjustment['value'] / 100 );
        }

        return 0;
    }

    private static function to_cents( $value ) {
        $value = Pricing::sanitize_decimal( $value );

        return '' === $value ? null : (int) round( (float) $value * 100 );
    }
}
