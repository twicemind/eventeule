# 🔒 Security Pre-Commit Checklist

Before the first commit/push to GitHub:

## ✅ Required Checks

- [ ] `.gitignore` contains all sensitive files
  - `.env`
  - `.env.local`
  - `config-local.php`
  - `.wp-env.override.json`

- [ ] No passwords/tokens in code
  ```bash
  grep -r "password\|secret\|token\|api.*key" \
    --exclude-dir=vendor \
    --exclude-dir=node_modules \
    --exclude="*.md" \
    src/ templates/ *.php
  ```

- [ ] Local configs are example files
  - `.env.example` ✅ (committed)
  - `.env` ❌ (not committed)
  - `config-local.example.php` ✅ (committed)
  - `config-local.php` ❌ (not committed)

- [ ] `.wp-env.json` contains only default values
  - No custom passwords
  - No production URLs
  - Only public plugin URLs

## 📝 To Commit

These files SHOULD be on GitHub:
- ✅ `.wp-env.json` (only if default values)
- ✅ `.wp-env.json.example` (always)
- ✅ `.env.example` (always)
- ✅ `config-local.example.php` (always)
- ✅ `.gitignore` (always)
- ✅ All `*.md` documentation files
- ✅ Source code in `src/`
- ✅ Templates in `templates/`
- ✅ Assets in `assets/`
- ✅ Scripts in `scripts/`

## ❌ NEVER Commit

These files MUST remain local:
- ❌ `.env`
- ❌ `config-local.php`
- ❌ `.wp-env.override.json`
- ❌ Any `*.backup`, `*.bak` files
- ❌ Database dumps (`*.sql`)
- ❌ `node_modules/`
- ❌ `vendor/` (recreated with composer install)

## 🔍 Final Check

```bash
# Show what would be committed
git add -A
git status

# Check diff
git diff --cached

# Search for secrets
git diff --cached | grep -i "password\|secret\|token\|api"

# Should find nothing!
```

## ⚠️ .wp-env.json Check

Check current `.wp-env.json`:

```bash
cat .wp-env.json
```

**Is OK if:**
- Only WordPress/Plugin download URLs
- Standard debug flags (WP_DEBUG, etc.)
- No passwords or tokens

**NOT OK if:**
- Custom database credentials
- API keys or tokens
- Production server URLs
- Personal data

If NOT OK:
```bash
# Move to .wp-env.override.json (will be ignored)
mv .wp-env.json .wp-env.override.json

# Use example as basis
cp .wp-env.json.example .wp-env.json
```

## 🚀 Ready for First Commit?

If all checks are ✅:

```bash
# Add everything
git add .

# Create commit
git commit -m "Initial commit - EventEule Plugin"

# Push to GitHub
git push origin main
```

## 📚 After Commit

1. Check GitHub repository: No secrets visible?
2. Create local configs:
   ```bash
   cp .env.example .env
   cp config-local.example.php config-local.php
   # Edit both files with your secrets
   ```

3. Test local development:
   ```bash
   npm run wp:start
   ```

4. Erstelle ersten Release:
   ```bash
   npm run release:patch
   git push && git push --tags
   ```

## 🆘 Hilfe

Falls du unsicher bist:
1. Lies [SECURITY.md](SECURITY.md)
2. Lies [LOCAL_SETUP.md](LOCAL_SETUP.md)
3. Erstelle ein Issue auf GitHub
