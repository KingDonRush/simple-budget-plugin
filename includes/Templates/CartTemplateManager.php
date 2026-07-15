<?php
/**
 * Backward-compatible facade for cart-modal template callers.
 */

namespace SBP\Templates;

if ( ! defined( 'ABSPATH' ) ) exit;

class CartTemplateManager {

    const ROLE_META = TemplateManager::ROLE_META;
    const ROLE_CART_MODAL = TemplateManager::ROLE_CART_MODAL;
    const EDITOR_PAGE_TEMPLATE = TemplateManager::EDITOR_PAGE_TEMPLATE;

    public static function is_elementor_available() {
        return TemplateManager::is_elementor_available();
    }

    public static function get_templates() {
        return TemplateManager::get_templates( self::ROLE_CART_MODAL );
    }

    public static function get_template_options( $include_empty = true ) {
        return TemplateManager::get_template_options( self::ROLE_CART_MODAL, $include_empty );
    }

    public static function create_cart_modal_template( $title = '' ) {
        return TemplateManager::create_template( self::ROLE_CART_MODAL, $title );
    }

    public static function get_edit_url( $template_id ) {
        return TemplateManager::get_edit_url( $template_id );
    }

    public static function is_cart_template( $template_id ) {
        return TemplateManager::is_template( $template_id, self::ROLE_CART_MODAL );
    }

    public static function can_render_template( $template_id ) {
        return TemplateManager::can_render_template( $template_id, self::ROLE_CART_MODAL );
    }

    public static function render_template( $template_id ) {
        return TemplateManager::render_template( $template_id, self::ROLE_CART_MODAL, true );
    }

    public static function delete_cart_template( $template_id ) {
        return TemplateManager::delete_template( $template_id, self::ROLE_CART_MODAL );
    }

    public static function ensure_editor_surface( $template_id ) {
        TemplateManager::ensure_editor_surface( $template_id );
    }
}
