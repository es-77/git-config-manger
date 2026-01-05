#!/bin/bash

# Define paths
SOURCE_DIR="$PWD/nativephp/electron/dist"
BUILD_ROOT="../desktop-git-config-manager-build/Linux"

# Create build root if it doesn't exist
mkdir -p "$BUILD_ROOT"

# Detect latest version
echo "Checking for existing versions in $BUILD_ROOT..."
LATEST_FOLDER=$(ls -1 "$BUILD_ROOT" 2>/dev/null | grep -E '^v[0-9]+\.[0-9]+\.[0-9]+$' | sort -V | tail -n 1)

if [ -z "$LATEST_FOLDER" ]; then
    NEW_VERSION="1.0.0"
    echo "No existing versions found. Starting with v$NEW_VERSION"
else
    echo "Latest version found: $LATEST_FOLDER"
    # Extract version number (remove 'v' prefix)
    CURRENT_VER=${LATEST_FOLDER#v}
    
    # Split into array
    IFS='.' read -r -a parts <<< "$CURRENT_VER"
    MAJOR=${parts[0]}
    MINOR=${parts[1]}
    PATCH=${parts[2]}
    
    # Increment patch version
    NEW_PATCH=$((PATCH + 1))
    NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"
    echo "Incrementing to v$NEW_VERSION"
fi

TARGET_DIR="$BUILD_ROOT/v$NEW_VERSION"

# Create target folder
mkdir -p "$TARGET_DIR"

# Zip and move AppImage
if ls "$SOURCE_DIR"/*.AppImage 1> /dev/null 2>&1; then
    APP_IMAGE=$(ls "$SOURCE_DIR"/*.AppImage | head -n 1)
    ZIP_NAME="$TARGET_DIR/git-config-manager-v$NEW_VERSION-x86_64.AppImage.zip"
    
    echo "Zipping AppImage..."
    zip -j "$ZIP_NAME" "$APP_IMAGE"
    echo "Created: $ZIP_NAME"
else
    echo "No AppImage found in $SOURCE_DIR"
fi

# Zip and move .deb
if ls "$SOURCE_DIR"/*.deb 1> /dev/null 2>&1; then
    DEB_FILE=$(ls "$SOURCE_DIR"/*.deb | head -n 1)
    ZIP_NAME="$TARGET_DIR/git-config-manager-v$NEW_VERSION_amd64.deb.zip"
    
    echo "Zipping .deb..."
    zip -j "$ZIP_NAME" "$DEB_FILE"
    echo "Created: $ZIP_NAME"
else
    echo "No .deb found in $SOURCE_DIR"
fi

echo ""
echo "---------------------------------------------------"
echo "Build v$NEW_VERSION processed successfully!"
echo "Files are located in: $TARGET_DIR"
echo "---------------------------------------------------"
