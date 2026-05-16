<?php
/**
 * Shared cart item rendering for AJAX and Elementor widgets.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class CartRenderer {

    const MAX_CART_ITEMS = 100;
    const MAX_ITEM_QUANTITY = 999;
    const MAX_LABEL_LENGTH = 80;

    public static function normalize_product_ids( $product_ids ) {
        $product_ids = array_map( 'absint', (array) $product_ids );
        $product_ids = array_filter( $product_ids );
        $product_ids = array_values( array_unique( $product_ids ) );

        return array_slice( $product_ids, 0, self::MAX_CART_ITEMS );
    }

    public static function normalize_display_args( $args = [] ) {
        $args = is_array( $args ) ? $args : [];
        $remove_position = sanitize_key( $args['remove_position'] ?? 'inline_end' );

        return [
            'show_image'      => self::to_bool( $args['show_image'] ?? true ),
            'show_remove'     => self::to_bool( $args['show_remove'] ?? true ),
            'remove_text'     => self::normalize_label( $args['remove_text'] ?? '', __( 'Remover', 'simple-budget-plugin-sbp' ) ),
            'remove_position' => in_array( $remove_position, [ 'inline_start', 'inline_end', 'top', 'bottom' ], true ) ? $remove_position : 'inline_end',
            'show_quantity'   => self::to_bool( $args['show_quantity'] ?? false ),
            'quantity_label'  => self::normalize_label( $args['quantity_label'] ?? '', __( 'Quantidade', 'simple-budget-plugin-sbp' ) ),
        ];
    }

    public static function normalize_quantities( $quantities ) {
        $normalized = [];

        if ( ! is_array( $quantities ) ) {
            return $normalized;
        }

        foreach ( $quantities as $product_id => $quantity ) {
            $product_id = absint( $product_id );
            $quantity   = absint( $quantity );

            if ( $product_id && $quantity > 0 ) {
                $normalized[ (string) $product_id ] = min( $quantity, self::MAX_ITEM_QUANTITY );
            }
        }

        return $normalized;
    }

    public static function get_allowed_post_types() {
        $allowed_types = get_option( 'sbp_product_post_types', [] );
        $allowed_types = array_map( 'sanitize_key', (array) $allowed_types );
        $allowed_types = array_filter( array_unique( $allowed_types ) );

        return array_values( $allowed_types );
    }

    public static function is_valid_product_id( $product_id ) {
        $product_id = absint( $product_id );
        $post       = $product_id ? get_post( $product_id ) : null;

        if ( ! $post || 'publish' !== get_post_status( $post ) ) {
            return false;
        }

        $allowed_types = self::get_allowed_post_types();

        return empty( $allowed_types ) || in_array( $post->post_type, $allowed_types, true );
    }

    public static function render_items( $product_ids, $args = [], $quantities = [] ) {
        $product_ids = self::normalize_product_ids( $product_ids );

        if ( empty( $product_ids ) ) {
            return '';
        }

        $display = self::normalize_display_args( $args );
        $quantities = self::normalize_quantities( $quantities );
        $query   = self::query_items( $product_ids );

        if ( ! $query->have_posts() ) {
            return '';
        }

        ob_start();

        while ( $query->have_posts() ) {
            $query->the_post();

            $id        = get_the_ID();
            $image_url = get_the_post_thumbnail_url( $id, 'thumbnail' );
            $quantity  = $quantities[ (string) $id ] ?? 1;
            $has_actions = $display['show_quantity'] || $display['show_remove'];
            ?>
            <div class="sbp-cart-item sbp-cart-item--actions-<?php echo esc_attr( $display['remove_position'] ); ?>" data-sbp-product-id="<?php echo esc_attr( $id ); ?>">
                <?php if ( $display['show_image'] && $image_url ) : ?>
                    <div class="sbp-cart-item__media">
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>">
                    </div>
                <?php endif; ?>

                <div class="sbp-cart-item__body">
                    <h4 class="sbp-cart-item__title"><?php echo esc_html( get_the_title() ); ?></h4>
                </div>

                <?php if ( $has_actions ) : ?>
                    <div class="sbp-cart-item__actions">
                        <?php if ( $display['show_quantity'] ) : ?>
                            <label class="sbp-quantity-control">
                                <span class="sbp-quantity-control__label"><?php echo esc_html( $display['quantity_label'] ); ?></span>
                                <input
                                    type="number"
                                    class="sbp-quantity-field sbp-quantity"
                                    data-sbp-product-id="<?php echo esc_attr( $id ); ?>"
                                    min="1"
                                    step="1"
                                    value="<?php echo esc_attr( max( 1, $quantity ) ); ?>"
                                />
                            </label>
                        <?php endif; ?>

                        <?php if ( $display['show_remove'] ) : ?>
                            <button
                                type="button"
                                class="sbp-remove-from-cart sbp-budget-action"
                                data-sbp-action="remove"
                                data-sbp-product-id="<?php echo esc_attr( $id ); ?>"
                            >
                                <?php echo esc_html( $display['remove_text'] ); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    private static function query_items( array $product_ids ) {
        $allowed_types = self::get_allowed_post_types();
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

    private static function normalize_label( $value, $fallback ) {
        $value = sanitize_text_field( $value );

        if ( '' === $value ) {
            return $fallback;
        }

        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $value, 0, self::MAX_LABEL_LENGTH );
        }

        return substr( $value, 0, self::MAX_LABEL_LENGTH );
    }
}
