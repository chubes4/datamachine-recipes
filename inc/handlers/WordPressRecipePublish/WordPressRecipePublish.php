<?php
namespace DataMachineRecipes\Handlers\WordPressRecipePublish;

use DataMachine\Core\Steps\Publish\Handlers\PublishHandler;
use DataMachine\Core\Steps\HandlerRegistrationTrait;
use DataMachine\Core\WordPress\WordPressSettingsResolver;
use DataMachine\Core\WordPress\TaxonomyHandler;
use DataMachine\Core\WordPress\WordPressPublishHelper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Recipe publishing with Schema.org structured data.
 *
 * Creates WordPress posts with embedded Recipe Schema blocks for SEO and rich snippets.
 *
 * @package DataMachineRecipes\WordPressRecipePublish
 * @since 1.0.0
 */
class WordPressRecipePublish extends PublishHandler {
    use HandlerRegistrationTrait;

    protected $taxonomy_handler;

    public function __construct() {
        parent::__construct('wordpress_recipe_publish');
        $this->taxonomy_handler = new TaxonomyHandler();
    }

    /**
     * Register the handler.
     */
    public static function register(): void {
        self::registerHandler(
            'wordpress_recipe_publish',
            'publish',
            self::class,
            __('WordPress Recipe', 'datamachine-recipes'),
            __('Publish recipes to WordPress with Schema.org structured data', 'datamachine-recipes'),
            false,
            null,
            WordPressRecipePublishSettings::class,
            function($tools, $handler_slug, $handler_config) {
                if ($handler_slug === 'wordpress_recipe_publish') {
                    $base_params = self::get_recipe_parameters();
                    $taxonomy_params = TaxonomyHandler::getTaxonomyToolParameters($handler_config);
                    
                    $tools['wordpress_recipe_publish'] = [
                        'class' => self::class,
                        'method' => 'handle_tool_call',
                        'handler' => 'wordpress_recipe_publish',
                        'description' => 'Create WordPress recipe posts with Schema.org structured data markup for SEO-optimized recipe content including ingredients, instructions, timing, and nutrition.',
                        'parameters' => array_merge($base_params, $taxonomy_params),
                        'handler_config' => $handler_config
                    ];
                }
                return $tools;
            }
        );
    }

    /**
     * Execute recipe publishing.
     *
     * @param array $parameters AI tool parameters
     * @param array $handler_config Handler configuration
     * @return array Success/failure response
     * @since 1.0.0
     */
    protected function executePublish(array $parameters, array $handler_config): array {
        // Parent PublishHandler ensures job_id and engine are present
        $job_id = $parameters['job_id'];
        $engine = $parameters['engine'];

        if ( empty( $parameters['post_title'] ) ) {
            return $this->errorResponse('Recipe title is required');
        }

        if ( empty( $handler_config ) ) {
            return $this->errorResponse('Empty handler configuration for wordpress_recipe_publish');
        }

        $post_status = WordPressSettingsResolver::getPostStatus($handler_config);
        $post_author = WordPressSettingsResolver::getPostAuthor($handler_config);
        $post_type = $handler_config['post_type'] ?? 'post';

        if ( empty( $post_type ) ) {
            return $this->errorResponse('Missing required post_type in handler configuration');
        }

        if ( empty( $post_status ) ) {
            return $this->errorResponse('Missing required post_status in handler configuration');
        }

        if ( empty( $post_author ) ) {
            return $this->errorResponse('Missing required post_author in handler configuration');
        }

        $handler_config = apply_filters('datamachine_apply_global_defaults', $handler_config, 'wordpress_recipe_publish', 'publish');

        $recipe_block_result = $this->create_recipe_schema_block( $parameters, $handler_config );

        if ( ! $recipe_block_result['success'] ) {
            return $this->errorResponse('Failed to create recipe block: ' . $recipe_block_result['error']);
        }

        $content = wp_unslash( $parameters['post_content'] ?? '' );
        
        // Apply source attribution using core helper
        $content = WordPressPublishHelper::applySourceAttribution($content, $engine->getSourceUrl(), $handler_config);
        
        // Append recipe block
        $content .= "\n\n" . $recipe_block_result['block'];
        
        // Filter content for security
        $content = wp_filter_post_kses( $content );

        $post_data = [
            'post_title' => sanitize_text_field( $parameters['post_title'] ),
            'post_content' => $content,
            'post_status' => $post_status,
            'post_author' => $post_author,
            'post_type' => $post_type
        ];

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            $error_msg = 'Failed to create post: ' . ( is_wp_error( $post_id ) ? $post_id->get_error_message() : 'Invalid post ID' );
            return $this->errorResponse($error_msg);
        }

        // Attach featured image if available and configured
        WordPressPublishHelper::attachImageToPost($post_id, $engine->getImagePath(), $handler_config);

        // Use shared taxonomy processing for standard public taxonomies.
        $taxonomy_results = $this->taxonomy_handler->processTaxonomies( $post_id, $parameters, $handler_config, $engine->all() );

        // Store post_id in engine data for downstream handlers
        apply_filters('datamachine_engine_data', null, $job_id, [
            'post_id' => $post_id,
            'published_url' => get_permalink($post_id)
        ]);

        return $this->successResponse([
            'post_id' => $post_id,
            'post_title' => $parameters['post_title'],
            'post_url' => get_permalink( $post_id ),
            'edit_url' => get_edit_post_link( $post_id, 'raw' ),
            'taxonomy_results' => $taxonomy_results
        ]);
    }
    
    /**
     * Get base recipe parameters.
     *
     * @return array
     */
    private static function get_recipe_parameters(): array {
        return [
            'post_title' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The title of the blog post'
            ],
            'post_content' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Recipe article content formatted as WordPress Gutenberg blocks. Use <!-- wp:paragraph --><p>Content</p><!-- /wp:paragraph --> for paragraphs.

HEADINGS - Use proper heading hierarchy for recipe sections:
• H2: <!-- wp:heading --><h2 class="wp-block-heading">Ingredients</h2><!-- /wp:heading -->
• H3: <!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Instructions</h3><!-- /wp:heading -->
• H4: <!-- wp:heading {"level":4} --><h4 class="wp-block-heading">Tips & Notes</h4><!-- /wp:heading -->

LISTS - Use correct block syntax:
• Unordered lists: <!-- wp:list --><ul class="wp-block-list"><li>Item 1</li><li>Item 2</li></ul><!-- /wp:list -->
• Ordered lists (recipe steps): <!-- wp:list {"ordered":true} --><ol class="wp-block-list"><li>Step 1</li><li>Step 2</li></ol><!-- /wp:list -->

Use ordered lists for recipe instructions and cooking steps to ensure proper formatting and semantic HTML.'
            ],
            'recipeName' => [
                'type' => 'string',
                'required' => true,
                'description' => 'The name of the recipe'
            ],
            'description' => [
                'type' => 'string',
                'description' => 'A description of the recipe'
            ],
            'prepTime' => [
                'type' => 'string',
                'description' => 'Preparation time in ISO 8601 format (e.g., PT30M for 30 minutes)'
            ],
            'cookTime' => [
                'type' => 'string',
                'description' => 'Cooking time in ISO 8601 format (e.g., PT1H for 1 hour)'
            ],
            'totalTime' => [
                'type' => 'string',
                'description' => 'Total time in ISO 8601 format (prep + cook time)'
            ],
            'recipeYield' => [
                'type' => 'string',
                'description' => 'Number of servings or yield (e.g., "4 servings", "12 muffins")'
            ],
            'recipeCategory' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'Recipe categories (e.g., ["appetizer", "main course", "dessert"])'
            ],
            'recipeCuisine' => [
                'type' => 'string',
                'description' => 'The cuisine type (e.g., "Italian", "Mexican", "American")'
            ],
            'cookingMethod' => [
                'type' => 'string',
                'description' => 'Cooking method (e.g., "baking", "grilling", "frying")'
            ],
            'recipeIngredient' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'List of ingredients with quantities (e.g., ["2 cups flour", "1 tsp salt"])'
            ],
            'recipeInstructions' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'Step-by-step cooking instructions'
            ],
            'keywords' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'Keywords or tags for the recipe'
            ],
            'suitableForDiet' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'Dietary restrictions (e.g., ["vegetarian", "gluten-free", "low-carb"])'
            ],
            'nutrition' => [
                'type' => 'object',
                'properties' => [
                    'calories' => ['type' => 'string', 'description' => 'Calories per serving'],
                    'fatContent' => ['type' => 'string', 'description' => 'Fat content'],
                    'carbohydrateContent' => ['type' => 'string', 'description' => 'Carbohydrate content'],
                    'proteinContent' => ['type' => 'string', 'description' => 'Protein content'],
                    'sodiumContent' => ['type' => 'string', 'description' => 'Sodium content'],
                    'fiberContent' => ['type' => 'string', 'description' => 'Fiber content']
                ],
                'description' => 'Nutritional information for the recipe'
            ],
            'video' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Video title'],
                    'description' => ['type' => 'string', 'description' => 'Video description'],
                    'contentUrl' => ['type' => 'string', 'description' => 'Video URL'],
                    'thumbnailUrl' => ['type' => 'string', 'description' => 'Video thumbnail URL'],
                    'duration' => ['type' => 'string', 'description' => 'Video duration in ISO 8601 format']
                ],
                'description' => 'Recipe video information'
            ],
            'tool' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'Cooking tools or equipment needed'
            ],
            'supply' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'Supplies consumed during cooking (beyond ingredients)'
            ],
            'estimatedCost' => [
                'type' => 'string',
                'description' => 'Estimated cost to make the recipe'
            ],
            'datePublished' => [
                'type' => 'string',
                'description' => 'Publication date in ISO 8601 format (auto-generated if not provided)'
            ],
            'job_id' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Job ID for tracking workflow execution'
            ]
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
        
        $author_id = $handler_config['post_author'];
        $author_name = apply_filters( 'datamachine_wordpress_user_display_name', null, $author_id );
        if ( $author_name ) {
            $recipe_data['author'] = [
                'name' => sanitize_text_field( $author_name ),
                'url' => esc_url_raw( get_author_posts_url( $author_id ) )
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
        
        $block_html = '<!-- wp:datamachine-recipes/recipe-schema ' . $block_attributes . ' -->' . "\n" .
                     '<!-- /wp:datamachine-recipes/recipe-schema -->';
        
        return [
            'success' => true,
            'block' => $block_html
        ];
    }
    
    private function sanitize_array( $input ): array {
        if ( ! is_array( $input ) ) {
            return [];
        }
        return array_map( 'sanitize_text_field', array_filter( $input ) );
    }
    
    // Taxonomy assignment is handled centrally by TaxonomyHandler via applyTaxonomies().

    /**
     * Assign pre-selected taxonomy term.
     *
     * @param int $post_id Post ID
     * @param string $taxonomy_name Taxonomy name
     * @param int $term_id Term ID
     * @return array|null Assignment result
     */
    // Direct term assignment is handled by TaxonomyHandler when handler_config indicates pre-selected terms.

    /**
     * Assign AI-decided taxonomy terms with dynamic term creation.
     *
     * @param int $post_id Post ID
     * @param string $taxonomy_name Taxonomy name
     * @param string|array $term_value Term value(s)
     * @return array|null Assignment result
     */
    // AI-decided taxonomy assignment is now handled by the centralized TaxonomyHandler's assignTaxonomy.

}