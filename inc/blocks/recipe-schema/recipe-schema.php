<?php
/**
 * Recipe Schema Gutenberg Block Implementation
 *
 * Provides complete Schema.org Recipe structured data generation through a Gutenberg block.
 * Renders both microdata and JSON-LD markup for optimal SEO and rich snippets in search results.
 * Integrates with WordPress post rating system and supports comprehensive recipe attributes.
 *
 * @package DM_Recipes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Register Recipe Schema block with WordPress.
 *
 * Registers the dm-recipes/recipe-schema block type using the compiled block.json
 * definition and server-side rendering callback for Schema.org markup generation.
 *
 * @since 1.0.0
 */
function dm_recipes_register_recipe_schema_block() {
    register_block_type( DM_RECIPES_PLUGIN_DIR . 'build/recipe-schema', array(
        'render_callback' => 'dm_recipes_render_recipe_schema_block',
    ) );
}

/**
 * Render Recipe Schema block with comprehensive structured data.
 *
 * Generates both microdata and JSON-LD structured data for Schema.org Recipe compliance.
 * Outputs hidden HTML elements with microdata attributes and injects JSON-LD script
 * for search engine optimization and rich snippet generation.
 *
 * @param array $attributes Block attributes containing recipe data from Gutenberg editor
 * @return string Complete HTML output with Schema.org markup (hidden from frontend)
 * @since 1.0.0
 */
function dm_recipes_render_recipe_schema_block( $attributes ) {
    global $post;
    
    $defaults = [
        'recipeName' => '',
        'description' => '',
        'prepTime' => '',
        'cookTime' => '',
        'totalTime' => '',
        'recipeYield' => '',
        'recipeCategory' => [],
        'recipeCuisine' => '',
        'recipeIngredient' => [],
        'recipeInstructions' => [],
        'nutrition' => [],
        'suitableForDiet' => [],
        'keywords' => [],
        'cookingMethod' => '',
        'video' => [],
        'author' => ['name' => '', 'url' => ''],
        'datePublished' => '',
        'estimatedCost' => '',
        'tool' => [],
        'supply' => []
    ];
    
    $attributes = wp_parse_args( $attributes, $defaults );

    ob_start();
    ?>
    
    <!-- Recipe Schema Data (hidden from frontend display) -->
    <div class="recipe-schema-data" style="display: none;" itemscope itemtype="https://schema.org/Recipe">
        
        <?php if ( ! empty( $attributes['recipeName'] ) ) : ?>
            <meta itemprop="name" content="<?php echo esc_attr( $attributes['recipeName'] ); ?>" />
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['description'] ) ) : ?>
            <meta itemprop="description" content="<?php echo esc_attr( wp_strip_all_tags( $attributes['description'] ) ); ?>" />
        <?php endif; ?>
        
        <?php
        // Use featured image for recipe schema
        $featured_image_url = get_the_post_thumbnail_url( $post->ID, 'full' );
        if ( $featured_image_url ) : ?>
            <meta itemprop="image" content="<?php echo esc_url( $featured_image_url ); ?>" />
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['prepTime'] ) ) : ?>
            <meta itemprop="prepTime" content="<?php echo esc_attr( $attributes['prepTime'] ); ?>" />
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['cookTime'] ) ) : ?>
            <meta itemprop="cookTime" content="<?php echo esc_attr( $attributes['cookTime'] ); ?>" />
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['totalTime'] ) ) : ?>
            <meta itemprop="totalTime" content="<?php echo esc_attr( $attributes['totalTime'] ); ?>" />
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['recipeYield'] ) ) : ?>
            <meta itemprop="recipeYield" content="<?php echo esc_attr( $attributes['recipeYield'] ); ?>" />
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['recipeCuisine'] ) ) : ?>
            <meta itemprop="recipeCuisine" content="<?php echo esc_attr( $attributes['recipeCuisine'] ); ?>" />
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['recipeIngredient'] ) ) : ?>
            <?php foreach ( $attributes['recipeIngredient'] as $ingredient ) : ?>
                <meta itemprop="recipeIngredient" content="<?php echo esc_attr( $ingredient ); ?>" />
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['recipeInstructions'] ) ) : ?>
            <?php foreach ( $attributes['recipeInstructions'] as $index => $instruction ) : ?>
                <div itemprop="recipeInstructions" itemscope itemtype="https://schema.org/HowToStep">
                    <meta itemprop="text" content="<?php echo esc_attr( wp_strip_all_tags( $instruction ) ); ?>" />
                    <meta itemprop="name" content="Step <?php echo esc_attr( $index + 1 ); ?>" />
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php
        $rating_value = get_post_meta( $post->ID, 'rating_value', true );
        $review_count = get_post_meta( $post->ID, 'review_count', true );
        
        if ( $review_count > 0 && $rating_value >= 1 && $rating_value <= 5 ) :
            $average_rating = round( floatval( $rating_value ), 2 );
        ?>
            <div itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                <meta itemprop="ratingValue" content="<?php echo esc_attr( $average_rating ); ?>" />
                <meta itemprop="reviewCount" content="<?php echo esc_attr( intval( $review_count ) ); ?>" />
            </div>
        <?php endif; ?>
        
        <div itemprop="author" itemscope itemtype="https://schema.org/Person">
            <meta itemprop="name" content="<?php echo esc_attr( $attributes['author']['name'] ); ?>" />
            <?php if ( ! empty( $attributes['author']['url'] ) ) : ?>
                <meta itemprop="url" content="<?php echo esc_url( $attributes['author']['url'] ); ?>" />
            <?php endif; ?>
        </div>
        
        <?php if ( ! empty( $attributes['recipeCategory'] ) ) : ?>
            <?php foreach ( $attributes['recipeCategory'] as $category ) : ?>
                <meta itemprop="recipeCategory" content="<?php echo esc_attr( $category ); ?>" />
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['keywords'] ) ) : ?>
            <meta itemprop="keywords" content="<?php echo esc_attr( implode( ', ', $attributes['keywords'] ) ); ?>" />
        <?php endif; ?>
        
        <?php if ( ! empty( $attributes['cookingMethod'] ) ) : ?>
            <meta itemprop="cookingMethod" content="<?php echo esc_attr( $attributes['cookingMethod'] ); ?>" />
        <?php endif; ?>
        
        <?php 
        $date_published = ! empty( $attributes['datePublished'] ) ? $attributes['datePublished'] : get_the_date( 'c', $post->ID );
        ?>
        <meta itemprop="datePublished" content="<?php echo esc_attr( $date_published ); ?>" />
        
    </div>
    
    <?php
    $schema_data = dm_recipes_generate_recipe_jsonld( $attributes, $post );
    if ( ! empty( $schema_data ) ) {
        echo '<script type="application/ld+json">' . wp_json_encode( $schema_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
    }
    
    return ob_get_clean();
}

/**
 * Generate JSON-LD structured data for Schema.org Recipe.
 *
 * Transforms recipe block attributes into complete Schema.org Recipe JSON-LD format
 * with proper @context and @type declarations. Includes aggregate rating integration
 * from WordPress post meta and comprehensive recipe property mapping.
 *
 * @param array   $attributes Recipe block attributes from Gutenberg
 * @param WP_Post $post       WordPress post object for date and rating context
 * @return array Complete Schema.org Recipe structured data array
 * @since 1.0.0
 */
function dm_recipes_generate_recipe_jsonld( $attributes, $post ) {
    $schema = array(
        '@context' => 'https://schema.org/',
        '@type' => 'Recipe'
    );
    
    if ( ! empty( $attributes['recipeName'] ) ) {
        $schema['name'] = $attributes['recipeName'];
    }
    
    if ( ! empty( $attributes['description'] ) ) {
        $schema['description'] = wp_strip_all_tags( $attributes['description'] );
    }
    
    // Use featured image for recipe schema
    $featured_image_url = get_the_post_thumbnail_url( $post->ID, 'full' );
    if ( $featured_image_url ) {
        $schema['image'] = $featured_image_url;
    }
    
    if ( ! empty( $attributes['prepTime'] ) ) {
        $schema['prepTime'] = $attributes['prepTime'];
    }
    
    if ( ! empty( $attributes['cookTime'] ) ) {
        $schema['cookTime'] = $attributes['cookTime'];
    }
    
    if ( ! empty( $attributes['totalTime'] ) ) {
        $schema['totalTime'] = $attributes['totalTime'];
    }
    
    if ( ! empty( $attributes['recipeYield'] ) ) {
        $schema['recipeYield'] = $attributes['recipeYield'];
    }
    
    if ( ! empty( $attributes['recipeCategory'] ) ) {
        $schema['recipeCategory'] = $attributes['recipeCategory'];
    }
    
    if ( ! empty( $attributes['recipeCuisine'] ) ) {
        $schema['recipeCuisine'] = $attributes['recipeCuisine'];
    }
    
    if ( ! empty( $attributes['recipeIngredient'] ) ) {
        $schema['recipeIngredient'] = $attributes['recipeIngredient'];
    }
    
    if ( ! empty( $attributes['recipeInstructions'] ) ) {
        $schema['recipeInstructions'] = array();
        foreach ( $attributes['recipeInstructions'] as $index => $instruction ) {
            $schema['recipeInstructions'][] = array(
                '@type' => 'HowToStep',
                'name' => 'Step ' . ( $index + 1 ),
                'text' => wp_strip_all_tags( $instruction )
            );
        }
    }
    
    $schema['author'] = array(
        '@type' => 'Person',
        'name' => $attributes['author']['name']
    );
    
    if ( ! empty( $attributes['author']['url'] ) ) {
        $schema['author']['url'] = $attributes['author']['url'];
    }
    
    $schema['datePublished'] = ! empty( $attributes['datePublished'] ) 
        ? $attributes['datePublished'] 
        : get_the_date( 'c', $post->ID );
    
    $rating_value = get_post_meta( $post->ID, 'rating_value', true );
    $review_count = get_post_meta( $post->ID, 'review_count', true );
    
    if ( $review_count > 0 && $rating_value >= 1 && $rating_value <= 5 ) {
        $schema['aggregateRating'] = array(
            '@type' => 'AggregateRating',
            'ratingValue' => round( floatval( $rating_value ), 2 ),
            'reviewCount' => intval( $review_count )
        );
    }
    
    if ( ! empty( $attributes['keywords'] ) ) {
        $schema['keywords'] = $attributes['keywords'];
    }
    
    if ( ! empty( $attributes['cookingMethod'] ) ) {
        $schema['cookingMethod'] = $attributes['cookingMethod'];
    }
    
    if ( ! empty( $attributes['nutrition'] ) && array_filter( $attributes['nutrition'] ) ) {
        $schema['nutrition'] = array(
            '@type' => 'NutritionInformation'
        );
        
        foreach ( $attributes['nutrition'] as $key => $value ) {
            if ( ! empty( $value ) ) {
                $schema['nutrition'][$key] = $value;
            }
        }
    }
    
    if ( ! empty( $attributes['suitableForDiet'] ) ) {
        $schema['suitableForDiet'] = $attributes['suitableForDiet'];
    }
    
    if ( ! empty( $attributes['video'] ) && ! empty( $attributes['video']['contentUrl'] ) ) {
        $schema['video'] = array(
            '@type' => 'VideoObject',
            'name' => $attributes['video']['name'],
            'description' => $attributes['video']['description'],
            'contentUrl' => $attributes['video']['contentUrl']
        );
        
        if ( ! empty( $attributes['video']['thumbnailUrl'] ) ) {
            $schema['video']['thumbnailUrl'] = $attributes['video']['thumbnailUrl'];
        }
        
        if ( ! empty( $attributes['video']['duration'] ) ) {
            $schema['video']['duration'] = $attributes['video']['duration'];
        }
    }
    
    return $schema;
}

/**
 * Convert ISO 8601 duration to human-readable format.
 *
 * Parses ISO 8601 duration strings (PT30M, PT1H30M) and converts them to
 * human-readable format for frontend display. Handles hours and minutes
 * with proper pluralization.
 *
 * @param string $duration ISO 8601 duration string (e.g., "PT30M", "PT1H30M")
 * @return string Human-readable duration (e.g., "30 minutes", "1 hour 30 minutes")
 * @since 1.0.0
 */
function dm_recipes_format_duration( $duration ) {
    if ( strpos( $duration, 'PT' ) === 0 ) {
        $duration = substr( $duration, 2 );
        
        $hours = 0;
        $minutes = 0;
        
        if ( preg_match( '/(\d+)H/', $duration, $matches ) ) {
            $hours = intval( $matches[1] );
        }
        
        if ( preg_match( '/(\d+)M/', $duration, $matches ) ) {
            $minutes = intval( $matches[1] );
        }
        
        $parts = array();
        if ( $hours > 0 ) {
            $parts[] = $hours . ' ' . ( $hours === 1 ? 'hour' : 'hours' );
        }
        if ( $minutes > 0 ) {
            $parts[] = $minutes . ' ' . ( $minutes === 1 ? 'minute' : 'minutes' );
        }
        
        return implode( ' ', $parts );
    }
    
    return $duration;
}

