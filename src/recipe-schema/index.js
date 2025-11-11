/**
 * Recipe Schema Block - Gutenberg Editor Interface
 *
 * React-based Gutenberg block editor providing comprehensive Schema.org Recipe data input.
 * Features specialized components for duration input, array management, and tag handling.
 * Generates structured data attributes for server-side Schema.org markup rendering.
 *
 * @package DataMachineRecipes
 * @since 1.0.0
 */

import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { 
    PanelBody, 
    TextControl, 
    TextareaControl, 
    Button, 
    SelectControl, 
    ToggleControl,
    Notice,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Duration Input Component
 *
 * Specialized input component for ISO 8601 duration fields (prepTime, cookTime, totalTime).
 * Provides separate hour/minute inputs and automatically converts to ISO 8601 format.
 * Parses existing duration values on mount for editing existing recipes.
 *
 * @param {string}   label    Field label for display
 * @param {string}   value    Current ISO 8601 duration value (e.g., "PT30M")
 * @param {Function} onChange Callback when duration value changes
 */
const DurationInput = ({ label, value, onChange }) => {
    const [hours, setHours] = useState(0);
    const [minutes, setMinutes] = useState(0);
    
    useEffect(() => {
        if (value && value.startsWith('PT')) {
            const duration = value.substring(2);
            const hourMatch = duration.match(/(\d+)H/);
            const minuteMatch = duration.match(/(\d+)M/);
            
            if (hourMatch) setHours(parseInt(hourMatch[1]));
            if (minuteMatch) setMinutes(parseInt(minuteMatch[1]));
        }
    }, []);
    
    useEffect(() => {
        let newValue = 'PT';
        if (hours > 0) newValue += hours + 'H';
        if (minutes > 0) newValue += minutes + 'M';
        if (newValue === 'PT') newValue = '';
        
        onChange(newValue);
    }, [hours, minutes]);
    
    return (
        <div className="recipe-duration-input">
            <label>{label}</label>
            <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
                <NumberControl
                    label={__('Hours', 'data-machine-recipes')}
                    value={hours}
                    min={0}
                    onChange={(value) => setHours(parseInt(value) || 0)}
                />
                <NumberControl
                    label={__('Minutes', 'data-machine-recipes')}
                    value={minutes}
                    min={0}
                    max={59}
                    onChange={(value) => setMinutes(parseInt(value) || 0)}
                />
            </div>
        </div>
    );
};

/**
 * Array Input Component
 *
 * Dynamic array input component for managing lists of strings (ingredients, instructions).
 * Provides add/remove functionality with textarea inputs for multi-line content.
 * Maintains array state and provides callbacks for parent component updates.
 *
 * @param {string}   label       Field label for display
 * @param {Array}    items       Current array of string values
 * @param {Function} onChange    Callback when array values change
 * @param {string}   placeholder Placeholder text for new items
 */
const ArrayInput = ({ label, items, onChange, placeholder }) => (
    <div className="recipe-array-input">
        <label>{label}</label>
        {items.map((item, index) => (
            <div key={index} style={{ display: 'flex', gap: '10px', marginBottom: '10px' }}>
                <TextareaControl
                    value={item}
                    onChange={(value) => {
                        const updateItem = (itemIndex, newValue) => {
                            const newItems = [...items];
                            newItems[itemIndex] = newValue;
                            onChange(newItems);
                        };
                        updateItem(index, value);
                    }}
                    placeholder={placeholder}
                    rows={2}
                />
                <Button
                    isSecondary
                    isDestructive
                    onClick={() => {
                        const removeItem = (itemIndex) => {
                            const newItems = items.filter((item, index) => index !== itemIndex);
                            onChange(newItems);
                        };
                        removeItem(index);
                    }}
                >
                    {__('Remove', 'data-machine-recipes')}
                </Button>
            </div>
        ))}
        <Button
            isPrimary
            onClick={() => {
                onChange([...items, '']);
            }}
        >
            {__('Add Item', 'data-machine-recipes')}
        </Button>
    </div>
);

/**
 * Tag Input Component
 *
 * Tag management component for arrays of short strings (categories, keywords, diet types).
 * Provides visual tag display with removal buttons and text input for adding new tags.
 * Prevents duplicate entries and handles keyboard interaction (Enter to add).
 *
 * @param {string}   label    Field label for display
 * @param {Array}    tags     Current array of tag strings
 * @param {Function} onChange Callback when tag array changes
 */
const TagInput = ({ label, tags, onChange }) => {
    const [inputValue, setInputValue] = useState('');
    
    const addTag = () => {
        if (inputValue.trim() && !tags.includes(inputValue.trim())) {
            onChange([...tags, inputValue.trim()]);
            setInputValue('');
        }
    };
    
    return (
        <div className="recipe-tag-input">
            <label>{label}</label>
            <div style={{ marginBottom: '10px' }}>
                {tags.map((tag, index) => (
                    <span 
                        key={index}
                        className="recipe-tag"
                        style={{ 
                            display: 'inline-block', 
                            background: '#e0e0e0', 
                            padding: '2px 8px', 
                            margin: '2px', 
                            borderRadius: '3px' 
                        }}
                    >
                        {tag}
                        <button
                            onClick={() => {
                                const removeTag = (tagIndex) => {
                                    onChange(tags.filter((tag, index) => index !== tagIndex));
                                };
                                removeTag(index);
                            }}
                            style={{ marginLeft: '5px', background: 'none', border: 'none', cursor: 'pointer' }}
                        >
                            ×
                        </button>
                    </span>
                ))}
            </div>
            <div style={{ display: 'flex', gap: '10px' }}>
                <TextControl
                    value={inputValue}
                    onChange={setInputValue}
                    onKeyPress={(e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            addTag();
                        }
                    }}
                    placeholder={__('Enter tag and press Enter', 'data-machine-recipes')}
                />
                <Button isSecondary onClick={addTag}>
                    {__('Add', 'data-machine-recipes')}
                </Button>
            </div>
        </div>
    );
};

/**
 * Register Recipe Schema Block
 *
 * Main block registration with comprehensive edit interface for Schema.org Recipe data.
 * Provides organized field groups (Basic Info, Timing, Categories, Nutrition, etc.)
 * and uses server-side rendering for Schema.org markup generation.
 */
registerBlockType('data-machine-recipes/recipe-schema', {
    title: __('Recipe Schema', 'data-machine-recipes'),
    icon: 'food',
    category: 'common',
    description: __('Complete Schema.org Recipe structured data block', 'data-machine-recipes'),
    
    edit: ({ attributes, setAttributes }) => {
        const {
            recipeName,
            description,
            prepTime,
            cookTime,
            totalTime,
            recipeYield,
            recipeCategory,
            recipeCuisine,
            recipeIngredient,
            recipeInstructions,
            nutrition,
            suitableForDiet,
            keywords,
            cookingMethod,
            video,
            author,
            estimatedCost,
            tool,
            supply
        } = attributes;

        const blockProps = useBlockProps();
        
        const cuisineOptions = [
            { label: __('Select Cuisine', 'data-machine-recipes'), value: '' },
            { label: __('American', 'data-machine-recipes'), value: 'American' },
            { label: __('Italian', 'data-machine-recipes'), value: 'Italian' },
            { label: __('Mexican', 'data-machine-recipes'), value: 'Mexican' },
            { label: __('Chinese', 'data-machine-recipes'), value: 'Chinese' },
            { label: __('Indian', 'data-machine-recipes'), value: 'Indian' },
            { label: __('French', 'data-machine-recipes'), value: 'French' },
            { label: __('Mediterranean', 'data-machine-recipes'), value: 'Mediterranean' },
            { label: __('Asian', 'data-machine-recipes'), value: 'Asian' },
            { label: __('European', 'data-machine-recipes'), value: 'European' },
            { label: __('Other', 'data-machine-recipes'), value: 'Other' }
        ];
        
        return (
            <div {...blockProps}>
                <div style={{ 
                    background: '#f8f9fa', 
                    border: '1px solid #e0e0e0', 
                    borderRadius: '4px', 
                    padding: '16px', 
                    marginBottom: '20px' 
                }}>
                    <h3 style={{ margin: '0 0 8px 0', color: '#1e1e1e' }}>
                        🍽️ {__('Recipe Schema Block', 'data-machine-recipes')}
                    </h3>
                    <p style={{ margin: 0, color: '#666', fontSize: '14px' }}>
                        {__('This block generates structured data for search engines using the post featured image. Content is not displayed on the frontend but provides rich recipe information for SEO and search results.', 'data-machine-recipes')}
                    </p>
                </div>

                <div style={{ marginBottom: '24px' }}>
                    <h4 style={{ marginBottom: '12px', color: '#1e1e1e' }}>
                        {__('Basic Information', 'data-machine-recipes')}
                    </h4>
                    
                    <TextControl
                        label={__('Recipe Name', 'data-machine-recipes')}
                        value={recipeName}
                        onChange={(value) => setAttributes({ recipeName: value })}
                        style={{ marginBottom: '12px' }}
                    />
                    
                    <TextareaControl
                        label={__('Description', 'data-machine-recipes')}
                        value={description}
                        onChange={(value) => setAttributes({ description: value })}
                        rows={4}
                        style={{ marginBottom: '12px' }}
                    />
                    
                </div>

                <div style={{ marginBottom: '24px' }}>
                    <h4 style={{ marginBottom: '12px', color: '#1e1e1e' }}>
                        {__('Timing', 'data-machine-recipes')}
                    </h4>
                    
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '12px' }}>
                        <DurationInput
                            label={__('Prep Time', 'data-machine-recipes')}
                            value={prepTime}
                            onChange={(value) => setAttributes({ prepTime: value })}
                        />
                        
                        <DurationInput
                            label={__('Cook Time', 'data-machine-recipes')}
                            value={cookTime}
                            onChange={(value) => setAttributes({ cookTime: value })}
                        />
                        
                        <DurationInput
                            label={__('Total Time', 'data-machine-recipes')}
                            value={totalTime}
                            onChange={(value) => setAttributes({ totalTime: value })}
                        />
                        
                        <TextControl
                            label={__('Yield (servings)', 'data-machine-recipes')}
                            value={recipeYield}
                            onChange={(value) => setAttributes({ recipeYield: value })}
                            placeholder="e.g., 4 servings"
                        />
                    </div>
                </div>

                <div style={{ marginBottom: '24px' }}>
                    <h4 style={{ marginBottom: '12px', color: '#1e1e1e' }}>
                        {__('Categories & Cuisine', 'data-machine-recipes')}
                    </h4>
                    
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '12px' }}>
                        <SelectControl
                            label={__('Cuisine', 'data-machine-recipes')}
                            value={recipeCuisine}
                            options={cuisineOptions}
                            onChange={(value) => setAttributes({ recipeCuisine: value })}
                        />
                        
                        <TextControl
                            label={__('Cooking Method', 'data-machine-recipes')}
                            value={cookingMethod}
                            onChange={(value) => setAttributes({ cookingMethod: value })}
                            placeholder="e.g., Baking, Frying"
                        />
                    </div>
                    
                    <div style={{ marginTop: '12px' }}>
                        <TagInput
                            label={__('Recipe Categories', 'data-machine-recipes')}
                            tags={recipeCategory}
                            onChange={(value) => setAttributes({ recipeCategory: value })}
                        />
                    </div>
                </div>

                <div style={{ marginBottom: '24px' }}>
                    <ArrayInput
                        label={__('Recipe Ingredients', 'data-machine-recipes')}
                        items={recipeIngredient}
                        onChange={(value) => setAttributes({ recipeIngredient: value })}
                        placeholder="e.g., 1 cup flour, 2 eggs, 1/2 cup sugar"
                    />
                </div>

                <div style={{ marginBottom: '24px' }}>
                    <ArrayInput
                        label={__('Recipe Instructions', 'data-machine-recipes')}
                        items={recipeInstructions}
                        onChange={(value) => setAttributes({ recipeInstructions: value })}
                        placeholder="Enter each step of the recipe"
                    />
                </div>

                <div style={{ marginBottom: '24px' }}>
                    <h4 style={{ marginBottom: '12px', color: '#1e1e1e' }}>
                        {__('Nutrition Information (Optional)', 'data-machine-recipes')}
                    </h4>
                    
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '12px' }}>
                        <TextControl
                            label={__('Calories', 'data-machine-recipes')}
                            value={nutrition.calories || ''}
                            onChange={(value) => setAttributes({ nutrition: { ...nutrition, calories: value } })}
                            placeholder="250 calories"
                        />
                        
                        <TextControl
                            label={__('Carbs', 'data-machine-recipes')}
                            value={nutrition.carbohydrateContent || ''}
                            onChange={(value) => setAttributes({ nutrition: { ...nutrition, carbohydrateContent: value } })}
                            placeholder="30g"
                        />
                        
                        <TextControl
                            label={__('Protein', 'data-machine-recipes')}
                            value={nutrition.proteinContent || ''}
                            onChange={(value) => setAttributes({ nutrition: { ...nutrition, proteinContent: value } })}
                            placeholder="15g"
                        />
                        
                        <TextControl
                            label={__('Fat', 'data-machine-recipes')}
                            value={nutrition.fatContent || ''}
                            onChange={(value) => setAttributes({ nutrition: { ...nutrition, fatContent: value } })}
                            placeholder="10g"
                        />
                        
                        <TextControl
                            label={__('Serving Size', 'data-machine-recipes')}
                            value={nutrition.servingSize || ''}
                            onChange={(value) => setAttributes({ nutrition: { ...nutrition, servingSize: value } })}
                            placeholder="1 cup"
                        />
                    </div>
                </div>

                <div style={{ marginBottom: '24px' }}>
                    <h4 style={{ marginBottom: '12px', color: '#1e1e1e' }}>
                        {__('Additional Information (Optional)', 'data-machine-recipes')}
                    </h4>
                    
                    <div style={{ marginBottom: '12px' }}>
                        <TagInput
                            label={__('Keywords/Tags', 'data-machine-recipes')}
                            tags={keywords}
                            onChange={(value) => setAttributes({ keywords: value })}
                        />
                    </div>
                    
                    <div style={{ marginBottom: '12px' }}>
                        <TagInput
                            label={__('Suitable for Diet', 'data-machine-recipes')}
                            tags={suitableForDiet}
                            onChange={(value) => setAttributes({ suitableForDiet: value })}
                        />
                    </div>
                    
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                        <TextControl
                            label={__('Estimated Cost', 'data-machine-recipes')}
                            value={estimatedCost}
                            onChange={(value) => setAttributes({ estimatedCost: value })}
                            placeholder="$15, £10"
                        />
                    </div>
                    
                    <div style={{ marginTop: '12px' }}>
                        <ArrayInput
                            label={__('Tools/Equipment', 'data-machine-recipes')}
                            items={tool}
                            onChange={(value) => setAttributes({ tool: value })}
                            placeholder="e.g., mixing bowl, whisk, oven"
                        />
                    </div>
                </div>
            </div>
        );
    },
    
    save: () => null,
});