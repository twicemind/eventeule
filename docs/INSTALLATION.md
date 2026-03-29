# EventEule Installation & Getting Started

Step-by-step guide for installing EventEule on your WordPress site.

## Prerequisites

- ✅ WordPress 6.0 or higher
- ✅ PHP 7.4 or higher
- ✅ Write permissions in WordPress directory

## Installation

### Method 1: Automatic Installation (Recommended)

1. **Open WordPress Admin**
   - Go to your WordPress backend
   - URL: `https://your-domain.com/wp-admin`

2. **Install plugin**
   - Click **Plugins** → **Add New**
   - Click **Upload Plugin**
   - Select the downloaded `eventeule-x.x.x.zip` file
   - Click **Install Now**

3. **Activate plugin**
   - After successful installation a dialog appears
   - Click **Activate Plugin**

✅ **Done!** EventEule is now active.

### Method 2: Manual Installation (FTP)

1. **Unzip file**
   - Unzip `eventeule-x.x.x.zip` on your computer
   - You'll get a folder `eventeule`

2. **Upload via FTP**
   - Connect to your server via FTP
   - Navigate to `/wp-content/plugins/`
   - Upload the complete `eventeule` folder

3. **Activate plugin**
   - Go to WordPress Admin
   - Click **Plugins** → **Installed Plugins**
   - Find "EventEule" in the list
   - Click **Activate**

## After Installation

### 1. Open Admin Dashboard

A new menu item appears in WordPress: **EventEule** 🦉

Click on it to open the dashboard. Here you'll find:
- 📊 **Overview** - Statistics and Quick Actions
- 📅 **Calendar** - All events in calendar view
- 🎨 **Widget Design** - Customize colors for frontend widgets

### 2. Create First Event

**Option A: Via Dashboard**
1. Click **Add New Event** in the EventEule dashboard
2. Fill in the event details

**Option B: Via WordPress Menu**
1. Click **Events** → **Add New**
2. Enter a title (e.g., "Summer Festival 2026")
3. Write a description in the editor

### 3. Fill in Event Details

In the **Event Details** metabox you'll find:

#### 📅 Date & Time
- **Start date** (Required): When does the event start?
- **End date** (optional): For multi-day events
- **Start time** (e.g., "14:00"): Time
- **End time** (e.g., "18:00"): End time

#### 📍 Location
- **Location**: Address or place name (e.g., "Huisheim Library")

#### 🔗 Registration
- **Registration link**: URL for registration/tickets

#### 📝 Additional Info
- **Note**: Special information (e.g., "Please bring ID")

#### ⭐ Highlight
- **Featured**: Checkbox for important events

### 4. Assign Categories (optional)

1. Create categories: **Events** → **Categories**
   - Examples: "Workshops", "Readings", "Children"
2. Assign one or more categories when creating events

### 5. Publish Event

1. Check all details
2. Click **Publish** on the right
3. Your event is now live!

## Display Events on Website

### Method 1: Shortcode

Add to a page or post:

```
[eventeule_events]
```

**With options:**
```
[eventeule_events limit="5" category="workshops" featured_only="false"]
```

Available parameters:
- `limit` - Number of events (Default: 10)
- `category` - Filter by category slug
- `featured_only` - Only featured events (true/false)
- `show_past` - Show past events (true/false)

**Create example page:**
1. **Pages** → **Add New**
2. Title: "Events"
3. Content: `[eventeule_events limit="10"]`
4. Publish

### Method 2: Elementor (if installed)

If you use Elementor:

1. **Edit single event**
   - Open an event
   - Click **Edit with Elementor**

2. **Use Dynamic Tags**
   - Add a text widget
   - Click the Dynamic Tag icon (🔗)
   - Select **EventEule** → desired field
   - Available:
     - Event Start Date (with formatting)
     - Event End Date
     - Event Start Time
     - Event End Time
     - Event Location
     - Event Registration URL
     - Event Note

3. **Button with registration link**
   - Add a button widget
   - Under "Link" → Dynamic Tags → EventEule → Registration URL

## Customize Widget Design

1. Go to **EventEule** → **Widget Design** tab
2. Customize colors:
   - **Primary color** - Main color for buttons
   - **Secondary color** - Hover effects
   - **Accent color** - Featured badges
   - **Text color** - Font color
   - **Background color** - Widget background
   - **Border color** - Borders and lines
3. Click **Save Settings**
4. Colors will be applied to frontend widgets

## Automatic Updates

EventEule automatically checks for new versions from GitHub:

1. **Update notification** appears in **Dashboard** → **Updates**
2. Click **Update Now**
3. WordPress automatically downloads and installs the new version

**Recommendation:** Make a backup before major updates!

**Note:** Since the GitHub repository is public, no additional settings or tokens are required. Updates work automatically!

## Getting Started Checklist

- [ ] Plugin installed and activated
- [ ] First test event created
- [ ] Event categories created (e.g., Workshops, Readings)
- [ ] Event published and checked on website
- [ ] Page with event list created (`[eventeule_events]`)
- [ ] Widget colors customized (optional)
- [ ] Elementor integration tested (if available)

## Frequently Asked Questions

### Where do I find my events?

In WordPress Admin under **Events** → **All Events**

### How do I change an event?

**Events** → **All Events** → Click on the event title → Edit

### Can I hide past events?

Yes! Use the shortcode:
```
[eventeule_events show_past="false"]
```

### How do I create an event overview page?

1. **Pages** → **Add New**
2. Title: "Events" or "Schedule"
3. Content: `[eventeule_events limit="20"]`
4. Publish
5. Add the page to your menu

### Can I import events?

Currently only manually. CSV import is planned for a future version.

### Events are not displayed?

Check:
- Is the event **published**? (not "draft")
- Does the event have a **start date**?
- Is the date in the **future**? (with `show_past="false"`)
- Is the shortcode placed correctly?

### How do I delete an event?

**Events** → **All Events** → Hover over event title → **Trash**

Permanently delete: **Events** → **Trash** → **Delete Permanently**

### Design doesn't match my theme?

1. Go to **EventEule** → **Widget Design**
2. Adjust colors to match your theme
3. Save settings

Alternatively: Override templates (see Advanced Usage below)

## Advanced Usage

### Template Override

You can customize the event list to match your theme:

1. Copy `wp-content/plugins/eventeule/templates/public/events-list.php`
2. To `wp-content/themes/your-theme/eventeule/events-list.php`
3. Customize the file as desired
4. EventEule will automatically use your version

### REST API

EventEule provides a REST API:

```
GET /wp-json/eventeule/v1/events
```

Parameters:
- `limit` - Number (default: 10)
- `category` - Category slug
- `featured_only` - true/false
- `show_past` - true/false

### Hooks for Developers

```php
// Custom logic after event creation
add_action('eventeule_after_save_event', function($post_id) {
    // Your code here
});
```

## Support & Help

### Documentation
- 📖 [Complete Documentation](https://github.com/twicemind/eventeule)
- 📝 [Changelog](https://github.com/twicemind/eventeule/releases)

### Report a Problem
- 🐛 [GitHub Issues](https://github.com/twicemind/eventeule/issues)
- 📧 Email: support@twicemind.com

### System info for support

Helpful when reporting problems:

```
WordPress version: Dashboard → Updates (top right)
PHP version: Dashboard → Site Health → Info → Server
EventEule version: Plugins → EventEule (below the name)
Theme: Appearance → Themes (current theme)
Other active plugins: Plugins → Installed Plugins
```

## Uninstallation

If you want to remove EventEule:

1. **Create a backup!** (events will be lost)
2. **Plugins** → **Installed Plugins**
3. Find "EventEule"
4. Click **Deactivate**
5. Click **Delete**
6. Confirm deletion

**Warning:** All events, categories, and settings will be deleted!

---

## Next Steps

- ✍️ Create more events
- 🎨 Design event pages with Elementor
- 📱 Test display on mobile devices
- 🔔 Enable automatic updates
- 📊 Use the dashboard for overview

Good luck with EventEule! 🦉
