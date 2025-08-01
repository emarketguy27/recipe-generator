<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (!defined('RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL')) {
    define('RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL', true);
}

if (defined('RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL') && RECIPE_GENERATOR_REMOVE_DATA_ON_UNINSTALL) {
    // ======================
    // POSTS & ATTACHMENTS
    // ======================
    $posts = get_posts([
        'post_type'      => 'ai_recipe',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids'
    ]);

    foreach ($posts as $post_id) {
        // Delete attachments first if any
        $attachments = get_children([
            'post_parent' => $post_id,
            'post_type'   => 'attachment',
            'fields'      => 'ids'
        ]);

        foreach ($attachments as $attachment_id) {
            wp_delete_attachment($attachment_id, true);
        }

        wp_delete_post($post_id, true);
    }

    // ======================
    // TAXONOMIES & TERMS
    // ======================
    // First register the taxonomies - Required to ensure explicit registration during clean environment of Uninstall.
    register_taxonomy('ai_recipe_category', 'ai_recipe');
    register_taxonomy('ai_recipe_tag', 'ai_recipe');

    $taxonomies = ['ai_recipe_category', 'ai_recipe_tag'];
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'fields'     => 'ids'
        ]);

        foreach ($terms as $term_id) {
            $result = wp_delete_term($term_id, $taxonomy);
        }
        
        // Clean taxonomy cache
        clean_taxonomy_cache($taxonomy);
    }

    // Unregister taxonomies
    unregister_taxonomy('ai_recipe_category');
    unregister_taxonomy('ai_recipe_tag');

    // ======================
    // USER SAVED RECIPES
    // ======================
    $users = get_users([
        'meta_key' => 'ai_saved_recipes',
        'fields'   => 'ids'
    ]);

    foreach ($users as $user_id) {
        delete_user_meta($user_id, 'ai_saved_recipes');
        
        // Get all user meta keys starting with 'recipe_generator'
        $meta_keys = array_keys(get_user_meta($user_id));
        foreach ($meta_keys as $meta_key) {
            if (strpos($meta_key, 'recipe_generator') === 0) {
                delete_user_meta($user_id, $meta_key);
            }
        }
    }

    // ======================
    // PLUGIN OPTIONS
    // ======================
    $all_options = wp_load_alloptions();
    foreach ($all_options as $name => $value) {
        if (strpos($name, 'recipe_generator') === 0) {
            delete_option($name);
        }
    }

    // ======================
    // TRANSIENTS & CACHE
    // ======================
    // Get all transients from cache
    $transient_keys = [];
    $alloptions = wp_load_alloptions();
    foreach ($alloptions as $name => $value) {
        if (strpos($name, '_transient_') === 0 && stripos($name, 'recipe') !== false) {
            $transient_keys[] = str_replace('_transient_', '', $name);
        }
        if (strpos($name, '_transient_timeout_') === 0 && stripos($name, 'recipe') !== false) {
            $transient_keys[] = str_replace('_transient_timeout_', '', $name);
        }
    }

    // Delete unique transients
    $transient_keys = array_unique($transient_keys);
    foreach ($transient_keys as $transient) {
        delete_transient($transient);
    }

    // ======================
    // CLEANUP
    // ======================
    flush_rewrite_rules();
    wp_cache_flush();
}