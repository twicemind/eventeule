# Quick Start: First Release

## Prerequisites

### GitHub Repository
- Repository must be **public**
- This way, no tokens are needed for updates

### GitHub Secrets
- `GITHUB_TOKEN` is automatically provided by GitHub Actions
- No additional configuration necessary

## Step 1: Create Release

### Easy Way (Recommended): Interactive Release

```bash
npm run release
```

This command **automatically** performs all steps:
1. ✓ Checks Git status
2. ✓ Runs tests
3. ✓ Asks for Release type (Patch/Minor/Major)
4. ✓ Shows your changes
5. ✓ Bumps version
6. ✓ Creates commit with detailed message
7. ✓ Asks whether to push to GitHub
8. ✓ Pushes code + tags
9. ✓ GitHub Actions automatically creates release

**That's it!** 🎉

### Manual Way: Individual Commands

If you need more control:

```bash
# Make sure all changes are committed
git status

# Create the first release (1.0.0)
npm run release:patch

# Or if you want to start directly with 1.0.0:
# The script sets the version from 1.0.0 to 1.0.1
# Alternatively you can manually set the version to 1.0.0 in all files
```

## Step 2: Check Release on GitHub

If you used `npm run release` (recommended), code and tags have already been pushed.

If you used the manual commands, push now:

```bash
git push origin main
git push --tags
```

Then:

1. Open https://github.com/twicemind/eventeule/actions
2. Wait until "Create Release" workflow completes (about 2-3 minutes)
3. Open https://github.com/twicemind/eventeule/releases
4. You should now see a new release with a ZIP file

## Step 3: Test Plugin in WordPress

### Option A: Test Automatic Update

1. Install the old version of the plugin in WordPress
2. Wait a few minutes (or force update check)
3. Go to Dashboard → Updates
4. The plugin should be shown as updatable
5. Click "Update Now"

### Option B: Manual Update

1. Download the ZIP file from GitHub Release
2. In WordPress Admin: Plugins → Add New → Upload Plugin
3. Select ZIP file and install

## Further Releases

### Simply with npm run release

```bash
npm run release
```

Done! The script asks you for the release type and does the rest.

### Or with specific commands

```bash
# Bugfix Release (1.0.1 -> 1.0.2)
npm run release:patch && git push && git push --tags

# Feature Release (1.0.2 -> 1.1.0)
npm run release:minor && git push && git push --tags

# Breaking Change Release (1.1.0 -> 2.0.0)
npm run release:major && git push && git push --tags
```

## Troubleshooting

### GitHub Actions fails

Check the logs at: https://github.com/twicemind/eventeule/actions

Common causes:
- Composer dependencies missing → run `composer install`
- Node dependencies missing → run `npm install`
- ZIP command fails → Check if all files are present

### WordPress doesn't detect updates

1. Check if the plugin is installed and activated
2. Check if `composer require yahnis-elsts/plugin-update-checker` was run
3. Check WordPress debug log for errors
4. Force update check in WordPress: Dashboard → Updates → Check for Updates

### Public vs. Private Repository

**Public Repository (Recommended):**
✅ No configuration necessary
✅ Updates work automatically
✅ Easier for end users

**Private Repository:**
If you want to make the repository private:
1. Create a GitHub Personal Access Token with `repo` scope
2. Create `config-local.php` with token (see LOCAL_SETUP.md)
3. Token is only required for production site, not local
