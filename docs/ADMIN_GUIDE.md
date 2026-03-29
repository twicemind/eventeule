# EventEule - Where to Find What?

Overview of all menu items and functions in WordPress Admin.

## 🗂️ Main Menu

### EventEule 🦉 (Dashboard)
**Access:** In WordPress menu on left → **EventEule**

Three tabs available:

#### 📊 Tab: Overview
- **Statistics**
  - Total number of events
  - Upcoming events
  - Past events
  - Featured events

- **Quick Access**
  - Button: New Event
  - Button: All Events
  - Button: Manage Categories

- **Upcoming Events**
  - List of next 5 events
  - With date, location, edit link

#### 📅 Tab: Calendar
- Chronological overview of all events
- Grouped by months
- With all details (date, time, location)

#### 🎨 Tab: Widget Design
- Color settings for frontend widgets
- 6 customizable colors:
  - Primary color
  - Secondary color
  - Accent color
  - Text color
  - Background color
  - Border color

---

## 📅 Events (Post Type)

**Access:** WordPress menu on left → **Events**

### Submenus:

#### All Events
**Events** → **All Events**
- List of all created events
- Columns: Title, Start date, Location, Categories
- Actions: Edit, Quick Edit, Trash

#### Add New
**Events** → **Add New**
- Create new event
- Editor for description
- Meta-box "Event Details" on right

#### Categories
**Events** → **Categories**
- Manage event categories
- Examples: Workshops, Readings, Children's Events
- Slug is used for shortcode filter

---

## 📝 Edit Event

**When editing an event:**

### Main Area (Center)
- **Title**: Event name
- **Editor**: Description/content
- Optional: Elementor button "Edit with Elementor"

### Event Details Meta-Box (Right)

#### Fields:
1. **Start date** ⭐ (Required)
   - Format: DD.MM.YYYY
   - Example: 15.06.2026

2. **End date** (optional)
   - For multi-day events

3. **Start time** (optional)
   - Format: HH:MM
   - Example: 14:00

4. **End time** (optional)
   - Example: 18:00

5. **Location** (optional)
   - Address or place name
   - Example: "Huisheim Library, Main Street 1"

6. **Registration link** (optional)
   - Full URL
   - Example: https://eventbrite.com/...

7. **Note / Additional info** (optional)
   - Free text for additional information
   - Example: "Please bring ID"

8. **Featured** (Checkbox)
   - Mark event as "Featured"
   - Featured events can be filtered

### Categories Meta-Box (right)
- Assign one or more categories
- Create new category directly possible

### Featured Image (right)
- Optional: Upload image for the event
- Will be displayed on frontend

---

## 🔍 Where do I find...?

### ... my event list?
→ **Events** → **All Events**

### ... the dashboard with statistics?
→ **EventEule** (main menu item)

### ... the color settings?
→ **EventEule** → Tab **Widget Design**

### ... the categories?
→ **Events** → **Categories**

### ... a specific event?
→ **Events** → **All Events** → Use search (top right)

### ... deleted events?
→ **Events** → **Trash** (if available)

### ... update notifications?
→ **Dashboard** → **Updates**

### ... plugin settings?
→ Currently only colors in EventEule dashboard
→ More settings in future versions

---

## 🎯 Common Tasks

### Create event
1. **Events** → **Add New**
2. Fill in title + details
3. **Publish**

### Edit event
1. **Events** → **All Events**
2. Click on event title
3. Make changes
4. **Update**

### Delete event
1. **Events** → **All Events**
2. Hover over event title
3. Click **Trash**

### Restore event
1. **Events** → **Trash**
2. Hover over event title
3. Click **Restore**

### Create category
1. **Events** → **Categories**
2. Enter name (e.g. "Workshops")
3. Slug is created automatically
4. **Add New Category**

### Change colors
1. Open **EventEule** dashboard
2. Select tab **Widget Design**
3. Adjust colors with color picker
4. **Save Settings**

### Add event list to page
1. **Pages** → **Add New**
2. Title: "Events"
3. Content: `[eventeule_events]`
4. **Publish**

---

## 💡 Tips

### Use quick edit
**Events** → **All Events** → Hover over event → **Quick Edit**
- Quickly change date, title, categories
- Without full editor

### Bulk actions
In the event list you can:
- Select multiple events (checkboxes)
- Choose action (e.g. move to trash)
- Click **Apply**

### Sort columns
In **All Events**:
- Click on column headers
- Sorts by title, date, etc.

### Filter by category
In **All Events**:
- Dropdown "All Categories" (at top)
- Select category
- Click **Filter**

---

## 🔗 More Help

- 📖 [Complete Installation Guide](INSTALLATION.md)
- 🚀 [Quick Start Guide](QUICKSTART_USER.md)
- 🐛 [Report Problem](https://github.com/twicemind/eventeule/issues)
- 📧 Support: support@twicemind.com

---

**EventEule** - All Features at a Glance 🦉
