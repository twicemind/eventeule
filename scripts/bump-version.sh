#!/usr/bin/env bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check for --auto flag
AUTO_MODE=false
if [ "$2" == "--auto" ] || [ "$1" == "--auto" ]; then
    AUTO_MODE=true
fi

# Help text
function show_help {
    echo "Usage: $0 [major|minor|patch] [--auto]"
    echo ""
    echo "Bump version and create a new release"
    echo ""
    echo "Arguments:"
    echo "  major    Bump major version (1.0.0 -> 2.0.0)"
    echo "  minor    Bump minor version (1.0.0 -> 1.1.0)"
    echo "  patch    Bump patch version (1.0.0 -> 1.0.1)"
    echo "  --auto   Skip confirmation prompts and git clean check"
    echo ""
    echo "Example: $0 patch"
    echo "Example: $0 patch --auto"
    exit 0
}

# Check arguments
if [ "$1" == "-h" ] || [ "$1" == "--help" ]; then
    show_help
fi

if [ -z "$1" ] || [ "$1" == "--auto" ]; then
    echo -e "${RED}Error: Version type required (major, minor, or patch)${NC}"
    show_help
fi

VERSION_TYPE=$1

# Check if git is clean (skip in auto mode)
if [ "$AUTO_MODE" = false ] && [ -n "$(git status --porcelain)" ]; then
    echo -e "${RED}Error: Git working directory is not clean. Commit or stash changes first.${NC}"
    git status --short
    exit 1
fi

# Read current version from EventEule.php
CURRENT_VERSION=$(grep "Version:" EventEule.php | sed 's/.*Version:\s*//' | tr -d ' ')
echo -e "${YELLOW}Current version: ${CURRENT_VERSION}${NC}"

# Split version
IFS='.' read -r -a version_parts <<< "$CURRENT_VERSION"
major="${version_parts[0]}"
minor="${version_parts[1]}"
patch="${version_parts[2]}"

# Calculate new version
case "$VERSION_TYPE" in
    major)
        major=$((major + 1))
        minor=0
        patch=0
        ;;
    minor)
        minor=$((minor + 1))
        patch=0
        ;;
    patch)
        patch=$((patch + 1))
        ;;
    *)
        echo -e "${RED}Error: Invalid version type. Use major, minor, or patch.${NC}"
        exit 1
        ;;
esac

NEW_VERSION="${major}.${minor}.${patch}"
echo -e "${GREEN}New version: ${NEW_VERSION}${NC}"

# Get confirmation (skip in auto mode)
if [ "$AUTO_MODE" = false ]; then
    read -p "Update version to ${NEW_VERSION}? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Aborted.${NC}"
        exit 0
    fi
fi

echo -e "${YELLOW}Updating version numbers...${NC}"

# Update version in EventEule.php
sed -i.bak "s/Version: ${CURRENT_VERSION}/Version: ${NEW_VERSION}/" EventEule.php
sed -i.bak "s/define('EVENTEULE_VERSION', '${CURRENT_VERSION}');/define('EVENTEULE_VERSION', '${NEW_VERSION}');/" EventEule.php
rm EventEule.php.bak

# Update version in package.json (if exists)
if [ -f package.json ]; then
    sed -i.bak "s/\"version\": \"${CURRENT_VERSION}\"/\"version\": \"${NEW_VERSION}\"/" package.json
    rm package.json.bak
fi

# Update version in composer.json (if exists)
if [ -f composer.json ]; then
    sed -i.bak "s/\"version\": \"${CURRENT_VERSION}\"/\"version\": \"${NEW_VERSION}\"/" composer.json
    rm composer.json.bak
fi

# Update version in README.md (if exists)
if [ -f README.md ]; then
    sed -i.bak "s/Version: ${CURRENT_VERSION}/Version: ${NEW_VERSION}/" README.md
    rm README.md.bak 2>/dev/null || true
fi

echo -e "${GREEN}Version numbers updated successfully${NC}"

# Git commit and tag (skip in auto mode - release.sh handles this)
if [ "$AUTO_MODE" = false ]; then
    echo -e "${YELLOW}Creating git commit and tag...${NC}"

    git add EventEule.php package.json composer.json README.md 2>/dev/null || true
    git commit -m "Bump version to ${NEW_VERSION}"
    git tag -a "v${NEW_VERSION}" -m "Release version ${NEW_VERSION}"

    echo -e "${GREEN}✓ Version bumped to ${NEW_VERSION}${NC}"
    echo -e "${GREEN}✓ Git tag v${NEW_VERSION} created${NC}"
    echo ""
    echo -e "${YELLOW}Next steps:${NC}"
    echo -e "  1. Review the changes: ${GREEN}git show${NC}"
    echo -e "  2. Push to GitHub: ${GREEN}git push && git push --tags${NC}"
    echo -e "  3. GitHub Actions will automatically create a release"
else
    # In auto mode, just create the tag
    git tag -a "v${NEW_VERSION}" -m "Release version ${NEW_VERSION}"
    echo -e "${GREEN}✓ Version bumped to ${NEW_VERSION}${NC}"
    echo -e "${GREEN}✓ Git tag v${NEW_VERSION} created${NC}"
fi
