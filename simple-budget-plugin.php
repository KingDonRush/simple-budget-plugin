<?php
/**
 * Plugin Name: Simple Budget Plugin
 * Description: Plugin para orçamentos personalizados em sites com Elementor e CPTs.
 * Version: 2.3.4
 * Author: Guilherme Silva
 * Text Domain: simple-budget-plugin-sbp
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ===========================
 * Definições de Constantes
 * ===========================
 */
define( 'SBP_VERSION', '2.3.4' );
define( 'SBP_FILE', __FILE__ );
define( 'SBP_PATH', plugin_dir_path( __FILE__ ) );
define( 'SBP_URL',  plugin_dir_url( __FILE__ ) );

/**
 * ===========================
 * Autoloader (namespaces SBP\)
 * ===========================
 */
require_once SBP_PATH . 'includes/Core/Autoloader.php';

use SBP\Core\Main;
use SBP\Core\Activator;
use SBP\Core\Deactivator;

/**
 * ===========================
 * Hooks de Ativação / Desativação
 * ===========================
 */
register_activation_hook( __FILE__, [ Activator::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Deactivator::class, 'deactivate' ] );

/**
 * ===========================
 * Bootstrap
 * ===========================
 */
function sbp_run_plugin() {
    $plugin = new Main();
    $plugin->run();
}
sbp_run_plugin();
