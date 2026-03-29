#!/usr/bin/env bash
set -e

# EventEule Test Runner
# Simple smoke tests for release validation

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}   EventEule Tests 🦉${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

FAILED=0
TOTAL=0

# Test 1: PHP Syntax Check
echo -e "${YELLOW}[1/4]${NC} PHP Syntax Check..."
((TOTAL++))
if php -l EventEule.php > /dev/null 2>&1; then
    echo -e "${GREEN}  ✓ EventEule.php${NC}"
else
    echo -e "${RED}  ✗ EventEule.php has syntax errors${NC}"
    ((FAILED++))
fi

# Check all PHP files in src/
PHP_FILES=$(find src -name "*.php" 2>/dev/null)
if [ -n "$PHP_FILES" ]; then
    while IFS= read -r file; do
        if ! php -l "$file" > /dev/null 2>&1; then
            echo -e "${RED}  ✗ $file has syntax errors${NC}"
            ((FAILED++))
        fi
    done <<< "$PHP_FILES"
fi

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}  ✓ All PHP files OK${NC}"
fi

# Test 2: Required Files
echo -e "${YELLOW}[2/4]${NC} Required Files Check..."
((TOTAL++))
REQUIRED_FILES=(
    "EventEule.php"
    "src/Plugin.php"
    "README.md"
    "composer.json"
    "package.json"
)

MISSING=0
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo -e "${RED}  ✗ Missing: $file${NC}"
        ((MISSING++))
    fi
done

if [ $MISSING -eq 0 ]; then
    echo -e "${GREEN}  ✓ All required files present${NC}"
else
    ((FAILED++))
fi

# Test 3: Version Consistency
echo -e "${YELLOW}[3/4]${NC} Version Consistency Check..."
((TOTAL++))

VERSION_PHP=$(grep "Version:" EventEule.php | sed 's/.*Version:\s*//' | tr -d ' \r\n')
VERSION_README=$(grep "Stable tag:" README.md | sed 's/.*Stable tag:\s*//' | tr -d ' \r\n' 2>/dev/null || echo "")
VERSION_PACKAGE=$(node -p "require('./package.json').version" 2>/dev/null || echo "")

echo -e "  EventEule.php: ${VERSION_PHP}"
echo -e "  README.md:     ${VERSION_README:-<not found>}"
echo -e "  package.json:  ${VERSION_PACKAGE}"

# Check only the most important versions
if [ -n "$VERSION_PHP" ] && [ -n "$VERSION_PACKAGE" ] && [ "$VERSION_PHP" = "$VERSION_PACKAGE" ]; then
    if [ -z "$VERSION_README" ] || [ "$VERSION_PHP" = "$VERSION_README" ]; then
        echo -e "${GREEN}  ✓ Versions consistent${NC}"
    else
        echo -e "${RED}  ✗ Versions inconsistent!${NC}"
        ((FAILED++))
    fi
else
    echo -e "${RED}  ✗ Versions inconsistent!${NC}"
    ((FAILED++))
fi

# Test 4: Composer Dependencies
echo -e "${YELLOW}[4/4]${NC} Composer Dependencies Check..."
((TOTAL++))

if [ ! -d "vendor" ]; then
    echo -e "${RED}  ✗ vendor/ directory missing${NC}"
    echo -e "${YELLOW}  → Run 'composer install'${NC}"
    ((FAILED++))
elif [ ! -f "vendor/autoload.php" ]; then
    echo -e "${RED}  ✗ vendor/autoload.php missing${NC}"
    ((FAILED++))
else
    echo -e "${GREEN}  ✓ Composer dependencies OK${NC}"
fi

# Summary
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}  ✓ All tests passed! ($TOTAL/$TOTAL)${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    exit 0
else
    echo -e "${RED}  ✗ $FAILED of $TOTAL tests failed${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    exit 1
fi
