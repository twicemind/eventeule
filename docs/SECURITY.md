# Security Policy

## Safe Handling of Credentials

### What is NOT committed to GitHub

EventEule uses various mechanisms to keep security-relevant information local:

#### Automatically Ignored Files (.gitignore)

```
.env                    # Environment variables
.env.local              # Local environment variables
.env.*.local            # Environment-specific variables
config-local.php        # Local PHP configuration
.wp-env.override.json   # Local WordPress configuration
```

### Secure Credential Management

#### GitHub Token (for private repositories)

**NEVER directly in code:**
```php
// ❌ WRONG - will be committed
$this->updateChecker->setAuthentication('ghp_token123');
```

**Instead - Environment variable or local config:**
```php
// ✅ CORRECT - local only
// In .env:
GITHUB_ACCESS_TOKEN=ghp_token123

// Or in config-local.php:
return ['github_token' => 'ghp_token123'];
```

#### Database Credentials

Production credentials belong **NEVER** in the repository:
- Not in code files
- Not in comments
- Not in commit messages
- Not in .wp-env.json

Use instead:
- `.env` for local development
- `config-local.php` for local PHP configuration
- Server-level environment variables for production

### Best Practices

1. **Check before each commit:**
   ```bash
   git diff --cached  # Show what will be committed
   git status         # Check status
   ```

2. **Search for accidentally committed secrets:**
   ```bash
   grep -r "password\|secret\|token" --exclude-dir=vendor --exclude-dir=node_modules
   ```

3. **Use template files:**
   - `.env.example` instead of `.env`
   - `config-local.example.php` instead of `config-local.php`
   - `.wp-env.json.example` for documentation

4. **Token rotation:**
   - Set expiration date for GitHub tokens
   - Rotate tokens regularly
   - Revoke old tokens

5. **Minimal permissions:**
   - GitHub Token: only `repo` scope (if repository is private)
   - Grant no additional permissions

### Secrets accidentally committed?

**Act immediately:**

1. **Revoke token/secret:**
   - GitHub: https://github.com/settings/tokens
   - Other service: Respective admin interface

2. **Create new token:**
   - With new, secure value
   - Enter in local config

3. **Clean Git history:**
   ```bash
   # Remove sensitive file from history
   git filter-branch --force --index-filter \
     "git rm --cached --ignore-unmatch path/to/file" \
     --prune-empty --tag-name-filter cat -- --all
   
   # Force push (CAUTION!)
   git push origin --force --all
   git push origin --force --tags
   ```

4. **Inform team members:**
   - Everyone must clone fresh
   - Old clones still contain the secrets

### Perform Security Audit

#### Automated with grep:

```bash
# Search for potential secrets
grep -r -i "password\|secret\|token\|api.*key\|credential\|auth" \
  --exclude-dir=vendor \
  --exclude-dir=node_modules \
  --exclude-dir=.git \
  --exclude="*.md" \
  --exclude="SECURITY.md"
```

#### Manual inspection:

- [ ] No passwords in code files
- [ ] No hardcoded API keys
- [ ] No database credentials in repository
- [ ] All local configs in .gitignore
- [ ] Template files (*.example) are present
- [ ] README contains no sensitive information

### Reporting Security Issues

If you find a security issue:

**DON'T:**
- Create a public GitHub issue
- Post the problem on social media
- Share details publicly

**Instead:**
1. Send an email to: [security@twicemind.com]
2. Describe the problem in detail
3. Wait for response (max. 48h)
4. Cooperate on the solution

### Responsible Disclosure

We follow the principle of "Responsible Disclosure":
- No public disclosure before fix
- Credit for discoverer (if desired)
- Transparent communication
- Fast response time

### Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |
| < 1.0   | :x:                |

### Security Updates

Security patches are released as **patch releases**:
- Critical vulnerabilities: Immediate release
- Medium severity: Within 7 days
- Low severity: Next regular release

Updates appear automatically in WordPress Admin.

### Additional Resources

- [LOCAL_SETUP.md](LOCAL_SETUP.md) - Local development environment
- [RELEASE.md](RELEASE.md) - Release-Prozess
- [WordPress Security Best Practices](https://wordpress.org/support/article/hardening-wordpress/)
