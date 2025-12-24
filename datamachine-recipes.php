<?php
/**
 * Plugin Name: Data Machine Recipes
 * Plugin URI: https://github.com/chubes4/datamachine-recipes
 * Description: Extends Data Machine to publish recipes with Schema.org structured data via WordPress Recipe Publish Handler and Recipe Schema Gutenberg Block.
 * Version: 1.1.0
 * Author: Chris Huber
 * Author URI: https://chubes.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: datamachine-recipes
 * Domain Path: /languages
 * Requires PHP: 8.2
 * Requires at least: 6.2
 * Requires Plugins: data-machine
 * Network: false
 *
 * @package DataMachineRecipes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
define( 'DATAMACHINE_RECIPES_VERSION', '1.1.0' );
define( 'DATAMACHINE_RECIPES_PLUGIN_FILE', __FILE__ );
define( 'DATAMACHINE_RECIPES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DATAMACHINE_RECIPES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( DATAMACHINE_RECIPES_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once DATAMACHINE_RECIPES_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Initialize DM-Recipes plugin functionality.
 *
 * Loads translation textdomain, registers Data Machine handler filters,
 * and initializes Recipe Schema Gutenberg block. Called on WordPress 'init' hook.
 *
 * @since 1.0.0
 */
function datamachine_recipes_init() {
    load_plugin_textdomain( 'datamachine-recipes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // Register Recipe Schema Block
    if ( class_exists( 'DataMachineRecipes\Blocks\RecipeSchemaBlock' ) ) {
        DataMachineRecipes\Blocks\RecipeSchemaBlock::register();
    }

    // Register handlers
    if ( class_exists( 'DataMachineRecipes\Handlers\WordPressRecipePublish\WordPressRecipePublish' ) ) {
        DataMachineRecipes\Handlers\WordPressRecipePublish\WordPressRecipePublish::register();
    }
}

/**
 * Plugin activation callback.
 *
 * Flushes rewrite rules to ensure proper URL structure.
 * Plugin dependency handled by WordPress via Requires Plugins header.
 *
 * @since 1.0.0
 */
function datamachine_recipes_activate() {
    flush_rewrite_rules();
}

/**
 * Plugin deactivation callback.
 *
 * Performs cleanup operations including flushing rewrite rules
 * to remove any custom URL structures.
 *
 * @since 1.0.0
 */
function datamachine_recipes_deactivate() {
    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'datamachine_recipes_activate' );
register_deactivation_hook( __FILE__, 'datamachine_recipes_deactivate' );

add_action( 'init', 'datamachine_recipes_init' );