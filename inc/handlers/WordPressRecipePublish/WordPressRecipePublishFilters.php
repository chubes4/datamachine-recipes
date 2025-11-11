<?php
namespace DataMachineRecipes\WordPressRecipePublish;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register WordPress Recipe Publish handler with Data Machine filter system.
 *
 * Registers handler discovery, AI tool generation, settings configuration, and
 * handler directives with Data Machine's filter-based architecture. Enables
 * dynamic AI tool generation based on taxonomy configuration and provides
 * comprehensive recipe publishing capabilities.
 *
 * @since 1.0.0
 */
function datamachine_recipes_register_wordpress_recipe_publish_filters() {
    
    add_filter( 'datamachine_handlers', function( $handlers ) {
        $handlers['wordpress_recipe_publish'] = [
            'type' => 'publish',
            'class' => WordPressRecipePublish::class,
            'label' => __( 'WordPress Recipe', 'datamachine-recipes' ),
            'description' => __( 'Publish recipes to WordPress with Schema.org structured data markup', 'datamachine-recipes' )
        ];
        return $handlers;
    } );

    add_filter( 'ai_tools', function( $tools, $handler_slug = null, $handler_config = [] ) {
        if ( $handler_slug === 'wordpress_recipe_publish' ) {
            // Extract config using Data Machine pattern (handles nested structure)
            $recipe_config = $handler_config['wordpress_recipe_publish'] ?? $handler_config;
            
            // Apply global defaults like Data Machine WordPress publisher
            $recipe_config = apply_filters('datamachine_apply_global_defaults', $recipe_config, 'wordpress_recipe_publish', 'publish');
            
            $tools['wordpress_recipe_publish'] = datamachine_recipes_get_dynamic_recipe_tool($recipe_config);
        }
        return $tools;
    }, 10, 3 );
    
    add_filter( 'datamachine_handler_settings', function( $all_settings ) {
        $all_settings['wordpress_recipe_publish'] = new WordPressRecipePublishSettings();
        return $all_settings;
    } );



    add_filter('datamachine_tool_success_message', function($default_message, $tool_name, $tool_result, $tool_parameters) {
        if ($tool_name === 'wordpress_recipe_publish') {
            $data = $tool_result['data'] ?? [];
            $title = $data['post_title'] ?? $tool_parameters['post_title'] ?? '';
            $url = $data['post_url'] ?? '';
            $post_id = $data['post_id'] ?? '';
            
            if (!empty($title)) {
                if (!empty($url)) {
                    return "SUCCESS: Recipe published successfully. Recipe: '{$title}' is now live at {$url} (ID: {$post_id}). Your recipe has been created and is ready to cook!";
                } else {
                    return "SUCCESS: Recipe created successfully. Recipe: '{$title}' (ID: {$post_id}). Your recipe has been published to WordPress as requested.";
                }
            }
        }
        return $default_message;
    }, 10, 4);
    
}

/**
 * Generate dynamic recipe tool based on taxonomy configuration.
 * Follows Data Machine pattern for dynamic tool generation.
 *
 * @param array $recipe_config Recipe handler configuration
 * @return array Dynamic tool configuration with wordpress_recipe_publish tool definition
 */
function datamachine_recipes_get_dynamic_recipe_tool(array $recipe_config): array {
    $tool = [
        'class' => WordPressRecipePublish::class,
        'method' => 'handle_tool_call',
        'handler' => 'wordpress_recipe_publish',
        'description' => 'Create WordPress recipe posts with Schema.org structured data markup for SEO-optimized recipe content including ingredients, instructions, timing, and nutrition.',
        'parameters' => [
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
            ]
        ],
        'handler_config' => $recipe_config
    ];
    
    $taxonomies = get_taxonomies(['public' => true], 'objects');
    
    foreach ($taxonomies as $taxonomy) {
        $excluded = apply_filters('datamachine_wordpress_system_taxonomies', []);
        if (in_array($taxonomy->name, $excluded)) {
            continue;
        }

        $field_key = "taxonomy_{$taxonomy->name}_selection";
        $selection = $recipe_config[$field_key] ?? 'skip';
        
        if ($selection === 'ai_decides') {
            $parameter_name = $taxonomy->name === 'category' ? 'category' :
                             ($taxonomy->name === 'post_tag' ? 'tags' : $taxonomy->name);
            
            if ($taxonomy->hierarchical) {
                $tool['parameters'][$parameter_name] = [
                    'type' => 'string',
                    'required' => true,
                    'description' => "Select most appropriate {$taxonomy->name} based on content"
                ];
            } else {
                $tool['parameters'][$parameter_name] = [
                    'type' => 'array',
                    'required' => true,
                    'description' => "Choose one or more relevant {$taxonomy->name} for the content"
                ];
            }
        }
    }
    
    return $tool;
}

datamachine_recipes_register_wordpress_recipe_publish_filters();
