<?php
/**
 * Admin metabox view for Simple Budget pricing.
 */

namespace SBP\Support;

if ( ! defined( 'ABSPATH' ) ) exit;

class PricingMetaBox {

    public static function render( array $pricing ) {
        ?>
        <div class="sbp-pricing-fields">
            <p>
                <label for="sbp-price-mode"><strong><?php esc_html_e( 'Mode', 'simple-budget-plugin-sbp' ); ?></strong></label>
                <select id="sbp-price-mode" name="sbp_pricing[mode]" class="widefat">
                    <?php foreach ( Pricing::modes() as $mode => $label ) : ?>
                        <option value="<?php echo esc_attr( $mode ); ?>" <?php selected( $pricing['mode'], $mode ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="sbp-price"><strong><?php esc_html_e( 'Fixed / starting price', 'simple-budget-plugin-sbp' ); ?></strong></label>
                <input id="sbp-price" type="number" min="0" step="0.01" name="sbp_pricing[price]" class="widefat" value="<?php echo esc_attr( $pricing['price'] ); ?>" />
            </p>
            <p>
                <label for="sbp-price-min"><strong><?php esc_html_e( 'Range min', 'simple-budget-plugin-sbp' ); ?></strong></label>
                <input id="sbp-price-min" type="number" min="0" step="0.01" name="sbp_pricing[price_min]" class="widefat" value="<?php echo esc_attr( $pricing['price_min'] ); ?>" />
            </p>
            <p>
                <label for="sbp-price-max"><strong><?php esc_html_e( 'Range max', 'simple-budget-plugin-sbp' ); ?></strong></label>
                <input id="sbp-price-max" type="number" min="0" step="0.01" name="sbp_pricing[price_max]" class="widefat" value="<?php echo esc_attr( $pricing['price_max'] ); ?>" />
            </p>
            <p>
                <label for="sbp-price-currency"><strong><?php esc_html_e( 'Currency', 'simple-budget-plugin-sbp' ); ?></strong></label>
                <input id="sbp-price-currency" type="text" maxlength="3" name="sbp_pricing[currency]" class="widefat" value="<?php echo esc_attr( $pricing['currency'] ); ?>" />
            </p>
            <p>
                <label for="sbp-price-unit"><strong><?php esc_html_e( 'Unit', 'simple-budget-plugin-sbp' ); ?></strong></label>
                <input id="sbp-price-unit" type="text" maxlength="32" name="sbp_pricing[unit]" class="widefat" value="<?php echo esc_attr( $pricing['unit'] ); ?>" placeholder="<?php echo esc_attr__( 'per room, m², package...', 'simple-budget-plugin-sbp' ); ?>" />
            </p>
            <p>
                <label for="sbp-price-label"><strong><?php esc_html_e( 'Custom label', 'simple-budget-plugin-sbp' ); ?></strong></label>
                <input id="sbp-price-label" type="text" maxlength="80" name="sbp_pricing[label]" class="widefat" value="<?php echo esc_attr( $pricing['label'] ); ?>" />
            </p>
            <?php BudgetValueMeta::render_fields( $pricing['values'] ); ?>
        </div>
        <?php
    }
}
