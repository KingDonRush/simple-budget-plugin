<?php
/**
 * Cart shell normalization for Budget Button render attributes.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class CartShellConfig {

    const DEFAULT_SHELL = 'modal';
    const DEFAULT_ANIMATION = 'fade_scale';
    const DEFAULT_PANEL_WIDTH = '560px';
    const DEFAULT_OVERLAY_COLOR = '#000000';
    const DEFAULT_OVERLAY_OPACITY = 50;

    public static function get_data_attributes( array $settings ) {
        $attributes = [
            'data-sbp-shell'           => self::sanitize_shell( $settings['cart_shell'] ?? self::DEFAULT_SHELL ),
            'data-sbp-animation'       => self::sanitize_animation( $settings['cart_animation'] ?? self::DEFAULT_ANIMATION ),
            'data-sbp-panel-width'     => self::format_panel_width( $settings['cart_panel_width'] ?? [], self::DEFAULT_PANEL_WIDTH ),
            'data-sbp-overlay-color'   => sanitize_hex_color( $settings['cart_overlay_color'] ?? self::DEFAULT_OVERLAY_COLOR ) ?: self::DEFAULT_OVERLAY_COLOR,
            'data-sbp-overlay-opacity' => self::format_percentage_slider( $settings['cart_overlay_opacity'] ?? [], self::DEFAULT_OVERLAY_OPACITY ),
            'data-sbp-close-overlay'   => ( $settings['cart_close_on_overlay'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-close-escape'    => ( $settings['cart_close_on_escape'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-show-close'      => ( $settings['cart_show_close'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
        ];

        self::maybe_add_attribute(
            $attributes,
            'data-sbp-shell-tablet',
            self::sanitize_shell( $settings['cart_shell_tablet'] ?? '', '' )
        );
        self::maybe_add_attribute(
            $attributes,
            'data-sbp-shell-mobile',
            self::sanitize_shell( $settings['cart_shell_mobile'] ?? '', '' )
        );
        self::maybe_add_attribute(
            $attributes,
            'data-sbp-animation-tablet',
            self::sanitize_animation( $settings['cart_animation_tablet'] ?? '', '' )
        );
        self::maybe_add_attribute(
            $attributes,
            'data-sbp-animation-mobile',
            self::sanitize_animation( $settings['cart_animation_mobile'] ?? '', '' )
        );
        self::maybe_add_attribute(
            $attributes,
            'data-sbp-panel-width-tablet',
            self::format_panel_width( $settings['cart_panel_width_tablet'] ?? [], '' )
        );
        self::maybe_add_attribute(
            $attributes,
            'data-sbp-panel-width-mobile',
            self::format_panel_width( $settings['cart_panel_width_mobile'] ?? [], '' )
        );
        self::maybe_add_attribute(
            $attributes,
            'data-sbp-overlay-opacity-tablet',
            self::format_percentage_slider( $settings['cart_overlay_opacity_tablet'] ?? [], '' )
        );
        self::maybe_add_attribute(
            $attributes,
            'data-sbp-overlay-opacity-mobile',
            self::format_percentage_slider( $settings['cart_overlay_opacity_mobile'] ?? [], '' )
        );

        return $attributes;
    }

    private static function maybe_add_attribute( array &$attributes, $name, $value ) {
        if ( '' !== $value ) {
            $attributes[ $name ] = $value;
        }
    }

    private static function format_panel_width( $value, $fallback ) {
        if ( ! is_array( $value ) || ! isset( $value['size'] ) || '' === $value['size'] ) {
            return $fallback;
        }

        $size = (float) $value['size'];
        if ( $size <= 0 ) {
            return $fallback;
        }

        $unit = isset( $value['unit'] ) && in_array( $value['unit'], [ 'px', 'vw' ], true ) ? $value['unit'] : 'px';
        $size = self::format_number( $size );

        return $size . $unit;
    }

    private static function format_percentage_slider( $value, $fallback ) {
        if ( ! is_array( $value ) || ! isset( $value['size'] ) || '' === $value['size'] ) {
            return $fallback;
        }

        $size = max( 0, min( 100, (float) $value['size'] ) );

        return self::format_number( $size );
    }

    private static function sanitize_shell( $shell, $fallback = self::DEFAULT_SHELL ) {
        $allowed = [ 'modal', 'drawer_right', 'drawer_left', 'bottom_sheet' ];

        return in_array( $shell, $allowed, true ) ? $shell : $fallback;
    }

    private static function sanitize_animation( $animation, $fallback = self::DEFAULT_ANIMATION ) {
        $allowed = [ 'fade', 'fade_scale', 'slide', 'none' ];

        return in_array( $animation, $allowed, true ) ? $animation : $fallback;
    }

    private static function format_number( $value ) {
        $value = (string) (float) $value;

        return false === strpos( $value, '.' ) ? $value : rtrim( rtrim( $value, '0' ), '.' );
    }
}
