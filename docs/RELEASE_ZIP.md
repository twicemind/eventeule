# Release ZIP Contents

## Files Included in Release

The automatically created release ZIP (`eventeule-x.x.x.zip`) contains **only** the files necessary for WordPress:

```
eventeule/
├── EventEule.php              # Main plugin file
├── uninstall.php              # Uninstall script
├── LICENSE                    # License
├── README.md                  # Project overview
├── src/                       # PHP classes
│   ├── Plugin.php
│   ├── Admin/
│   ├── Api/
│   ├── Domain/
│   ├── Frontend/
│   ├── Integration/
│   ├── Repository/
│   └── Support/
├── assets/                    # CSS, JS, Images
│   ├── css/
│   ├── js/
│   └── img/
├── languages/                 # Translations
│   ├── eventeule-de_DE.po
│   └── eventeule-de_DE.mo
├── templates/                 # Template files
│   ├── admin/
│   └── public/
└── vendor/                    # Composer dependencies (Production)
    └── autoload.php
```

## NOT Included

The following files/folders are **deliberately excluded**:

### Developer Files
- `.github/` - GitHub Actions workflows
- `tests/` - Test files
- `scripts/` - Build scripts
- `node_modules/` - npm dependencies
- `docs/` - Documentation (only README.md in root)

### Configuration Files
- `composer.json`, `composer.lock` - Composer config
- `package.json`, `package-lock.json` - npm config
- `.wp-env.json` - wp-env config
- `.env.example` - Environment template
- `config-local.example.php` - Config template

### Git Files
- `.git/` - Git repository
- `.gitignore` - Git ignore rules

## Size

Typical ZIP size: **~500 KB - 1 MB**

Without these exclusions the ZIP would be: **~50-100 MB** (mainly node_modules)

## Automatic Creation

The ZIP is automatically created by GitHub Actions when:

1. Git tag is created (`v1.0.0`)
2. GitHub Actions workflow starts
3. Composer dependencies are installed (Production only: `--no-dev`)
4. Only necessary folders are copied to `eventeule/`
5. ZIP is created from `eventeule/` folder
6. ZIP is uploaded as release asset

See: [.github/workflows/release.yml](../.github/workflows/release.yml)

## Check What's in the ZIP

```bash
# Download ZIP from GitHub Release
# Then:
unzip -l eventeule-1.0.0.zip | head -50
```

## Manually Create ZIP (for testing)

```bash
# In project root:
mkdir -p dist/eventeule
cp EventEule.php dist/eventeule/
cp uninstall.php dist/eventeule/
cp LICENSE dist/eventeule/
cp README.md dist/eventeule/
cp -r src dist/eventeule/
cp -r assets dist/eventeule/
cp -r languages dist/eventeule/
cp -r templates dist/eventeule/
cp -r vendor dist/eventeule/

cd dist
zip -r eventeule-test.zip eventeule/
cd ..

# Test:
unzip -l dist/eventeule-test.zip
```

## Why These Files?

### Necessary for WordPress

- **EventEule.php** - Plugin header, WordPress recognizes plugin by this
- **src/** - All PHP classes of the plugin
- **vendor/** - PHP dependencies (Plugin Update Checker, PSR-4 Autoloader)
- **assets/** - CSS/JS for frontend and admin
- **languages/** - Translations (WordPress i18n)
- **templates/** - PHP template files
- **uninstall.php** - WordPress calls when uninstalling

### Optional but Useful

- **LICENSE** - License information
- **README.md** - Project overview (helpful for support)

### NOT Necessary

- **docs/** - Only for developers, not for WordPress users
- **tests/** - Only for CI/CD
- **scripts/** - Only for development
- **composer.json** - WordPress only needs vendor/, not the config
- **package.json** - Only for npm build process
- **.github/** - Only for GitHub Actions

---

← Back to [Documentation](README.md)
