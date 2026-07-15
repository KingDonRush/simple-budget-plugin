<?php
/**
 * Elementor integration and widget registration.
 */

namespace SBP\Elementor;

use SBP\Elementor\DynamicTags\ItemDescription;
use SBP\Elementor\DynamicTags\ItemImage;
use SBP\Elementor\DynamicTags\ItemPrice;
use SBP\Elementor\DynamicTags\ItemTitle;
use SBP\Elementor\DynamicTags\SummaryAdjustment;
use SBP\Elementor\DynamicTags\SummaryEstimatedRange;
use SBP\Elementor\DynamicTags\SummaryStatus;
use SBP\Elementor\DynamicTags\SummarySubtotal;
use SBP\Elementor\Widgets\BudgetButton;
use SBP\Elementor\Widgets\BudgetList;
use SBP\Elementor\Widgets\BudgetQuantity;
use SBP\Templates\TemplateManager;
use SBP\Templates\TemplateRequest;

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
        add_action( 'elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ] );
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

        if ( TemplateRequest::should_register_runtime_elements( TemplateManager::ROLE_BUDGET_ITEM ) ) {
            $widgets_manager->register( new BudgetQuantity() );
        }
    }

    public function register_dynamic_tags( $dynamic_tags_manager ) {
        if ( TemplateRequest::should_register_runtime_elements( TemplateManager::ROLE_BUDGET_ITEM ) ) {
            $dynamic_tags_manager->register_group(
                'sbp-budget-item',
                [
                    'title' => esc_html__( 'Simple Budget Item', 'simple-budget-plugin-sbp' ),
                ]
            );
            $dynamic_tags_manager->register( new ItemImage() );
            $dynamic_tags_manager->register( new ItemTitle() );
            $dynamic_tags_manager->register( new ItemDescription() );
            $dynamic_tags_manager->register( new ItemPrice() );
        }

        if ( TemplateRequest::should_register_runtime_elements( TemplateManager::ROLE_BUDGET_SUMMARY ) ) {
            $dynamic_tags_manager->register_group(
                'sbp-budget-summary',
                [
                    'title' => esc_html__( 'Simple Budget Summary', 'simple-budget-plugin-sbp' ),
                ]
            );
            $dynamic_tags_manager->register( new SummarySubtotal() );
            $dynamic_tags_manager->register( new SummaryAdjustment() );
            $dynamic_tags_manager->register( new SummaryEstimatedRange() );
            $dynamic_tags_manager->register( new SummaryStatus() );
        }
    }
}
