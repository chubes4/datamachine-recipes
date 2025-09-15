<?php
namespace DM_Recipes\WordPressRecipePublish;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WordPress Recipe Publish Handler
 * 
 * Handles AI tool execution for recipe publishing with comprehensive Schema.org structured data.
 * Integrates with Data Machine's filter-based architecture to create WordPress posts with
 * embedded Recipe Schema blocks for optimal SEO and rich snippets.
 * 
 * @package DM_Recipes\WordPressRecipePublish
 * @since 1.0.0
 */
class WordPressRecipePublish {

    /**
     * Handle AI tool execution for recipe publishing.
     *
     * Creates a WordPress post with the provided content and embeds a Recipe Schema block
     * containing comprehensive Schema.org structured data. Processes configuration from
     * Data Machine's handler settings including taxonomy assignments.
     *
     * @param array $parameters AI tool parameters containing recipe data and post content
     * @param array $tool_def   Tool definition with handler configuration
     * @return array Success/failure response with nested data object and tool_name for Data Machine
     * @since 1.0.0
     */
    public function handle_tool_call( array $parameters, array $tool_def = [] ): array {
        if ( empty( $parameters['post_title'] ) ) {
            return [
                'success' => false,
                'error' => 'Recipe title is required'
            ];
        }

        if ( empty( $tool_def['handler_config'] ) ) {
            return [
                'success' => false,
                'error' => 'Missing handler_config in tool definition'
            ];
        }
        
        $handler_config = $tool_def['handler_config']['wordpress_recipe_publish'] ?? $tool_def['handler_config'];
        
        if ( empty( $handler_config ) ) {
            return [
                'success' => false,
                'error' => 'Empty handler configuration for wordpress_recipe_publish'
            ];
        }
        
        if ( empty( $handler_config['post_type'] ) ) {
            return [
                'success' => false,
                'error' => 'Missing required post_type in handler configuration'
            ];
        }
        
        if ( empty( $handler_config['post_status'] ) ) {
            return [
                'success' => false,
                'error' => 'Missing required post_status in handler configuration'
            ];
        }
        
        if ( empty( $handler_config['post_author'] ) ) {
            return [
                'success' => false,
                'error' => 'Missing required post_author in handler configuration'
            ];
        }
        
        $handler_config = apply_filters('dm_apply_global_defaults', $handler_config, 'wordpress_recipe_publish', 'publish');

        $recipe_block_result = $this->create_recipe_schema_block( $parameters, $handler_config );
        
        if ( ! $recipe_block_result['success'] ) {
            return [
                'success' => false,
                'error' => 'Failed to create recipe block: ' . $recipe_block_result['error']
            ];
        }

        $content = wp_kses_post( wp_unslash( $parameters['post_content'] ?? '' ) );
        $content .= "\n\n" . $recipe_block_result['block'];

        $post_data = [
            'post_title' => sanitize_text_field( $parameters['post_title'] ),
            'post_content' => $content,
            'post_status' => $handler_config['post_status'],
            'post_author' => $handler_config['post_author'],
            'post_type' => $handler_config['post_type']
        ];

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return [
                'success' => false,
                'error' => 'Failed to create post: ' . ( is_wp_error( $post_id ) ? $post_id->get_error_message() : 'Invalid post ID' )
            ];
        }
        
        $taxonomy_results = $this->process_taxonomies_from_settings( $post_id, $parameters, $handler_config );
        

        return [
            'success' => true,
            'data' => [
                'post_id' => $post_id,
                'post_title' => $parameters['post_title'],
                'post_url' => get_permalink( $post_id ),
                'edit_url' => get_edit_post_link( $post_id, 'raw' ),
                'taxonomy_results' => $taxonomy_results
            ],
            'tool_name' => 'wordpress_recipe_publish'
        ];
    }
    
    /**
     * Create Recipe Schema Gutenberg block from AI parameters.
     *
     * Transforms AI-provided recipe data into a properly formatted Gutenberg block
     * with comprehensive Schema.org Recipe attributes. Handles sanitization,
     * validation, and JSON encoding for block attributes.
     *
     * @param array $parameters     AI tool parameters containing recipe data
     * @param array $handler_config Handler configuration for author attribution
     * @return array Success/failure response with generated block HTML
     * @since 1.0.0
     */
    private function create_recipe_schema_block( array $parameters, array $handler_config = [] ): array {
        $recipe_data = [
            'recipeName' => sanitize_text_field( $parameters['recipeName'] ?? '' ),
            'description' => wp_kses_post( $parameters['description'] ?? '' ),
            'prepTime' => sanitize_text_field( $parameters['prepTime'] ?? '' ),
            'cookTime' => sanitize_text_field( $parameters['cookTime'] ?? '' ),
            'totalTime' => sanitize_text_field( $parameters['totalTime'] ?? '' ),
            'recipeYield' => sanitize_text_field( $parameters['recipeYield'] ?? '' ),
            'recipeCuisine' => sanitize_text_field( $parameters['recipeCuisine'] ?? '' ),
            'cookingMethod' => sanitize_text_field( $parameters['cookingMethod'] ?? '' ),
            'recipeIngredient' => $this->sanitize_array( $parameters['recipeIngredient'] ?? [] ),
            'recipeInstructions' => $this->sanitize_array( $parameters['recipeInstructions'] ?? [] ),
            'recipeCategory' => $this->sanitize_array( $parameters['recipeCategory'] ?? [] ),
            'keywords' => $this->sanitize_array( $parameters['keywords'] ?? [] ),
            'suitableForDiet' => $this->sanitize_array( $parameters['suitableForDiet'] ?? [] )
        ];
        
        if ( ! empty( $parameters['images'] ) && is_array( $parameters['images'] ) ) {
            $recipe_data['images'] = array_map( function( $image ) {
                return [
                    'url' => esc_url_raw( $image['url'] ?? '' ),
                    'alt' => sanitize_text_field( $image['alt'] ?? '' )
                ];
            }, $parameters['images'] );
        }
        
        $author_user = get_userdata( $handler_config['post_author'] );
        if ( $author_user ) {
            $recipe_data['author'] = [
                'name' => sanitize_text_field( $author_user->display_name ),
                'url' => esc_url_raw( get_author_posts_url( $author_user->ID ) )
            ];
        }
        
        if ( ! empty( $parameters['nutrition'] ) && is_array( $parameters['nutrition'] ) ) {
            $recipe_data['nutrition'] = array_map( 'sanitize_text_field', $parameters['nutrition'] );
        }
        
        if ( ! empty( $parameters['video'] ) && is_array( $parameters['video'] ) ) {
            $recipe_data['video'] = [
                'name' => sanitize_text_field( $parameters['video']['name'] ?? '' ),
                'description' => sanitize_text_field( $parameters['video']['description'] ?? '' ),
                'contentUrl' => esc_url_raw( $parameters['video']['contentUrl'] ?? '' ),
                'thumbnailUrl' => esc_url_raw( $parameters['video']['thumbnailUrl'] ?? '' ),
                'duration' => sanitize_text_field( $parameters['video']['duration'] ?? '' )
            ];
        }
        
        if ( ! empty( $parameters['tool'] ) && is_array( $parameters['tool'] ) ) {
            $recipe_data['tool'] = $this->sanitize_array( $parameters['tool'] );
        }
        
        if ( ! empty( $parameters['supply'] ) && is_array( $parameters['supply'] ) ) {
            $recipe_data['supply'] = $this->sanitize_array( $parameters['supply'] );
        }
        
        if ( ! empty( $parameters['estimatedCost'] ) ) {
            $recipe_data['estimatedCost'] = sanitize_text_field( $parameters['estimatedCost'] );
        }
        
        $recipe_data['datePublished'] = sanitize_text_field( $parameters['datePublished'] ?? '' ) ?: current_time( 'c' );
        
        $block_attributes = wp_json_encode( $recipe_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        
        if ( $block_attributes === false ) {
            return [
                'success' => false,
                'error' => 'Failed to encode recipe data as JSON: ' . json_last_error_msg()
            ];
        }
        
        $block_html = '<!-- wp:dm-recipes/recipe-schema ' . $block_attributes . ' -->' . "\n" .
                     '<!-- /wp:dm-recipes/recipe-schema -->';
        
        return [
            'success' => true,
            'block' => $block_html
        ];
    }
    
    /**
     * Sanitize array input for recipe data.
     *
     * Filters and sanitizes array values, removing empty entries and applying
     * sanitize_text_field to each element for security.
     *
     * @param mixed $input Raw input to sanitize
     * @return array Sanitized array with filtered, clean values
     * @since 1.0.0
     */
    private function sanitize_array( $input ): array {
        if ( ! is_array( $input ) ) {
            return [];
        }
        return array_map( 'sanitize_text_field', array_filter( $input ) );
    }
    
    /**
     * Process taxonomies based on handler configuration settings.
     * Copied from Data Machine WordPress publisher.
     *
     * @param int   $post_id        Post ID
     * @param array $parameters     AI tool parameters
     * @param array $handler_config Handler configuration from settings
     * @return array Taxonomy processing results
     */
    private function process_taxonomies_from_settings(int $post_id, array $parameters, array $handler_config): array {
        $taxonomy_results = [];
        
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        
        foreach ($taxonomies as $taxonomy) {
            if (in_array($taxonomy->name, ['post_format', 'nav_menu', 'link_category'])) {
                continue;
            }
            
            $field_key = "taxonomy_{$taxonomy->name}_selection";
            
            if ( ! isset( $handler_config[$field_key] ) ) {
                $taxonomy_results[$taxonomy->name] = [
                    'success' => false,
                    'error' => "Missing taxonomy configuration: {$field_key}"
                ];
                continue;
            }
            
            $selection = $handler_config[$field_key];
            
            if ($selection === 'skip') {
                continue;
                
            } elseif ($selection === 'ai_decides') {
                $param_name = $taxonomy->name === 'category' ? 'category' : 
                             ($taxonomy->name === 'post_tag' ? 'tags' : $taxonomy->name);
                
                if (!empty($parameters[$param_name])) {
                    $taxonomy_result = $this->assign_taxonomy($post_id, $taxonomy->name, $parameters[$param_name]);
                    $taxonomy_results[$taxonomy->name] = $taxonomy_result;
                }
                
            } elseif (is_numeric($selection)) {
                $term_id = absint($selection);
                $term = get_term($term_id, $taxonomy->name);
                
                if (!is_wp_error($term) && $term) {
                    $result = wp_set_object_terms($post_id, [$term_id], $taxonomy->name);
                    
                    if (is_wp_error($result)) {
                        $taxonomy_results[$taxonomy->name] = [
                            'success' => false,
                            'error' => $result->get_error_message()
                        ];
                    } else {
                        $taxonomy_results[$taxonomy->name] = [
                            'success' => true,
                            'taxonomy' => $taxonomy->name,
                            'term_count' => 1,
                            'terms' => [$term->name]
                        ];
                    }
                }
            }
        }
        
        return $taxonomy_results;
    }
    
    /**
     * Assign custom taxonomy to post.
     * Copied from Data Machine WordPress publisher.
     *
     * @param int    $post_id       Post ID
     * @param string $taxonomy_name Taxonomy name
     * @param mixed  $taxonomy_value Taxonomy value (string or array)
     * @return array Assignment result
     */
    private function assign_taxonomy(int $post_id, string $taxonomy_name, $taxonomy_value): array {
        if (!taxonomy_exists($taxonomy_name)) {
            return [
                'success' => false,
                'error' => "Taxonomy '{$taxonomy_name}' does not exist"
            ];
        }
        
        $taxonomy_obj = get_taxonomy($taxonomy_name);
        $term_ids = [];
        
        $terms = is_array($taxonomy_value) ? $taxonomy_value : [$taxonomy_value];
        
        foreach ($terms as $term_name) {
            $term_name = sanitize_text_field($term_name);
            if (empty($term_name)) continue;
            
            $term = get_term_by('name', $term_name, $taxonomy_name);
            if (!$term) {
                $term_result = wp_insert_term($term_name, $taxonomy_name);
                if (is_wp_error($term_result)) {
                    continue;
                }
                $term_ids[] = $term_result['term_id'];
            } else {
                $term_ids[] = $term->term_id;
            }
        }
        
        if (!empty($term_ids)) {
            $result = wp_set_object_terms($post_id, $term_ids, $taxonomy_name);
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'error' => $result->get_error_message()
                ];
            }
        }
        
        return [
            'success' => true,
            'taxonomy' => $taxonomy_name,
            'term_count' => count($term_ids),
            'terms' => $terms
        ];
    }
    
}