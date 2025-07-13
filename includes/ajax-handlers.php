<?php
// AJAX Handlers
add_action('wp_ajax_recipe_generator_reset_prompt', function() {
    check_ajax_referer('recipe_generator_ajax_nonce', '_wpnonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied.', 'recipe-generator'));
    }
    
    $prompt_manager = Recipe_Generator_Prompt_Manager::get_instance();
    $prompt_manager->reset_prompt_to_default();
    
    wp_send_json_success([
        'default_prompt' => $prompt_manager->get_default_prompt()
    ]);
});

add_action('wp_ajax_recipe_generator_add_dietary_option', function() {
    check_ajax_referer('recipe_generator_ajax_nonce', '_wpnonce');
    
    if (!current_user_can('manage_options') || empty($_POST['option'])) {
        wp_send_json_error(__('Invalid request.', 'recipe-generator'));
    }
    
    $option = sanitize_text_field($_POST['option']);
    $key = sanitize_key($option);
    
    $prompt_manager = Recipe_Generator_Prompt_Manager::get_instance();
    $prompt_manager->add_dietary_option($key, $option);
    
    wp_send_json_success([
        'key' => $key,
        'label' => $option
    ]);
});

add_action('wp_ajax_recipe_generator_remove_dietary_option', function() {
    check_ajax_referer('recipe_generator_ajax_nonce', '_wpnonce');
    
    if (!current_user_can('manage_options') || empty($_POST['key'])) {
        wp_send_json_error(__('Invalid request.', 'recipe-generator'));
    }
    
    $key = sanitize_key($_POST['key']);
    $prompt_manager = Recipe_Generator_Prompt_Manager::get_instance();
    
    // Don't allow removing default options
    $default_options = $prompt_manager->get_default_dietary_options();
    if (array_key_exists($key, $default_options)) {
        wp_send_json_error(__('Cannot remove default options.', 'recipe-generator'));
    }
    
    $prompt_manager->remove_dietary_option($key);
    wp_send_json_success();
});

add_action('wp_ajax_recipe_generator_reset_dietary_options', function() {
    check_ajax_referer('recipe_generator_ajax_nonce', '_wpnonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied.', 'recipe-generator'));
    }
    
    $prompt_manager = Recipe_Generator_Prompt_Manager::get_instance();
    $prompt_manager->reset_dietary_options_to_default();
    wp_send_json_success();
});

add_action('wp_ajax_recipe_generator_test_connection', function() {
    check_ajax_referer('recipe_generator_test_connection', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied.', 'recipe-generator'));
    }

    $providers = Recipe_Generator_Providers::get_instance();
    $selected_provider = get_option('recipe_generator_selected_provider', '');
    $api_endpoint = $providers->get_endpoint($selected_provider);
    $api_key = get_option('recipe_generator_api_key', '');

    if (empty($api_key)) {
        wp_send_json_error(__('API key is not configured.', 'recipe-generator'));
    }

    if (empty($api_endpoint)) {
        wp_send_json_error(__('No API endpoint configured for this provider.', 'recipe-generator'));
    }

    // Provider-specific test requests
    $test_request = [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ],
        'timeout' => 10
    ];

    // Adjust based on provider requirements
    switch ($selected_provider) {
        case 'OpenAI':
            $test_request['method'] = 'POST';
            $test_request['body'] = json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => 'ping']],
                'max_tokens' => 1
            ]);
            break;
        case 'Deepseek':
            $test_request['method'] = 'POST';
            $test_request['body'] = json_encode([
                'model' => 'deepseek-chat',
                'messages' => [['role' => 'user', 'content' => 'ping']],
                'max_tokens' => 1
            ]);
            break;
            
        case 'Anthropic':
            $test_request['method'] = 'POST';
            $test_request['body'] = json_encode([
                'model' => 'claude-instant-1',
                'messages' => [['role' => 'user', 'content' => 'ping']],
                'max_tokens' => 1
            ]);
            break;
        
            case 'Google AI':
            $test_request['method'] = 'POST';
            $test_request['body'] = json_encode([
                'model' => 'gemini-pro',
                'messages' => [['role' => 'user', 'content' => 'ping']],
                'max_tokens' => 1
            ]);
            break;
            
        default:
            // Fallback to GET request
            $test_request['method'] = 'GET';
    }

    $response = wp_remote_request($api_endpoint, $test_request);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    if ($response_code === 200) {
        wp_send_json_success(__('API connection successful!', 'recipe-generator'));
    } else {
        $error_message = sprintf(
            __('API returned status %d. Response: %s', 'recipe-generator'),
            $response_code,
            $response_body
        );
        wp_send_json_error($error_message);
    }
});

add_action('wp_ajax_recipe_generator_test_prompt', function() {
    check_ajax_referer('recipe_generator_ajax_nonce', '_wpnonce');
    error_log('[Recipe Generator] Test prompt initiated'); //DEBUG
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied.', 'recipe-generator'));
    }
    
    $args = [
        'servings' => !empty($_POST['servings']) ? absint($_POST['servings']) : 4,
        'include_ingredients' => !empty($_POST['include']) ? sanitize_text_field($_POST['include']) : '',
        'exclude_ingredients' => !empty($_POST['exclude']) ? sanitize_text_field($_POST['exclude']) : '',
        'dietary' => !empty($_POST['dietary']) ? array_map('sanitize_text_field', $_POST['dietary']) : []
    ];
    error_log('[Recipe Generator] Test args: ' . print_r($args, true)); //DEBUG
    
    $api_handler = Recipe_Generator_API_Handler::get_instance();
    $result = $api_handler->handle_prompt_request($args, true);

    error_log('[Recipe Generator] Handler result: ' . print_r($result, true)); //DEBUG
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    // Add token usage display if available
    $token_html = '';
    if (!empty($result['usage'])) {
        $token_html = '<div class="token-usage">
            <h4>Token Usage</h4>
            <ul>
                <li><strong>Prompt Tokens:</strong> '.esc_html($result['usage']['prompt_tokens']).'</li>
                <li><strong>Completion Tokens:</strong> '.esc_html($result['usage']['completion_tokens']).'</li>
                <li><strong>Total Tokens:</strong> '.esc_html($result['usage']['total_tokens']).'</li>
            </ul>
        </div>';
    }

    wp_send_json_success([
        'prompt' => $result['prompt'],
        // 'response' => $result['formatted_response'] ?? 'No response content'
        'response' => $result['formatted_response'] . $token_html
    ]);
});

add_action('wp_ajax_recipe_generator_generate_recipe', 'recipe_generator_handle_frontend_request');
add_action('wp_ajax_nopriv_recipe_generator_generate_recipe', 'recipe_generator_handle_frontend_request');
add_action('wp_ajax_save_ai_recipe_to_favorites', 'handle_save_ai_recipe_to_favorites');


function recipe_generator_handle_frontend_request() {
    check_ajax_referer('recipe_generator_ajax_nonce', '_wpnonce');
    
    $args = [
        'servings' => !empty($_POST['servings']) ? absint($_POST['servings']) : 4,
        'include_ingredients' => !empty($_POST['include']) ? sanitize_text_field($_POST['include']) : '',
        'exclude_ingredients' => !empty($_POST['exclude']) ? sanitize_text_field($_POST['exclude']) : '',
        'dietary' => !empty($_POST['dietary']) ? array_map('sanitize_key', $_POST['dietary']) : []
    ];
    
    $api_handler = Recipe_Generator_API_Handler::get_instance();
    $result = $api_handler->handle_prompt_request($args);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    // Just return the formatted response that we know works in admin
    wp_send_json_success([
        'html' => $result['formatted_response']
    ]);
}

// Standalone helper function
function recipe_generator_format_recipe_for_display($recipe_data) {
    if (is_string($recipe_data)) {
        $recipe = json_decode($recipe_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '<div class="error">Failed to parse recipe data</div>';
        }
    } else {
        $recipe = $recipe_data;
    }
    
    // Basic validation
    if (!isset($recipe['recipe_name'])) {
        return '<div class="error">Invalid recipe format received</div>';
    }
    
    ob_start(); ?>
    <div class="recipe-container">
        <h2><?php echo esc_html($recipe['recipe_name']); ?></h2>
        <?php if (!empty($recipe['description'])) : ?>
            <p class="description"><?php echo esc_html($recipe['description']); ?></p>
        <?php endif; ?>
        
        <div class="recipe-meta">
            <?php if (!empty($recipe['servings'])) : ?>
                <span>Servings: <?php echo esc_html($recipe['servings']); ?></span>
            <?php endif; ?>
            <?php if (!empty($recipe['preparation_time'])) : ?>
                <span>Prep: <?php echo esc_html($recipe['preparation_time']); ?></span>
            <?php endif; ?>
            <?php if (!empty($recipe['cooking_time'])) : ?>
                <span>Cook: <?php echo esc_html($recipe['cooking_time']); ?></span>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($recipe['ingredients'])) : ?>
            <h3>Ingredients</h3>
            <ul>
                <?php foreach ((array)$recipe['ingredients'] as $ingredient) : ?>
                    <li><?php echo esc_html($ingredient); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <?php if (!empty($recipe['method'])) : ?>
            <h3>Method</h3>
            <ol>
                <?php foreach ((array)$recipe['method'] as $step) : ?>
                    <li><?php echo esc_html($step); ?></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function handle_save_ai_recipe_to_favorites() {
    check_ajax_referer('recipe_generator_frontend_nonce', '_wpnonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to save recipes');
    }

    $user_id = get_current_user_id();
    $recipe_id = sanitize_text_field($_POST['recipe_id']);

    // Validate ID format
    if (!preg_match('/^recipe_[a-z0-9]+_[a-z0-9]+_\d+$/', $recipe_id)) {
        wp_send_json_error('Invalid recipe ID format');
    }
    
    $recipe_html = wp_kses_post($_POST['recipe_html']);
    
    // Extract recipe name from HTML (fallback to ID if not found)
    $recipe_name = 'AI Recipe';
    if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $recipe_html, $matches)) {
        $recipe_name = sanitize_text_field(strip_tags($matches[1]));
    }

    // Get existing saved recipes
    $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
    
    // Add new recipe
    $saved_recipes[$recipe_id] = [
        'id' => $recipe_id,
        'name' => $recipe_name,
        'html' => $recipe_html,
        'saved_at' => current_time('mysql'),
        'saved_at_gmt' => current_time('mysql', true)
    ];

    update_user_meta($user_id, 'ai_saved_recipes', $saved_recipes);
    
    wp_send_json_success();
}