<?php
class Recipe_Generator_Admin_Main {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
    }

    public function add_menu_pages() {
        add_menu_page(
            __('Recipe Generator', 'recipe-generator'),
            __('Recipe Generator', 'recipe-generator'),
            'manage_options',
            'recipe-generator',
            array($this, 'render_main_page'),
            'dashicons-food',
            6
        );
    }

    public function render_main_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'recipe-generator'));
        }
        
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Recipe Generator', 'recipe-generator') . '</h1>';
        echo '<p>' . esc_html__('Welcome to the Recipe Generator plugin.', 'recipe-generator') . '</p>';
        
        // Link to AI Settings page
        $ai_settings_url = admin_url('admin.php?page=recipe-generator-ai-settings');
        echo '<p><a href="' . esc_url($ai_settings_url) . '" class="button">' . esc_html__('Go to AI Settings', 'recipe-generator') . '</a></p>';
        
        echo '</div>';
    }
}