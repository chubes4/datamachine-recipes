# Data Machine Recipes

A WordPress plugin that extends Data Machine with comprehensive recipe publishing capabilities, featuring full Schema.org structured data support and agentic AI integration.

## Features

- **WordPress Recipe Publish Handler**: AI-powered recipe publishing through Data Machine's Pipeline+Flow system
- **Complete Schema.org Recipe Support**: Full implementation of Schema.org Recipe markup with JSON-LD and microdata
- **Gutenberg Recipe Schema Block**: Native WordPress block for recipe structured data
- **Multi-Provider AI Integration**: Compatible with OpenAI, Anthropic, Google, Grok, and OpenRouter
- **Comprehensive Recipe Attributes**: Support for ingredients, instructions, timing, nutrition, dietary restrictions, and more
- **SEO Optimized**: Rich snippets and search engine optimization through proper structured data

## Requirements

- WordPress 5.0+
- PHP 7.4+
- [Data Machine](https://github.com/chubes4/data-machine) plugin
- Composer (for development)

## Installation

### From Release ZIP
1. Download the latest release ZIP file
2. Upload to WordPress via **Plugins > Add New > Upload Plugin**
3. Activate the plugin
4. Ensure Data Machine plugin is also installed and activated

### Development Installation
```bash
# Clone the repository
git clone https://github.com/chubes4/dm-recipes.git
cd dm-recipes

# Install PHP dependencies
composer install

# Install npm dependencies for Gutenberg block development
npm install

# Build frontend assets
npm run build

# Create production build
./build.sh
```

## How It Works

### AI-Powered Recipe Publishing
1. **Data Machine Integration**: The plugin registers a `wordpress_recipe_publish` handler with Data Machine's filter-based discovery system
2. **AI Tool Registration**: Provides the `wordpress_recipe_publish` AI tool with comprehensive Schema.org Recipe parameters
3. **Automated Publishing**: AI agents can create WordPress posts with embedded Recipe Schema blocks containing complete structured data
4. **Schema.org Compliance**: Generated posts include both JSON-LD and microdata markup for optimal SEO

### Recipe Schema Block
- **Complete Attribute Support**: All Schema.org Recipe properties including nutrition, dietary restrictions, cooking methods, and video content
- **Gutenberg Integration**: Native WordPress block editor integration with sophisticated React components
- **Dual Structured Data Output**: Automatic generation of both JSON-LD structured data and HTML microdata
- **WordPress Integration**: Uses post author data and rating system for aggregate ratings
- **Search Engine Optimization**: Rich snippets and enhanced search results through proper Schema.org markup
- **Enhanced Formatting**: Comprehensive Gutenberg block syntax guidelines for optimal content structure

## Usage

### Through Data Machine Pipelines
Configure a Data Machine pipeline with:
1. **Fetch Handler**: Retrieve recipe data from external sources
2. **AI Processing**: Transform and enhance recipe content
3. **WordPress Recipe Publish Handler**: Create WordPress posts with Schema.org markup

### Manual Block Usage
Add the "Recipe Schema" block to any WordPress post or page and fill in the recipe details for automatic structured data generation.

## AI Tool Parameters

The `wordpress_recipe_publish` tool accepts comprehensive recipe data:

- **Post Content**: `post_title`, `post_content` (with Gutenberg block formatting guidelines)
- **Recipe Basics**: `recipeName`, `description`, `images`, `author`
- **Timing**: `prepTime`, `cookTime`, `totalTime` (ISO 8601 format)
- **Content**: `recipeIngredient[]`, `recipeInstructions[]`, `recipeYield`
- **Classification**: `recipeCategory[]`, `recipeCuisine`, `cookingMethod`, `keywords[]`
- **Dietary**: `suitableForDiet[]`, `nutrition{}`
- **Media**: `video{}`, `images[]`
- **Equipment**: `tool[]`, `supply[]`
- **Additional**: `estimatedCost`, `datePublished`

## Development

### Commands
```bash
# Install dependencies
composer install                         # PHP dependencies
npm install                              # Frontend dependencies

# Frontend development (Gutenberg blocks)
npm run start                            # Development with hot reload
npm run build                            # Production build for blocks
npm run lint:js                          # JavaScript linting
npm run lint:css                         # CSS linting
npm run format                           # Auto-format code

# PHP development
composer lint                            # Run PHP CodeSniffer
composer lint:fix                        # Auto-fix coding standards
composer lint:php                        # WordPress-specific linting
composer lint:fix:php                    # WordPress auto-fix

# Production build (dual system)
./build.sh                               # Complete production build with validation
```

### Build System
- **Dual Build Architecture**: Frontend (npm/wp-scripts) + Backend (Composer/PHP) build systems
- **Modern Gutenberg Development**: React components compiled from `src/` to `build/` with @wordpress/scripts
- **Automated ZIP Creation**: Production-ready plugin packages via `build.sh` with comprehensive validation
- **Dependency Management**: Separate PHP (Composer) and JavaScript (npm) dependency trees
- **Code Quality**: PHP CodeSniffer with WordPress standards + ESLint/Stylelint for frontend
- **Asset Compilation**: Source files transformed to production-optimized JavaScript and CSS
- **File Validation**: Build process validates both compiled assets and essential plugin files
- **Clean Distribution**: Excludes source files, development dependencies, and build tools from production

## Architecture

### Handler Registration
Self-registering via WordPress filters:
- `dm_handlers` - Handler discovery
- `ai_tools` - AI tool registration with dynamic taxonomy parameters
- `dm_handler_settings` - Configuration management
- `dm_handler_directives` - AI model guidance for recipe content generation
- `dm_tool_success_message` - Custom success message formatting for recipe publishing

### File Structure
```
dm-recipes/
├── dm-recipes.php                       # Main plugin file with autoloader
├── build.sh                             # Dual-system production build script
├── composer.json                        # PHP dependencies, autoloading, linting scripts
├── package.json                         # npm dependencies and wp-scripts configuration
├── src/                                 # Frontend source files
│   └── recipe-schema/                   # React components and block source
│       ├── index.js                     # Sophisticated React editor interface
│       ├── block.json                   # Block definition with Schema.org attributes
│       └── style.scss                   # Block styling
├── build/                               # Compiled frontend assets (auto-generated)
│   └── recipe-schema/                   # Production-ready JavaScript and assets
│       ├── index.js                     # Compiled React components
│       ├── block.json                   # Processed block definition
│       └── index.asset.php              # WordPress asset dependencies
├── inc/
│   ├── handlers/WordPressRecipePublish/ # Data Machine handler implementation
│   │   ├── WordPressRecipePublish.php   # Main handler with AI tool call method
│   │   ├── WordPressRecipePublishFilters.php  # Filter-based registration system
│   │   └── WordPressRecipePublishSettings.php # Configuration management
│   └── blocks/recipe-schema/            # Server-side block registration
│       ├── recipe-schema.php            # Block rendering with microdata/JSON-LD
│       └── index.php                    # Block initialization hooks
└── .claude/
    └── recipe-schema.md                 # Schema.org implementation reference
```


## Contributing

1. Fork the repository
2. Create a feature branch
3. Follow WordPress coding standards
4. Run `composer lint` before committing
5. Submit a pull request

## License

GPL-2.0-or-later - See LICENSE file for details.

## Author

**Chris Huber**
- Website: [chubes.net](https://chubes.net)
- GitHub: [@chubes4](https://github.com/chubes4) 