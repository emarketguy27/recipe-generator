<?php
if (!defined('ABSPATH')) {
    exit;
}

class Recipe_Generator_Admin_Saved_Recipes {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_submenu_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        
    }

    public function add_submenu_page() {
        add_submenu_page(
            'recipe-generator',
            __('Saved Recipes', 'recipe-generator'),
            __('Saved Recipes', 'recipe-generator'),
            'manage_options',
            'recipe-generator-saved-recipes',
            array($this, 'render_page')
        );
    }

    public function enqueue_assets($hook) {
        if ('recipe-generator_page_recipe-generator-saved-recipes' !== $hook) {
            return;
        }

        $plugin = Recipe_Generator::get_instance();
        $plugin->enqueue_admin_assets($hook);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'recipe-generator'));
        }

        $saved_recipes_table = new Recipe_Generator_Saved_Recipes_List_Table();
        $saved_recipes_table->prepare_items();
        ?>
        <div class="wrap recipe-generator-admin">
            <h1><?php esc_html_e('Saved Recipes', 'recipe-generator'); ?></h1>
            
            <form method="post">
                <?php $saved_recipes_table->display(); ?>
            </form>
        </div>
        <?php
    }
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Recipe_Generator_Saved_Recipes_List_Table extends WP_List_Table {
    // Add bulk actions
    public function get_bulk_actions() {
        return [
            'create_post' => __('Create WordPress Post', 'recipe-generator'),
            'delete' => __('Delete', 'recipe-generator')
        ];
    }

    // Handle bulk actions
    // public function process_bulk_action() {
    //     error_log('Bulk action triggered: ' . $this->current_action()); // Debug

    //     if (!current_user_can('manage_options')) {
    //         return;
    //     }

    //     $recipe_ids = isset($_REQUEST['recipe']) ? (array)$_REQUEST['recipe'] : [];
    //     $user_id = isset($_REQUEST['user_id']) ? absint($_REQUEST['user_id']) : 0;

    //     if (empty($recipe_ids) || empty($user_id)) {
    //         return;
    //     }

    //     switch ($this->current_action()) {
    //         case 'create_post':
    //             foreach ($recipe_ids as $index => $recipe_id) {
    //                 $user_id = $user_ids[$index] ?? 0;
    //                 error_log("Processing recipe $recipe_id for user $user_id");
                    
    //                 $result = $this->create_recipe_post($recipe_id, $user_id);
    //                 if ($result) {
    //                     error_log("Created post ID: $result");
    //                 } else {
    //                     error_log("Failed to create post for recipe $recipe_id");
    //                 }
    //             }
    //             break;
                
    //         case 'delete':
    //             $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
    //             foreach ($recipe_ids as $recipe_id) {
    //                 if (isset($saved_recipes[$recipe_id])) {
    //                     unset($saved_recipes[$recipe_id]);
    //                 }
    //             }
    //             update_user_meta($user_id, 'ai_saved_recipes', $saved_recipes);
    //             break;
    //     }
    //     add_action('admin_notices', function() {
    //         echo '<div class="notice notice-success is-dismissible"><p>Bulk action completed.</p></div>';
    //     });
    // }
    public function process_bulk_action() {
        if ($this->current_action() !== 'delete' || !current_user_can('manage_options')) {
            return;
        }
        error_log('Bulk delete request received: ' . print_r($_REQUEST, true)); // Debug

        $recipe_ids = isset($_REQUEST['recipe']) ? (array)$_REQUEST['recipe'] : [];
        $user_id = isset($_REQUEST['user_id']) ? absint($_REQUEST['user_id']) : 0;

        if (empty($recipe_ids) || empty($user_id)) {
            return;
        }

        $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
        foreach ($recipe_ids as $recipe_id) {
            unset($saved_recipes[$recipe_id]);
        }
        update_user_meta($user_id, 'ai_saved_recipes', $saved_recipes);
    }

    // Method to create WordPress post from recipe
    // private function create_recipe_post($recipe_id, $user_id) {
    //     $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
        
    //     if (!isset($saved_recipes[$recipe_id])) {
    //         return false;
    //     }

    //     $recipe = $saved_recipes[$recipe_id];
        
    //     // Create post
    //     $post_id = wp_insert_post([
    //         'post_title'   => $recipe['name'],
    //         'post_content' => $recipe['html'],
    //         'post_status' => 'publish',
    //         'post_author' => $user_id,
    //         'post_type'   => 'post'
    //     ]);

    //     if (is_wp_error($post_id)) {
    //         return false;
    //     }

    //     // Set Recipe category
    //     $recipe_category = get_category_by_slug('recipe');
    //     if ($recipe_category) {
    //         wp_set_post_categories($post_id, [$recipe_category->term_id]);
    //     }

    //     // Set dietary tags as post tags
    //     if (!empty($recipe['dietary_tags'])) {
    //         wp_set_post_tags($post_id, $recipe['dietary_tags']);
    //     }

    //     // Add custom meta
    //     update_post_meta($post_id, '_recipe_generator_id', $recipe_id);
    //     update_post_meta($post_id, '_recipe_original_user', $user_id);

    //     return true;
    // }
    private function create_recipe_post($recipe_id, $user_id) {
        error_log("Recipe Creation Instigated - create_recipe_post()");
        $saved_recipes = get_user_meta($user_id, 'ai_saved_recipes', true) ?: [];
        
        if (!isset($saved_recipes[$recipe_id])) {
            error_log("Recipe $recipe_id not found for user $user_id");
            return false;
        }

        $recipe = $saved_recipes[$recipe_id];
        
        // Build blocks array
        $blocks = [];
        
        // 1. Title (handled by post_title)
        
        // 2. Description
        if (!empty($recipe['description'])) {
            $blocks[] = [
                'blockName' => 'core/paragraph',
                'attrs' => [],
                'innerHTML' => '<p>' . esc_html($recipe['description']) . '</p>'
            ];
        }
        
        // 3. Recipe Meta (simple paragraph for now)
        $meta = [];
        if (!empty($recipe['servings'])) $meta[] = 'Servings: ' . $recipe['servings'];
        if (!empty($recipe['preparation_time'])) $meta[] = 'Prep: ' . $recipe['preparation_time'];
        if (!empty($recipe['cooking_time'])) $meta[] = 'Cook: ' . $recipe['cooking_time'];
        
        if (!empty($meta)) {
            $blocks[] = [
                'blockName' => 'core/paragraph',
                'attrs' => [],
                'innerHTML' => '<p>' . implode(' | ', $meta) . '</p>'
            ];
        }
        
        // 4. Ingredients
        if (!empty($recipe['ingredients'])) {
            $blocks[] = [
                'blockName' => 'core/heading',
                'attrs' => ['level' => 3],
                'innerHTML' => '<h3>Ingredients</h3>'
            ];
            
            $ingredient_items = array_map(function($item) {
                return '<!-- wp:list-item --><li>' . esc_html($item) . '</li><!-- /wp:list-item -->';
            }, $recipe['ingredients']);
            
            $blocks[] = [
                'blockName' => 'core/list',
                'attrs' => [],
                'innerHTML' => '<ul>' . implode('', $ingredient_items) . '</ul>'
            ];
        }
        
        // 5. Instructions
        if (!empty($recipe['method'])) {
            $blocks[] = [
                'blockName' => 'core/heading',
                'attrs' => ['level' => 3],
                'innerHTML' => '<h3>Instructions</h3>'
            ];
            
            $method_items = array_map(function($item) {
                return '<!-- wp:list-item --><li>' . esc_html($item) . '</li><!-- /wp:list-item -->';
            }, $recipe['method']);
            
            $blocks[] = [
                'blockName' => 'core/list',
                'attrs' => ['ordered' => true],
                'innerHTML' => '<ol>' . implode('', $method_items) . '</ol>'
            ];
        }
        
        // Create the post
        $post_id = wp_insert_post([
            'post_title' => $recipe['name'],
            'post_content' => serialize_blocks($blocks),
            'post_status' => 'draft',
            'post_author' => $user_id,
            'post_type' => 'ai_recipe',
            'meta_input' => [
                '_recipe_generator_id' => $recipe_id,
                '_recipe_original_user' => $user_id
            ]
        ]);
        
        if (is_wp_error($post_id)) {
            error_log('Post creation failed: ' . $post_id->get_error_message());
            return false;
        }
        
        // Set taxonomy terms
        if ($recipe_category = get_category_by_slug('recipe')) {
            wp_set_post_categories($post_id, [$recipe_category->term_id]);
        }
        
        if (!empty($recipe['dietary_tags'])) {
            wp_set_post_tags($post_id, $recipe['dietary_tags']);
        }
        
        return $post_id;
    }

    // Helper methods for block creation
    private function create_paragraph_block($content) {
        return [
            'blockName' => 'core/paragraph',
            'attrs' => [],
            'innerBlocks' => [],
            'innerHTML' => '<p>' . esc_html($content) . '</p>',
            'innerContent' => ['<p>' . esc_html($content) . '</p>']
        ];
    }

    private function create_heading_block($content, $level = 2) {
        return [
            'blockName' => 'core/heading',
            'attrs' => ['level' => $level],
            'innerBlocks' => [],
            'innerHTML' => '<h' . $level . '>' . esc_html($content) . '</h' . $level . '>',
            'innerContent' => ['<h' . $level . '>' . esc_html($content) . '</h' . $level . '>']
        ];
    }

    private function create_list_block($items, $ordered = false) {
        $list_items = array_map(function($item) {
            return [
                'blockName' => 'core/list-item',
                'attrs' => [],
                'innerHTML' => '<li>' . esc_html($item) . '</li>',
                'innerContent' => ['<li>' . esc_html($item) . '</li>']
            ];
        }, $items);

        return [
            'blockName' => 'core/list',
            'attrs' => ['ordered' => $ordered],
            'innerBlocks' => $list_items,
            'innerHTML' => $ordered ? '<ol></ol>' : '<ul></ul>',
            'innerContent' => array_fill(0, count($list_items) * 2, null)
        ];
    }

    private function create_columns_block($inner_blocks) {
        $columns = array_map(function($block) {
            return [
                'blockName' => 'core/column',
                'attrs' => [],
                'innerBlocks' => [$block],
                'innerHTML' => '<div class="wp-block-column"></div>',
                'innerContent' => ['', '']
            ];
        }, $inner_blocks);

        return [
            'blockName' => 'core/columns',
            'attrs' => [],
            'innerBlocks' => $columns,
            'innerHTML' => '<div class="wp-block-columns"></div>',
            'innerContent' => array_fill(0, count($columns) * 2, null)
        ];
    }

    public function __construct() {
        parent::__construct([
            'singular' => 'saved_recipe',
            'plural'   => 'saved_recipes',
            'ajax'     => false
        ]);
    }

    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'recipe_name'    => __('Recipe Name', 'recipe-generator'),
            'description'    => __('Description', 'recipe-generator'),
            'dietary_tags'   => __('Dietary Tags', 'recipe-generator'),
            'saved_date'    => __('Saved Date', 'recipe-generator'),
            'user_name'     => __('User', 'recipe-generator'),
            'post_status'   => __('Post Status', 'recipe-generator'), 
        ];
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="recipe[]" value="%s" data-user-id="%d" />',
            $item['id'],
            $item['user_id']
        );
    }

    public function get_sortable_columns() {
        return [
            'recipe_name' => ['recipe_name', false],
            'saved_date' => ['saved_date', true]
        ];
    }

    public function prepare_items() {
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
        
        // Get all users with saved recipes
        $users = get_users([
            'meta_key' => 'ai_saved_recipes',
            'meta_compare' => 'EXISTS'
        ]);
        
        $data = [];
        
        foreach ($users as $user) {
            $saved_recipes = get_user_meta($user->ID, 'ai_saved_recipes', true) ?: [];
            
            foreach ($saved_recipes as $recipe_id => $recipe) {
                // Use stored description if available
                $description = $recipe['description'] ?? '';
                
                // Get dietary tags - now properly stored in the data
                $dietary_tags = '';
                if (!empty($recipe['dietary_tags'])) {
                    $dietary_tags = is_array($recipe['dietary_tags']) 
                        ? implode(', ', $recipe['dietary_tags'])
                        : $recipe['dietary_tags'];
                }
                
                $data[] = [
                    'id'            => $recipe_id,
                    'user_id'       => $user->ID,
                    'user_name'     => $user->display_name,
                    'recipe_name'   => $recipe['name'] ?? __('Untitled Recipe', 'recipe-generator'),
                    'description'   => $description,
                    'dietary_tags'  => $dietary_tags,
                    'saved_date'    => $recipe['saved_at'] ?? '',
                    'html'          => $recipe['html'] ?? ''
                ];
            }
        }
        
        // Sorting
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'saved_date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'desc';
        
        usort($data, function($a, $b) use ($orderby, $order) {
            $result = strcmp($a[$orderby], $b[$orderby]);
            return $order === 'asc' ? $result : -$result;
        });
        
        // Pagination
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page
        ]);
        
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);
        
        $this->items = $data;
    }

    public function column_default($item, $column_name) {
        return $item[$column_name] ?? '';
    }

    public function column_recipe_name($item) {
        $actions = [
            'view' => sprintf(
                '<a href="#" class="view-recipe" data-recipe-html="%s">%s</a>',
                esc_attr(wp_json_encode($item['html'])),
                __('View', 'recipe-generator')
            ),
            'delete' => sprintf(
                '<a href="%s" class="submitdelete">%s</a>',
                wp_nonce_url(
                    add_query_arg([
                        'action' => 'delete_recipe',
                        'recipe_id' => $item['id'],
                        'user_id' => $item['user_id']
                    ], admin_url('admin.php')),
                    'delete_recipe_' . $item['id']
                ),
                __('Delete', 'recipe-generator')
            )
        ];

        return sprintf(
            '<strong>%1$s</strong>%2$s',
            esc_html($item['recipe_name']),
            $this->row_actions($actions)
        );
    }

    public function column_user_name($item) {
        return '<a href="' . get_edit_user_link($item['user_id']) . '">' . esc_html($item['user_name']) . '</a>';
    }

    public function column_saved_date($item) {
        return $item['saved_date'] ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['saved_date'])) : '';
    }

    public function no_items() {
        _e('No saved recipes found.', 'recipe-generator');
    }

    public function column_post_status($item) {
        // Find if this recipe has been converted to a post
        $post_id = $this->find_recipe_post($item['id']);
        
        if (!$post_id) {
            return '<span class="recipe-status"><span class="dashicons dashicons-no-alt"></span> '.__('No Post', 'recipe-generator').'</span>';
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return '<span class="recipe-status"><span class="dashicons dashicons-warning"></span> '.__('Invalid Post', 'recipe-generator').'</span>';
        }
        
        $status_map = [
            'publish' => ['dashicons dashicons-yes-alt', __('Published', 'recipe-generator')],
            'draft'   => ['dashicons dashicons-edit', __('Draft', 'recipe-generator')],
            'pending' => ['dashicons dashicons-clock', __('Pending', 'recipe-generator')],
            'future'  => ['dashicons dashicons-calendar', __('Scheduled', 'recipe-generator')],
            'private' => ['dashicons dashicons-lock', __('Private', 'recipe-generator')],
            'trash'   => ['dashicons dashicons-trash', __('Trashed', 'recipe-generator')]
        ];
        
        $status_info = $status_map[$post->post_status] ?? ['dashicons dashicons-warning', __('Unknown', 'recipe-generator')];
        
        return sprintf(
            '<span class="recipe-status"><span class="%s"></span> %s <a href="%s" target="_blank">%s</a></span>',
            $status_info[0],
            $status_info[1],
            get_edit_post_link($post_id),
            __('(Edit)', 'recipe-generator')
        );
    }

    private function find_recipe_post($recipe_id) {
        return recipe_generator_find_recipe_post($recipe_id);
    }
}