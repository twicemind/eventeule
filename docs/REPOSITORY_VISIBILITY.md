# GitHub Repository Visibility

Decision guide: Public or private?

## 🌍 Public Repository (Recommended)

### ✅ Advantages

1. **No tokens needed**
   - Updates work without configuration
   - End users don't need to set up anything
   
2. **Simple release process**
   - Releases are directly downloadable
   - Anyone can download the ZIP file
   
3. **Open Source**
   - Community can view code
   - Pull requests possible
   - Issues can be created by anyone
   
4. **Free**
   - GitHub Pages free
   - Unlimited collaborators
   - Unlimited build minutes

### ❌ Disadvantages

1. **Source code visible**
   - Everyone can see the code
   - No "proprietary" software
   
2. **No code secrecy**
   - Competitors can study code
   - License enforcement more complex

## 🔒 Private Repository

### ✅ Advantages

1. **Code remains secret**
   - Only team members see code
   - Proprietary features protected

2. **Controlled access**
   - Determine who sees code
   - Commercial use simpler

### ❌ Disadvantages

1. **Token required**
   - Every WordPress installation needs token
   - More complex setup procedure
   - Token management necessary

2. **More complex updates**
   - End users must configure token
   - Or: You host updates yourself

3. **Costs** (with many collaborators)
   - GitHub Free: 3 collaborators
   - More: GitHub Team ($4/user/month)

## 📊 Comparison

| Feature | Public | Private |
|---------|-----------|--------|
| **Updates without token** | ✅ Yes | ❌ No (token needed) |
| **Setup complexity** | 🟢 Simple | 🔴 Complex |
| **Code visibility** | 🌍 Public | 🔒 Private |
| **Costs** | 💚 Free | 💛 Possible costs |
| **Community contributions** | ✅ Possible | ❌ Not possible |
| **Issue tracking** | 🌍 Public | 🔒 Team-internal |

## 🎯 Recommendation for EventEule

### ✅ **Public repository recommended**

**Reasons:**
1. EventEule is an event management plugin
2. No business-critical algorithms
3. Simple updates for all users
4. Community can help (Issues, Pull Requests)
5. Transparent and trustworthy

### Exceptions (Private makes sense):

- You sell the plugin commercially
- Special features that are competitive advantage
- Customer-specific customizations
- Internal use only

## 🚀 Make Repository Public

### Step 1: On GitHub

1. Go to https://github.com/twicemind/eventeule/settings
2. Scroll to **Danger Zone**
3. Click **Change repository visibility**
4. Select **Make public**
5. Confirm with repository name: `twicemind/eventeule`

### Step 2: Verify

```bash
# Repository URL should work without login
https://github.com/twicemind/eventeule

# Releases should be visible
https://github.com/twicemind/eventeule/releases
```

### Step 3: Create first release

```bash
# Bump version
npm run release:patch

# Push to GitHub
git push && git push --tags
```

GitHub Actions automatically creates public release!

## 📝 What changes in the code?

### With public repository:

**Nothing!** The code works automatically:

```php
// src/Support/Updater.php
$githubToken = $this->get_github_token();

if (!empty($githubToken)) {
    // Only if token available (optional for private repos)
    $this->updateChecker->setAuthentication($githubToken);
}
// Works without token for public repos!
```

### With private repository:

**End users must configure token:**

1. Create token on GitHub
2. In WordPress installation:
   - Create `config-local.php`
   - Enter token: `'github_token' => 'ghp_xxx'`

## 🔄 Switch from private to public

### Steps:

1. **Check sensitive data**
   - No API keys in code?
   - No passwords in commits?
   - `.gitignore` complete?

2. **Make repository public**
   - See above

3. **Update documentation**
   - Remove references to "Private Repository"
   - Update installation guide
   - Mark token setup as optional

4. **Remove token**
   - From all production installations:
   - Delete `config-local.php` or remove token
   - Works automatically without token afterwards

## 🛡️ Security Check

### Before switching to public:

```bash
# Search for sensitive data
grep -r "password\|secret\|token\|api.*key" \
  --exclude-dir=vendor \
  --exclude-dir=node_modules \
  --exclude="*.md" \
  src/ *.php

# Should find nothing (except in comments/docs)!
```

### Check Git history:

```bash
# Search entire history
git log --all --full-history --source -- '*password*' '*secret*' '*token*'

# Should show no commits with sensitive files!
```

If sensitive data found:
→ See [SECURITY.md](SECURITY.md) → "Secrets accidentally committed"

## 📚 More Info

- [SECURITY.md](SECURITY.md) - Secure handling
- [PRE_COMMIT_CHECKLIST.md](PRE_COMMIT_CHECKLIST.md) - Before first push
- [INSTALLATION.md](INSTALLATION.md) - End user installation

## ❓ FAQ

### Can I switch back to private later?

Yes, anytime:
1. GitHub Settings → Change visibility → Make private
2. End users must then configure token
3. Updates only work with token

### Does a public repository cost anything?

No! GitHub is completely free for public repositories:
- Unlimited collaborators
- Unlimited build minutes
- Unlimited storage

### Will old releases also become public?

Yes, all previous releases become publicly visible once the repository is public.

### Can I keep certain branches private?

No, either completely public or completely private. Alternative:
- Separate repositories for private features
- Git submodules for private components

---

**Recommendation:** Make EventEule public! 🌍  
Easier for everyone, no token management, community contributions possible.
