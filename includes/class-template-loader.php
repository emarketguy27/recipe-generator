<?php
/**
 * Handles loading and registering block templates for the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Recipe_Generator_Template_Loader {
    /**
     * Register block templates.
     */
    public static function register_templates() {
        // Register single recipe template
        register_block_template('recipe-generator//single-recipe', [
            'title'       => __('Recipe Template', 'recipe-generator'),
            'description' => __('Pre-designed template for AI-recipe posts', 'recipe-generator'),
            'content'     => self::get_template_content('single-recipe'),
        ]);

        // Register single recipe with sidebar template
        register_block_template('recipe-generator//single-recipe-with-sidebar', [
            'title'       => __('Recipe Template with Sidebar', 'recipe-generator'),
            'description' => __('Pre-designed template for AI-recipe posts', 'recipe-generator'),
            'content'     => self::get_template_content('single-recipe-with-sidebar'),
        ]);

        // Regsiter Recipes Archive Template
        register_block_template( 'recipe-generator//archive-recipe', [
            'title'       => __( 'Recipe Archive', 'recipe-generator' ),
            'description' => __( 'Displays recipe posts archives, taxonomies and searches.', 'recipe-generator' ),
            'content'     => self::get_template_content( 'archive-recipe' ),
            'post_types'  => ['ai_recipe'] // Explicitly associate ai recipe CPT
        ] );
    }

    /**
     * Get template content from file with validation.
     *
     * @param string $template_name Template name without extension.
     * @return string Template content.
     */
    private static function get_template_content($template_name) {
        $template_path = RECIPE_GENERATOR_PATH . "templates/{$template_name}.html";

        // Validate template file exists and is readable
        if (!file_exists($template_path) || !is_readable($template_path)) {
            error_log(sprintf(
                __('Template file %s does not exist or is not readable', 'recipe-generator'),
                $template_path
            ));
            return '';
        }

        // Get file contents with error suppression in case read fails
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $content = @file_get_contents($template_path);

        if (false === $content) {
            error_log(sprintf(
                __('Failed to read template file: %s', 'recipe-generator'),
                $template_path
            ));
            return '';
        }

        return $content;
    }
}