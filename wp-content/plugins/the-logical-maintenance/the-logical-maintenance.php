<?php
/**
 * Plugin Name: The Logical Maintenance
 * Description: Maintenance mode powered by the Gutenberg content of the fixed Maintenance page.
 * Version: 0.1.0
 * Plugin URI: https://github.com/michelediss/the-logical-theme
 * Author: Michele Paolino
 * Author URI: https://michelepaolino.com
 * Text Domain: the-logical-maintenance
 * Domain Path: /languages
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/class-the-logical-maintenance-plugin.php';

function the_logical_maintenance(): The_Logical_Maintenance_Plugin
{
    return The_Logical_Maintenance_Plugin::instance(__FILE__);
}

register_activation_hook(__FILE__, ['The_Logical_Maintenance_Plugin', 'activate']);

the_logical_maintenance();
