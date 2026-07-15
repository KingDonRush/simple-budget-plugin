<?php
/**
 * Context-aware quantity control for Budget Item Templates.
 */

namespace SBP\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use SBP\Elementor\ElementorIntegration;
use SBP\Support\BudgetContext;
use SBP\Support\CartRenderer;
use SBP\Templates\TemplateManager;
use SBP\Templates\TemplateRequest;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetQuantity extends Widget_Base {

    public function get_name() {
        return 'sbp-budget-quantity';
    }

    public function get_title() {
        return esc_html__( 'Budget Quantity', 'simple-budget-plugin-sbp' );
    }

    public function get_icon() {
        return 'eicon-number-field';
    }

    public function get_categories() {
        return [ ElementorIntegration::CATEGORY ];
    }

    public function get_keywords() {
        return [ 'budget', 'quantity', 'stepper', 'item', 'orcamento' ];
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
        $this->start_controls_section(
            'section_quantity',
            [
                'label' => esc_html__( 'Quantity', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->add_control(
            'mode',
            [
                'label'   => esc_html__( 'Mode', 'simple-budget-plugin-sbp' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'stepper',
                'options' => [
                    'input'   => esc_html__( 'Number Input', 'simple-budget-plugin-sbp' ),
                    'stepper' => esc_html__( 'Minus / Value / Plus', 'simple-budget-plugin-sbp' ),
                ],
            ]
        );

        $this->add_control(
            'show_label',
            [
                'label'        => esc_html__( 'Show Label', 'simple-budget-plugin-sbp' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

        $this->add_control(
            'label',
            [
                'label'     => esc_html__( 'Label', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => esc_html__( 'Quantity', 'simple-budget-plugin-sbp' ),
                'condition' => [
                    'show_label' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'decrement_icon',
            [
                'label'     => esc_html__( 'Decrease Icon', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::ICONS,
                'condition' => [
                    'mode' => 'stepper',
                ],
            ]
        );

        $this->add_control(
            'decrement_text',
            [
                'label'       => esc_html__( 'Decrease Text', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '−',
                'description' => esc_html__( 'Used when no decrease icon is selected.', 'simple-budget-plugin-sbp' ),
                'condition'   => [
                    'mode' => 'stepper',
                ],
            ]
        );

        $this->add_control(
            'increment_icon',
            [
                'label'     => esc_html__( 'Increase Icon', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::ICONS,
                'condition' => [
                    'mode' => 'stepper',
                ],
            ]
        );

        $this->add_control(
            'increment_text',
            [
                'label'       => esc_html__( 'Increase Text', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '+',
                'description' => esc_html__( 'Used when no increase icon is selected.', 'simple-budget-plugin-sbp' ),
                'condition'   => [
                    'mode' => 'stepper',
                ],
            ]
        );

        $this->add_control(
            'preview_quantity',
            [
                'label'       => esc_html__( 'Preview Quantity', 'simple-budget-plugin-sbp' ),
                'type'        => Controls_Manager::NUMBER,
                'min'         => 1,
                'max'         => CartRenderer::MAX_ITEM_QUANTITY,
                'step'        => 1,
                'default'     => 2,
                'description' => esc_html__( 'Editor-only value. Runtime quantity comes from the visitor budget.', 'simple-budget-plugin-sbp' ),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_wrapper_style',
            [
                'label' => esc_html__( 'Control', 'simple-budget-plugin-sbp' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'control_gap',
            [
                'label'      => esc_html__( 'Gap', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 40 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-quantity' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .sbp-budget-quantity__control' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'wrapper_background',
            [
                'label'     => esc_html__( 'Background', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-quantity' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'wrapper_border',
                'selector' => '{{WRAPPER}} .sbp-budget-quantity',
            ]
        );

        $this->add_responsive_control(
            'wrapper_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-quantity' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'wrapper_padding',
            [
                'label'      => esc_html__( 'Padding', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-quantity' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .sbp-budget-quantity__label',
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label'     => esc_html__( 'Label Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-quantity__label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_value_style',
            [
                'label' => esc_html__( 'Value', 'simple-budget-plugin-sbp' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'value_typography',
                'selector' => '{{WRAPPER}} .sbp-budget-quantity__input',
            ]
        );

        $this->add_control(
            'value_color',
            [
                'label'     => esc_html__( 'Text Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-quantity__input' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'value_background',
            [
                'label'     => esc_html__( 'Background', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-quantity__input' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'value_border',
                'selector' => '{{WRAPPER}} .sbp-budget-quantity__input',
            ]
        );

        $this->add_responsive_control(
            'value_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-quantity__input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'value_padding',
            [
                'label'      => esc_html__( 'Padding', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-quantity__input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'value_width',
            [
                'label'      => esc_html__( 'Width', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 32, 'max' => 160 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-quantity__input' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_buttons_style',
            [
                'label'     => esc_html__( 'Stepper Buttons', 'simple-budget-plugin-sbp' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'mode' => 'stepper',
                ],
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label'     => esc_html__( 'Color', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-quantity__step' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .sbp-budget-quantity__step svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .sbp-budget-quantity__step',
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label'     => esc_html__( 'Background', 'simple-budget-plugin-sbp' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sbp-budget-quantity__step' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .sbp-budget-quantity__step',
            ]
        );

        $this->add_responsive_control(
            'button_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-quantity__step' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_size',
            [
                'label'      => esc_html__( 'Size', 'simple-budget-plugin-sbp' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 24, 'max' => 96 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .sbp-budget-quantity__step' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $item = BudgetContext::current_item();
        $is_preview = ! $item && TemplateRequest::is_role( TemplateManager::ROLE_BUDGET_ITEM );

        if ( ! $item && ! $is_preview ) {
            return;
        }

        $item = $item ?: BudgetContext::preview_item();
        $quantity = $is_preview
            ? absint( $settings['preview_quantity'] ?? 2 )
            : absint( $item['quantity'] ?? 1 );
        $quantity = min( CartRenderer::MAX_ITEM_QUANTITY, max( 1, $quantity ) );
        $product_id = absint( $item['id'] ?? 0 );
        $mode = 'input' === ( $settings['mode'] ?? '' ) ? 'input' : 'stepper';
        $label = sanitize_text_field( $settings['label'] ?? __( 'Quantity', 'simple-budget-plugin-sbp' ) );

        $this->add_render_attribute( 'wrapper', 'class', [ 'sbp-budget-quantity', 'sbp-budget-quantity--' . $mode ] );
        $this->add_render_attribute( 'wrapper', 'data-sbp-product-id', $product_id );

        if ( $is_preview ) {
            $this->add_render_attribute( 'wrapper', 'data-sbp-editor-preview', 'yes' );
        }

        $this->add_render_attribute( 'input', [
            'class'               => [ 'sbp-budget-quantity__input', 'sbp-quantity-field', 'sbp-quantity' ],
            'type'                => 'number',
            'min'                 => 1,
            'max'                 => CartRenderer::MAX_ITEM_QUANTITY,
            'step'                => 1,
            'value'               => $quantity,
            'data-sbp-product-id' => $product_id,
            'aria-label'          => $label,
        ] );
        ?>
        <div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
            <span class="sbp-budget-quantity__label<?php echo ( $settings['show_label'] ?? '' ) === 'yes' ? '' : ' screen-reader-text'; ?>">
                <?php echo esc_html( $label ); ?>
            </span>
            <span class="sbp-budget-quantity__control">
                <?php if ( 'stepper' === $mode ) : ?>
                    <?php $this->render_step_button( 'decrement', -1, $settings['decrement_icon'] ?? [], __( 'Decrease quantity', 'simple-budget-plugin-sbp' ), $settings['decrement_text'] ?? '−' ); ?>
                <?php endif; ?>
                <input <?php $this->print_render_attribute_string( 'input' ); ?> />
                <?php if ( 'stepper' === $mode ) : ?>
                    <?php $this->render_step_button( 'increment', 1, $settings['increment_icon'] ?? [], __( 'Increase quantity', 'simple-budget-plugin-sbp' ), $settings['increment_text'] ?? '+' ); ?>
                <?php endif; ?>
            </span>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var mode = 'input' === settings.mode ? 'input' : 'stepper';
        var quantity = Math.max( 1, Math.min( <?php echo absint( CartRenderer::MAX_ITEM_QUANTITY ); ?>, parseInt( settings.preview_quantity, 10 ) || 2 ) );
        var label = settings.label || '<?php echo esc_js( __( 'Quantity', 'simple-budget-plugin-sbp' ) ); ?>';
        var decrementText = settings.decrement_text || '−';
        var incrementText = settings.increment_text || '+';
        var decrementIcon = elementor.helpers.renderIcon( view, settings.decrement_icon, { 'aria-hidden': true }, 'i', 'object' );
        var incrementIcon = elementor.helpers.renderIcon( view, settings.increment_icon, { 'aria-hidden': true }, 'i', 'object' );
        #>
        <div class="sbp-budget-quantity sbp-budget-quantity--{{ mode }}" data-sbp-editor-preview="yes">
            <span class="sbp-budget-quantity__label<# if ( 'yes' !== settings.show_label ) { #> screen-reader-text<# } #>">{{ label }}</span>
            <span class="sbp-budget-quantity__control">
                <# if ( 'stepper' === mode ) { #>
                    <button type="button" class="sbp-budget-quantity__step" aria-label="<?php echo esc_attr__( 'Decrease quantity', 'simple-budget-plugin-sbp' ); ?>" disabled>
                        <# if ( decrementIcon.rendered ) { #>{{{ decrementIcon.value }}}<# } else { #>{{ decrementText }}<# } #>
                    </button>
                <# } #>
                <input class="sbp-budget-quantity__input sbp-quantity-field sbp-quantity" type="number" min="1" max="<?php echo absint( CartRenderer::MAX_ITEM_QUANTITY ); ?>" step="1" value="{{ quantity }}" aria-label="{{ label }}" disabled />
                <# if ( 'stepper' === mode ) { #>
                    <button type="button" class="sbp-budget-quantity__step" aria-label="<?php echo esc_attr__( 'Increase quantity', 'simple-budget-plugin-sbp' ); ?>" disabled>
                        <# if ( incrementIcon.rendered ) { #>{{{ incrementIcon.value }}}<# } else { #>{{ incrementText }}<# } #>
                    </button>
                <# } #>
            </span>
        </div>
        <?php
    }

    private function render_step_button( $key, $step, $icon, $label, $fallback ) {
        $fallback = sanitize_text_field( $fallback );

        if ( '' === $fallback ) {
            $fallback = $step < 0 ? '−' : '+';
        }

        $this->add_render_attribute( $key, [
            'class'         => [ 'sbp-budget-quantity__step', 'sbp-budget-quantity__step--' . sanitize_html_class( $key ) ],
            'type'          => 'button',
            'data-sbp-step' => (int) $step,
            'aria-label'    => $label,
        ] );
        ?>
        <button <?php $this->print_render_attribute_string( $key ); ?>>
            <?php if ( ! empty( $icon['value'] ) ) : ?>
                <?php Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
            <?php else : ?>
                <span aria-hidden="true"><?php echo esc_html( $fallback ); ?></span>
            <?php endif; ?>
        </button>
        <?php
    }
}
