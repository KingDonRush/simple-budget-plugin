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
        $this->register_list_style_controls();
        $this->register_quantity_style_controls();
        $this->register_remove_button_style_controls();
        $this->register_submit_button_style_controls();
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
            'show_image',
            [
                'label'        => esc_html__( 'Show Image', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
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
            ]
        );

        $this->add_control(
            'quantity_label',
            [
                'label'     => esc_html__( 'Quantity Label', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Quantidade', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'show_quantity' => 'yes',
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
                    'show_remove' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_submit',
            [
                'label'        => esc_html__( 'Show Submit Button', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'simple-budget-plugin-sbp' ),
                'label_off'    => esc_html__( 'No', 'simple-budget-plugin-sbp' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            ]
        );

        $this->add_control(
            'submit_empty_behavior',
            [
                'label'     => esc_html__( 'When Budget Is Empty', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'hide',
                'options'   => [
                    'hide'       => esc_html__( 'Hide button', 'simple-budget-plugin-sbp' ),
                    'disable'    => esc_html__( 'Disable button', 'simple-budget-plugin-sbp' ),
                    'show_error' => esc_html__( 'Keep visible and show message', 'simple-budget-plugin-sbp' ),
                ],
                'condition' => [
                    'show_submit' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'submit_empty_animation',
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
                    'show_submit'           => 'yes',
                    'submit_empty_behavior' => 'disable',
                ],
            ]
        );

        $this->add_control(
            'submit_text',
            [
                'label'     => esc_html__( 'Submit Text', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Enviar orçamento via WhatsApp', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'show_submit' => 'yes',
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
                    '{{WRAPPER}} .sbp-cart-item img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
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
                    'show_remove' => 'yes',
                ],
            ]
        );

        $this->add_control(
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

    private function register_submit_button_style_controls() {
        $this->start_controls_section(
            'section_submit_style',
            [
                'label'     => esc_html__( 'Submit Button', 'simple-budget-plugin-sbp' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_submit' => 'yes',
                ],
            ]
        );

        $this->register_button_style_group( '.sbp-budget-listing__submit.elementor-button', 'submit' );

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

    protected function render() {
        $settings = $this->get_settings_for_display();

        $empty_message = $settings['empty_message'] ?? __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' );
        $submit_text   = $settings['submit_text'] ?? __( 'Enviar orçamento via WhatsApp', 'simple-budget-plugin-sbp' );

        $this->add_render_attribute( 'wrapper', [
            'class'                           => 'sbp-budget-listing',
            'data-sbp-empty-message'          => $empty_message,
            'data-sbp-show-image'             => ( $settings['show_image'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-show-remove'            => ( $settings['show_remove'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-remove-text'            => $settings['remove_text'] ?? __( 'Remover', 'simple-budget-plugin-sbp' ),
            'data-sbp-remove-position'        => $this->sanitize_remove_position( $settings['remove_position'] ?? 'inline_end' ),
            'data-sbp-show-quantity'          => ( $settings['show_quantity'] ?? 'yes' ) === 'yes' ? 'yes' : 'no',
            'data-sbp-quantity-label'         => $settings['quantity_label'] ?? __( 'Quantidade', 'simple-budget-plugin-sbp' ),
            'data-sbp-submit-empty-behavior'  => $this->sanitize_empty_behavior( $settings['submit_empty_behavior'] ?? 'hide' ),
            'data-sbp-submit-empty-animation' => $this->sanitize_empty_animation( $settings['submit_empty_animation'] ?? 'shake' ),
        ] );
        ?>
        <div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
            <div class="sbp-budget-listing__items" aria-live="polite">
                <p class="sbp-budget-listing__empty"><?php echo esc_html( $empty_message ); ?></p>
            </div>

            <?php if ( ( $settings['show_submit'] ?? 'yes' ) === 'yes' ) : ?>
                <a href="#" class="elementor-button sbp-budget-action sbp-budget-listing__submit sbp-is-hidden" data-sbp-action="send_whatsapp" data-sbp-empty-behavior="<?php echo esc_attr( $this->sanitize_empty_behavior( $settings['submit_empty_behavior'] ?? 'hide' ) ); ?>" data-sbp-empty-animation="<?php echo esc_attr( $this->sanitize_empty_animation( $settings['submit_empty_animation'] ?? 'shake' ) ); ?>" role="button">
                    <span class="elementor-button-content-wrapper">
                        <span class="elementor-button-text"><?php echo esc_html( $submit_text ); ?></span>
                    </span>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var emptyMessage = settings.empty_message || '<?php echo esc_js( __( 'Seu carrinho está vazio.', 'simple-budget-plugin-sbp' ) ); ?>';
        var submitText = settings.submit_text || '<?php echo esc_js( __( 'Enviar orçamento via WhatsApp', 'simple-budget-plugin-sbp' ) ); ?>';
        #>
        <div class="sbp-budget-listing"
            data-sbp-empty-message="{{ emptyMessage }}"
            data-sbp-show-image="{{ settings.show_image || 'yes' }}"
            data-sbp-show-remove="{{ settings.show_remove || 'yes' }}"
            data-sbp-remove-text="{{ settings.remove_text || '<?php echo esc_js( __( 'Remover', 'simple-budget-plugin-sbp' ) ); ?>' }}"
            data-sbp-remove-position="{{ settings.remove_position || 'inline_end' }}"
            data-sbp-show-quantity="{{ settings.show_quantity || 'yes' }}"
            data-sbp-quantity-label="{{ settings.quantity_label || '<?php echo esc_js( __( 'Quantidade', 'simple-budget-plugin-sbp' ) ); ?>' }}"
            data-sbp-submit-empty-behavior="{{ settings.submit_empty_behavior || 'hide' }}"
            data-sbp-submit-empty-animation="{{ settings.submit_empty_animation || 'shake' }}"
        >
            <div class="sbp-budget-listing__items" aria-live="polite">
                <p class="sbp-budget-listing__empty">{{{ emptyMessage }}}</p>
            </div>

            <# if ( 'yes' === settings.show_submit ) { #>
                <a href="#" class="elementor-button sbp-budget-action sbp-budget-listing__submit" data-sbp-action="send_whatsapp" data-sbp-empty-behavior="{{ settings.submit_empty_behavior || 'hide' }}" data-sbp-empty-animation="{{ settings.submit_empty_animation || 'shake' }}" role="button">
                    <span class="elementor-button-content-wrapper">
                        <span class="elementor-button-text">{{{ submitText }}}</span>
                    </span>
                </a>
            <# } #>
        </div>
        <?php
    }

    private function sanitize_remove_position( $position ) {
        $allowed = [ 'inline_start', 'inline_end', 'top', 'bottom' ];

        return in_array( $position, $allowed, true ) ? $position : 'inline_end';
    }

    private function sanitize_empty_behavior( $behavior ) {
        $allowed = [ 'hide', 'disable', 'show_error' ];

        return in_array( $behavior, $allowed, true ) ? $behavior : 'hide';
    }

    private function sanitize_empty_animation( $animation ) {
        $allowed = [ 'none', 'shake', 'pulse' ];

        return in_array( $animation, $allowed, true ) ? $animation : 'shake';
    }
}
