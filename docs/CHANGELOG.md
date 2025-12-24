# Changelog

All notable changes to Data Machine Recipes will be documented in this file.

## [1.1.0] - 2025-12-24

### Added
- WordPress "Requires Plugins" header for native dependency management
- HandlerRegistrationTrait integration for self-registration with Data Machine's filter-based discovery
- PSR-4 Composer autoloading via vendor/autoload.php

### Changed
- **Breaking: Minimum PHP version increased from 7.4 to 8.2**
- **Breaking: Minimum WordPress version increased from 5.0 to 6.2**
- **Breaking: Minimum Data Machine version increased from v0.5.8 to v0.6.0**
- Plugin namespace from `dm-recipes` to `datamachine-recipes` throughout
- PHP namespace from `DM_Recipes` to `DataMachineRecipes`
- PHP constants from `DATA_MACHINE_RECIPES_*` to `DATAMACHINE_RECIPES_*`
- WordPress filters from `dm_*` to `datamachine_*` prefix
- Block namespace from `data-machine-recipes/recipe-schema` to `datamachine-recipes/recipe-schema`
- Handler architecture: Consolidated WordPressRecipePublishFilters.php into WordPressRecipePublish.php
- Block architecture: Merged inc/blocks/recipe-schema/ directory into single RecipeSchemaBlock.php
- Build system: Enhanced validation and improved asset compilation
- Security: Updated all npm dependencies to latest stable versions
- Security: Applied force resolutions for moderate npm vulnerabilities

### Fixed
- Recipe image handling: Removed redundant image field from block, now uses WordPress post featured image
- PublishStep detection: Fixed return format to match Data Machine conventions with 'data' wrapper and 'tool_name' field
- Activation hook: Removed manual plugin dependency checking, now handled by WordPress Requires Plugins header

### Removed
- Manual plugin activation dependency validation
- WordPressRecipePublishFilters.php (consolidated into WordPressRecipePublish.php)
- inc/blocks/recipe-schema/index.php (functionality moved to RecipeSchemaBlock.php)
- inc/blocks/recipe-schema/recipe-schema.php (consolidated into RecipeSchemaBlock.php)
- Custom PSR-4 autoloader (replaced with Composer autoload)

## [1.0.0] - 2025-09-09

### Added
- Initial release of Data Machine Recipes
- WordPress Recipe Publish Handler with AI tool integration
- Complete Schema.org Recipe Gutenberg block with all standard properties
- Dual structured data output: JSON-LD and microdata
- Comprehensive recipe attributes including nutrition, dietary restrictions, video support
- Multi-provider AI integration (OpenAI, Anthropic, Google, Grok, OpenRouter)
- Gutenberg block with sophisticated React-based editor interface
- Custom DurationInput, ArrayInput, and TagInput React components
- WordPress post taxonomy assignment via handler settings
- Rich snippet support for search engine optimization
