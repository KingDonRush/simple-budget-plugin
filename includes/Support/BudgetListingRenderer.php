<?php
/**
 * Composes legacy markup or Elementor templates for a Budget Listing.
 */

namespace SBP\Support;

use SBP\Templates\TemplateManager;

if ( ! defined( 'ABSPATH' ) ) exit;

class BudgetListingRenderer {

    public static function normalize_args( $args ) {
        $args = is_array( $args ) ? $args : [];
        $item_layout = sanitize_key( $args['item_layout'] ?? 'built_in' );
        $summary_mode = sanitize_key( $args['summary_mode'] ?? 'hidden' );

        if ( ! in_array( $item_layout, [ 'built_in', 'template' ], true ) ) {
            $item_layout = 'built_in';
        }

        if ( ! in_array( $summary_mode, [ 'hidden', 'built_in', 'template' ], true ) ) {
            $summary_mode = 'hidden';
        }

        return [
            'item_layout'        => $item_layout,
            'item_template_id'   => absint( $args['item_template_id'] ?? 0 ),
            'summary_mode'       => $summary_mode,
            'summary_template_id'=> absint( $args['summary_template_id'] ?? 0 ),
            'display'            => CartRenderer::normalize_display_args( $args['display'] ?? $args ),
            'adjustment'         => BudgetCalculator::normalize_adjustment(
                [
                    'type'  => $args['adjustment_type'] ?? 'none',
                    'value' => $args['adjustment_value'] ?? '',
                    'label' => $args['adjustment_label'] ?? __( 'Adjustment', 'simple-budget-plugin-sbp' ),
                ]
            ),
            'labels'             => [
                'subtotal'        => self::normalize_label( $args['subtotal_label'] ?? '', __( 'Subtotal', 'simple-budget-plugin-sbp' ) ),
                'adjustment'      => self::normalize_label( $args['adjustment_label'] ?? '', __( 'Adjustment', 'simple-budget-plugin-sbp' ) ),
                'estimated_range' => self::normalize_label( $args['estimated_range_label'] ?? '', __( 'Estimated range', 'simple-budget-plugin-sbp' ) ),
            ],
        ];
    }

    public static function render( $product_ids, $args = [], $quantities = [] ) {
        $args = self::normalize_args( $args );
        $items = CartRenderer::get_items_data( $product_ids, $quantities );

        if ( empty( $items ) ) {
            return [
                'items_html'               => '',
                'summary_html'             => '',
                'item_template_fallback'   => false,
                'summary_template_fallback'=> false,
            ];
        }

        $item_template_fallback = false;

        if (
            'template' === $args['item_layout']
            && TemplateManager::can_render_template( $args['item_template_id'], TemplateManager::ROLE_BUDGET_ITEM )
        ) {
            $items_html = self::render_template_items( $items, $args['item_template_id'] );

            if ( '' === trim( $items_html ) ) {
                $item_template_fallback = true;
                $items_html = CartRenderer::render_item_records( $items, $args['display'] );
            }
        } else {
            $item_template_fallback = 'template' === $args['item_layout'];
            $items_html = CartRenderer::render_item_records( $items, $args['display'] );
        }

        $calculation = BudgetCalculator::calculate( $items, $args['adjustment'] );
        $summary = self::render_summary( $calculation, $args );

        return [
            'items_html'               => $items_html,
            'summary_html'             => $summary['html'],
            'item_template_fallback'   => $item_template_fallback,
            'summary_template_fallback'=> $summary['fallback'],
        ];
    }

    public static function render_preview_items( $template_id, $count = 3, $quantity = 2 ) {
        $template_id = absint( $template_id );

        if ( ! TemplateManager::can_render_template( $template_id, TemplateManager::ROLE_BUDGET_ITEM ) ) {
            return '';
        }

        $count = min( 6, max( 1, absint( $count ) ) );
        $quantity = min( CartRenderer::MAX_ITEM_QUANTITY, max( 1, absint( $quantity ) ) );
        $css_rendered = false;
        $html = '';

        for ( $index = 1; $index <= $count; $index++ ) {
            $item = BudgetContext::preview_item();
            $item['quantity'] = $quantity;
            $item['title'] = sprintf(
                /* translators: %d: preview item number. */
                __( 'Budget item title %d', 'simple-budget-plugin-sbp' ),
                $index
            );
            $rendered = BudgetContext::with_item(
                $item,
                function () use ( $template_id, &$css_rendered ) {
                    $content = TemplateManager::render_template(
                        $template_id,
                        TemplateManager::ROLE_BUDGET_ITEM,
                        ! $css_rendered
                    );

                    if ( '' !== trim( $content ) ) {
                        $css_rendered = true;
                    }

                    return $content;
                }
            );

            if ( '' !== trim( $rendered ) ) {
                $html .= self::wrap_template_item( $rendered, 0, true );
            }
        }

        return $html;
    }

    public static function render_preview_summary( $template_id ) {
        $template_id = absint( $template_id );

        if ( ! TemplateManager::can_render_template( $template_id, TemplateManager::ROLE_BUDGET_SUMMARY ) ) {
            return '';
        }

        return BudgetContext::with_summary(
            BudgetContext::preview_summary(),
            function () use ( $template_id ) {
                return TemplateManager::render_template(
                    $template_id,
                    TemplateManager::ROLE_BUDGET_SUMMARY,
                    true
                );
            }
        );
    }

    private static function render_template_items( array $items, $template_id ) {
        $css_rendered = false;
        $html = '';

        foreach ( $items as $item ) {
            $rendered = BudgetContext::with_item(
                $item,
                function () use ( $template_id, &$css_rendered ) {
                    $content = TemplateManager::render_template(
                        $template_id,
                        TemplateManager::ROLE_BUDGET_ITEM,
                        ! $css_rendered
                    );

                    if ( '' !== trim( $content ) ) {
                        $css_rendered = true;
                    }

                    return $content;
                }
            );

            if ( '' !== trim( $rendered ) ) {
                $html .= self::wrap_template_item( $rendered, $item['id'], false );
            }
        }

        return $html;
    }

    private static function render_summary( array $calculation, array $args ) {
        if ( 'hidden' === $args['summary_mode'] ) {
            return [ 'html' => '', 'fallback' => false ];
        }

        if (
            'template' === $args['summary_mode']
            && TemplateManager::can_render_template( $args['summary_template_id'], TemplateManager::ROLE_BUDGET_SUMMARY )
        ) {
            $context = BudgetSummary::context( $calculation );
            $html = BudgetContext::with_summary(
                $context,
                function () use ( $args ) {
                    return TemplateManager::render_template(
                        $args['summary_template_id'],
                        TemplateManager::ROLE_BUDGET_SUMMARY,
                        true
                    );
                }
            );

            if ( '' !== trim( $html ) ) {
                return [
                    'html' => '<div class="sbp-budget-listing__summary-template">' . $html . '</div>',
                    'fallback' => false,
                ];
            }
        }

        return [
            'html' => BudgetSummary::render_builtin( $calculation, $args['labels'] ),
            'fallback' => 'template' === $args['summary_mode'],
        ];
    }

    private static function wrap_template_item( $html, $product_id, $preview ) {
        $attributes = ' class="sbp-budget-listing__template-item"';

        if ( $product_id ) {
            $attributes .= ' data-sbp-product-id="' . esc_attr( $product_id ) . '"';
        }

        if ( $preview ) {
            $attributes .= ' data-sbp-editor-preview="yes"';
        }

        return '<div' . $attributes . '>' . $html . '</div>';
    }

    private static function normalize_label( $label, $fallback ) {
        $label = sanitize_text_field( $label );

        if ( '' === $label ) {
            return $fallback;
        }

        return function_exists( 'mb_substr' ) ? mb_substr( $label, 0, 80 ) : substr( $label, 0, 80 );
    }
}
