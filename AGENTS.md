# AGENTS.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**Version**: 1.1.0

## Migration Status

**Prefix Migration**: ✅ Complete - `datamachine_recipes` namespace and prefixes

**REST API Integration**: Filter-based handler registration via `datamachine_handlers` filter - no custom endpoints needed

**Data Machine Compatibility**: ✅ Compatible with Data Machine v0.8.0+ (requires Universal Engine architecture)

## Architecture Overview

**DM-Recipes** is a Data Machine extension plugin that adds recipe publishing capabilities with full Schema.org structured data support. It integrates with the Data Machine Pipeline+Flow system through a **filter-based discovery** architecture.

### Core Components

#### WordPress Recipe Publish Handler (`/inc/Handlers/WordPressRecipePublish/`)
- **Main Handler**: `WordPressRecipePublish.php` - AI tool execution and post creation
- **Settings Management**: `WordPressRecipePublishSettings.php` - Configuration handling
- **Registration**: Uses `HandlerRegistrationTrait` for filter-based discovery

#### Recipe Schema Gutenberg Block (Modern React Implementation)
- **Source Files**: `/src/recipe-schema/` - React components and block definitions
- **Compiled Assets**: `/build/recipe-schema/` - Production-ready JavaScript and JSON files
- **Server Components**: `/inc/Blocks/RecipeSchemaBlock.php` - PHP registration and rendering
- **React Editor Interface**: Sophisticated UI with duration inputs, array managers, and tag components

## Integration with Data Machine

### Handler Registration Pattern
Handlers self-register via `datamachine_handlers` and `chubes_ai_tools` filters using `HandlerRegistrationTrait` in `WordPressRecipePublish.php`. See ecosystem root AGENTS.md file for standard pattern documentation.

## Schema.org Recipe Implementation

### Block Attributes
The `datamachine-recipes/recipe-schema` block supports complete Schema.org Recipe markup including:

- **Basic Info**: `recipeName`, `description`, `images`, `author`
- **Timing**: `prepTime`, `cookTime`, `totalTime` (ISO 8601 format)
- **Content**: `recipeIngredient[]`, `recipeInstructions[]`, `recipeYield`
- **Classification**: `recipeCategory[]`, `recipeCuisine`, `keywords[]`
- **Advanced**: `nutrition{}`, `suitableForDiet[]`, `video{}`, `tool[]`, `supply[]`

### Structured Data Output
The block generates comprehensive Schema.org Recipe markup:
1. **Microdata**: HTML with `itemscope`, `itemtype`, and `itemprop` attributes
2. **JSON-LD**: Complete Schema.org Recipe structured data for search engines
3. **WordPress Integration**: Uses post author data and rating system for aggregate ratings
4. **SEO Optimization**: Rich snippets and enhanced search results through proper structured data

## Development Commands

### Frontend Development (Gutenberg Blocks)
```bash
# Install npm dependencies
npm install                              # Install @wordpress/scripts and React dependencies
npm run start                            # Development with hot reload and file watching
npm run build                            # Production build (compiles src/ to build/)
npm run lint:js                          # ESLint JavaScript checks
npm run lint:css                         # Stylelint CSS checks
npm run format                           # Auto-format JavaScript and CSS
```

### PHP Development
```bash
# Install PHP dependencies and linting
composer install                         # Install development dependencies
composer lint                            # Run PHP CodeSniffer checks
composer lint:fix                        # Auto-fix PHP coding standard issues
composer lint:php                        # PHP CodeSniffer with WordPress standards
composer lint:fix:php                    # Auto-fix with WordPress standards
```

### Production Build Process
```bash
# Dual build system deployment
./build.sh                               # Complete production build

# Process:
# 1. Install production PHP dependencies (composer install --no-dev)
# 2. Install npm dependencies and run frontend build (npm ci && npm run build)
# 3. Copy files using rsync excluding development files and source directories
# 4. Validate all required files including compiled build/ assets
# 5. Create ZIP file for WordPress deployment
# 6. Restore development dependencies
```

## File Structure

```
datamachine-recipes/
├── datamachine-recipes.php              # Main plugin file (uses Requires Plugins header)
├── build.sh                             # Production build script with dual-system support
├── composer.json                        # PHP dependencies and autoloading
├── package.json                         # npm dependencies and wp-scripts
├── src/                                 # Frontend source files
│   └── recipe-schema/                   # React components and block source
│       ├── index.js                     # React editor (registers datamachine-recipes/recipe-schema)
│       ├── block.json                   # Block definition and attributes
│       └── style.scss                   # Block styling
├── build/                               # Compiled frontend assets (generated)
│   └── recipe-schema/                   # Production-ready JavaScript and assets
├── inc/
│   ├── Handlers/WordPressRecipePublish/ # Data Machine handler implementation
│   │   ├── WordPressRecipePublish.php   # Main handler class with HandlerRegistrationTrait
│   │   └── WordPressRecipePublishSettings.php # Configuration
│   └── blocks/
│       └── RecipeSchemaBlock.php        # Server-side block registration and rendering
├── README.md                            # Plugin documentation
└── .claude/
    └── recipe-schema.md                 # Schema.org Recipe reference
```

## Implementation Status

### Handler Registration ✅
The `WordPressRecipePublish.php` handler uses `HandlerRegistrationTrait` for self-registration with Data Machine's filter-based discovery system via `datamachine_handlers` and `chubes_ai_tools` filters.

### AI Tool Integration ✅
The handler fully implements the `handle_tool_call()` method with comprehensive parameter processing, WordPress post creation, Recipe Schema block embedding, error handling, and Data Machine-compliant response structure. Features include:
- **Custom Success Messaging**: Recipe-specific success message formatting with post title and URL
- **Gutenberg Block Formatting Guidelines**: Comprehensive instructions for proper WordPress block syntax
- **Enhanced Error Handling**: Detailed validation and error reporting for AI agents
- **Data Machine Compliance**: Structured return format with nested `data` object and `tool_name` field

### Gutenberg Block Implementation ✅
Recipe Schema block (`datamachine-recipes/recipe-schema`) features sophisticated React-based editor interface with comprehensive Schema.org support:
- **Block Registration**: Registered as `datamachine-recipes/recipe-schema` in JavaScript and PHP
- **React Components**: Custom `DurationInput`, `ArrayInput`, and `TagInput` components for advanced UI interactions
- **Comprehensive Form Interface**: Categorized sections for basic info, timing, categories, ingredients, instructions, nutrition, and additional metadata
- **Real-time Validation**: ISO 8601 duration parsing/formatting and interactive array management
- **Schema.org Compliance**: Complete attribute definition matching Schema.org Recipe specification
- **Server-side Rendering**: PHP rendering with microdata and JSON-LD output
- **Modern WordPress Integration**: Built with @wordpress/scripts, wp-scripts build system, and WordPress components
- **Production Assets**: Compiled JavaScript and CSS optimized for WordPress deployment

### Build System ✅
Dual-system production build is fully implemented:
- **Frontend Build**: npm with @wordpress/scripts for React component compilation
- **Backend Build**: Composer with PSR-4 autoloading and PHP dependency management
- **Unified Build Script**: `build.sh` handles both npm and Composer builds with validation
- **Asset Compilation**: Transforms `src/` React components into production `build/` assets
- **Dependency Management**: Separate development and production dependencies for both systems
- **File Validation**: Automated verification of compiled assets and essential plugin files
- **Clean Distribution**: Excludes source files, development dependencies, and build tools from production ZIP


## Data Machine Integration Points

### Pipeline Flow Integration
With full implementation complete, recipes can be processed through Data Machine pipelines:
1. **Fetch Handler** retrieves recipe data from external sources
2. **AI Processing** transforms and enhances recipe content  
3. **Recipe Publish Handler** creates WordPress posts with Schema.org markup
4. **Scheduling** allows automated recipe publishing workflows

The plugin provides complete agentic recipe publishing capabilities with comprehensive Schema.org structured data support.

### Multi-Provider AI Support
The handler integrates with Data Machine's AI infrastructure supporting:
- OpenAI, Anthropic, Google, Grok, OpenRouter providers
- Tool-first agentic execution
- Structured data extraction and validation