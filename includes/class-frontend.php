<?php
add_shortcode('recipe_generator', function($atts) {
    // Enqueue required assets
    wp_enqueue_style('recipe-generator-frontend');
    wp_enqueue_script('recipe-generator-frontend');
    
    // Get dietary options
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
            
            <button type="submit" class="rg-submit">
                <?php esc_html_e('Generate Recipe', 'recipe-generator'); ?>
            </button>
            
            <div class="rg-loading" style="display:none;">
                <div class="rg-spinner"></div>
                <span><?php esc_html_e('Creating your recipe...', 'recipe-generator'); ?></span>
            </div>
        </form>
        
        <div id="recipe-results" style="display:none;"></div>
    </div>
    <?php
    return ob_get_clean();
});