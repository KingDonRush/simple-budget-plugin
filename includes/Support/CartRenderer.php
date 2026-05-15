<?php
/**
 * Shared cart item rendering for AJAX, shortcodes, and Elementor widgets.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class CartRenderer {

    public static function normalize_product_ids( $product_ids ) {
        $product_ids = array_map( 'intval', (array) $product_ids );
        $product_ids = array_filter( $product_ids );

        return array_values( array_unique( $product_ids ) );
    }

    public static function normalize_display_args( $args = [] ) {
        $args = is_array( $args ) ? $args : [];

        return [
            'show_image'  => self::to_bool( $args['show_image'] ?? true ),
            'show_remove' => self::to_bool( $args['show_remove'] ?? true ),
            'remove_text' => sanitize_text_field( $args['remove_text'] ?? __( 'Remover', 'simple-budget-plugin-sbp' ) ),
        ];
    }

    public static function render_items( $product_ids, $args = [] ) {
        $product_ids = self::normalize_product_ids( $product_ids );

        if ( empty( $product_ids ) ) {
            return '';
        }

        $display = self::normalize_display_args( $args );
        $query   = self::query_items( $product_ids );

        if ( ! $query->have_posts() ) {
            return '';
        }

        ob_start();

        while ( $query->have_posts() ) {
            $query->the_post();

            $id        = get_the_ID();
            $image_url = get_the_post_thumbnail_url( $id, 'thumbnail' );
            ?>
            <div class="sbp-cart-item" data-sbp-product-id="<?php echo esc_attr( $id ); ?>">
                <?php if ( $display['show_image'] && $image_url ) : ?>
                    <div class="sbp-cart-item__media">
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>">
                    </div>
                <?php endif; ?>

                <div class="sbp-cart-item__body">
                    <h4 class="sbp-cart-item__title"><?php echo esc_html( get_the_title() ); ?></h4>
                </div>

                <?php if ( $display['show_remove'] ) : ?>
                    <button
                        type="button"
                        class="sbp-remove-from-cart sbp-budget-action"
                        data-sbp-action="remove"
                        data-sbp-product-id="<?php echo esc_attr( $id ); ?>"
                        data-product-id="<?php echo esc_attr( $id ); ?>"
                    >
                        <?php echo esc_html( $display['remove_text'] ); ?>
                    </button>
                <?php endif; ?>
            </div>
            <?php
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    private static function query_items( array $product_ids ) {
        $allowed_types = get_option( 'sbp_product_post_types', [] );
        $query_types   = ! empty( $allowed_types ) ? $allowed_types : 'any';

        return new \WP_Query([
            'post_type'      => $query_types,
            'post__in'       => $product_ids,
            'orderby'        => 'post__in',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);
    }

    private static function to_bool( $value ) {
        if ( is_bool( $value ) ) {
            return $value;
        }

        return in_array( (string) $value, [ '1', 'true', 'yes', 'on' ], true );
    }
}
