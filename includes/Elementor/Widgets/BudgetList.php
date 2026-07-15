<?php
/**
 * Elementor widget for listing budget items.
 */

namespace SBP\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use SBP\Elementor\ElementorIntegration;
use SBP\Support\BudgetCalculator;
use SBP\Support\BudgetContext;
use SBP\Support\BudgetListingRenderer;
use SBP\Support\BudgetSummary;
use SBP\Support\CartRenderer;
use SBP\Templates\TemplateManager;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetList extends Widget_Base {

    public function get_name() {
        return 'sbp-budget-list';
    }

    public function get_title() {
        return esc_html__( 'Budget Listing', 'simple-budget-plugin-sbp' );
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return [ ElementorIntegration::CATEGORY ];
    }

    public function get_keywords() {
        return [ 'budget', 'quote', 'cart', 'list', 'listing', 'orcamento' ];
    }

    public function get_script_depends() {
        return [ 'sbp-script' ];
    }

    public function get_style_depends() {
        return [ 'sbp-styles' ];
    }

    public function has_widget_inner_wrapper(): bool {
        return false;
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_summary_controls();
        $this->register_preview_controls();
        $this->register_list_style_controls();
        $this->register_price_style_controls();
        $this->register_quantity_style_controls();
        $this->register_remove_button_style_controls();
        $this->register_summary_style_controls();
    }

    private function register_content_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Budget Listing', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'empty_message',
            [
                'label'   => esc_html__( 'Empty Message', 'simple-budget-plugin-sbp' ),
                'type'    => Controls_Manager::TEXT,
                'default' => esc_html__( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'item_layout',
            [
                'label'     => esc_html__( 'Item Layout', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'built_in',
                'options'   => [
                    'built_in' => esc_html__( 'Built-in', 'simple-budget-plugin-sbp' ),
                    'template' => esc_html__( 'Elementor Template', 'simple-budget-plugin-sbp' ),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'item_template_id',
            [
                'label'       => esc_html__( 'Item Template', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '',
                'options'     => TemplateManager::get_template_options( TemplateManager::ROLE_BUDGET_ITEM ),
                'description' => esc_html__( 'Create reusable item templates in Simple Budget > Templates.', 'simple-budget-plugin-sbp' ),
                'condition'   => [
                    'item_layout' => 'template',
                ],
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label'        => esc_html__( 'Show Image', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'item_layout' => 'built_in',
                ],
            ]
        );

        $this->add_control(
            'show_remove',
            [
                'label'        => esc_html__( 'Show Remove Button', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'item_layout' => 'built_in',
                ],
            ]
        );

        $this->add_control(
            'show_quantity',
            [
                'label'        => esc_html__( 'Show Quantity', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'item_layout' => 'built_in',
                ],
            ]
        );

        $this->add_control(
            'show_price',
            [
                'label'        => esc_html__( 'Show Price', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => [
                    'item_layout' => 'built_in',
                ],
            ]
        );

        $this->add_control(
            'price_label',
            [
                'label'     => esc_html__( 'Price Label', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Preço', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'item_layout' => 'built_in',
                    'show_price'  => 'yes',
                ],
            ]
        );

        $this->add_control(
            'quantity_label',
            [
                'label'     => esc_html__( 'Quantity Label', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Quantidade', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'item_layout'  => 'built_in',
                    'show_quantity'=> 'yes',
                ],
            ]
        );

        $this->add_control(
            'remove_text',
            [
                'label'     => esc_html__( 'Remove Text', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Remover', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'item_layout' => 'built_in',
                    'show_remove' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_summary_controls() {
        $this->start_controls_section(
            'section_summary',
            [
                'label' => esc_html__( 'Budget Summary', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'summary_mode',
            [
                'label'   => esc_html__( 'Summary', 'simple-budget-plugin-sbp' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'hidden',
                'options' => [
                    'hidden'   => esc_html__( 'Hidden', 'simple-budget-plugin-sbp' ),
                    'built_in' => esc_html__( 'Built-in', 'simple-budget-plugin-sbp' ),
                    'template' => esc_html__( 'Elementor Template', 'simple-budget-plugin-sbp' ),
                ],
            ]
        );

        $this->add_control(
            'summary_template_id',
            [
                'label'       => esc_html__( 'Summary Template', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '',
                'options'     => TemplateManager::get_template_options( TemplateManager::ROLE_BUDGET_SUMMARY ),
                'description' => esc_html__( 'Uses summary dynamic tags from the selected reusable template.', 'simple-budget-plugin-sbp' ),
                'condition'   => [
                    'summary_mode' => 'template',
                ],
            ]
        );

        $this->add_control(
            'subtotal_label',
            [
                'label'     => esc_html__( 'Subtotal Label', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Subtotal', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'summary_mode' => 'built_in',
                ],
            ]
        );

        $this->add_control(
            'estimated_range_label',
            [
                'label'     => esc_html__( 'Estimated Range Label', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Estimated range', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'summary_mode' => 'built_in',
                ],
            ]
        );

        $this->add_control(
            'adjustment_type',
            [
                'label'     => esc_html__( 'Adjustment', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'none',
                'options'   => [
                    'none'       => esc_html__( 'None', 'simple-budget-plugin-sbp' ),
                    'fixed'      => esc_html__( 'Fixed amount', 'simple-budget-plugin-sbp' ),
                    'percentage' => esc_html__( 'Percentage', 'simple-budget-plugin-sbp' ),
                ],
                'condition' => [
                    'summary_mode!' => 'hidden',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'adjustment_label',
            [
                'label'     => esc_html__( 'Adjustment Label', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Adjustment', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'summary_mode!'  => 'hidden',
                    'adjustment_type!'=> 'none',
                ],
            ]
        );

        $this->add_control(
            'adjustment_value',
            [
                'label'     => esc_html__( 'Adjustment Value', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 0,
                'step'      => 0.01,
                'default'   => 0,
                'condition' => [
                    'summary_mode!'  => 'hidden',
                    'adjustment_type!'=> 'none',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_preview_controls() {
        $this->start_controls_section(
            'section_design_preview',
            [
                'label' => esc_html__( 'Design Preview', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'preview_items',
            [
                'label'        => esc_html__( 'Preview Items in Editor', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Shows sample items only inside the Elementor editor. The frontend still uses the visitor cart.', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'preview_post_type',
            [
                'label'       => esc_html__( 'Preview Post Type', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '',
                'options'     => $this->get_preview_post_type_options(),
                'description' => esc_html__( 'Use a real post type to preview titles, images, quantity fields, and remove buttons while designing.', 'simple-budget-plugin-sbp' ),
                'condition'   => [
                    'preview_items' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'preview_item_ids',
            [
                'label'       => esc_html__( 'Preview Item IDs', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => '12, 34, 56',
                'description' => esc_html__( 'Optional comma-separated post IDs. Leave empty to use recent posts from the selected post type.', 'simple-budget-plugin-sbp' ),
                'condition'   => [
                    'preview_items' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'preview_count',
            [
                'label'     => esc_html__( 'Preview Count', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 6,
                'step'      => 1,
                'default'   => 3,
                'condition' => [
                    'preview_items' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'preview_quantity',
            [
                'label'     => esc_html__( 'Preview Quantity', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => CartRenderer::MAX_ITEM_QUANTITY,
                'step'      => 1,
                'default'   => 1,
                'condition' => [
                    'preview_items'  => 'yes',
                    'show_quantity'  => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_quantity_style_controls() {
        $this->start_controls_section(
            'section_quantity_style',
            [
                'label'     => esc_html__( 'Quantity', 'simple-budget-plugin-sbp' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'item_layout'  => 'built_in',
                    'show_quantity' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'quantity_typography',
                'selector' => '{{WRAPPER}} .sbp-quantity-field',
            ]
        );

        $this->add_control(
            'quantity_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-quantity-field' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quantity_background',
            [
                'label'     => esc_html__( 'Background', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-quantity-field' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'quantity_border',
                'selector' => '{{WRAPPER}} .sbp-quantity-field',
            ]
        );

        $this->add_responsive_control(
            'quantity_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-quantity-field' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'quantity_padding',
            [
                'label'      => esc_html__( 'Padding', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-quantity-field' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_price_style_controls() {
        $this->start_controls_section(
            'section_price_style',
            [
                'label'     => esc_html__( 'Price', 'simple-budget-plugin-sbp' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'item_layout' => 'built_in',
                    'show_price' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'price_typography',
                'selector' => '{{WRAPPER}} .sbp-cart-item__price',
            ]
        );

        $this->add_control(
            'price_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-cart-item__price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'price_value_color',
            [
                'label'     => esc_html__( 'Value Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-cart-item__price-value' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_list_style_controls() {
        $this->start_controls_section(
            'section_list_style',
            [
                'label' => esc_html__( 'List', 'simple-budget-plugin-sbp' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'item_gap',
            [
                'label'      => esc_html__( 'Item Gap', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 64,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-listing__items' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => esc_html__( 'Item Padding', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-cart-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'item_background',
            [
                'label'     => esc_html__( 'Item Background', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-cart-item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'item_border',
                'selector' => '{{WRAPPER}} .sbp-cart-item',
            ]
        );

        $this->add_responsive_control(
            'item_radius',
            [
                'label'      => esc_html__( 'Item Radius', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-cart-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_size',
            [
                'label'      => esc_html__( 'Image Size', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min' => 24,
                        'max' => 180,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-cart-item img, {{WRAPPER}} .sbp-cart-item__preview-media' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .sbp-cart-item__title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label'     => esc_html__( 'Title Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-cart-item__title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'empty_color',
            [
                'label'     => esc_html__( 'Empty Message Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-listing__empty' => 'color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();
    }

    private function register_remove_button_style_controls() {
        $this->start_controls_section(
            'section_remove_style',
            [
                'label'     => esc_html__( 'Remove Button', 'simple-budget-plugin-sbp' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'item_layout' => 'built_in',
                    'show_remove' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'remove_position',
            [
                'label'   => esc_html__( 'Position', 'simple-budget-plugin-sbp' ),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'inline_end',
                'options' => [
                    'inline_start' => [
                        'title' => esc_html__( 'Start', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'inline_end'   => [
                        'title' => esc_html__( 'End', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                    'top'          => [
                        'title' => esc_html__( 'Top', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-v-align-top',
                    ],
                    'bottom'       => [
                        'title' => esc_html__( 'Bottom', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-v-align-bottom',
                    ],
                ],
                'classes' => 'elementor-control-start-end',
            ]
        );

        $this->add_responsive_control(
            'remove_spacing',
            [
                'label'      => esc_html__( 'Spacing', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
                'range'      => [
                    'px'  => [
                        'max' => 100,
                    ],
                    'em'  => [
                        'max' => 10,
                    ],
                    'rem' => [
                        'max' => 10,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-cart-item'          => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .sbp-cart-item__actions' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->register_button_style_group( '.sbp-remove-from-cart', 'remove' );

        $this->end_controls_section();
    }

    private function register_button_style_group( $selector, $prefix ) {
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => $prefix . '_typography',
                'selector' => '{{WRAPPER}} ' . $selector,
            ]
        );

        $this->start_controls_tabs( $prefix . '_tabs' );

        $this->start_controls_tab(
            $prefix . '_normal',
            [
                'label' => esc_html__( 'Normal', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            $prefix . '_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ' . $selector => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => $prefix . '_background',
                'types'    => [ 'classic', 'gradient' ],
                'exclude'  => [ 'image' ],
                'selector' => '{{WRAPPER}} ' . $selector,
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            $prefix . '_hover',
            [
                'label' => esc_html__( 'Hover', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            $prefix . '_hover_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ' . $selector . ':hover, {{WRAPPER}} ' . $selector . ':focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => $prefix . '_hover_background',
                'types'    => [ 'classic', 'gradient' ],
                'exclude'  => [ 'image' ],
                'selector' => '{{WRAPPER}} ' . $selector . ':hover, {{WRAPPER}} ' . $selector . ':focus',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => $prefix . '_border',
                'selector'  => '{{WRAPPER}} ' . $selector,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => $prefix . '_box_shadow',
                'selector' => '{{WRAPPER}} ' . $selector,
            ]
        );

        $this->add_responsive_control(
            $prefix . '_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} ' . $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            $prefix . '_padding',
            [
                'label'      => esc_html__( 'Padding', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} ' . $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
    }

    private function register_summary_style_controls() {
        $this->start_controls_section(
            'section_summary_style',
            [
                'label'     => esc_html__( 'Built-in Summary', 'simple-budget-plugin-sbp' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'summary_mode' => 'built_in',
                ],
            ]
        );

        $this->add_responsive_control(
            'summary_gap',
            [
                'label'      => esc_html__( 'Row Gap', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 48 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-summary' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'summary_padding',
            [
                'label'      => esc_html__( 'Padding', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-summary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'summary_typography',
                'selector' => '{{WRAPPER}} .sbp-budget-summary',
            ]
        );

        $this->add_control(
            'summary_label_color',
            [
                'label'     => esc_html__( 'Label Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-summary__label, {{WRAPPER}} .sbp-budget-summary__status' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'summary_value_color',
            [
                'label'     => esc_html__( 'Value Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-summary__value' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'summary_divider_color',
            [
                'label'     => esc_html__( 'Divider Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-summary__row' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        if ( BudgetContext::has_item() || BudgetContext::has_summary() ) {
            return;
        }

        $settings = $this->get_settings_for_display();

        $empty_message = $settings['empty_message'] ?? __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' );
        $is_preview    = $this->is_design_preview_enabled( $settings );
        $preview_html  = $is_preview ? $this->render_design_preview_items( $settings ) : '';
        $preview_summary_html = $is_preview ? $this->render_design_preview_summary( $settings ) : '';
        $item_template_invalid = 'template' === ( $settings['item_layout'] ?? 'built_in' )
            && ! TemplateManager::can_render_template( absint( $settings['item_template_id'] ?? 0 ), TemplateManager::ROLE_BUDGET_ITEM );
        $summary_template_invalid = 'template' === ( $settings['summary_mode'] ?? 'hidden' )
            && ! TemplateManager::can_render_template( absint( $settings['summary_template_id'] ?? 0 ), TemplateManager::ROLE_BUDGET_SUMMARY );

        $this->add_render_attribute( 'wrapper', [
            'class'                           => 'sbp-budget-listing',
            'data-sbp-empty-message'          => $empty_message,
            'data-sbp-show-image'             => ( $settings['show_image'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-show-remove'            => ( $settings['show_remove'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-remove-text'            => $settings['remove_text'] ?? __( 'Remover', 'simple-budget-plugin-sbp' ),
            'data-sbp-remove-position'        => $this->sanitize_remove_position( $settings['remove_position'] ?? 'inline_end' ),
            'data-sbp-remove-position-tablet' => $this->sanitize_remove_position( $settings['remove_position_tablet'] ?? '', '' ),
            'data-sbp-remove-position-mobile' => $this->sanitize_remove_position( $settings['remove_position_mobile'] ?? '', '' ),
            'data-sbp-show-quantity'          => ( $settings['show_quantity'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-quantity-label'         => $settings['quantity_label'] ?? __( 'Quantidade', 'simple-budget-plugin-sbp' ),
            'data-sbp-show-price'             => ( $settings['show_price'] ?? 'no' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-price-label'            => $settings['price_label'] ?? __( 'Preço', 'simple-budget-plugin-sbp' ),
            'data-sbp-item-layout'            => 'template' === ( $settings['item_layout'] ?? '' ) ? 'template' : 'built_in',
            'data-sbp-item-template-id'       => absint( $settings['item_template_id'] ?? 0 ),
            'data-sbp-summary-mode'           => $this->sanitize_summary_mode( $settings['summary_mode'] ?? 'hidden' ),
            'data-sbp-summary-template-id'    => absint( $settings['summary_template_id'] ?? 0 ),
            'data-sbp-subtotal-label'         => $settings['subtotal_label'] ?? __( 'Subtotal', 'simple-budget-plugin-sbp' ),
            'data-sbp-adjustment-type'        => $this->sanitize_adjustment_type( $settings['adjustment_type'] ?? 'none' ),
            'data-sbp-adjustment-label'       => $settings['adjustment_label'] ?? __( 'Adjustment', 'simple-budget-plugin-sbp' ),
            'data-sbp-adjustment-value'       => $settings['adjustment_value'] ?? 0,
            'data-sbp-estimated-range-label'  => $settings['estimated_range_label'] ?? __( 'Estimated range', 'simple-budget-plugin-sbp' ),
        ] );

        if ( $is_preview ) {
            $this->add_render_attribute( 'wrapper', 'data-sbp-editor-preview', 'yes' );
        }
        ?>
        <div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
            <?php if ( $is_preview && ( $item_template_invalid || $summary_template_invalid ) ) : ?>
                <div class="elementor-alert elementor-alert-warning" role="status">
                    <?php esc_html_e( 'A selected Simple Budget template is missing or unavailable. The built-in fallback is shown.', 'simple-budget-plugin-sbp' ); ?>
                </div>
            <?php endif; ?>
            <div class="sbp-budget-listing__items" aria-live="polite">
                <?php if ( '' !== $preview_html ) : ?>
                    <?php echo $preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php else : ?>
                    <p class="sbp-budget-listing__empty"><?php echo esc_html( $empty_message ); ?></p>
                <?php endif; ?>
            </div>

            <div class="sbp-budget-listing__summary" aria-live="polite">
                <?php echo $preview_summary_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

        </div>
        <?php
    }

    private function sanitize_remove_position( $position, $fallback = 'inline_end' ) {
        $allowed = [ 'inline_start', 'inline_end', 'top', 'bottom' ];

        return in_array( $position, $allowed, true ) ? $position : $fallback;
    }

    private function sanitize_summary_mode( $mode ) {
        return in_array( $mode, [ 'hidden', 'built_in', 'template' ], true ) ? $mode : 'hidden';
    }

    private function sanitize_adjustment_type( $type ) {
        return in_array( $type, [ 'none', 'fixed', 'percentage' ], true ) ? $type : 'none';
    }

    private function is_design_preview_enabled( array $settings ) {
        return 'yes' === ( $settings['preview_items'] ?? 'yes' ) && $this->is_elementor_edit_mode();
    }

    private function is_elementor_edit_mode() {
        return class_exists( '\Elementor\Plugin' )
            && isset( \Elementor\Plugin::$instance->editor )
            && method_exists( \Elementor\Plugin::$instance->editor, 'is_edit_mode' )
            && \Elementor\Plugin::$instance->editor->is_edit_mode();
    }

    private function render_design_preview_items( array $settings ) {
        if ( 'template' === ( $settings['item_layout'] ?? 'built_in' ) ) {
            $template_html = BudgetListingRenderer::render_preview_items(
                absint( $settings['item_template_id'] ?? 0 ),
                min( 6, max( 1, absint( $settings['preview_count'] ?? 3 ) ) ),
                min( CartRenderer::MAX_ITEM_QUANTITY, max( 1, absint( $settings['preview_quantity'] ?? 2 ) ) )
            );

            if ( '' !== trim( $template_html ) ) {
                return $template_html;
            }
        }

        $preview_ids = $this->resolve_preview_item_ids( $settings );

        $display = [
            'show_image'      => ( $settings['show_image'] ?? 'yes' ) === 'yes',
            'show_remove'     => ( $settings['show_remove'] ?? 'yes' ) === 'yes',
            'remove_text'     => $settings['remove_text'] ?? __( 'Remover', 'simple-budget-plugin-sbp' ),
            'remove_position' => $this->sanitize_remove_position( $settings['remove_position'] ?? 'inline_end' ),
            'show_quantity'   => ( $settings['show_quantity'] ?? 'yes' ) === 'yes',
            'quantity_label'  => $settings['quantity_label'] ?? __( 'Quantidade', 'simple-budget-plugin-sbp' ),
            'show_price'      => ( $settings['show_price'] ?? 'no' ) === 'yes',
            'price_label'     => $settings['price_label'] ?? __( 'Preço', 'simple-budget-plugin-sbp' ),
        ];
        $quantity = min( CartRenderer::MAX_ITEM_QUANTITY, max( 1, absint( $settings['preview_quantity'] ?? 1 ) ) );
        $quantities = [];

        foreach ( $preview_ids as $preview_id ) {
            $quantities[ (string) $preview_id ] = $quantity;
        }

        if ( ! empty( $preview_ids ) ) {
            $preview_html = CartRenderer::render_items( $preview_ids, $display, $quantities, $this->sanitize_preview_post_type( $settings['preview_post_type'] ?? '' ) );

            if ( '' !== trim( $preview_html ) ) {
                return $preview_html;
            }
        }

        return CartRenderer::render_placeholder_items( min( 6, max( 1, absint( $settings['preview_count'] ?? 3 ) ) ), $display, $quantity );
    }

    private function render_design_preview_summary( array $settings ) {
        $mode = $this->sanitize_summary_mode( $settings['summary_mode'] ?? 'hidden' );

        if ( 'hidden' === $mode ) {
            return '';
        }

        if ( 'template' === $mode ) {
            $template_html = BudgetListingRenderer::render_preview_summary( absint( $settings['summary_template_id'] ?? 0 ) );

            if ( '' !== trim( $template_html ) ) {
                return '<div class="sbp-budget-listing__summary-template">' . $template_html . '</div>';
            }
        }

        $calculation = BudgetCalculator::calculate(
            [
                [
                    'quantity' => 1,
                    'pricing'  => [
                        'mode'      => 'range',
                        'price_min' => 4000,
                        'price_max' => 5200,
                        'currency'  => 'BRL',
                    ],
                ],
            ],
            [
                'type'  => $settings['adjustment_type'] ?? 'none',
                'value' => $settings['adjustment_value'] ?? 0,
                'label' => $settings['adjustment_label'] ?? __( 'Adjustment', 'simple-budget-plugin-sbp' ),
            ]
        );

        return BudgetSummary::render_builtin(
            $calculation,
            [
                'subtotal'        => $settings['subtotal_label'] ?? __( 'Subtotal', 'simple-budget-plugin-sbp' ),
                'adjustment'      => $settings['adjustment_label'] ?? __( 'Adjustment', 'simple-budget-plugin-sbp' ),
                'estimated_range' => $settings['estimated_range_label'] ?? __( 'Estimated range', 'simple-budget-plugin-sbp' ),
            ]
        );
    }

    private function resolve_preview_item_ids( array $settings ) {
        $manual_ids = $this->parse_preview_item_ids( $settings['preview_item_ids'] ?? '' );

        if ( ! empty( $manual_ids ) ) {
            return $manual_ids;
        }

        $post_type = $this->sanitize_preview_post_type( $settings['preview_post_type'] ?? '' );
        $count     = min( 6, max( 1, absint( $settings['preview_count'] ?? 3 ) ) );

        return CartRenderer::get_preview_item_ids( $post_type, $count );
    }

    private function parse_preview_item_ids( $value ) {
        if ( is_array( $value ) ) {
            return CartRenderer::normalize_product_ids( $value );
        }

        $ids = preg_split( '/[\s,]+/', (string) $value );

        return CartRenderer::normalize_product_ids( $ids );
    }

    private function sanitize_preview_post_type( $post_type ) {
        $post_type = sanitize_key( $post_type );

        if ( '' === $post_type ) {
            return '';
        }

        if ( ! post_type_exists( $post_type ) ) {
            return '';
        }

        $post_type_object = get_post_type_object( $post_type );

        return $post_type_object && $post_type_object->public ? $post_type : '';
    }

    private function get_preview_post_type_options() {
        $options = [
            '' => esc_html__( 'Use allowed post types', 'simple-budget-plugin-sbp' ),
        ];
        $post_types = get_post_types( [ 'public' => true ], 'objects' );

        foreach ( $post_types as $post_type => $object ) {
            if ( in_array( $post_type, [ 'attachment', 'elementor_library' ], true ) ) {
                continue;
            }

            $options[ $post_type ] = $object->labels->singular_name ?: $object->label;
        }

        return $options;
    }
}
