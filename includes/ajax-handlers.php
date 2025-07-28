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
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied.', 'recipe-generator'));
    }
    
    $args = [
        'servings' => !empty($_POST['servings']) ? absint($_POST['servings']) : 4,
        'include_ingredients' => !empty($_POST['include']) ? sanitize_text_field($_POST['include']) : '',
        'exclude_ingredients' => !empty($_POST['exclude']) ? sanitize_text_field($_POST['exclude']) : '',
        'dietary' => !empty($_POST['dietary']) ? array_map('sanitize_text_field', $_POST['dietary']) : []
    ];
    
    $api_handler = Recipe_Generator_API_Handler::get_instance();
    $result = $api_handler->handle_prompt_request($args, true);

    
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

add_action('wp_ajax_delete_saved_recipe', function() {
    check_ajax_referer('recipe_generator_frontend_nonce', '_wpnonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('Authentication required');
    }
    
    $user_id = get_current_user_id();
    $recipe_id = sanitize_text_field($_POST['recipe_id']);
    $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
    
    if (isset($saved_recipes[$recipe_id])) {
        unset($saved_recipes[$recipe_id]);
        update_user_meta($user_id, 'ai_saved_recipes', $saved_recipes);
        wp_send_json_success();
    }
    
    wp_send_json_error('Recipe not found');
});

add_action('admin_action_delete_recipe', function() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'recipe-generator'));
    }

    $recipe_id = sanitize_text_field($_GET['recipe_id']);
    $user_id = absint($_GET['user_id']);

    check_admin_referer('delete_recipe_' . $recipe_id);

    $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
    
    if (isset($saved_recipes[$recipe_id])) {
        unset($saved_recipes[$recipe_id]);
        update_user_meta($user_id, 'ai_saved_recipes', $saved_recipes);
    }

    wp_redirect(admin_url('admin.php?page=recipe-generator-saved-recipes'));
    exit;
});

add_action('wp_ajax_recipe_generator_bulk_create_posts', function() {
    check_ajax_referer('recipe_generator_ajax_nonce', '_wpnonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied.', 'recipe-generator'));
    }

    $recipes = isset($_POST['recipes']) ? (array)$_POST['recipes'] : [];
    $created = 0;
    $created_posts = [];
    
    foreach ($recipes as $recipe_data) {
        $recipe_id = sanitize_text_field($recipe_data['id']);
        $user_id = absint($recipe_data['user_id']);
        
        // Get the full recipe data
        $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
        
        if (!isset($saved_recipes[$recipe_id])) {
            error_log("[Recipe Generator] Recipe not found: {$recipe_id}");
            continue;
        }
        
        $recipe = $saved_recipes[$recipe_id];

        // Parse HTML content
        $dom = new DOMDocument();
        @$dom->loadHTML($recipe['html']);
        $xpath = new DOMXPath($dom);
        
        // Extract all elements

        $servings = $xpath->query("//p[contains(., 'Servings:')]")->item(0)->nodeValue ?? '';
        $prep_time = $xpath->query("//p[contains(., 'Prep Time:')]")->item(0)->nodeValue ?? '';
        $cook_time = $xpath->query("//p[contains(., 'Cook Time:')]")->item(0)->nodeValue ?? '';
        $prep_mins = (int)preg_replace('/[^0-9]/', '', $prep_time);
        $cook_mins = (int)preg_replace('/[^0-9]/', '', $cook_time);
        $total_time = $prep_mins + $cook_mins;
        
        $ingredients = [];
        $ingredient_nodes = $xpath->query("//ul[@class='recipe-ingredients']/li");
        foreach ($ingredient_nodes as $node) {
            $text = trim($node->nodeValue);
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (function_exists('mb_convert_encoding')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            }
            $text = str_replace(['Â°', 'â„¢'], ['°', '™'], $text);
            $ingredients[] = $text;
        }
        
        $instructions = [];
        $instruction_nodes = $xpath->query("//ol[@class='recipe-instructions']/li");
        foreach ($instruction_nodes as $node) {
            $text = trim($node->nodeValue);
            // Fix double-encoded UTF-8 characters
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Convert to proper UTF-8 if needed
            if (function_exists('mb_convert_encoding')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            }
            // Clean any remaining encoding artifacts
            $text = str_replace(['Â°', 'â„¢'], ['°', '™'], $text);
            $instructions[] = $text;
        }
        
        $nutrition = [];
        $nutrition_nodes = $xpath->query("//ul[@class='recipe-nutrition']/li");
        foreach ($nutrition_nodes as $node) {
            $nutrition[] = trim($node->nodeValue);
        }

        // Parse nutrition information
        $nutrition_meta = [
            '_recipe_calories' => '',
            '_recipe_fat' => '',               // fatContent
            '_recipe_carbohydrates' => '',     // carbohydrateContent
            '_recipe_fiber' => '',             // fiberContent
            '_recipe_protein' => '',           // proteinContent
            '_recipe_sugar' => '',             // sugarContent
            '_recipe_sodium' => '',            // sodiumContent
            '_recipe_cholesterol' => ''        // cholesterolContent
        ];

        foreach ($nutrition as $nutrition_item) {
            if (preg_match('/(calories|fat|carbs|carbohydrates|protein|sugar|fiber|sodium|cholesterol):?\s*([0-9\.]+)\s*(kcal|g|mg)?/i', 
                strtolower($nutrition_item), $matches)) {
                
                $nutri_type = $matches[1];
                // Standardize field names
                if ($nutri_type === 'carbs') $nutri_type = 'carbohydrates';
                
                $value = $matches[2] . ($matches[3] ?? '');
                $meta_key = '_recipe_' . $nutri_type;
                
                if (array_key_exists($meta_key, $nutrition_meta)) {
                    $nutrition_meta[$meta_key] = $value;
                }
            }
        }

        // Generate block content
        $blocks = [];

        $blocks[] = '<!-- wp:group {"layout":{"type":"constrained","wideSize":"1000px},"style":{"spacing":{"padding":{"top":"20px","bottom":"20px"}}}} -->';
        $blocks[] = '<div class="wp-block-group">';
        
        // 1. Description
        if (!empty($recipe['description'])) {
            $blocks[] = '<!-- wp:paragraph --><p>' . esc_html($recipe['description']) . '</p><!-- /wp:paragraph -->';
        }

        // 2. Meta Group 1 (Times/Servings)
        $meta_items = [
            'servings' => [
                'icon' => 'groups',
                'label' => '',
                'value' => $servings
            ],
            'prep_time' => [
                'icon' => 'clock',
                'label' => '',
                'value' => $prep_time
            ],
            'cook_time' => [
                'icon' => 'food',
                'label' => '',
                'value' => $cook_time
            ]
        ];

        $blocks[] = '<!-- wp:group {"style":{"spacing":{"blockGap":"0.5em"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->';
        $blocks[] = '<div class="wp-block-group">';

        foreach ($meta_items as $item) {
            if (!empty($item['value'])) {
                $blocks[] = sprintf(
                    '<!-- wp:paragraph {"style":{"layout":{"selfStretch":"fixed","flexSize":"auto"}},"className":"recipe-meta-item"} -->
                    <p class="recipe-meta-item" style="display:inline-flex;align-items:center;gap:0.3em;margin:0 !important;">
                        <span class="dashicons dashicons-%s" style="font-size:1.4em;width:auto;height:auto;color:var(--wp--preset--color--accent-1)"></span>
                        <span>%s: %s</span>
                    </p>
                    <!-- /wp:paragraph -->',
                    esc_attr($item['icon']),
                    esc_html($item['label']),
                    esc_html($item['value'])
                );
            }
        }

        $blocks[] = '</div>';
        $blocks[] = '<!-- /wp:group -->';
        
        // 4. Ingredients
        if (!empty($ingredients)) {
            $blocks[] = '<!-- wp:heading {"level":3} --><h3>Ingredients</h3><!-- /wp:heading -->';
            $ingredient_blocks = array_map(function($item) {
                return '<!-- wp:list-item --><li>' . esc_html($item) . '</li><!-- /wp:list-item -->';
            }, $ingredients);
            $blocks[] = '<!-- wp:list --><ul>' . implode('', $ingredient_blocks) . '</ul><!-- /wp:list -->';
        }
        
        // 5. Instructions
        if (!empty($instructions)) {
            $blocks[] = '<!-- wp:heading {"level":3} --><h3>Instructions</h3><!-- /wp:heading -->';
            $instruction_blocks = array_map(function($item) {
                $clean = htmlspecialchars($item, ENT_HTML5 | ENT_QUOTES, 'UTF-8');
                return '<!-- wp:list-item --><li>' . $clean . '</li><!-- /wp:list-item -->';
            }, $instructions);
            $blocks[] = '<!-- wp:list {"ordered":true} --><ol>' . implode('', $instruction_blocks) . '</ol><!-- /wp:list -->';
        }
        
        // 6. Nutrition
        if (!empty($nutrition)) {
            $blocks[] = '<!-- wp:heading {"level":3} --><h3>Nutritional Information</h3><!-- /wp:heading -->';
            $nutrition_blocks = array_map(function($item) {
                return '<!-- wp:list-item --><li>' . esc_html($item) . '</li><!-- /wp:list-item -->';
            }, $nutrition);
            $blocks[] = '<!-- wp:list --><ul>' . implode('', $nutrition_blocks) . '</ul><!-- /wp:list -->';
        }
        $blocks[] = '</div>';
        $blocks[] = '<!-- /wp:group -->';

        // Create the post
        $post_id = wp_insert_post([
            'post_title'   => $recipe['name'],
            'post_content' => implode("\n\n", $blocks),
            'post_status'  => 'draft',
            'post_author'  => $user_id,
            'post_type'    => 'ai_recipe',
            'meta_input'   => array_merge([
                '_recipe_servings' => $servings,
                '_recipe_prep_time' => $prep_mins,
                '_recipe_cook_time' => $cook_mins,
                '_recipe_total_time' => $total_time,
                '_recipe_generator_id' => $recipe_id,
                '_recipe_original_user' => $user_id
            ], $nutrition_meta)
        ]);
        
        if (is_wp_error($post_id)) {
            error_log('[Recipe Generator] Post creation failed: ' . $post_id->get_error_message());
            continue;
        }

        // Set category and tags
        $default_category = get_term_by('slug', 'main-dishes', 'ai_recipe_category');
        if ($default_category) {
            wp_set_object_terms($post_id, $default_category->term_id, 'ai_recipe_category');
        }
        
        if (!empty($recipe['dietary_tags'])) {
            // Convert tag names to term IDs or create new terms
            $term_ids = array();
            foreach ($recipe['dietary_tags'] as $tag_name) {
                $term = term_exists($tag_name, 'ai_recipe_tag');
                if (!$term) {
                    $term = wp_insert_term($tag_name, 'ai_recipe_tag');
                }
                if (!is_wp_error($term)) {
                    $term_ids[] = (int)$term['term_id'];
                }
            }
            wp_set_object_terms($post_id, $term_ids, 'ai_recipe_tag');
        }

        // Add custom meta
        update_post_meta($post_id, '_recipe_generator_id', $recipe_id);
        update_post_meta($post_id, '_recipe_original_user', $user_id);

        $created++;
        $created_posts[] = [
            'recipe_id' => $recipe_id,
            'post_id' => $post_id,
            'edit_link' => get_edit_post_link($post_id, 'raw')
        ];
        
        error_log("[Recipe Generator] Successfully created post ID: {$post_id}");
    }
    
    wp_send_json_success([
        'count' => $created,
        'created_posts' => $created_posts,
        'message' => sprintf(_n('Created %d post', 'Created %d posts', $created), $created)
    ]);
});

add_action('wp_ajax_check_recipe_post', function() {
    check_ajax_referer('recipe_generator_frontend_nonce', '_wpnonce');
    $recipe_id = sanitize_text_field($_POST['recipe_id']);
    $post_id = recipe_generator_find_recipe_post($recipe_id);
    
    wp_send_json_success([
        'has_post' => (bool)$post_id,
        'post_url' => $post_id ? get_permalink($post_id) : null
    ]);
});

// Standalone helper functions
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

    // Custom sanitizer that preserves existing encoded chars
    $safe_output = function($string) {
        // First decode any existing entities to prevent double encoding
        $decoded = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Then properly escape only what needs escaping
        return htmlspecialchars($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    };
    
    ob_start(); ?>
    <div class="recipe-container">
        <h2><?php echo $safe_output($recipe['recipe_name']); ?></h2>
        <?php if (!empty($recipe['description'])) : ?>
            <p class="description"><?php echo $safe_output($recipe['description']); ?></p>
        <?php endif; ?>
        
        <div class="recipe-meta">
            <?php if (!empty($recipe['servings'])) : ?>
                <span>Servings: <?php echo $safe_output($recipe['servings']); ?></span>
            <?php endif; ?>
            <?php if (!empty($recipe['preparation_time'])) : ?>
                <span>Prep: <?php echo $safe_output($recipe['preparation_time']); ?></span>
            <?php endif; ?>
            <?php if (!empty($recipe['cooking_time'])) : ?>
                <span>Cook: <?php echo $safe_output($recipe['cooking_time']); ?></span>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($recipe['ingredients'])) : ?>
            <h3>Ingredients</h3>
            <ul>
                <?php foreach ((array)$recipe['ingredients'] as $ingredient) : ?>
                    <li><?php echo $safe_output($ingredient); ?></li>
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
    
    // Extract recipe name from HTML
    $recipe_name = 'AI Recipe';
    if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $recipe_html, $matches)) {
        $recipe_name = sanitize_text_field(strip_tags($matches[1]));
    }

    // Extract description from HTML
    $description = '';
    if (preg_match('/<p class="recipe-description">(.*?)<\/p>/i', $recipe_html, $matches)) {
        $description = wp_strip_all_tags($matches[1]);
    }

    // Extract dietary tags from HTML
    // $dietary_tags = [];
    // if (preg_match_all('/<span class="dietary-tag">(.*?)<\/span>/i', $recipe_html, $matches)) {
    //     $dietary_tags = array_map('sanitize_text_field', $matches[1]);
    // }
    $dietary_tags = [];
    if (isset($_POST['dietary_tags'])) {
        if (is_array($_POST['dietary_tags'])) {
            $dietary_tags = array_map('sanitize_text_field', $_POST['dietary_tags']);
        } else {
            // Handle both comma-separated and other string formats
            $tags_string = sanitize_text_field($_POST['dietary_tags']);
            $dietary_tags = array_filter(array_map('trim', explode(',', $tags_string)));
        }
    }
    
    // Get existing saved recipes
    $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
    
    // Add new recipe with all metadata
    $saved_recipes[$recipe_id] = [
        'id' => $recipe_id,
        'name' => $recipe_name,
        'description' => $description,
        'html' => $recipe_html,
        'dietary_tags' => $dietary_tags, // Store the extracted tags
        'saved_at' => current_time('mysql'),
        'saved_at_gmt' => current_time('mysql', true)
    ];

    update_user_meta($user_id, 'ai_saved_recipes', $saved_recipes);
    
    wp_send_json_success([
        'recipe_name' => $recipe_name,
        'recipe_id' => $recipe_id
    ]);
}

function recipe_generator_find_recipe_post($recipe_id) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_recipe_generator_id' AND meta_value = %s LIMIT 1",
        $recipe_id
    ));
}

