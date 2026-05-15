<?php
/**
 * Elementor integration and widget registration.
 */

namespace SBP\Elementor;

use SBP\Elementor\Widgets\BudgetButton;
use SBP\Elementor\Widgets\BudgetList;

if ( ! defined( 'ABSPATH' ) ) exit;

class ElementorIntegration {

    const CATEGORY = 'simple-budget-plugin';

    public function init_hooks() {
        add_action( 'plugins_loaded', [ $this, 'init' ], 20 );
    }

    public function init() {
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
    }

    public function register_category( $elements_manager ) {
        $elements_manager->add_category(
            self::CATEGORY,
            [
                'title' => esc_html__( 'Simple Budget', 'simple-budget-plugin-sbp' ),
                'icon'  => 'eicon-cart',
            ]
        );
    }

    public function register_widgets( $widgets_manager ) {
        $widgets_manager->register( new BudgetButton() );
        $widgets_manager->register( new BudgetList() );
    }
}
