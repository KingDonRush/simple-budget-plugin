<?php
/**
 * Resolves the Simple Budget template currently being edited or rendered.
 */

namespace SBP\Templates;

if ( ! defined( 'ABSPATH' ) ) exit;

class TemplateRequest {

    public static function current_role() {
        return TemplateManager::get_template_role( self::current_document_id() );
    }

    public static function is_role( $role ) {
        return sanitize_key( $role ) === self::current_role();
    }

    public static function should_register_runtime_elements( $role ) {
        $role = sanitize_key( $role );

        if ( ! TemplateManager::is_valid_role( $role ) ) {
            return false;
        }

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return true;
        }

        if ( ! is_admin() ) {
            return true;
        }

        if ( wp_doing_ajax() && self::is_sbp_frontend_ajax() ) {
            return true;
        }

        return self::is_role( $role );
    }

    public static function current_document_id() {
        $candidate_keys = [ 'post', 'post_id', 'editor_post_id', 'elementor-preview' ];

        foreach ( $candidate_keys as $key ) {
            if ( isset( $_REQUEST[ $key ] ) && is_scalar( $_REQUEST[ $key ] ) ) {
                $post_id = absint( wp_unslash( $_REQUEST[ $key ] ) );

                if ( $post_id ) {
                    return $post_id;
                }
            }
        }

        if ( isset( $_REQUEST['actions'] ) && is_string( $_REQUEST['actions'] ) ) {
            $actions = json_decode( wp_unslash( $_REQUEST['actions'] ), true );
            $post_id = self::find_post_id( $actions );

            if ( $post_id ) {
                return $post_id;
            }
        }

        $queried_id = get_queried_object_id();

        if ( $queried_id ) {
            return absint( $queried_id );
        }

        return absint( get_the_ID() );
    }

    private static function is_sbp_frontend_ajax() {
        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';

        return in_array( $action, [ 'sbp_get_cart_products', 'sbp_render_cart_template' ], true );
    }

    private static function find_post_id( $data ) {
        if ( ! is_array( $data ) ) {
            return 0;
        }

        foreach ( [ 'post_id', 'editor_post_id' ] as $key ) {
            if ( isset( $data[ $key ] ) ) {
                $post_id = absint( $data[ $key ] );

                if ( $post_id ) {
                    return $post_id;
                }
            }
        }

        foreach ( $data as $value ) {
            $post_id = self::find_post_id( $value );

            if ( $post_id ) {
                return $post_id;
            }
        }

        return 0;
    }
}
