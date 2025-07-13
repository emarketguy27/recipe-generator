<?php
/**
 * Plugin Name: Recipe Generator
 * Description: A simple recipe generator plugin
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('RECIPE_GENERATOR_VERSION', '1.0.0');
define('RECIPE_GENERATOR_PATH', plugin_dir_path(__FILE__));
define('RECIPE_GENERATOR_URL', plugin_dir_url(__FILE__));

class Recipe_Generator {
    private static $instance;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Include required files
        $this->includes();

        // Initialize components
        add_action('plugins_loaded', array($this, 'init'));

        // Register frontend assets hook
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }

    private function includes() {
        require_once RECIPE_GENERATOR_PATH . 'includes/class-prompt-manager.php';
        require_once RECIPE_GENERATOR_PATH . 'includes/class-providers.php';
        require_once RECIPE_GENERATOR_PATH . 'includes/class-api-handler.php';
        require_once RECIPE_GENERATOR_PATH . 'includes/ajax-handlers.php';
        require_once RECIPE_GENERATOR_PATH . 'includes/class-admin-ai-settings.php';
        require_once RECIPE_GENERATOR_PATH . 'includes/class-admin-main.php';
        require_once RECIPE_GENERATOR_PATH . 'includes/class-frontend.php';
    }

    public function init() {
        // Initialize admin interface
        // if (is_admin()) {
        //     new Recipe_Generator_Admin_AI_Settings();
        // }
    }

    /*** Enqueue frontend assets ***/
    public function enqueue_frontend_assets() {
        // Only load if shortcode is present or on specific pages
        global $post;
        if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'recipe_generator') || 
         has_shortcode($post->post_content, 'user_saved_recipes'))) {
            wp_enqueue_style(
                'recipe-generator-frontend',
                RECIPE_GENERATOR_URL . 'assets/css/frontend.css',
                [],
                RECIPE_GENERATOR_VERSION
            );
            
            wp_enqueue_script(
                'recipe-generator-frontend',
                RECIPE_GENERATOR_URL . 'assets/js/frontend.js',
                ['jquery'],
                RECIPE_GENERATOR_VERSION,
                true
            );
            
            wp_localize_script(
                'recipe-generator-frontend',
                'recipeGeneratorFrontendVars',
                [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('recipe_generator_frontend_nonce'),
                    'errorOccurred' => __('An error occurred. Please try again.', 'recipe-generator'),
                    'saved_recipes' => is_user_logged_in() ? get_user_meta(get_current_user_id(), 'ai_saved_recipes', true) : []
                ]
            );
        }
    }
    /*** Enqueue Admin assets ***/
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'recipe-generator') === false) {
            return;
        }
        // Enqueue admin CSS
        wp_enqueue_style(
            'recipe-generator-admin',
            RECIPE_GENERATOR_URL . 'assets/css/admin.css',
            [],
            RECIPE_GENERATOR_VERSION
        );
        
        // Enqueue admin JS
        wp_enqueue_script(
            'recipe-generator-admin',
            RECIPE_GENERATOR_URL . 'assets/js/admin.js',
            array('jquery'),
            RECIPE_GENERATOR_VERSION,
            true
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'recipe-generator-admin',
            'recipeGeneratorVars',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('recipe_generator_ajax_nonce'),
                'confirmResetPrompt' => __('Are you sure you want to reset the prompt to default?', 'recipe-generator'),
                'promptResetSuccess' => __('Prompt reset to default.', 'recipe-generator'),
                'confirmRemoveOption' => __('Are you sure you want to remove this option?', 'recipe-generator'),
                'confirmResetOptions' => __('Are you sure you want to reset all dietary options to default?', 'recipe-generator'),
                'remove' => __('Remove', 'recipe-generator'),
                'testing' => __('Testing...', 'recipe-generator'),
                'testPrompt' => __('Test Prompt', 'recipe-generator'),
                'errorOccurred' => __('An error occurred. Please try again.', 'recipe-generator')
            )
        );
    }
}

// Initialize the plugin
Recipe_Generator::get_instance();

register_deactivation_hook(__FILE__, function() {
    // Deactivation code if needed
});

add_action('plugins_loaded', function() {
    // Initialize provider system
    Recipe_Generator_Providers::get_instance();
    
    // Initialize admin interfaces
    if (is_admin()) {
        new Recipe_Generator_Admin_Main();
        new Recipe_Generator_Admin_AI_Settings();
    }
    
    // Future: Initialize frontend components here if needed
});
// Include AJAX handlers
require_once RECIPE_GENERATOR_PATH . 'includes/ajax-handlers.php';