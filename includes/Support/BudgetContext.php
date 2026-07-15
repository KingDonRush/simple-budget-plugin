<?php
/**
 * Scoped render contexts used by repeated Elementor budget templates.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetContext {

    private static $items = [];
    private static $summaries = [];

    public static function with_item( array $item, callable $callback ) {
        self::$items[] = self::normalize_item( $item );

        try {
            return $callback();
        } finally {
            array_pop( self::$items );
        }
    }

    public static function with_summary( array $summary, callable $callback ) {
        self::$summaries[] = self::normalize_summary( $summary );

        try {
            return $callback();
        } finally {
            array_pop( self::$summaries );
        }
    }

    public static function current_item() {
        return empty( self::$items ) ? null : end( self::$items );
    }

    public static function current_summary() {
        return empty( self::$summaries ) ? null : end( self::$summaries );
    }

    public static function has_item() {
        return null !== self::current_item();
    }

    public static function has_summary() {
        return null !== self::current_summary();
    }

    public static function preview_item() {
        return [
            'id'          => 0,
            'quantity'    => 2,
            'preview'     => true,
            'title'       => __( 'Budget item title', 'simple-budget-plugin-sbp' ),
            'description' => __( 'Short description used while composing this reusable item template.', 'simple-budget-plugin-sbp' ),
            'price'       => __( 'From R$ 1.200,00', 'simple-budget-plugin-sbp' ),
        ];
    }

    public static function preview_summary() {
        return [
            'subtotal'       => 'R$ 4.000,00',
            'adjustment'     => 'R$ 400,00',
            'estimated_range'=> 'R$ 4.400,00 – R$ 5.600,00',
            'status'          => __( 'Estimated range based on the selected scope.', 'simple-budget-plugin-sbp' ),
            'preview'         => true,
        ];
    }

    public static function item_value( $key ) {
        $item = self::current_item();
        $item = $item ?: self::preview_item();
        $key = sanitize_key( $key );

        if ( isset( $item[ $key ] ) && '' !== $item[ $key ] ) {
            return $item[ $key ];
        }

        $post_id = absint( $item['id'] ?? 0 );

        if ( ! $post_id ) {
            return '';
        }

        if ( 'title' === $key ) {
            return get_the_title( $post_id );
        }

        if ( 'description' === $key ) {
            $description = get_the_excerpt( $post_id );

            if ( '' === trim( $description ) ) {
                $post = get_post( $post_id );
                $description = $post ? wp_trim_words( wp_strip_all_tags( $post->post_content ), 24 ) : '';
            }

            return $description;
        }

        if ( 'price' === $key ) {
            return Pricing::display_for_post( $post_id );
        }

        return '';
    }

    public static function item_image() {
        $item = self::current_item();
        $item = $item ?: self::preview_item();
        $post_id = absint( $item['id'] ?? 0 );

        if ( $post_id ) {
            $image_id = get_post_thumbnail_id( $post_id );

            if ( $image_id ) {
                return [
                    'id'  => $image_id,
                    'url' => wp_get_attachment_image_url( $image_id, 'full' ),
                ];
            }
        }

        $placeholder = class_exists( '\Elementor\Utils' ) ? \Elementor\Utils::get_placeholder_image_src() : '';

        return [
            'id'  => 0,
            'url' => $placeholder,
        ];
    }

    public static function summary_value( $key ) {
        $summary = self::current_summary();
        $summary = $summary ?: self::preview_summary();
        $key = sanitize_key( $key );

        return isset( $summary[ $key ] ) ? (string) $summary[ $key ] : '';
    }

    private static function normalize_item( array $item ) {
        return [
            'id'          => absint( $item['id'] ?? 0 ),
            'quantity'    => min( CartRenderer::MAX_ITEM_QUANTITY, max( 1, absint( $item['quantity'] ?? 1 ) ) ),
            'preview'     => ! empty( $item['preview'] ),
            'title'       => sanitize_text_field( $item['title'] ?? '' ),
            'description' => sanitize_text_field( $item['description'] ?? '' ),
            'price'       => sanitize_text_field( $item['price'] ?? '' ),
        ];
    }

    private static function normalize_summary( array $summary ) {
        return [
            'subtotal'        => sanitize_text_field( $summary['subtotal'] ?? '' ),
            'adjustment'      => sanitize_text_field( $summary['adjustment'] ?? '' ),
            'estimated_range' => sanitize_text_field( $summary['estimated_range'] ?? '' ),
            'status'          => sanitize_text_field( $summary['status'] ?? '' ),
            'preview'         => ! empty( $summary['preview'] ),
        ];
    }
}
