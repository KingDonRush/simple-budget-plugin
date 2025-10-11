<?php
/**
 * Autoloader com suporte a namespaces SBP\
 * Simple autoloader for SBP namespaced classes (no Composer).
 */

namespace SBP\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Autoloader {

    public static function register() {
        spl_autoload_register( [ __CLASS__, 'autoload' ] );
    }

    private static function autoload( $class ) {
        // Apenas classes do namespace raiz SBP\
        if ( strpos( $class, 'SBP\\' ) !== 0 ) {
            return;
        }

        // Remove prefixo e converte \ para /
        $relative = str_replace( 'SBP\\', '', $class );
        $relative_path = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );

        $file = \SBP_PATH . 'includes/' . $relative_path . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        } elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[SBP] Classe não encontrada: ' . $class . ' (' . $file . ')' );
        }
    }
}

Autoloader::register();
