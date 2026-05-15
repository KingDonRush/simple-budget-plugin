<?php
/**
 * Elementor button-like widget for Simple Budget actions.
 */

namespace SBP\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use SBP\Elementor\ElementorIntegration;
use SBP\Templates\CartTemplateManager;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetButton extends Widget_Base {

    public function get_name() {
        return 'sbp-budget-button';
    }

    public function get_title() {
        return esc_html__( 'Budget Button', 'simple-budget-plugin-sbp' );
    }

    public function get_icon() {
        return 'eicon-button';
    }

    public function get_categories() {
        return [ ElementorIntegration::CATEGORY ];
    }

    public function get_keywords() {
        return [ 'budget', 'quote', 'cart', 'button', 'orcamento' ];
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
        $this->register_style_controls();
        $this->register_icon_style_controls();
    }

    private function register_content_controls() {
        $this->start_controls_section(
            'section_budget_action',
            [
                'label' => esc_html__( 'Budget Action', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'action',
            [
                'label'   => esc_html__( 'Action', 'simple-budget-plugin-sbp' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'add',
                'options' => [
                    'add'           => esc_html__( 'Add current item', 'simple-budget-plugin-sbp' ),
                    'toggle'        => esc_html__( 'Toggle current item (add/remove)', 'simple-budget-plugin-sbp' ),
                    'open_cart'     => esc_html__( 'Open budget popup', 'simple-budget-plugin-sbp' ),
                    'close_cart'    => esc_html__( 'Close budget popup', 'simple-budget-plugin-sbp' ),
                    'send_whatsapp' => esc_html__( 'Send WhatsApp budget', 'simple-budget-plugin-sbp' ),
                    'remove'        => esc_html__( 'Legacy: remove current item', 'simple-budget-plugin-sbp' ),
                ],
                'description' => esc_html__( 'Use Budget Listing controls for per-item remove buttons. The legacy remove action remains available for older templates.', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'product_source',
            [
                'label'     => esc_html__( 'Item Source', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'current',
                'options'   => [
                    'current' => esc_html__( 'Current post', 'simple-budget-plugin-sbp' ),
                    'manual'  => esc_html__( 'Manual post ID', 'simple-budget-plugin-sbp' ),
                ],
                'condition' => [
                    'action' => [ 'add', 'remove', 'toggle' ],
                ],
            ]
        );

        $this->add_control(
            'cart_template_id',
            [
                'label'       => esc_html__( 'Cart Template', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '',
                'options'     => CartTemplateManager::get_template_options(),
                'description' => esc_html__( 'Create and edit templates in Simple Budget > Templates.', 'simple-budget-plugin-sbp' ),
                'condition'   => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'cart_shell_heading',
            [
                'label'     => esc_html__( 'Cart Shell', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'cart_shell',
            [
                'label'     => esc_html__( 'Display Type', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'modal',
                'options'   => [
                    'modal'        => esc_html__( 'Centered modal', 'simple-budget-plugin-sbp' ),
                    'drawer_right' => esc_html__( 'Side cart - right', 'simple-budget-plugin-sbp' ),
                    'drawer_left'  => esc_html__( 'Side cart - left', 'simple-budget-plugin-sbp' ),
                    'bottom_sheet' => esc_html__( 'Bottom sheet', 'simple-budget-plugin-sbp' ),
                ],
                'condition' => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'cart_animation',
            [
                'label'     => esc_html__( 'Animation', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'fade_scale',
                'options'   => [
                    'fade'       => esc_html__( 'Fade', 'simple-budget-plugin-sbp' ),
                    'fade_scale' => esc_html__( 'Fade + scale', 'simple-budget-plugin-sbp' ),
                    'slide'      => esc_html__( 'Slide', 'simple-budget-plugin-sbp' ),
                    'none'       => esc_html__( 'None', 'simple-budget-plugin-sbp' ),
                ],
                'condition' => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_responsive_control(
            'cart_panel_width',
            [
                'label'      => esc_html__( 'Panel Width', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vw' ],
                'range'      => [
                    'px' => [
                        'min' => 280,
                        'max' => 980,
                    ],
                    'vw' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'size' => 560,
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'size' => 520,
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'size' => 92,
                    'unit' => 'vw',
                ],
                'condition'  => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'cart_overlay_color',
            [
                'label'     => esc_html__( 'Overlay Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#000000',
                'condition' => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'cart_overlay_opacity',
            [
                'label'      => esc_html__( 'Overlay Opacity', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ '%' ],
                'range'      => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default'    => [
                    'size' => 50,
                    'unit' => '%',
                ],
                'condition'  => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'cart_close_on_overlay',
            [
                'label'        => esc_html__( 'Close on Overlay Click', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'cart_close_on_escape',
            [
                'label'        => esc_html__( 'Close on ESC', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'cart_show_close',
            [
                'label'        => esc_html__( 'Show Outside Close Button', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'condition'    => [
                    'action' => 'open_cart',
                ],
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label'     => esc_html__( 'Post ID', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::NUMBER,
                'min'       => 1,
                'condition' => [
                    'action'         => [ 'add', 'remove', 'toggle' ],
                    'product_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'quantity',
            [
                'label'       => esc_html__( 'Quantity', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::NUMBER,
                'min'         => 1,
                'step'        => 1,
                'default'     => 1,
                'description' => esc_html__( 'Initial quantity stored when this item is added to the budget.', 'simple-budget-plugin-sbp' ),
                'condition'   => [
                    'action' => [ 'add', 'toggle' ],
                ],
            ]
        );

        $this->add_control(
            'empty_cart_behavior',
            [
                'label'     => esc_html__( 'When Budget Is Empty', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'disable',
                'options'   => [
                    'disable'    => esc_html__( 'Disable button', 'simple-budget-plugin-sbp' ),
                    'hide'       => esc_html__( 'Hide button', 'simple-budget-plugin-sbp' ),
                    'show_error' => esc_html__( 'Keep visible and show message', 'simple-budget-plugin-sbp' ),
                ],
                'condition' => [
                    'action' => 'send_whatsapp',
                ],
            ]
        );

        $this->add_control(
            'empty_cart_animation',
            [
                'label'     => esc_html__( 'Blocked Animation', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'shake',
                'options'   => [
                    'none'  => esc_html__( 'None', 'simple-budget-plugin-sbp' ),
                    'shake' => esc_html__( 'Shake', 'simple-budget-plugin-sbp' ),
                    'pulse' => esc_html__( 'Pulse', 'simple-budget-plugin-sbp' ),
                ],
                'condition' => [
                    'action'              => 'send_whatsapp',
                    'empty_cart_behavior' => 'disable',
                ],
            ]
        );

        $this->add_control(
            'text',
            [
                'label'       => esc_html__( 'Text', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [ 'active' => true ],
                'default'     => esc_html__( 'Adicionar ao orçamento', 'simple-budget-plugin-sbp' ),
                'placeholder' => esc_html__( 'Adicionar ao orçamento', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'button_type',
            [
                'label'        => esc_html__( 'Type', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SELECT,
                'default'      => '',
                'options'      => [
                    ''        => esc_html__( 'Default', 'simple-budget-plugin-sbp' ),
                    'info'    => esc_html__( 'Info', 'simple-budget-plugin-sbp' ),
                    'success' => esc_html__( 'Success', 'simple-budget-plugin-sbp' ),
                    'warning' => esc_html__( 'Warning', 'simple-budget-plugin-sbp' ),
                    'danger'  => esc_html__( 'Danger', 'simple-budget-plugin-sbp' ),
                ],
                'prefix_class' => 'elementor-button-',
            ]
        );

        $this->add_control(
            'size',
            [
                'label'   => esc_html__( 'Size', 'simple-budget-plugin-sbp' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'sm',
                'options' => [
                    'xs' => esc_html__( 'Extra Small', 'simple-budget-plugin-sbp' ),
                    'sm' => esc_html__( 'Small', 'simple-budget-plugin-sbp' ),
                    'md' => esc_html__( 'Medium', 'simple-budget-plugin-sbp' ),
                    'lg' => esc_html__( 'Large', 'simple-budget-plugin-sbp' ),
                    'xl' => esc_html__( 'Extra Large', 'simple-budget-plugin-sbp' ),
                ],
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label'       => esc_html__( 'Icon', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::ICONS,
                'skin'        => 'inline',
                'label_block' => false,
                'default'     => [
                    'value'   => 'fas fa-plus',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'button_css_id',
            [
                'label'       => esc_html__( 'Button ID', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [ 'active' => true ],
                'default'     => '',
                'description' => esc_html__( 'Optional unique ID without spaces.', 'simple-budget-plugin-sbp' ),
                'separator'   => 'before',
            ]
        );

        $this->end_controls_section();
    }

    private function register_style_controls() {
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Button', 'simple-budget-plugin-sbp' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label'        => esc_html__( 'Position', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::CHOOSE,
                'options'      => [
                    'left'    => [
                        'title' => esc_html__( 'Left', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center'  => [
                        'title' => esc_html__( 'Center', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'right'   => [
                        'title' => esc_html__( 'Right', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__( 'Stretch', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-h-align-stretch',
                    ],
                ],
                'prefix_class' => 'elementor%s-align-',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'typography',
                'global'   => [
                    'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                ],
                'selector' => '{{WRAPPER}} .elementor-button',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name'     => 'text_shadow',
                'selector' => '{{WRAPPER}} .elementor-button',
            ]
        );

        $this->start_controls_tabs( 'tabs_button_style' );

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'fill: {{VALUE}}; color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'           => 'background',
                'types'          => [ 'classic', 'gradient' ],
                'exclude'        => [ 'image' ],
                'selector'       => '{{WRAPPER}} .elementor-button',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color'      => [
                        'global' => [
                            'default' => Global_Colors::COLOR_ACCENT,
                        ],
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-button',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__( 'Hover', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'hover_color',
            [
                'label'     => esc_html__( 'Text Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button:hover svg, {{WRAPPER}} .elementor-button:focus svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'button_background_hover',
                'types'    => [ 'classic', 'gradient' ],
                'exclude'  => [ 'image' ],
                'selector' => '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus',
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus',
            ]
        );

        $this->add_control(
            'button_hover_transition_duration',
            [
                'label'      => esc_html__( 'Transition Duration', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 's', 'ms' ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-button' => 'transition-duration: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => esc_html__( 'Hover Animation', 'simple-budget-plugin-sbp' ),
                'type'  => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => 'border',
                'selector'  => '{{WRAPPER}} .elementor-button',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_padding',
            [
                'label'      => esc_html__( 'Padding', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator'  => 'before',
            ]
        );

        $this->end_controls_section();
    }

    private function register_icon_style_controls() {
        $this->start_controls_section(
            'section_icon_style',
            [
                'label'     => esc_html__( 'Icon', 'simple-budget-plugin-sbp' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'icon_align',
            [
                'label'                => esc_html__( 'Icon Position', 'simple-budget-plugin-sbp' ),
                'type'                 => Controls_Manager::CHOOSE,
                'default'              => is_rtl() ? 'row-reverse' : 'row',
                'options'              => [
                    'row'         => [
                        'title' => esc_html__( 'Start', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'row-reverse' => [
                        'title' => esc_html__( 'End', 'simple-budget-plugin-sbp' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'classes'              => 'elementor-control-start-end',
                'selectors_dictionary' => [
                    'left'  => is_rtl() ? 'row-reverse' : 'row',
                    'right' => is_rtl() ? 'row' : 'row-reverse',
                ],
                'selectors'            => [
                    '{{WRAPPER}} .elementor-button-content-wrapper' => 'flex-direction: {{VALUE}};',
                ],
                'condition'            => [
                    'text!'                 => '',
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'icon_indent',
            [
                'label'      => esc_html__( 'Icon Spacing', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'range'      => [
                    'px'  => [
                        'max' => 50,
                    ],
                    'em'  => [
                        'max' => 5,
                    ],
                    'rem' => [
                        'max' => 5,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-button .elementor-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'text!'                 => '',
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label'      => esc_html__( 'Size', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [
                        'min' => 6,
                        'max' => 80,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .elementor-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( 'tabs_icon_style' );

        $this->start_controls_tab(
            'tab_icon_normal',
            [
                'label' => esc_html__( 'Normal', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label'     => esc_html__( 'Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button-icon'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_icon_hover',
            [
                'label' => esc_html__( 'Hover', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'icon_hover_color',
            [
                'label'     => esc_html__( 'Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover .elementor-button-icon, {{WRAPPER}} .elementor-button:focus .elementor-button-icon'         => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button:hover .elementor-button-icon svg, {{WRAPPER}} .elementor-button:focus .elementor-button-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( empty( $settings['text'] ) && empty( $settings['selected_icon']['value'] ) ) {
            return;
        }

        $action     = $this->sanitize_action( $settings['action'] ?? 'add' );
        $product_id = $this->resolve_product_id( $settings );

        $this->add_render_attribute( 'wrapper', 'class', [ 'elementor-button-wrapper', 'sbp-budget-button' ] );
        $this->add_render_attribute( 'button', 'class', [ 'elementor-button', 'sbp-budget-action' ] );
        $this->add_render_attribute( 'button', 'href', '#' );
        $this->add_render_attribute( 'button', 'role', 'button' );
        $this->add_render_attribute( 'button', 'data-sbp-action', $action );

        if ( $product_id && in_array( $action, [ 'add', 'remove', 'toggle' ], true ) ) {
            $this->add_render_attribute( 'button', 'data-sbp-product-id', $product_id );
        }

        if ( in_array( $action, [ 'add', 'toggle' ], true ) ) {
            $this->add_render_attribute( 'button', 'data-sbp-quantity', max( 1, absint( $settings['quantity'] ?? 1 ) ) );
        }

        if ( 'open_cart' === $action && ! empty( $settings['cart_template_id'] ) ) {
            $this->add_render_attribute( 'button', 'data-sbp-template-id', absint( $settings['cart_template_id'] ) );
        }

        if ( 'open_cart' === $action ) {
            $this->add_cart_shell_attributes( $settings );
        }

        if ( 'send_whatsapp' === $action ) {
            $this->add_render_attribute( 'button', 'data-sbp-empty-behavior', $this->sanitize_empty_behavior( $settings['empty_cart_behavior'] ?? 'disable' ) );
            $this->add_render_attribute( 'button', 'data-sbp-empty-animation', $this->sanitize_empty_animation( $settings['empty_cart_animation'] ?? 'shake' ) );
        }

        if ( ! empty( $settings['button_css_id'] ) ) {
            $this->add_render_attribute( 'button', 'id', sanitize_html_class( $settings['button_css_id'] ) );
        }

        if ( ! empty( $settings['size'] ) ) {
            $this->add_render_attribute( 'button', 'class', 'elementor-size-' . sanitize_html_class( $settings['size'] ) );
        } else {
            $this->add_render_attribute( 'button', 'class', 'elementor-size-sm' );
        }

        if ( ! empty( $settings['hover_animation'] ) ) {
            $this->add_render_attribute( 'button', 'class', 'elementor-animation-' . sanitize_html_class( $settings['hover_animation'] ) );
        }
        ?>
        <div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
            <a <?php $this->print_render_attribute_string( 'button' ); ?>>
                <?php $this->render_text( $settings ); ?>
            </a>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        if ( '' === settings.text && ( ! settings.selected_icon || '' === settings.selected_icon.value ) ) {
            return;
        }

        view.addRenderAttribute( 'wrapper', 'class', 'elementor-button-wrapper sbp-budget-button' );
        view.addRenderAttribute( 'button', 'class', 'elementor-button sbp-budget-action' );
        view.addRenderAttribute( 'button', 'href', '#' );
        view.addRenderAttribute( 'button', 'role', 'button' );
        view.addRenderAttribute( 'button', 'data-sbp-action', settings.action || 'add' );

        if ( settings.size ) {
            view.addRenderAttribute( 'button', 'class', 'elementor-size-' + settings.size );
        } else {
            view.addRenderAttribute( 'button', 'class', 'elementor-size-sm' );
        }

        if ( settings.button_css_id ) {
            view.addRenderAttribute( 'button', 'id', settings.button_css_id );
        }

        if ( 'open_cart' === settings.action && settings.cart_template_id ) {
            view.addRenderAttribute( 'button', 'data-sbp-template-id', settings.cart_template_id );
        }

        if ( 'add' === settings.action || 'toggle' === settings.action ) {
            view.addRenderAttribute( 'button', 'data-sbp-quantity', settings.quantity || 1 );
        }

        if ( 'open_cart' === settings.action ) {
            function sbpResponsiveWidth( value, fallback ) {
                return value && value.size ? value.size + ( value.unit || 'px' ) : fallback;
            }

            var width = sbpResponsiveWidth( settings.cart_panel_width, '560px' );
            var widthTablet = sbpResponsiveWidth( settings.cart_panel_width_tablet, '' );
            var widthMobile = sbpResponsiveWidth( settings.cart_panel_width_mobile, '' );
            var opacity = settings.cart_overlay_opacity && settings.cart_overlay_opacity.size ? settings.cart_overlay_opacity.size : 50;

            view.addRenderAttribute( 'button', 'data-sbp-shell', settings.cart_shell || 'modal' );
            view.addRenderAttribute( 'button', 'data-sbp-animation', settings.cart_animation || 'fade_scale' );
            view.addRenderAttribute( 'button', 'data-sbp-panel-width', width );
            if ( widthTablet ) {
                view.addRenderAttribute( 'button', 'data-sbp-panel-width-tablet', widthTablet );
            }
            if ( widthMobile ) {
                view.addRenderAttribute( 'button', 'data-sbp-panel-width-mobile', widthMobile );
            }
            view.addRenderAttribute( 'button', 'data-sbp-overlay-color', settings.cart_overlay_color || '#000000' );
            view.addRenderAttribute( 'button', 'data-sbp-overlay-opacity', opacity );
            view.addRenderAttribute( 'button', 'data-sbp-close-overlay', settings.cart_close_on_overlay || 'yes' );
            view.addRenderAttribute( 'button', 'data-sbp-close-escape', settings.cart_close_on_escape || 'yes' );
            view.addRenderAttribute( 'button', 'data-sbp-show-close', settings.cart_show_close || 'yes' );
        }

        if ( 'send_whatsapp' === settings.action ) {
            view.addRenderAttribute( 'button', 'data-sbp-empty-behavior', settings.empty_cart_behavior || 'disable' );
            view.addRenderAttribute( 'button', 'data-sbp-empty-animation', settings.empty_cart_animation || 'shake' );
        }

        if ( settings.hover_animation ) {
            view.addRenderAttribute( 'button', 'class', 'elementor-animation-' + settings.hover_animation );
        }

        view.addRenderAttribute( 'content-wrapper', 'class', 'elementor-button-content-wrapper' );
        view.addRenderAttribute( 'icon', 'class', 'elementor-button-icon' );
        view.addRenderAttribute( 'text', 'class', 'elementor-button-text' );

        var iconHTML = elementor.helpers.renderIcon( view, settings.selected_icon, { 'aria-hidden': true }, 'i', 'object' );
        #>
        <div {{{ view.getRenderAttributeString( 'wrapper' ) }}}>
            <a {{{ view.getRenderAttributeString( 'button' ) }}}>
                <span {{{ view.getRenderAttributeString( 'content-wrapper' ) }}}>
                    <# if ( settings.selected_icon && settings.selected_icon.value && iconHTML.rendered ) { #>
                        <span {{{ view.getRenderAttributeString( 'icon' ) }}}>{{{ iconHTML.value }}}</span>
                    <# } #>
                    <# if ( settings.text ) { #>
                        <span {{{ view.getRenderAttributeString( 'text' ) }}}>{{{ settings.text }}}</span>
                    <# } #>
                </span>
            </a>
        </div>
        <?php
    }

    private function render_text( array $settings ) {
        $this->add_render_attribute( [
            'content-wrapper' => [
                'class' => 'elementor-button-content-wrapper',
            ],
            'icon'            => [
                'class' => 'elementor-button-icon',
            ],
            'text'            => [
                'class' => 'elementor-button-text',
            ],
        ] );
        ?>
        <span <?php $this->print_render_attribute_string( 'content-wrapper' ); ?>>
            <?php if ( ! empty( $settings['selected_icon']['value'] ) ) : ?>
                <span <?php $this->print_render_attribute_string( 'icon' ); ?>>
                    <?php Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                </span>
            <?php endif; ?>

            <?php if ( ! empty( $settings['text'] ) ) : ?>
                <span <?php $this->print_render_attribute_string( 'text' ); ?>><?php echo wp_kses_post( $settings['text'] ); ?></span>
            <?php endif; ?>
        </span>
        <?php
    }

    private function resolve_product_id( array $settings ) {
        if ( 'manual' === ( $settings['product_source'] ?? '' ) ) {
            return absint( $settings['product_id'] ?? 0 );
        }

        return absint( get_the_ID() ?: get_queried_object_id() );
    }

    private function sanitize_action( $action ) {
        $allowed = [ 'add', 'remove', 'toggle', 'open_cart', 'close_cart', 'send_whatsapp' ];

        return in_array( $action, $allowed, true ) ? $action : 'add';
    }

    private function add_cart_shell_attributes( array $settings ) {
        $width_desktop = $this->format_panel_width( $settings['cart_panel_width'] ?? [], '560px' );
        $width_tablet  = $this->format_panel_width( $settings['cart_panel_width_tablet'] ?? [], '' );
        $width_mobile  = $this->format_panel_width( $settings['cart_panel_width_mobile'] ?? [], '' );

        $overlay_opacity = $settings['cart_overlay_opacity']['size'] ?? 50;
        $overlay_opacity = max( 0, min( 100, (float) $overlay_opacity ) );

        $this->add_render_attribute( 'button', 'data-sbp-shell', $this->sanitize_shell( $settings['cart_shell'] ?? 'modal' ) );
        $this->add_render_attribute( 'button', 'data-sbp-animation', $this->sanitize_cart_animation( $settings['cart_animation'] ?? 'fade_scale' ) );
        $this->add_render_attribute( 'button', 'data-sbp-panel-width', $width_desktop );
        if ( '' !== $width_tablet ) {
            $this->add_render_attribute( 'button', 'data-sbp-panel-width-tablet', $width_tablet );
        }
        if ( '' !== $width_mobile ) {
            $this->add_render_attribute( 'button', 'data-sbp-panel-width-mobile', $width_mobile );
        }
        $this->add_render_attribute( 'button', 'data-sbp-overlay-color', sanitize_hex_color( $settings['cart_overlay_color'] ?? '#000000' ) ?: '#000000' );
        $this->add_render_attribute( 'button', 'data-sbp-overlay-opacity', $overlay_opacity );
        $this->add_render_attribute( 'button', 'data-sbp-close-overlay', ( $settings['cart_close_on_overlay'] ?? 'yes' ) === 'yes' ? 'yes' : 'no' );
        $this->add_render_attribute( 'button', 'data-sbp-close-escape', ( $settings['cart_close_on_escape'] ?? 'yes' ) === 'yes' ? 'yes' : 'no' );
        $this->add_render_attribute( 'button', 'data-sbp-show-close', ( $settings['cart_show_close'] ?? 'yes' ) === 'yes' ? 'yes' : 'no' );
    }

    private function format_panel_width( $value, $fallback ) {
        if ( ! is_array( $value ) || ! isset( $value['size'] ) || '' === $value['size'] ) {
            return $fallback;
        }

        $size = (float) $value['size'];
        if ( $size <= 0 ) {
            return $fallback;
        }

        $unit = isset( $value['unit'] ) && in_array( $value['unit'], [ 'px', 'vw' ], true ) ? $value['unit'] : 'px';
        $size = rtrim( rtrim( (string) $size, '0' ), '.' );

        return $size . $unit;
    }

    private function sanitize_shell( $shell ) {
        $allowed = [ 'modal', 'drawer_right', 'drawer_left', 'bottom_sheet' ];

        return in_array( $shell, $allowed, true ) ? $shell : 'modal';
    }

    private function sanitize_cart_animation( $animation ) {
        $allowed = [ 'fade', 'fade_scale', 'slide', 'none' ];

        return in_array( $animation, $allowed, true ) ? $animation : 'fade_scale';
    }

    private function sanitize_empty_behavior( $behavior ) {
        $allowed = [ 'disable', 'hide', 'show_error' ];

        return in_array( $behavior, $allowed, true ) ? $behavior : 'disable';
    }

    private function sanitize_empty_animation( $animation ) {
        $allowed = [ 'none', 'shake', 'pulse' ];

        return in_array( $animation, $allowed, true ) ? $animation : 'shake';
    }

    public function on_import( $element ) {
        return Icons_Manager::on_import_migration( $element, 'icon', 'selected_icon' );
    }
}
