<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (!defined('RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL')) {
    define('RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL', true);
}

global $wpdb;

if (defined('RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL') && RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL) {
    
    // ======================
    // WARNING MESSAGE SYSTEM
    // ======================

    error_log('Recipe Generator Uninstall: Starting cleanup');

    // ======================
    // POSTS & ATTACHMENTS
    // ======================
    $posts = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'ai_recipe'");
    foreach ($posts as $post_id) {
        // Delete attachments first if any
        $attachments = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
            WHERE post_parent = %d 
            AND post_type = 'attachment'",
            $post_id
        ));
        
        foreach ($attachments as $attachment_id) {
            wp_delete_attachment($attachment_id, true);
        }
        
        wp_delete_post($post_id, true);

        error_log('Recipe Generator Uninstall: Deleted ' . count($posts) . ' recipes');
    }

    // ======================
    // TAXONOMIES & TERMS
    // ======================
    $taxonomies = ['ai_recipe_category', 'ai_recipe_tag'];
    foreach ($taxonomies as $taxonomy) {
        // Get all terms
        $terms = $wpdb->get_results($wpdb->prepare(
            "SELECT t.term_id, tt.term_taxonomy_id 
            FROM {$wpdb->terms} t 
            JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
            WHERE tt.taxonomy = %s",
            $taxonomy
        ));
        
        foreach ($terms as $term) {
            // Delete term relationships
            $wpdb->delete(
                $wpdb->term_relationships,
                ['term_taxonomy_id' => $term->term_taxonomy_id]
            );
            
            // Delete term meta
            $wpdb->delete(
                $wpdb->termmeta,
                ['term_id' => $term->term_id]
            );
            
            // Delete term taxonomy
            $wpdb->delete(
                $wpdb->term_taxonomy,
                ['term_taxonomy_id' => $term->term_taxonomy_id]
            );
            
            // Delete term
            $wpdb->delete(
                $wpdb->terms,
                ['term_id' => $term->term_id]
            );
        }
        error_log('Recipe Generator Uninstall: Deleted ' . count($taxonomies) . ' recipes');
    }

    // ======================
    // USER SAVED RECIPES
    // ======================
    $wpdb->query(
        "DELETE FROM {$wpdb->usermeta} 
        WHERE meta_key = 'ai_saved_recipes' 
        OR meta_key LIKE '%recipe_generator%'"
    );

    // ======================
    // PLUGIN OPTIONS
    // ======================
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE 'recipe_generator%'"
    );

    // ======================
    // TRANSIENTS & CACHE
    // ======================
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient%recipe%' 
        OR option_name LIKE '_transient_timeout%recipe%'"
    );

    // ======================
    // CLEANUP
    // ======================
    if (function_exists('flush_rewrite_rules')) {
        flush_rewrite_rules();
    }
    
    $remaining = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'ai_recipe'");
    if ($remaining > 0) {
        error_log('Recipe Generator Uninstall: Failed to delete all recipes - ' . $remaining . ' remain');
    }

    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}