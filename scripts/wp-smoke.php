<?php
/**
 * Runtime smoke checks executed through WP-CLI by scripts/verify.sh.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit( 1 );
}

function sbp_smoke_assert( $condition, $label ) {
    if ( ! $condition ) {
        throw new RuntimeException( 'SBP smoke failed: ' . $label );
    }

    echo $label . "=ok\n";
}

sbp_smoke_assert( defined( 'SBP_VERSION' ), 'plugin-version' );
sbp_smoke_assert( class_exists( '\SBP\Support\CartRenderer' ), 'cart-renderer' );
sbp_smoke_assert( class_exists( '\SBP\Support\BudgetCalculator' ), 'budget-calculator' );
sbp_smoke_assert( class_exists( '\SBP\Templates\TemplateManager' ), 'template-manager' );

$placeholder = \SBP\Support\CartRenderer::render_placeholder_items(
    2,
    [ 'show_quantity' => true, 'show_remove' => true, 'show_image' => true ],
    3
);
sbp_smoke_assert(
    false !== strpos( $placeholder, 'sbp-cart-item--preview' ) && false !== strpos( $placeholder, 'value="3"' ),
    'legacy-placeholder'
);

$shell = \SBP\Support\CartShellConfig::get_data_attributes(
    [
        'cart_panel_width' => [ 'size' => 640, 'unit' => 'px' ],
        'cart_shell'       => 'drawer_right',
    ]
);
sbp_smoke_assert( 'drawer_right' === $shell['data-sbp-shell'] && '640px' === $shell['data-sbp-panel-width'], 'shell-config' );

do_action( 'wp_enqueue_scripts' );
sbp_smoke_assert(
    wp_script_is( 'sbp-script', 'registered' ) && wp_style_is( 'sbp-styles', 'registered' ),
    'asset-registration'
);

$button = new \SBP\Elementor\Widgets\BudgetButton();
$button_controls_method = new ReflectionMethod( $button, 'register_controls' );
$button_controls_method->setAccessible( true );
$button_controls_method->invoke( $button );
$button_controls = $button->get_controls();
sbp_smoke_assert( isset( $button_controls['cart_template_id'], $button_controls['cart_shell'] ), 'button-controls' );

$listing = new \SBP\Elementor\Widgets\BudgetList();
$listing_controls_method = new ReflectionMethod( $listing, 'register_controls' );
$listing_controls_method->setAccessible( true );
$listing_controls_method->invoke( $listing );
$listing_controls = $listing->get_controls();
sbp_smoke_assert(
    isset(
        $listing_controls['item_layout'],
        $listing_controls['item_template_id'],
        $listing_controls['summary_mode'],
        $listing_controls['summary_template_id'],
        $listing_controls['adjustment_type']
    ),
    'listing-controls'
);

$tags = \Elementor\Plugin::$instance->dynamic_tags->get_tags();
$expected_tags = [
    'sbp-item-image',
    'sbp-item-title',
    'sbp-item-description',
    'sbp-item-price',
    'sbp-summary-subtotal',
    'sbp-summary-adjustment',
    'sbp-summary-estimated-range',
    'sbp-summary-status',
];
sbp_smoke_assert( empty( array_diff( $expected_tags, array_keys( $tags ) ) ), 'dynamic-tags' );

$context_value = \SBP\Support\BudgetContext::with_item(
    [ 'id' => 0, 'title' => 'Scoped item', 'quantity' => 2, 'preview' => true ],
    function () {
        return \SBP\Support\BudgetContext::item_value( 'title' );
    }
);
sbp_smoke_assert( 'Scoped item' === $context_value && ! \SBP\Support\BudgetContext::has_item(), 'item-context-scope' );

$exact = \SBP\Support\BudgetCalculator::calculate(
    [ [ 'quantity' => 2, 'pricing' => [ 'mode' => 'fixed', 'price' => 1000, 'currency' => 'BRL' ] ] ],
    [ 'type' => 'percentage', 'value' => 10 ]
);
sbp_smoke_assert( 'exact' === $exact['status'] && 220000 === $exact['total_min'] && 220000 === $exact['total_max'], 'calculator-exact' );

$range = \SBP\Support\BudgetCalculator::calculate(
    [ [ 'quantity' => 2, 'pricing' => [ 'mode' => 'range', 'price_min' => 100, 'price_max' => 200, 'currency' => 'BRL' ] ] ],
    [ 'type' => 'fixed', 'value' => 50 ]
);
sbp_smoke_assert( 'range' === $range['status'] && 25000 === $range['total_min'] && 45000 === $range['total_max'], 'calculator-range' );

$open = \SBP\Support\BudgetCalculator::calculate(
    [ [ 'pricing' => [ 'mode' => 'from', 'price' => 100, 'currency' => 'BRL' ] ] ]
);
sbp_smoke_assert( 'open' === $open['status'] && null === $open['total_max'], 'calculator-open' );

$partial = \SBP\Support\BudgetCalculator::calculate(
    [
        [ 'pricing' => [ 'mode' => 'fixed', 'price' => 100, 'currency' => 'BRL' ] ],
        [ 'pricing' => [ 'mode' => 'hidden', 'currency' => 'BRL' ] ],
    ]
);
sbp_smoke_assert( 'partial' === $partial['status'] && null === $partial['total_max'], 'calculator-partial' );

$mixed = \SBP\Support\BudgetCalculator::calculate(
    [
        [ 'pricing' => [ 'mode' => 'fixed', 'price' => 100, 'currency' => 'BRL' ] ],
        [ 'pricing' => [ 'mode' => 'fixed', 'price' => 100, 'currency' => 'USD' ] ],
    ]
);
sbp_smoke_assert( 'mixed_currency' === $mixed['status'] && null === $mixed['total_min'], 'calculator-mixed-currency' );

$invalid = \SBP\Support\BudgetCalculator::calculate(
    [ [ 'pricing' => [ 'mode' => 'range', 'price_min' => 200, 'price_max' => 100, 'currency' => 'BRL' ] ] ]
);
sbp_smoke_assert( 'partial' === $invalid['status'] && 1 === $invalid['invalid_count'], 'calculator-invalid-range' );

$administrators = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
sbp_smoke_assert( ! empty( $administrators ), 'administrator-fixture' );
wp_set_current_user( absint( $administrators[0] ) );

$created_ids = [];

try {
    $post_a = wp_insert_post(
        [
            'post_type'    => 'post',
            'post_status'  => 'publish',
            'post_title'   => 'SBP smoke alpha',
            'post_excerpt' => 'Alpha description',
        ]
    );
    $post_b = wp_insert_post(
        [
            'post_type'    => 'post',
            'post_status'  => 'publish',
            'post_title'   => 'SBP smoke beta',
            'post_excerpt' => 'Beta description',
        ]
    );
    $created_ids[] = $post_a;
    $created_ids[] = $post_b;

    update_post_meta( $post_a, \SBP\Support\Pricing::META_MODE, 'fixed' );
    update_post_meta( $post_a, \SBP\Support\Pricing::META_PRICE, 1000 );
    update_post_meta( $post_a, \SBP\Support\Pricing::META_CURRENCY, 'BRL' );
    update_post_meta( $post_b, \SBP\Support\Pricing::META_MODE, 'from' );
    update_post_meta( $post_b, \SBP\Support\Pricing::META_PRICE, 500 );
    update_post_meta( $post_b, \SBP\Support\Pricing::META_PRICE_MAX, 800 );
    update_post_meta( $post_b, \SBP\Support\Pricing::META_CURRENCY, 'BRL' );

    $item_template = \SBP\Templates\TemplateManager::create_template( \SBP\Templates\TemplateManager::ROLE_BUDGET_ITEM, 'SBP smoke item' );
    $summary_template = \SBP\Templates\TemplateManager::create_template( \SBP\Templates\TemplateManager::ROLE_BUDGET_SUMMARY, 'SBP smoke summary' );
    sbp_smoke_assert( ! is_wp_error( $item_template ) && ! is_wp_error( $summary_template ), 'template-creation' );
    $created_ids[] = $item_template;
    $created_ids[] = $summary_template;

    $rendered = \SBP\Support\BudgetListingRenderer::render(
        [ $post_a, $post_b ],
        [
            'item_layout'        => 'template',
            'item_template_id'   => $item_template,
            'summary_mode'       => 'template',
            'summary_template_id'=> $summary_template,
            'adjustment_type'    => 'percentage',
            'adjustment_value'   => 10,
        ],
        [ (string) $post_a => 2, (string) $post_b => 1 ]
    );
    sbp_smoke_assert(
        false !== strpos( $rendered['items_html'], 'SBP smoke alpha' )
        && false !== strpos( $rendered['items_html'], 'data-sbp-action="remove"' )
        && false !== strpos( $rendered['summary_html'], 'R$ 2.750,00' ),
        'template-listing-render'
    );
} finally {
    foreach ( array_reverse( array_filter( $created_ids, 'is_numeric' ) ) as $created_id ) {
        wp_delete_post( absint( $created_id ), true );
    }
}
