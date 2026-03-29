# Release Workflow

## One-Click Release

```bash
npm run release
```

This **one** command does everything:

### What happens automatically?

1. **Check Git status** 🔍
   - Shows uncommitted changes
   - Asks if to continue

2. **Run tests** ✅
   - PHP Syntax Check of all files
   - Required Files Check
   - Version Consistency Check
   - Composer Dependencies Check

3. **Choose release type** 🎯
   - Shows your changes
   - Asks: Patch (1.0.0 → 1.0.1) / Minor (1.0.0 → 1.1.0) / Major (1.0.0 → 2.0.0)
   - You choose

4. **Bump version** 🔢
   - Updates EventEule.php
   - Updates package.json
   - Updates composer.json
   - Updates README.md
   - Creates Git tag

5. **Create commit** 💾
   - Generates detailed commit message with:
     - Release Version
     - Release Type
     - Previous Version
     - List of all changed files
   - Asks before committing

6. **Push to GitHub** 🚀
   - Asks if to push
   - Pushes code + tags
   - GitHub Actions takes over the rest

7. **GitHub Actions** ⚙️
   - Automatically creates release
   - Packs ZIP file
   - Publishes release

8. **WordPress Updates** 🔄
   - Plugin Update Checker checks GitHub
   - WordPress shows update
   - One-click update for users

## Alternative: Manual Commands

If you need more control:

```bash
# Only tests
npm test

# Only version bump (without push)
npm run release:patch  # or :minor or :major

# Push separately
git push && git push --tags
```

## Beispiel-Session

```bash
$ npm run release

╔══════════════════════════════════════╗
║   EventEule Smart Release 🦉         ║
╚══════════════════════════════════════╝

[1/7] Checking Git status...
⚠️  Uncommitted changes found:
 M src/Admin/Admin.php
 M assets/css/admin.css
Do you want to continue? (y/n) y

[2/7] Running tests...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   EventEule Tests 🦉
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
[1/4] PHP Syntax Check...
  ✓ All PHP files OK
[2/4] Required Files Check...
  ✓ All required files present
[3/4] Version Consistency Check...
  ✓ Versions consistent
[4/4] Composer Dependencies Check...
  ✓ Composer Dependencies OK
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  ✓ All tests passed! (4/4)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✓ Tests successful

[3/7] Determining release type...

Changes:
src/Admin/Admin.php
assets/css/admin.css

Which release type?
  1) Patch   (1.0.0 → 1.0.1) - Bugfixes, small changes
  2) Minor   (1.0.0 → 1.1.0) - New features, backwards compatible
  3) Major   (1.0.0 → 2.0.0) - Breaking changes

Choose (1/2/3): 2
✓ Release type: minor

[4/7] Determining version...
Current version: 1.0.0

[5/7] Increasing version to minor...
✓ New version: 1.1.0

[6/7] Creating commit...

Commit message:
---
Release v1.1.0

Release-Type: minor
Previous Version: 1.0.0

Changes:
  - src/Admin/Admin.php
  - assets/css/admin.css
---

Create commit? (y/n) y
✓ Commit created

[7/7] Push to GitHub...

Push to GitHub (with tags)? (y/n) y

╔══════════════════════════════════════╗
║   ✓ Release successful! 🎉          ║
╚══════════════════════════════════════╝

Release v1.1.0

Next steps:
  1. GitHub Actions automatically creates release
  2. Check: https://github.com/twicemind/eventeule/releases
  3. WordPress installations receive update notification
```

## What if something goes wrong?

### Tests fail
```bash
$ npm run release
...
[2/7] Running tests...
  ✗ 1 of 4 tests failed
❌ Tests failed!
```

**Solution:** Fix errors, then try again.

### Version was bumped but not pushed

```bash
$ npm run release
...
Push to GitHub (with tags)? (y/n) n
Not pushed. You can push later with:
  git push && git push --tags
```

**Solution:** 
```bash
git push && git push --tags
```

### Release aborted after version bump

If you abort the release process after the version was already changed:

```bash
# Undo last commit
git reset --hard HEAD~1

# Or: Simply run again
npm run release
```

## Tips

### Before Release

✅ Test your changes locally
✅ Check `npm test` runs successfully
✅ Commit all changes (or choose "y" for uncommitted changes)

### After Release

✅ Check GitHub Actions: https://github.com/twicemind/eventeule/actions
✅ Check release was created: https://github.com/twicemind/eventeule/releases
✅ Test update in WordPress (can take 5-10 minutes)

### Understanding Release Types

- **Patch (1.0.0 → 1.0.1)**
  - Bugfixes
  - Small improvements
  - Fix typos
  - No new features

- **Minor (1.0.0 → 1.1.0)**
  - New features
  - New functionality
  - Backwards compatible
  - Existing features continue to work

- **Major (1.0.0 → 2.0.0)**
  - Breaking changes
  - Incompatible with old version
  - Major changes
  - Users may need to adjust config

## See Also

- [QUICKSTART.md](QUICKSTART.md) - First Release
- [RELEASE.md](RELEASE.md) - Detailed Release Documentation
- [PRE_COMMIT_CHECKLIST.md](PRE_COMMIT_CHECKLIST.md) - What to Check Before a Commit
