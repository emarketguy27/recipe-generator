<?php
class Recipe_Generator_Frontend {
    private static $instance;
    private $assets_enqueued = false;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('recipe_generator', [$this, 'recipe_shortcode_handler']);
        add_shortcode('user_saved_recipes', [$this, 'saved_recipes_shortcode_handler']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function recipe_shortcode_handler($atts) {
        $this->set_assets_flag();
        
        // Your existing shortcode content remains exactly the same
        $prompt_manager = Recipe_Generator_Prompt_Manager::get_instance();
        $dietary_options = $prompt_manager->get_dietary_options();
        
        ob_start(); ?>
        <div class="recipe-generator-frontend">
            <form id="recipe-generator-form">
                <?php wp_nonce_field('recipe_generator_ajax_nonce', '_wpnonce'); ?>
                
                <div class="form-group">
                    <label for="rg-servings"><?php esc_html_e('Servings', 'recipe-generator'); ?></label>
                    <input type="number" id="rg-servings" name="servings" min="1" max="20" value="2">
                </div>
                
                <div class="form-group">
                    <label for="rg-include"><?php esc_html_e('Must Include Ingredients', 'recipe-generator'); ?></label>
                    <input type="text" id="rg-include" name="include" 
                        placeholder="<?php esc_attr_e('e.g., chicken, potatoes', 'recipe-generator'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="rg-exclude"><?php esc_html_e('Must Exclude Ingredients', 'recipe-generator'); ?></label>
                    <input type="text" id="rg-exclude" name="exclude" 
                        placeholder="<?php esc_attr_e('e.g., nuts, dairy', 'recipe-generator'); ?>">
                </div>
                
                <?php if (!empty($dietary_options)) : ?>
                    <div class="form-group">
                        <h3><?php esc_html_e('Dietary Requirements', 'recipe-generator'); ?></h3>
                        <div class="dietary-options">
                            <?php foreach ($dietary_options as $key => $label) : ?>
                                <label class="dietary-option">
                                    <input type="checkbox" name="dietary[]" value="<?php echo esc_attr($key); ?>">
                                    <?php echo esc_html($label); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="rg-submit wp-element-button">
                    <?php esc_html_e('Generate Recipe', 'recipe-generator'); ?>
                </button>
                
                <div class="rg-loading" style="display:none;">
                    <div class="rg-spinner"></div>
                    <p class="loading-text">Crafting your perfect recipe...</p>
                </div>
            </form>
            
            <div id="recipe-results" style="display:none;"></div>
            <div id="recipe-actions" style="display:none;">
                <button id="save-recipe-btn" class="rg-submit wp-element-button">Save to Favorites</button>
                <span id="save-status"></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function saved_recipes_shortcode_handler($atts) {
        $this->set_assets_flag();
        
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your saved recipes.</p>';
        }
        
        $saved_recipes = get_user_meta(get_current_user_id(), 'ai_saved_recipes', true) ?: [];
        $recipe_count = count($saved_recipes);
        
        if (empty($saved_recipes)) {
            return '<p>You have no saved recipes yet.</p>';
        }

        ob_start(); ?>
        <div class="user-saved-recipes">
            <h3>Your Saved Recipes <span class="recipe-count">(<?php echo $recipe_count; ?>)</span></h3>
            
            <?php if ($recipe_count > 0) : ?>
                <ul class="saved-recipes-list">
                    <?php foreach ($saved_recipes as $recipe_id => $recipe) : 
                        $dietary_tags = !empty($recipe['data']['dietary_tags']) ? $recipe['data']['dietary_tags'] : [];
                        ?>
                        <li class="saved-recipe-item" data-recipe-id="<?php echo esc_attr($recipe_id); ?>">
                            <div class="recipe-summary">
                                <h4><?php echo esc_html($recipe['name']); ?></h4>
                                <?php if (!empty($dietary_tags)) : ?>
                                    <div class="dietary-tags">
                                        <?php foreach ($dietary_tags as $tag) : ?>
                                            <span class="dietary-tag"><?php echo esc_html($tag); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <button class="view-recipe-btn wp-element-button">View Recipe</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>You have no saved recipes yet.</p>
            <?php endif; ?>
        </div>

        <!-- Modal Structure -->
        <div id="recipe-modal" class="recipe-modal" style="display:none;">
            <div class="modal-content">
                
                <div class="modal-actions">
                    <button class="modal-action share-recipe" title="Share Recipe">
                        <span class="dashicons dashicons-share"></span> Share
                    </button>
                    <button class="modal-action print-recipe" title="Print Recipe">
                        <span class="dashicons dashicons-printer"></span> Print
                    </button>
                    <a href="#" class="modal-action view-post-btn" style="display:none;" title="View Full Post">
                        <span class="dashicons dashicons-external"></span> View Post
                    </a>
                    <button class="modal-action delete-recipe" title="Delete Recipe">
                        <span class="dashicons dashicons-trash"></span> Delete
                    </button>
                    <span class="close-modal">&times;</span>
                </div>
                <div id="modal-recipe-content"></div>
            </div>
        </div>
        <div id="share-options-modal" class="share-modal" style="display:none;">
            <div class="share-modal-content">
                <span class="close-share-modal">&times;</span>
                <h4>Share Recipe</h4>
                <p style="font-size: .8rem;">Social sharing is only available when the saved recipe has an associated post - you can still email or copy/paste.</p>
                <div class="share-buttons">
                    <a href="#" class="share-btn modal-action platform-share-btn facebook disabled" data-platform="facebook">
                        <span class="dashicons dashicons-facebook"></span> Facebook
                    </a>
                    <a href="#" class="share-btn modal-action platform-share-btn twitter disabled" data-platform="twitter">
                        <span class="dashicons dashicons-twitter"></span> X (Twitter)
                    </a>
                    <a href="#" class="share-btn modal-action platform-share-btn pinterest disabled" data-platform="pinterest">
                        <span class="dashicons dashicons-pinterest"></span> Pinterest
                    </a>
                    <a href="#" class="share-btn modal-action platform-share-btn reddit disabled" data-platform="reddit">
                        <span class="dashicons dashicons-reddit"></span> Reddit
                    </a>
                    <a href="#" class="share-btn modal-action email-recipe" data-platform="email">
                        <span class="dashicons dashicons-email"></span> Email
                    </a>
                    <button class="share-btn modal-action copy-recipe" title="Copy Recipe">
                        <span class="dashicons dashicons-clipboard"></span> Copy
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function set_assets_flag() {
        $this->assets_enqueued = true;
    }

    public function enqueue_assets() {
        global $post;
        
        // Check if we've processed shortcodes
        if ($this->assets_enqueued) {
            $this->do_enqueue_assets();
            return;
        }
        
        // Fallback check for posts/pages
        if (is_a($post, 'WP_Post')) {
            if (has_shortcode($post->post_content, 'recipe_generator') || 
               has_shortcode($post->post_content, 'user_saved_recipes')) {
                $this->do_enqueue_assets();
            }
        }
    }

    private function do_enqueue_assets() {
        if (did_action('wp_enqueue_scripts') !== 1) return;
        
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
                'saved_recipes' => is_user_logged_in() ? get_user_meta(get_current_user_id(), 'ai_saved_recipes', true) : []
            ]
        );
    }
}

// Initialize
Recipe_Generator_Frontend::get_instance();
