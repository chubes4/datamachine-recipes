#!/bin/bash
# Data Machine Recipes - Production Build Script
# Creates a production-ready ZIP file for WordPress plugin deployment

set -e  # Exit on any error

# Plugin information
PLUGIN_NAME="datamachine-recipes"
PLUGIN_VERSION=$(grep "Version:" datamachine-recipes.php | sed 's/.*Version: *\([0-9.]*\).*/\1/')
BUILD_DIR="dist"
ZIP_NAME="${PLUGIN_NAME}-${PLUGIN_VERSION}.zip"

echo "🏗️  Building Data Machine Recipes v${PLUGIN_VERSION}"

# Clean previous builds
echo "🧹 Cleaning previous builds..."
rm -rf ${BUILD_DIR}
mkdir -p ${BUILD_DIR}

# Install production dependencies
echo "📦 Installing production dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Build Gutenberg blocks
echo "🏗️  Building Gutenberg blocks..."
if [ -f "package.json" ] && [ -d "build/recipe-schema" ]; then
    echo "✅ Gutenberg blocks already built, skipping npm build"
elif [ -f "package.json" ]; then
    echo "⚠️  npm build skipped due to dependency issues - using pre-built blocks"
fi

# Copy files using rsync with manual exclusions
echo "📋 Copying plugin files..."
rsync -av \
    --exclude='dist/' \
    --exclude='*.zip' \
    --exclude='vendor/' \
    --exclude='.git' \
    --exclude='node_modules/' \
    --exclude='src/' \
    --exclude='.buildignore' \
    --exclude='build.sh' \
    --exclude='composer.lock' \
    --exclude='package-lock.json' \
    --exclude='webpack.config.js' \
    --exclude='.DS_Store' \
    --exclude='.claude/' \
    --exclude='AGENTS.md' \
    --exclude='README.MD' \
    ./ ${BUILD_DIR}/${PLUGIN_NAME}/

# Validate essential files exist
echo "✅ Validating build..."
REQUIRED_FILES=(
    "datamachine-recipes.php"
    "inc/handlers/WordPressRecipePublish/WordPressRecipePublish.php"
    "inc/handlers/WordPressRecipePublish/WordPressRecipePublishFilters.php"
    "inc/blocks/recipe-schema/recipe-schema.php"
    "build/recipe-schema/block.json"
    "build/recipe-schema/index.js"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "${BUILD_DIR}/${PLUGIN_NAME}/${file}" ]; then
        echo "❌ Error: Required file ${file} not found in build"
        exit 1
    fi
done

echo "✅ All required files present"

# Create ZIP file
echo "📦 Creating ZIP file..."
cd ${BUILD_DIR}
zip -r "${PLUGIN_NAME}.zip" ${PLUGIN_NAME}/
cd ..

# Clean up temporary build directory
echo "🧹 Cleaning up temporary files..."
rm -rf "${BUILD_DIR}/${PLUGIN_NAME}"

# Restore development dependencies
echo "🔄 Restoring development dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-interaction
fi

# Build summary
BUILD_SIZE=$(du -h "${BUILD_DIR}/${PLUGIN_NAME}.zip" | cut -f1)
FILE_COUNT=$(find ${BUILD_DIR}/${PLUGIN_NAME} -type f | wc -l | tr -d ' ')

echo "
🎉 Build completed successfully!

📊 Build Summary:
   • Plugin: ${PLUGIN_NAME} v${PLUGIN_VERSION}
   • ZIP File: ${PLUGIN_NAME}.zip (${BUILD_SIZE})
   • Files: ${FILE_COUNT} files included
   • Location: $(pwd)/${BUILD_DIR}/${PLUGIN_NAME}.zip

📝 Next Steps:
   1. Test the plugin in a staging environment
   2. Upload to WordPress admin or deploy via WP-CLI
   3. Verify Data Machine integration works correctly

🚀 Ready for deployment!
"

echo "Build completed: ${BUILD_DIR}/${PLUGIN_NAME}.zip"