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
        ?>
        <div class="wrap recipe-generator-admin">
            <h1>Recipe Generator</h1>
            
            <!-- Existing content... -->
            
            <div class="settings-section">
                <h2>Shortcode Reference</h2>
                <div class="cards-wrapper">
                    <div class="shortcode-card">
                        <h3>Recipe Generator Form</h3>
                        <div class="shortcode-example">
                            <code>[recipe_generator]</code>
                            <button class="copy-shortcode" data-clipboard-text="[recipe_generator]">
                                Copy
                            </button>
                        </div>
                        <div class="shortcode-description">
                            <p>Displays the recipe generation form on any page or post. Users can generate AI-powered recipes by filling out the form.</p>
                            <p><strong>Implementation:</strong> Simply paste this shortcode into any WordPress post, page, or widget area.</p>
                        </div>
                    </div>
                    
                    <div class="shortcode-card">
                        <h3>Saved Recipes List</h3>
                        <div class="shortcode-example">
                            <code>[user_saved_recipes]</code>
                            <button class="copy-shortcode" data-clipboard-text="[user_saved_recipes]">
                                Copy
                            </button>
                        </div>
                        <div class="shortcode-description">
                            <p>Displays a logged-in user's saved recipes with the ability to view them in a modal.</p>
                            <p><strong>Implementation:</strong> Add to any page where users should view their saved recipes. Only works for logged-in users.</p>
                            <p><strong>Note:</strong> Make sure this is placed on a page accessible only to logged-in users.</p>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <?php
    }
}