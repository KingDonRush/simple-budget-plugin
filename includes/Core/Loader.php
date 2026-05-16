<?php
/**
 * Orquestrador de hooks.
 */

namespace SBP\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Loader {

    protected $actions = [];

    public function add_action( $hook, $instance, $method, $priority = 10, $accepted_args = 1 ) {
        $this->actions[] = compact( 'hook', 'instance', 'method', 'priority', 'accepted_args' );
    }

    public function run() {
        foreach ( $this->actions as $a ) {
            add_action( $a['hook'], [ $a['instance'], $a['method'] ], $a['priority'], $a['accepted_args'] );
        }
    }

    /**
     * Inicializa automaticamente classes que possuam init_hooks().
     */
    public function auto_init( array $classes ) {
        foreach ( $classes as $class_name ) {
            if ( ! class_exists( $class_name ) ) {
                continue;
            }
            $instance = new $class_name();

            if ( method_exists( $instance, 'init_hooks' ) ) {
                $instance->init_hooks();
            }
        }
    }
}
