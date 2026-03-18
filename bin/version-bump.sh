#!/bin/bash

PLUGIN_FILE="woolab-ic-dic.php"
README_FILE="readme.txt"

# Function to increment version number
increment_version() {
    local version=$1
    local major=$(echo "$version" | cut -d. -f1)
    local minor=$(echo "$version" | cut -d. -f2)
    local patch=$(echo "$version" | cut -d. -f3)

    # Increment patch version
    patch=$((patch + 1))

    echo "$major.$minor.$patch"
}

# Get current version from plugin header (handles tabs/spaces after "Version:")
header_version=$(grep -E "^[[:space:]]*Version:" "$PLUGIN_FILE" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+')
constant_version=$(grep "WOOLAB_IC_DIC_VERSION" "$PLUGIN_FILE" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+')
readme_version=$(grep -E "^Stable tag:" "$README_FILE" 2>/dev/null | grep -oE '[0-9]+\.[0-9]+\.[0-9]+')

echo "Current versions:"
echo "  Plugin header:   ${header_version:-not found}"
echo "  PHP constant:    ${constant_version:-not found}"
echo "  readme.txt:      ${readme_version:-not found}"
echo ""

current_version="$header_version"

if [ -z "$current_version" ]; then
    echo "Error: Could not detect version from $PLUGIN_FILE"
    exit 1
fi

# Ask for new version
read -p "Enter new version (press enter to auto-increment patch): " new_version

if [ -z "$new_version" ]; then
    new_version=$(increment_version "$current_version")
fi

echo ""

# Update version in plugin header (preserve tabs/spaces)
sed -i '' "s/\(Version:[[:space:]]*\)$header_version/\1$new_version/" "$PLUGIN_FILE"
echo "Updated plugin header to $new_version"

# Update version constant
if [ -n "$constant_version" ]; then
    sed -i '' "s/WOOLAB_IC_DIC_VERSION', '$constant_version'/WOOLAB_IC_DIC_VERSION', '$new_version'/" "$PLUGIN_FILE"
    echo "Updated PHP constant to $new_version"
fi

# Ask about updating readme.txt
read -p "Update stable tag in readme.txt? (y/n): " update_readme

if [ "$update_readme" = "y" ] || [ "$update_readme" = "Y" ]; then
    if [ -f "$README_FILE" ]; then
        sed -i '' "s/^Stable tag: .*/Stable tag: $new_version/" "$README_FILE"
        echo "Updated stable tag in readme.txt"
    else
        echo "readme.txt not found"
    fi
fi

echo ""
echo "Version bump to $new_version completed!"
