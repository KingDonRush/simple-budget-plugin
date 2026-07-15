<?php
/**
 * Finds Elementor documents that still depend on the removed Listing submit.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class InlineSubmitAudit {

    /**
     * Return post IDs whose Elementor data contains an enabled legacy submit.
     *
     * @param int $limit Maximum number of Elementor documents to inspect.
     * @return int[]
     */
    public static function find_affected_posts( $limit = 200 ) {
        global $wpdb;

        $limit = max( 1, min( 1000, absint( $limit ) ) );
        $like = '%' . $wpdb->esc_like( 'show_submit' ) . '%';
        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT pm.post_id
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = %s
                    AND pm.meta_value LIKE %s
                    AND p.post_type <> 'revision'
                    AND p.post_status NOT IN ('trash', 'auto-draft')
                ORDER BY pm.post_id ASC
                LIMIT %d",
                '_elementor_data',
                $like,
                $limit
            )
        );

        $affected = [];

        foreach ( $post_ids as $post_id ) {
            $elements = json_decode( (string) get_post_meta( $post_id, '_elementor_data', true ), true );

            if ( is_array( $elements ) && self::elements_use_inline_submit( $elements ) ) {
                $affected[] = absint( $post_id );
            }
        }

        return array_values( array_unique( array_filter( $affected ) ) );
    }

    /**
     * @param array $elements Elementor elements at any nesting level.
     */
    private static function elements_use_inline_submit( array $elements ) {
        foreach ( $elements as $element ) {
            if ( ! is_array( $element ) ) {
                continue;
            }

            if (
                'sbp-budget-list' === ( $element['widgetType'] ?? '' )
                && 'yes' === ( $element['settings']['show_submit'] ?? '' )
            ) {
                return true;
            }

            if (
                ! empty( $element['elements'] )
                && is_array( $element['elements'] )
                && self::elements_use_inline_submit( $element['elements'] )
            ) {
                return true;
            }
        }

        return false;
    }
}
