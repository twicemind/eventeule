# EventEule Release Process

This document describes the release process for the EventEule plugin.

## Automatic Release Process

### 1. Increase Version

There are three types of version bumps:

```bash
# Patch Release (1.0.0 -> 1.0.1) - Bugfixes
npm run release:patch

# Minor Release (1.0.0 -> 1.1.0) - New features, backwards compatible
npm run release:minor

# Major Release (1.0.0 -> 2.0.0) - Breaking changes
npm run release:major
```

The script does the following automatically:
- Checks if Git repository is clean
- Updates version number in all relevant files:
  - `EventEule.php`
  - `package.json`
  - `composer.json`
  - `README.md`
- Creates a Git commit with message "Bump version to X.X.X"
- Creates a Git tag `vX.X.X`

### 2. Push to GitHub

After version bump:

```bash
# Push commits and tags
git push && git push --tags
```

### 3. Automatic Release on GitHub

Once the tag is on GitHub, GitHub Actions automatically triggers:
- Build the plugin (composer install, npm ci)
- Create a ZIP archive without dev files
- Create a GitHub release with the ZIP as asset
- The release is then available at https://github.com/twicemind/eventeule/releases

## WordPress Plugin Updates

### Automatic Updates

The plugin uses [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) by Yahnis Elsts.

**How it works:**
1. The plugin regularly checks GitHub for new releases
2. If a new version is available, it's displayed in WordPress Admin
3. Users can install the update with one click

**Configuration:**
- Repository URL: `https://github.com/twicemind/eventeule`
- Update Checker class: `src/Support/Updater.php`
- Plugin header contains: `Update URI: https://github.com/twicemind/eventeule`

### Private Repository

If the repository is private:

1. Create a GitHub Personal Access Token with `repo` scope
2. Set the token in `src/Support/Updater.php`:

```php
$this->updateChecker->setAuthentication('your-token-here');
```

## Manual Installation

Users can also manually install the plugin:

1. Download ZIP file from [GitHub Release](https://github.com/twicemind/eventeule/releases)
2. In WordPress Admin: Plugins → Add New → Upload Plugin
3. Select ZIP file and install
4. Activate plugin

## Version Scheme

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR** (X.0.0): Breaking changes, not backwards compatible
- **MINOR** (0.X.0): New features, backwards compatible
- **PATCH** (0.0.X): Bugfixes, backwards compatible

## Checklist Before Release

- [ ] All tests pass successfully
- [ ] Changelog was updated
- [ ] README.md is up to date
- [ ] No debug output in code
- [ ] Git repository is clean (no uncommitted changes)
- [ ] Local tests performed with `npm run wp:start`

## Troubleshooting

### GitHub Release is not created

Check:
- GitHub Actions is enabled in the repository
- The tag was formed correctly (Format: `vX.X.X`)
- GitHub Actions workflow file exists: `.github/workflows/release.yml`

### WordPress doesn't detect update

Check:
- Plugin Update Checker is installed: `composer require yahnis-elsts/plugin-update-checker`
- Updater class is registered in `src/Plugin.php`
- GitHub repository is public or token is set
- Release was correctly created on GitHub

### ZIP file is too large

The release script automatically excludes:
- `node_modules/`
- `.git/`
- `tests/`
- Dev dependencies

If the file is still too large, check the `.github/workflows/release.yml` file and add more exclusions.
