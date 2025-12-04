<?php
namespace DataMachineRecipes\Handlers\WordPressRecipePublish;

use DataMachine\Core\Steps\Publish\Handlers\PublishHandlerSettings;
use DataMachine\Core\WordPress\WordPressSettingsHandler;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings configuration for WordPress Recipe Publish handler.
 *
 * Provides Data Machine settings interface for recipe publishing configuration.
 * Manages WordPress post settings (type, status, author) and dynamic taxonomy
 * selection fields with comprehensive validation and sanitization.
 *
 * @package DataMachineRecipes\WordPressRecipePublish
 * @since 1.0.0
 */
class WordPressRecipePublishSettings extends PublishHandlerSettings {

    /**
     * Get settings fields for WordPress recipe publish handler.
     *
     * @return array Associative array defining the settings fields
     */
    public static function get_fields(): array {
        $fields = self::get_local_fields();

        $fields = array_merge($fields, parent::get_common_fields());

        return $fields;
    }


    
    /**
     * Get settings fields specific to local WordPress recipe publishing.
     *
     * @return array Settings fields
     */
    private static function get_local_fields(): array {
        // Get standard WordPress publish fields
        $standard_fields = WordPressSettingsHandler::get_standard_publish_fields([
            'domain' => 'datamachine-recipes'
        ]);

        // Get dynamic taxonomy fields
        $taxonomy_fields = WordPressSettingsHandler::get_taxonomy_fields([
            'field_suffix' => '_selection',
            'first_options' => [
                'skip' => __('Skip', 'datamachine-recipes'),
                'ai_decides' => __('AI Decides', 'datamachine-recipes')
            ],
            'description_template' => __('Configure %1$s assignment: Skip to exclude from AI instructions, let AI choose, or select specific %2$s.', 'datamachine-recipes')
        ]);

        return array_merge($standard_fields, $taxonomy_fields);
    }
    
    /**
     * Sanitize WordPress recipe publish handler settings.
     *
     * @param array $raw_settings Raw settings input
     * @return array Sanitized settings
     */
    public static function sanitize(array $raw_settings): array {
        // Sanitize local settings
        $sanitized = self::sanitize_local_settings($raw_settings);

        // Sanitize common publish handler fields
        $sanitized = array_merge($sanitized, parent::sanitize($raw_settings));

        return $sanitized;
    }
    
    /**
     * Sanitize local WordPress settings.
     *
     * @param array $raw_settings Raw settings array
     * @return array Sanitized settings
     */
    private static function sanitize_local_settings(array $raw_settings): array {
        // Sanitize standard fields
        $sanitized = WordPressSettingsHandler::sanitize_standard_publish_fields($raw_settings);

        // Sanitize dynamic taxonomy selections
        $sanitized = array_merge($sanitized, WordPressSettingsHandler::sanitize_taxonomy_fields($raw_settings, [
            'field_suffix' => '_selection',
            'allowed_values' => ['skip', 'ai_decides'],
            'default_value' => 'skip'
        ]));

        return $sanitized;
    }
    
    /**
     * Determine if authentication is required based on current configuration.
     *
     * @param array $current_config Current configuration values for this handler
     * @return bool True if authentication is required, false otherwise
     */
    public static function requires_authentication(array $current_config = []): bool {
        return false;
    }
}