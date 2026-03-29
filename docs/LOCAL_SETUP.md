# Set Up Local Development Environment

This guide helps you configure the EventEule plugin locally and securely.

## ⚠️ Important: Security

**These files are NEVER committed:**
- `.env` - Environment variables with secrets
- `config-local.php` - Local PHP configuration
- `.wp-env.override.json` - Local WordPress environment overrides

They are included in `.gitignore` and remain **only on your computer**.

## Step 1: Basic Setup

```bash
# Clone repository
git clone https://github.com/twicemind/eventeule.git
cd eventeule

# Install dependencies
composer install
npm install
```

## Step 2: Create Local Configuration

### Option A: Environment Variables (.env)

```bash
# Copy the example file
cp .env.example .env

# Edit .env and add your secrets
nano .env
```

Add your GitHub token (only if repository is private):
```bash
GITHUB_ACCESS_TOKEN=ghp_yourTokenHere
```

### Option B: PHP Configuration File

```bash
# Copy the example file
cp config-local.example.php config-local.php

# Edit config-local.php
nano config-local.php
```

Add your GitHub token:
```php
return [
    'github_token' => 'ghp_yourTokenHere',
];
```

## Step 3: WordPress Test Environment

### Basic Setup
```bash
# Start local WordPress instance
npm run wp:start
```

The default login credentials are:
- URL: http://localhost:8888/wp-admin
- Username: `admin`
- Password: `password`

### Advanced Configuration (Optional)

If you need special settings (e.g., other ports, additional plugins):

```bash
# Copy base configuration
cp .wp-env.json .wp-env.override.json

# Edit .wp-env.override.json
nano .wp-env.override.json
```

Example for `.wp-env.override.json`:
```json
{
  "port": 8889,
  "plugins": [
    ".",
    "https://downloads.wordpress.org/plugin/elementor.latest-stable.zip",
    "https://downloads.wordpress.org/plugin/your-other-plugin.zip"
  ],
  "config": {
    "WP_DEBUG": true,
    "WP_DEBUG_LOG": true,
    "WP_DEBUG_DISPLAY": false
  }
}
```

## Step 4: Activate Plugin

```bash
# Activate plugin in WordPress
npm run wp:cli -- plugin activate eventeule

# Activate Elementor (optional)
npm run wp:cli -- plugin list
```

## Create GitHub Token (only for private repositories)

If your repository is private:

1. Go to https://github.com/settings/tokens
2. Click "Generate new token" → "Generate new token (classic)"
3. Name: `EventEule Local Development`
4. Expiration: Choose an appropriate duration
5. Scopes: Enable **repo** (all sub-options)
6. Click "Generate token"
7. **Copy the token immediately** (it's only shown once!)
8. Add it to `.env` or `config-local.php`

## Import Production Data (Optional)

### Import Database

```bash
# Export database from production server
mysqldump -h prod-host -u prod-user -p prod-database > prod-db.sql

# Import into local environment
npm run wp:cli -- db import prod-db.sql

# Adjust URLs
npm run wp:cli -- search-replace 'https://your-domain.com' 'http://localhost:8888' --all-tables

# Reset admin password
npm run wp:cli -- user update admin --user_pass=password
```

### Copy Uploads

```bash
# From production server
scp -r user@prod-server:/path/to/wp-content/uploads ./local-uploads/

# Add to .wp-env.override.json:
{
  "mappings": {
    "./local-uploads": "wp-content/uploads"
  }
}
```

## Troubleshooting

### Port already in use

Change the port in `.wp-env.override.json`:
```json
{
  "port": 8889,
  "testsPort": 8890
}
```

### Plugin updates not working

Check:
1. Is the GitHub token set correctly?
2. Is the repository public or private?
3. Check WordPress debug log: `npm run wp:logs`

### Local files are being committed

This shouldn't happen! Check:
```bash
# Show ignored files
git status --ignored

# .env and config-local.php should appear here
```

If not, check the `.gitignore` file.

## Useful Commands

```bash
# Start/stop WordPress
npm run wp:start
npm run wp:stop
npm run wp:destroy  # Complete deletion and restart

# Execute WP-CLI commands
npm run wp:cli -- plugin list
npm run wp:cli -- user list
npm run wp:cli -- db export backup.sql

# Show logs
npm run wp:logs

# Create release
npm run release:patch  # 1.0.0 -> 1.0.1
npm run release:minor  # 1.0.0 -> 1.1.0
npm run release:major  # 1.0.0 -> 2.0.0
```

## Security Checklist

- [ ] `.env` is listed in `.gitignore`
- [ ] `config-local.php` is listed in `.gitignore`
- [ ] `.wp-env.override.json` is listed in `.gitignore`
- [ ] No passwords or tokens in committed files
- [ ] GitHub token has minimal required permissions (only `repo`)
- [ ] Token expiration is set (not "No expiration")
- [ ] Production credentials are only in local, non-committed files

## Support

If you have problems:
1. Check the logs: `npm run wp:logs`
2. Check GitHub Issues: https://github.com/twicemind/eventeule/issues
3. Create a new issue with error details
