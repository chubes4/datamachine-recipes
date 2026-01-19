# Schema.org Recipe Implementation

This document outlines the Schema.org Recipe properties implemented in the Data Machine Recipes plugin, specifically for the `datamachine-recipes/recipe-schema` Gutenberg block.

## Block Name
- **Registered Name**: `datamachine-recipes/recipe-schema`
- **Text Domain**: `datamachine-recipes`

## Core Recipe Properties

### Basic Information
- `recipeName` (string) - The name of the recipe
- `description` (string) - Description of the recipe
- `images` (array) - Recipe images with URL and alt text
- `author` (object) - Recipe author with name and URL

### Timing (ISO 8601 Duration Format)
- `prepTime` (string) - Preparation time (e.g., "PT30M" for 30 minutes)
- `cookTime` (string) - Cooking time 
- `totalTime` (string) - Total time required

### Recipe Content
- `recipeIngredient` (array) - List of ingredients
- `recipeInstructions` (array) - Step-by-step instructions
- `recipeYield` (string) - Number of servings or yield

### Classification
- `recipeCategory` (array) - Recipe categories (appetizer, entree, etc.)
- `recipeCuisine` (string) - Cuisine type (French, Italian, etc.)
- `cookingMethod` (string) - Method of cooking (frying, steaming, etc.)
- `keywords` (array) - Keywords/tags for the recipe

### Nutritional Information
- `nutrition` (object) - Comprehensive nutrition data
  - `calories` - Calories per serving
  - `carbohydrateContent` - Carbohydrate content
  - `cholesterolContent` - Cholesterol content
  - `fatContent` - Fat content
  - `fiberContent` - Fiber content
  - `proteinContent` - Protein content
  - `saturatedFatContent` - Saturated fat content
  - `servingSize` - Serving size
  - `sodiumContent` - Sodium content
  - `sugarContent` - Sugar content
  - `transFatContent` - Trans fat content
  - `unsaturatedFatContent` - Unsaturated fat content

### Dietary Restrictions
- `suitableForDiet` (array) - Dietary restrictions (vegetarian, gluten-free, etc.)

### Media Content
- `video` (object) - Recipe video information
  - `name` - Video title
  - `description` - Video description
  - `thumbnailUrl` - Video thumbnail URL
  - `contentUrl` - Video content URL
  - `embedUrl` - Video embed URL
  - `uploadDate` - Video upload date
  - `duration` - Video duration (ISO 8601)

### Equipment and Supplies
- `tool` (array) - Tools required for the recipe
- `supply` (array) - Supplies needed for the recipe

### Additional Properties
- `datePublished` (string) - Publication date
- `estimatedCost` (string) - Estimated cost of recipe

## JSON-LD Output

The block automatically generates complete Schema.org Recipe structured data including:

- **Basic Recipe Information**: Name, description, images with proper @context and @type
- **ISO 8601 Timing**: Preparation time, cooking time, and total time in ISO 8601 duration format
- **HowToStep Instructions**: Recipe instructions with proper HowToStep markup and sequential naming
- **Person Author**: Author information from WordPress post author with name and URL
- **AggregateRating Integration**: Rating data from WordPress post meta (when available and valid)
- **NutritionInformation**: Comprehensive nutritional data with proper Schema.org NutritionInformation type
- **VideoObject Support**: Video content with proper VideoObject markup including contentUrl, thumbnailUrl, and duration
- **Additional Properties**: All recipe properties including cuisine, category, cooking method, dietary restrictions, tools, and supplies

## Microdata HTML Output

The block renders hidden semantic HTML with comprehensive microdata attributes:
- **Recipe Wrapper**: `itemscope itemtype="https://schema.org/Recipe"` on main container
- **Meta Elements**: Individual `<meta itemprop>` elements for all recipe properties
- **HowToStep Structure**: Nested `itemscope itemtype="https://schema.org/HowToStep"` for instructions
- **Person Markup**: Author information with `itemscope itemtype="https://schema.org/Person"`
- **AggregateRating Integration**: Rating markup with `itemscope itemtype="https://schema.org/AggregateRating"`
- **Hidden Implementation**: All microdata elements are hidden from frontend display but accessible to search engines

## Integration with WordPress

### Post Meta Integration
- **Aggregate Rating System**: Automatically includes `rating_value` and `review_count` post meta for Schema.org AggregateRating markup
- **Author Integration**: Uses WordPress post author data for Schema.org Person markup with name and URL
- **Publication Date Handling**: Falls back to post publication date for `datePublished` if not provided
- **WordPress User System**: Integrates with WordPress user system for author information with proper URL linking
- **Rating Validation**: Only includes aggregate ratings when review_count > 0 and rating_value is between 1-5

### Duration Formatting
- Converts ISO 8601 duration format (PT30M) to human-readable format (30 minutes)
- Handles hours and minutes combinations with proper pluralization
- Supports proper internationalization through `datamachine-recipes` text domain
- Function: `datamachine_recipes_format_duration()` handles ISO 8601 parsing and formatting

### Block Registration
- **Registration Function**: `datamachine_recipes_register_recipe_schema_block()` registers block on WordPress `init` hook
- **Server-side Rendering**: `datamachine_recipes_render_recipe_schema_block()` handles both microdata and JSON-LD output
- **JSON-LD Generation**: `datamachine_recipes_generate_recipe_jsonld()` creates complete Schema.org Recipe structured data
- **Hidden Output**: Block content is hidden from frontend display (style="display: none;") as it only provides structured data
- **Build Integration**: Uses compiled block definition from `/build/recipe-schema/` directory

## Text Domain Consistency
All user-facing strings use the `datamachine-recipes` text domain for proper internationalization support.