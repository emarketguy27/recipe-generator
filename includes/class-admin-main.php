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

            <!-- New Instructional Panel -->
             <div class="notice notice-info">
                <h2 style="margin-top: 0;"><?php echo esc_html__('Getting Started with Recipe Generator', 'recipe-generator'); ?></h2>
                <div class="instructions-wrapper">
                    
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-admin-network"></span> <?php echo esc_html__('1. API Configuration', 'recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo wp_kses(__('Before using the plugin, you <strong>must</strong>:', 'recipe-generator'), array('strong' => array())); ?></p>
                            <ol>
                                <li><?php 
                                    $settings_url = admin_url('admin.php?page=recipe-generator-ai-settings');
                                    printf(
                                        wp_kses(
                                            __('Select an API provider and add your API key in the <a href="%s">API Settings</a>', 'recipe-generator'),
                                            array('a' => array('href' => array()))
                                        ),
                                        esc_url($settings_url)
                                    );
                                    ?>
                                </li>
                                <li><?php echo esc_html__('Add your API key in the settings', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Save your changes', 'recipe-generator'); ?></li>
                            </ol>
                            <p class="warning"><strong><?php echo esc_html__('Important:', 'recipe-generator'); ?></strong> <?php echo esc_html__('The frontend recipe generator will not work without proper API configuration. Any changes must be saved before they take effect.', 'recipe-generator'); ?></p>
                            <p>
                                <?php
                                printf(
                                    wp_kses(
                                        __('<a href="%s" class="button button-primary">Go to API Settings</a>', 'recipe-generator'),
                                        array(
                                            'a' => array(
                                                'href' => array(),
                                                'class' => array()
                                            )
                                        )
                                    ),
                                    esc_url($settings_url)
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-editor-code"></span> <?php echo esc_html__('2. Prompt Engineering', 'recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('The Prompt Editor section is carefully optimized for:', 'recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Efficient token usage', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('High-quality recipe generation', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Adherence to all selectable parameters', 'recipe-generator'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('Changes should be made with caution. Use the "Test Prompt" section to verify changes before saving.', 'recipe-generator'); ?></p>
                            <p><?php echo esc_html__('A master reset option is available if you need to restore default prompts.', 'recipe-generator'); ?></p>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-admin-post"></span> <?php echo esc_html__('3. Saved Recipes System', 'recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('User-saved recipes are stored in user meta (not as posts) for clean separation:', 'recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Users can save favorites without creating public content', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Admins can convert saved recipes to actual posts via bulk actions', 'recipe-generator'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('Visit the "Saved Recipes" admin page to manage all user-saved recipes.', 'recipe-generator'); ?></p>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-media-text"></span> <?php echo esc_html__('4. SEO & Schema.org', 'recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('All generated recipes include automatic SEO optimization:', 'recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Complete Schema.org JSON-LD markup', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Rich text formatting for search engines', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Structured data for recipe cards in search results', 'recipe-generator'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-admin-appearance"></span> <?php echo esc_html__('5. Templates & Patterns', 'recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('The plugin includes several ready-to-use templates:', 'recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Archive templates for recipe categories/tags', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Multiple single recipe template options', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Editable via WordPress Template Editor', 'recipe-generator'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('Also included are several patterns with shortcodes for easy placement.', 'recipe-generator'); ?></p>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-carrot"></span> <?php echo esc_html__('6. Dietary Options', 'recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('The default dietary options can be customized:', 'recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Add new dietary options not included by default', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Remove options you don\'t need', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Reset to default schema.org options at any time', 'recipe-generator'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-shortcode"></span> <?php echo esc_html__('7. Shortcode Reference', 'recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('Use these shortcodes to add functionality anywhere:', 'recipe-generator'); ?></p>
                            <ul>
                                <li><code><?php echo esc_html('[recipe_generator]'); ?></code> - <?php echo esc_html__('Main recipe generation form', 'recipe-generator'); ?></li>
                                <li><code><?php echo esc_html('[user_saved_recipes]'); ?></code> - <?php echo esc_html__('User-specific saved recipes list', 'recipe-generator'); ?></li>
                                <li><code><?php echo esc_html('[recipe_user_profile]'); ?></code> - <?php echo esc_html__('User profile/login component', 'recipe-generator'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('See the shortcode reference section below for detailed usage and examples.', 'recipe-generator'); ?></p>

                            <div class="shortcode-card">
                                <h3>Recipe Generator Form</h3>
                                <div class="shortcode-example">
                                    <code>[recipe_generator]</code>
                                    <button class="copy-shortcode" data-clipboard-text="[recipe_generator]">
                                        Copy
                                    </button>
                                </div>
                                <div class="shortcode-description">
                                    <h4>Displays the recipe generation form on any page or post. Users can generate AI-powered recipes by filling out the form.</h4>
                                    <hr/>
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
                                    <h4>Displays a logged-in user's saved recipes with the ability to view them in a modal.</h4>
                                    <hr/>
                                    <p><strong>Implementation:</strong> Add to any page where users should view their saved recipes. Only works for logged-in users.</p>
                                    <p><strong>Note:</strong> Displays "Please log in to view your saved recipes." </p>
                                </div>
                            </div>
                            <div class="shortcode-card">
                                <h3>Log In / Logged In Shortcode</h3>
                                <div class="shortcode-example">
                                    <code>[recipe_user_profile]</code>
                                    <button class="copy-shortcode" data-clipboard-text="[recipe_user_profile]">
                                        Copy
                                    </button>
                                </div>
                                <div class="shortcode-example">
                                    <code>[recipe_user_profile show_avatar="false"]</code>
                                    <button class="copy-shortcode" data-clipboard-text="[recipe_user_profile show_avatar="false"]">
                                        Copy
                                    </button>
                                </div>
                                <div class="shortcode-example">
                                    <code>[recipe_user_profile avatar_size="150"]</code>
                                    <button class="copy-shortcode" data-clipboard-text="[recipe_user_profile avatar_size="150"]">
                                        Copy
                                    </button>
                                </div>
                                <div class="shortcode-description">
                                    <h4>Displays a Log In link, or the logged-in user's Avatar and User Name - with Log Out link</h4>
                                    <p><strong>Variations:</strong> Use any of these variations of the shortcode to include/exclude user avatar</p>
                                    <hr/>
                                    <p><strong>Implementation:</strong> Add to any page/post where you want AI Recipe Generator users to log in to save a recipe, or wherever registered users can view their saved recipes.</p>
                                    <img src="<?php echo esc_url(RECIPE_GENERATOR_URL . 'assets/images/rg-user.png'); ?>" alt="Saved Recipes Example">
                                    <p>(Example of default shortcode UI)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="instruction-section" style="height: fit-content;">
                        <h3><span class="dashicons dashicons-search"></span> <?php echo esc_html__('8. Querying Recipes', 'recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('All recipes are created as custom post type "AI Recipes".', 'recipe-generator'); ?></p>
                            <h4><?php echo esc_html__('To display them:', 'recipe-generator'); ?></h4>
                            <ul>
                                <li><?php echo esc_html__('Use the dedicated "AI Recipes" menu for management', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('If confident with adding code - Create query loops with post type', 'recipe-generator'); ?> <code>ai_recipe</code></li>
                                <li><?php echo esc_html__('Recipe tags will generate automatically with each recipe - and are set by the selection of "Dietary Options"', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Recipe Categories are created in the same way as normal wordpress posts"', 'recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Taxonomies available:', 'recipe-generator'); ?> <code>ai_recipe_category</code> <?php echo esc_html__('and', 'recipe-generator'); ?> <code>ai_recipe_tag</code></li>
                            </ul>
                            <p><?php echo esc_html__('Example using the editor:', 'recipe-generator'); ?></p>
                            <pre>
                                <div class="video-demo">
                                    <video controls width="100%" style="max-width: 800px; border: 1px solid #ddd; border-radius: 4px;">
                                        <source src="<?php echo esc_url(RECIPE_GENERATOR_URL . 'assets/images/query-loop-clip.mp4'); ?>" type="video/mp4">
                                        <?php echo esc_html__('Your browser does not support the video tag.', 'recipe-generator'); ?>
                                    </video>
                                    <p class="video-caption"><?php echo esc_html__('Creating a recipe query loop in the Block Editor', 'recipe-generator'); ?></p>
                                    <p class="video-caption" style="text-wrap: pretty;"><?php echo esc_html__('PLease note: Taxonomy archives will display automatically in a pre-built, customizable template. Best practice is to simply edit this template.', 'recipe-generator'); ?></p>
                                </div>
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}