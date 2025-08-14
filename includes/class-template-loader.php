<?php
/**
 * Handles loading and registering block templates for the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ai_Powered_Recipe_Generator_Template_Loader {
    /**
     * Register block templates.
     */
    public static function register_templates() {
        // Register single recipe template
        register_block_template('ai-powered-recipe-generator//single-recipe', [
            'title'       => __('Recipe Template - Basic', 'ai-powered-recipe-generator'),
            'description' => __('Pre-designed template for AI-recipe posts', 'ai-powered-recipe-generator'),
            'content'     => self::get_template_content('single-recipe'),
        ]);

        // Register single recipe no sidebar template
        register_block_template('ai-powered-recipe-generator//single-recipe-no-sidebar', [
            'title'       => __('Recipe Template - Hero Top - no Sidebar', 'ai-powered-recipe-generator'),
            'description' => __('Pre-designed template for AI-recipe posts', 'ai-powered-recipe-generator'),
            'content'     => self::get_template_content('single-recipe-no-sidebar'),
        ]);

        // Register single recipe no sidebar template
        register_block_template('ai-powered-recipe-generator//single-recipe-with-sidebar', [
            'title'       => __('Recipe Template - Hero Top - with Sidebar', 'ai-powered-recipe-generator'),
            'description' => __('Pre-designed template for AI-recipe posts', 'ai-powered-recipe-generator'),
            'content'     => self::get_template_content('single-recipe-with-sidebar'),
        ]);

        // Register single recipe with aside template - fully SEO Optimised
        register_block_template('ai-powered-recipe-generator//single-recipe-with-aside', [
            'title'       => __('Recipe Template with Aside', 'ai-powered-recipe-generator'),
            'description' => __('Pre-designed template for AI-recipe posts - with all elements SEO Optimized', 'ai-powered-recipe-generator'),
            'content'     => self::get_template_content('single-recipe-with-aside'),
        ]);

        // Regsiter Recipes Archive Template
        register_block_template( 'ai-powered-recipe-generator//archive-recipe', [
            'title'       => __( 'Recipe Archive', 'ai-powered-recipe-generator' ),
            'description' => __( 'Displays recipe posts archives, taxonomies and searches.', 'ai-powered-recipe-generator' ),
            'content'     => self::get_template_content( 'archive-recipe' ),
            'post_types'  => ['aiprg_recipe'] // Explicitly associate ai recipe CPT
        ] );
    }

    /**
     * Get template content from file with validation.
     *
     * @param string $template_name Template name without extension.
     * @return string Template content.
     */
    private static function get_template_content($template_name) {
        $template_path = AI_POWERED_RECIPE_GENERATOR_PATH . "templates/{$template_name}.html";

        // Validate template file exists and is readable
        if (!file_exists($template_path) || !is_readable($template_path)) {
            return '';
        }

        // Get file contents with error suppression in case read fails
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $content = @file_get_contents($template_path);

        if (false === $content) {
            return '';
        }

        return $content;
    }
}