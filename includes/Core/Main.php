<?php
/**
 * Núcleo do plugin (bootstrap).
 */

namespace SBP\Core;

use SBP\Core\Loader;
use SBP\Admin\Admin;
use SBP\Public_\Public_;   // Classe "Public_" fica no namespace SBP\Public_
use SBP\Ajax\Ajax;
use SBP\Shortcodes\Shortcodes;
use SBP\Elementor\ElementorIntegration;

if ( ! defined( 'ABSPATH' ) ) exit;

class Main {

    /** @var Loader */
    private $loader;

    public function __construct() {
        $this->loader = new Loader();
    }

    public function run() {
        $modules = [
            Admin::class,
            Public_::class,
            Ajax::class,
            Shortcodes::class,
            ElementorIntegration::class,
        ];

        $this->loader->auto_init( $modules );
        $this->loader->run();

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[SBP] Plugin iniciado com sucesso.' );
        }
    }
}
