<?php
// Exit if accessed directly
defined('WP_UNINSTALL_PLUGIN') || exit;

// Load plugin constants/functions
require_once plugin_dir_path(__FILE__) . 'recipe-generator.php';

// Only proceed if cleanup is enabled
if (!defined('RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL') || 
    !RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL) {
    return;
}

// Delete all custom post type posts
$post_ids = $wpdb->get_col(
    "SELECT ID FROM {$wpdb->posts} 
    WHERE post_type = 'ai_recipe'"
);

if (!empty($post_ids)) {
    // Delete post meta
    $wpdb->query(
        "DELETE FROM {$wpdb->postmeta} 
        WHERE post_id IN (" . implode(',', $post_ids) . ")"
    );
    
    // Delete posts
    $wpdb->query(
        "DELETE FROM {$wpdb->posts} 
        WHERE ID IN (" . implode(',', $post_ids) . ")"
    );
    
    // Clean up term relationships
    $wpdb->query(
        "DELETE FROM {$wpdb->term_relationships} 
        WHERE object_id IN (" . implode(',', $post_ids) . ")"
    );
}

// Delete custom taxonomies
$taxonomies = ['ai_recipe_category', 'ai_recipe_tag'];
foreach ($taxonomies as $taxonomy) {
    // Get all terms
    $terms = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT t.term_id, tt.term_taxonomy_id 
            FROM {$wpdb->terms} t 
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
            WHERE tt.taxonomy = %s",
            $taxonomy
        )
    );
    
    // Delete term meta and terms
    foreach ($terms as $term) {
        $wpdb->delete(
            $wpdb->termmeta,
            ['term_id' => $term->term_id]
        );
        $wpdb->delete(
            $wpdb->term_taxonomy,
            ['term_taxonomy_id' => $term->term_taxonomy_id]
        );
        $wpdb->delete(
            $wpdb->terms,
            ['term_id' => $term->term_id]
        );
    }
}

// Delete plugin options
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
    WHERE option_name LIKE 'recipe_generator_%'"
);

// Delete user meta (if your plugin stores any)
$wpdb->query(
    "DELETE FROM {$wpdb->usermeta} 
    WHERE meta_key LIKE 'recipe_generator_%'"
);

// Clear rewrite rules and cache
flush_rewrite_rules();
wp_cache_flush();