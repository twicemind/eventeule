#!/usr/bin/env bash
set -e

# EventEule Smart Release Script
# Runs tests, bumps version, commits and pushes

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}"
echo "╔══════════════════════════════════════╗"
echo "║   EventEule Smart Release 🦉         ║"
echo "╔══════════════════════════════════════╗"
echo -e "${NC}"

# Step 1: Check Git status
echo -e "${BLUE}[1/7] Checking Git status...${NC}"
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}⚠️  Uncommitted changes gefunden:${NC}"
    git status --short
    echo ""
    read -p "Do you want to continue? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}Abgebrochen.${NC}"
        exit 1
    fi
fi

# Step 2: Run tests
echo -e "${BLUE}[2/7] Running tests...${NC}"
if [ -f "package.json" ] && grep -q '"test"' package.json; then
    npm test || {
        echo -e "${RED}❌ Tests fehlgeschlagen!${NC}"
        exit 1
    }
    echo -e "${GREEN}✓ Tests erfolgreich${NC}"
else
    echo -e "${YELLOW}⚠️  Keine Tests konfiguriert (npm test)${NC}"
fi

# Step 3: Determine release type
echo -e "${BLUE}[3/7] Determining release type...${NC}"

# Analyze Git changes
CHANGES=$(git diff --cached --name-only 2>/dev/null || git diff --name-only 2>/dev/null || echo "")
NEW_FILES=$(git ls-files --others --exclude-standard 2>/dev/null | wc -l | xargs)

echo ""
echo -e "${CYAN}Changes:${NC}"
if [ -n "$CHANGES" ]; then
    echo "$CHANGES" | head -10
    CHANGE_COUNT=$(echo "$CHANGES" | wc -l | xargs)
    if [ "$CHANGE_COUNT" -gt 10 ]; then
        echo -e "${YELLOW}... and $((CHANGE_COUNT - 10)) more files${NC}"
    fi
else
    echo -e "${YELLOW}No staged changes found${NC}"
fi

if [ "$NEW_FILES" -gt 0 ]; then
    echo -e "${YELLOW}${NEW_FILES} new file(s) not in Git${NC}"
fi

echo ""
echo -e "${CYAN}Which release type?${NC}"
echo "  ${GREEN}1${NC}) Patch   (1.0.0 → 1.0.1) - Bugfixes, small changes"
echo "  ${GREEN}2${NC}) Minor   (1.0.0 → 1.1.0) - New features, backwards compatible"
echo "  ${GREEN}3${NC}) Major   (1.0.0 → 2.0.0) - Breaking changes"
echo ""
read -p "Choose (1/2/3): " -n 1 -r RELEASE_CHOICE
echo ""

case "$RELEASE_CHOICE" in
    1)
        RELEASE_TYPE="patch"
        ;;
    2)
        RELEASE_TYPE="minor"
        ;;
    3)
        RELEASE_TYPE="major"
        ;;
    *)
        echo -e "${RED}Invalid selection!${NC}"
        exit 1
        ;;
esac

echo -e "${GREEN}✓ Release type: ${RELEASE_TYPE}${NC}"

# Step 4: Read current version
echo -e "${BLUE}[4/7] Determining version...${NC}"
CURRENT_VERSION=$(grep "Version:" EventEule.php | sed 's/.*Version:\s*//' | tr -d ' ')
echo -e "Current version: ${YELLOW}${CURRENT_VERSION}${NC}"

# Step 5: Bump version
echo -e "${BLUE}[5/7] Bumping version to ${RELEASE_TYPE}...${NC}"
./scripts/bump-version.sh "$RELEASE_TYPE" --auto || {
    echo -e "${RED}❌ Version bump failed!${NC}"
    exit 1
}

# Read new version
NEW_VERSION=$(grep "Version:" EventEule.php | sed 's/.*Version:\s*//' | tr -d ' ')
echo -e "${GREEN}✓ New version: ${NEW_VERSION}${NC}"

# Step 5.5: Compile translations
echo -e "${BLUE}[5.5/7] Compiling translations...${NC}"
if command -v msgfmt &> /dev/null; then
    for po_file in languages/*.po; do
        if [ -f "$po_file" ]; then
            mo_file="${po_file%.po}.mo"
            msgfmt "$po_file" -o "$mo_file" && echo -e "${GREEN}✓ Compiled: $mo_file${NC}"
        fi
    done
else
    echo -e "${YELLOW}⚠️  msgfmt not found. Translation files not compiled.${NC}"
    echo -e "${YELLOW}   Install with: brew install gettext && brew link gettext${NC}"
fi

# Step 6: Generate commit message
echo -e "${BLUE}[6/7] Creating commit...${NC}"

# Generate detailed commit message
COMMIT_MSG="Release v${NEW_VERSION}

Release-Type: ${RELEASE_TYPE}
Previous Version: ${CURRENT_VERSION}

Changes:
"

# Add changes to commit message
if [ -n "$CHANGES" ]; then
    COMMIT_MSG="${COMMIT_MSG}
$(echo "$CHANGES" | sed 's/^/  - /' | head -20)
"
    CHANGE_COUNT=$(echo "$CHANGES" | wc -l | xargs)
    if [ "$CHANGE_COUNT" -gt 20 ]; then
        COMMIT_MSG="${COMMIT_MSG}
  ... and $((CHANGE_COUNT - 20)) more changes
"
    fi
else
    COMMIT_MSG="${COMMIT_MSG}
  - Version bump only
"
fi

echo ""
echo -e "${CYAN}Commit message:${NC}"
echo "---"
echo "$COMMIT_MSG"
echo "---"
echo ""

read -p "Create commit? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}Aborted. Version has already been changed!${NC}"
    echo -e "${YELLOW}Undo with: git reset --hard HEAD~1${NC}"
    exit 1
fi

# Stage all changes (including version changes)
git add -A

# Create commit
git commit -m "$COMMIT_MSG"

# Create tag AFTER commit
git tag -a "v${NEW_VERSION}" -m "Release version ${NEW_VERSION}"

echo -e "${GREEN}✓ Commit created${NC}"
echo -e "${GREEN}✓ Tag v${NEW_VERSION} created${NC}"

# Step 7: Push to GitHub
echo -e "${BLUE}[7/7] Push to GitHub...${NC}"

echo ""
read -p "Push to GitHub (with tags)? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Not pushed. You can push later with:${NC}"
    echo -e "  ${GREEN}git push && git push --tags${NC}"
    exit 0
fi

# Push commits and tags
git push && git push --tags

echo ""
echo -e "${GREEN}╔══════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✓ Release successful! 🎉          ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════╝${NC}"
echo ""
echo -e "${CYAN}Release v${NEW_VERSION}${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "  1. GitHub Actions automatically creates release"
echo -e "  2. Check: ${BLUE}https://github.com/twicemind/eventeule/releases${NC}"
echo -e "  3. WordPress installations receive update notification"
echo ""
