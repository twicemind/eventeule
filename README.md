# EventEule 🦉

WordPress Event Plugin for Events, Appointments and Activities.

## Features

- 📅 Event Management with date, time, location
- 🏷️ Event Categories
- ⭐ Featured Events
- 🎨 Elementor Integration (Dynamic Tags)
- 🎨 Customizable Widget Colors
- 📊 Admin Dashboard with Statistics and Calendar View
- 🔄 Automatic Updates via GitHub Releases
- 🌍 Multilingual (German/English)

## Installation

### From GitHub Release (Recommended)

1. Download the latest ZIP file from [Releases](https://github.com/twicemind/eventeule/releases)
2. In WordPress Admin: Plugins → Add New → Upload Plugin
3. Choose the ZIP file and install
4. Activate the plugin

📖 **Full Installation Guide:** [INSTALLATION.md](docs/INSTALLATION.md)

### Automatic Updates

The plugin automatically checks GitHub Releases and displays available updates in WordPress Admin. One-click updates directly from WordPress - **no configuration required**.

## Local Development

**⚠️ Important:** Please read the security notes first!

```bash
# Clone repository
git clone https://github.com/twicemind/eventeule.git
cd eventeule

# Install dependencies
composer install
npm install

# Create local configuration
cp .env.example .env
cp config-local.example.php config-local.php

# Start WordPress test environment
npm run wp:start
```

📖 **Full Guide:** [LOCAL_SETUP.md](docs/LOCAL_SETUP.md)

## Security

🔒 **Important:** This repository contains no sensitive data!

- All credentials remain local
- `.env` and local configs are in `.gitignore`
- GitHub tokens are only stored locally

📖 **Details:** [SECURITY.md](docs/SECURITY.md)

## Usage

### Shortcode

```php
[eventeule_events limit="5" category="workshops"]
```

Parameters:
- `limit` - Number of events (default: 10)
- `category` - Filter by category slug
- `featured_only` - Show only featured events (true/false)
- `show_past` - Show past events (true/false)

### Elementor

All event metadata is available as Dynamic Tags:
- Event Start Date (with format selection)
- Event End Date
- Event Start Time
- Event End Time
- Event Location
- Event Registration URL
- Event Note

### Template Override

Copy `templates/public/events-list.php` to your theme:
```
your-theme/eventeule/events-list.php
```

## Release Process

### One-Click Release (Recommended)

```bash
npm run release
```

This **one** command handles automatically:
- ✅ Run tests
- ✅ Choose release type (interactive)
- ✅ Bump version
- ✅ Create commit
- ✅ Push to GitHub
- ✅ GitHub Actions creates release

### Manual Releases

```bash
# Bump version
npm run release:patch  # 1.0.0 -> 1.0.1
npm run release:minor  # 1.0.0 -> 1.1.0
npm run release:major  # 1.0.0 -> 2.0.0

# Push to GitHub
git push && git push --tags
```

GitHub Actions automatically creates a release with installable ZIP.

📖 **Details:** 
- [RELEASE_WORKFLOW.md](docs/RELEASE_WORKFLOW.md) - One-Click Release Explained
- [RELEASE.md](docs/RELEASE.md) - Detailed Release Documentation
- [QUICKSTART.md](docs/QUICKSTART.md) - First Release

## Technology

- **PHP:** 7.4+
- **WordPress:** 6.0+
- **Architecture:** PSR-4 Autoloading, OOP
- **Build:** Composer, npm
- **CI/CD:** GitHub Actions
- **Updates:** Plugin Update Checker

## Project Structure

```
eventeule/
├── src/
│   ├── Admin/          # Admin Interface
│   ├── Api/            # REST API
│   ├── Domain/         # Post Types, Taxonomies
│   ├── Frontend/       # Public Templates
│   ├── Integration/    # Elementor, etc.
│   ├── Repository/     # Data Access
│   └── Support/        # Helper Classes
├── templates/          # Template Files
├── assets/            # CSS, JS, Images
├── languages/         # Translations
├── docs/              # Documentation
└── scripts/           # Build Scripts
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Documentation

📚 Full Documentation: **[docs/](docs/)**

Includes guides for:
- Installation and Configuration
- Local Development
- Release Process
- Security
- and more

## License

ISC License - see [LICENSE](LICENSE)

## Support

- 📧 Email: support@twicemind.com
- 🐛 Issues: [GitHub Issues](https://github.com/twicemind/eventeule/issues)
- 📖 Docs: [Wiki](https://github.com/twicemind/eventeule/wiki)

## Changelog

See [Releases](https://github.com/twicemind/eventeule/releases) for detailed changes.

---

Developed with 💙 by [twicemind](https://twicemind.com)
