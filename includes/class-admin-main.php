<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Ai_Powered_Recipe_Generator_Admin_Main {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
    }

    public function add_menu_pages() {
        add_menu_page(
            __('Recipe Generator', 'ai-powered-recipe-generator'),
            __('Recipe Generator', 'ai-powered-recipe-generator'),
            'manage_options',
            'ai-powered-recipe-generator',
            array($this, 'render_main_page'),
            'dashicons-food',
            6
        );
    }

    public function render_main_page() {
        ?>
        <div class="wrap ai-powered-recipe-generator-admin">
            <h1>Recipe Generator</h1>

            <!-- New Instructional Panel -->
             <div class="notice notice-info">
                <h2 style="margin-top: 0;"><?php echo esc_html__('Getting Started with Recipe Generator', 'ai-powered-recipe-generator'); ?></h2>
                <p><strong>FIRST THINGS FIRST: </strong>Visit Dashboard > Settings > Permalinks : Click "Save Changes"</p>
                <p class="warning">This flushes the permalinks cache and includes all AI Recipe Generator Templates, Patterns & Taxonomies.</p>
                <div class="instructions-wrapper">
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-admin-network"></span> <?php echo esc_html__('1. API Configuration', 'ai-powered-recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo wp_kses(__('Before using the plugin, you <strong>must</strong>:', 'ai-powered-recipe-generator'), array('strong' => array())); ?></p>
                            <ol>
                                <li><?php 
                                    $settings_url = admin_url('admin.php?page=ai-powered-recipe-generator-ai-settings');
                                    printf(
                                        wp_kses(
                                            /* translators: %s: URL to the API settings page */
                                            __('Select an API provider and add your API key in the <a href="%s">API Settings</a>', 'ai-powered-recipe-generator'),
                                            array('a' => array('href' => array()))
                                        ),
                                        esc_url($settings_url)
                                    );
                                    ?>
                                </li>
                                <li><?php echo esc_html__('Add your API key in the settings', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Save your changes', 'ai-powered-recipe-generator'); ?></li>
                            </ol>
                            <p class="warning"><strong><?php echo esc_html__('Important:', 'ai-powered-recipe-generator'); ?></strong> <?php echo esc_html__('The frontend recipe generator will not work without proper API configuration. Any changes must be saved before they take effect.', 'ai-powered-recipe-generator'); ?></p>
                            <p>
                                <?php
                                printf(
                                    wp_kses(
                                        /* translators: %s: URL to the API settings page */
                                        __('<a href="%s" class="button button-primary">Go to API Settings</a>', 'ai-powered-recipe-generator'),
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
                        <h3><span class="dashicons dashicons-editor-code"></span> <?php echo esc_html__('2. Prompt Engineering', 'ai-powered-recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('The Prompt Editor section is carefully optimized for:', 'ai-powered-recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Efficient token usage', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('High-quality recipe generation', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Adherence to all selectable parameters', 'ai-powered-recipe-generator'); ?></li>
                            </ul>
                            <p class="warning"><?php echo esc_html__('Changes should be made with caution. Use the "Test Prompt" section to verify changes before going "live".', 'ai-powered-recipe-generator'); ?></p>
                            <p><?php echo esc_html__('A master reset option is available if you need to restore default prompts.', 'ai-powered-recipe-generator'); ?></p>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-admin-post"></span> <?php echo esc_html__('3. Saved Recipes System', 'ai-powered-recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('User-saved recipes are stored in user meta (not as posts) for clean separation:', 'ai-powered-recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Users can save favorites without creating public content', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Admins can convert saved recipes to actual posts via bulk actions', 'ai-powered-recipe-generator'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('Visit the "Saved Recipes" admin page to manage all user-saved recipes.', 'ai-powered-recipe-generator'); ?></p>
                            <p>
                                <?php
                                $recipes_url = admin_url('admin.php?page=ai-powered-recipe-generator-saved-recipes');
                                printf(
                                    wp_kses(
                                        /* translators: %s: URL to the Saved Recipes page */
                                        __('<a href="%s" class="button button-primary">Go to Saved Recipes</a>', 'ai-powered-recipe-generator'),
                                        array(
                                            'a' => array(
                                                'href' => array(),
                                                'class' => array()
                                            )
                                        )
                                    ),
                                    esc_url($recipes_url)
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-media-text"></span> <?php echo esc_html__('4. SEO & Schema.org', 'ai-powered-recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('All generated recipes include automatic SEO optimization:', 'ai-powered-recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Complete Schema.org JSON-LD markup', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Rich text formatting for search engines', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Structured data for recipe cards in search results', 'ai-powered-recipe-generator'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-admin-appearance"></span> <?php echo esc_html__('5. Templates & Patterns', 'ai-powered-recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('The plugin includes several ready-to-use templates:', 'ai-powered-recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Archive templates for recipe categories/tags', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Multiple single recipe template options', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Editable via WordPress Template Editor', 'ai-powered-recipe-generator'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('Also included are several patterns with shortcodes for easy placement.', 'ai-powered-recipe-generator'); ?></p>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-carrot"></span> <?php echo esc_html__('6. Dietary Options', 'ai-powered-recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('The default dietary options can be customized:', 'ai-powered-recipe-generator'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('Add new dietary options not included by default', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Remove options you don\'t need', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Reset to default schema.org options at any time', 'ai-powered-recipe-generator'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="instruction-section">
                        <h3><span class="dashicons dashicons-shortcode"></span> <?php echo esc_html__('7. Shortcode Reference', 'ai-powered-recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('Use these shortcodes to add functionality anywhere:', 'ai-powered-recipe-generator'); ?></p>
                            <ul>
                                <li><code><?php echo esc_html('[ai_powered_recipe_generator]'); ?></code> - <?php echo esc_html__('Main recipe generation form', 'ai-powered-recipe-generator'); ?></li>
                                <li><code><?php echo esc_html('[user_saved_recipes]'); ?></code> - <?php echo esc_html__('User-specific saved recipes list', 'ai-powered-recipe-generator'); ?></li>
                                <li><code><?php echo esc_html('[recipe_user_profile]'); ?></code> - <?php echo esc_html__('User profile/login component', 'ai-powered-recipe-generator'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('See the shortcode reference section below for detailed usage and examples.', 'ai-powered-recipe-generator'); ?></p>

                            <div class="shortcode-card">
                                <h3>Recipe Generator Form</h3>
                                <div class="shortcode-example">
                                    <code>[ai_powered_recipe_generator]</code>
                                    <button class="copy-shortcode" data-clipboard-text="[ai_powered_recipe_generator]">
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
                                    <img src="<?php echo esc_url(AI_POWERED_RECIPE_GENERATOR_URL . 'assets/images/rg-user.png'); ?>" alt="Saved Recipes Example">
                                    <p>(Example of default shortcode UI)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="instruction-section" style="height: fit-content;">
                        <h3><span class="dashicons dashicons-search"></span> <?php echo esc_html__('8. Querying Recipes', 'ai-powered-recipe-generator'); ?></h3>
                        <div class="instruction-content">
                            <p><?php echo esc_html__('All recipes are created as custom post type "AI Recipes".', 'ai-powered-recipe-generator'); ?></p>
                            <h4><?php echo esc_html__('To display them:', 'ai-powered-recipe-generator'); ?></h4>
                            <ul>
                                <li><?php echo esc_html__('Use the dedicated "AI Recipes" menu for management', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('If confident with adding code - Create query loops with post type', 'ai-powered-recipe-generator'); ?> <code>aiprg_recipe</code></li>
                                <li><?php echo esc_html__('Recipe tags will generate automatically with each recipe - and are set by the selection of "Dietary Options"', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Recipe Categories are created in the same way as normal wordpress posts"', 'ai-powered-recipe-generator'); ?></li>
                                <li><?php echo esc_html__('Taxonomies available:', 'ai-powered-recipe-generator'); ?> <code>aiprg_recipe_category</code> <?php echo esc_html__('and', 'ai-powered-recipe-generator'); ?> <code>aiprg_recipe_tag</code></li>
                            </ul>
                            <p><?php echo esc_html__('Example using the editor:', 'ai-powered-recipe-generator'); ?></p>
                            <pre>
                                <div class="video-demo">
                                    <video controls width="100%" style="max-width: 800px; border: 1px solid #ddd; border-radius: 4px;">
                                        <source src="<?php echo esc_url(AI_POWERED_RECIPE_GENERATOR_URL . 'assets/images/query-loop-clip.mp4'); ?>" type="video/mp4">
                                        <?php echo esc_html__('Your browser does not support the video tag.', 'ai-powered-recipe-generator'); ?>
                                    </video>
                                    <p class="video-caption"><?php echo esc_html__('Creating a recipe query loop in the Block Editor', 'ai-powered-recipe-generator'); ?></p>
                                    <p class="video-caption" style="text-wrap: pretty;"><?php echo esc_html__('PLease note: Taxonomy archives will display automatically in a pre-built, customizable template. Best practice is to simply edit this template.', 'ai-powered-recipe-generator'); ?></p>
                                </div>
                            </pre>
                            <div class="important-info">
                               <h3><span class="dashicons dashicons-info"></span> Notes On Custom Post Types & Custom Taxonomies</h3>
                                <ul>
                                    <li style="text-wrap: balance; font-size: 1.4rem; line-height: 1.2;"><strong><?php echo esc_html__('The creation of Custom Post Types & associated Templates/Patterns and Taxonomies requires your "Permalinks" to be flushed...', 'ai-powered-recipe-generator'); ?></strong></li>
                                    <hr/>
                                    <li class="warning"><?php echo esc_html__('Go to the main WordPress dashboard > Settings > Permalinks', 'ai-powered-recipe-generator'); ?></li>
                                    <li class="warning"><?php echo esc_html__('Click "Save Changes" to flush and reset. All Custom Templates & Taxonomies will now be seen by WordPress.', 'ai-powered-recipe-generator'); ?></li>
                                </ul> 
                            </div>
                            <div class="warning-notice">
                                <h3>✨ IMPORTANT NOTICE</h3>
                                <ul>
                                    <li><?php echo esc_html__('Deactivation of Recipe Generator will NOT remove user saved recipes, created AI Recipes, taxonomies or templates from the database. Re-activation will restore everything.', 'ai-powered-recipe-generator'); ?></li>
                                    <li class="warning"><strong><?php echo esc_html__('Deletion of Recipe Generator WILL remove ALL user saved recipes, created AI Recipes, taxonomies and templates from the database. This action is permanent and NOT reversible.', 'ai-powered-recipe-generator'); ?></strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}