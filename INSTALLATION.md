# Installation Guide

## Quick Start

### Step 1: Download the Plugin

**From v0 Interface:**
1. Click the three dots (⋮) in the top right corner
2. Select "Download ZIP"
3. Extract the downloaded file

**From GitHub:**
1. Clone the repository or download as ZIP
2. Extract if necessary

### Step 2: Upload to WordPress

**Option A: Via WordPress Admin**
1. Go to **Plugins → Add New**
2. Click **Upload Plugin**
3. Choose the ZIP file
4. Click **Install Now**
5. Click **Activate Plugin**

**Option B: Via FTP/File Manager**
1. Upload the `vin-decoder-addon` folder to `/wp-content/plugins/`
2. Go to **Plugins** in WordPress admin
3. Find "Gravity Forms VIN Decoder Add-On"
4. Click **Activate**

### Step 3: Verify Installation

After activation, you should see:
- A new menu item: **Settings → VIN Decoder**
- No error messages
- Gravity Forms is detected and active

## Configuration

### 1. API Provider Setup

#### Using NHTSA (Free)

1. Go to **Settings → VIN Decoder**
2. Select **NHTSA (Free)** from the API Provider dropdown
3. Click **Save Changes**
4. No API key needed!

#### Using RapidAPI

1. Sign up at [RapidAPI](https://rapidapi.com/)
2. Subscribe to a VIN Decoder API
3. Copy your API key
4. Go to **Settings → VIN Decoder**
5. Select **RapidAPI VIN Decoder**
6. Paste your API key
7. Click **Save Changes**

#### Using CarMD

1. Sign up at [CarMD API](https://api.carmd.com/)
2. Get your API key
3. Go to **Settings → VIN Decoder**
4. Select **CarMD**
5. Paste your API key
6. Click **Save Changes**

### 2. Field Mapping Configuration

#### Find Your Form Field IDs

1. Edit your Gravity Form
2. Click on a field to see its settings
3. Note the **Field ID** (usually a number like 3, 4, 5)

#### Create Field Mappings

1. Go to **Settings → VIN Decoder → Field Mapping**
2. Click **Add Mapping**
3. Fill in the row:
   - **Form**: Select your Gravity Form
   - **VIN Data Field**: Choose what data to map (e.g., "Make")
   - **Gravity Forms Field ID**: Enter the field ID (e.g., "3")
4. Repeat for all fields you want to auto-populate
5. Click **Save Field Mappings**

#### Example Mapping

| Form | VIN Data Field | GF Field ID |
|------|----------------|-------------|
| Vehicle Form | Make | 3 |
| Vehicle Form | Model | 4 |
| Vehicle Form | Year | 5 |
| Vehicle Form | Trim | 6 |

### 3. Enable VIN Decoder on Form

#### Method 1: Using Field Settings (Recommended)

1. Edit your Gravity Form
2. Add or select a **Text** field for VIN input
3. In the **Appearance** tab, add CSS class: `vin-field`
4. In the **Advanced** tab, check **Enable VIN Decoder**
5. Save the form

#### Method 2: Using CSS Class

1. Edit your Gravity Form
2. Add or select a **Text** field for VIN input
3. In the **Appearance** tab, add CSS class: `vin-field`
4. Save the form

## Testing

### Test VIN Numbers

Use these valid VIN numbers for testing:

- `1HGBH41JXMN109186` - Honda Accord
- `1FTFW1ET5BFC10312` - Ford F-150
- `5YJSA1E14HF000001` - Tesla Model S
- `WBADT43452G920072` - BMW 3 Series

### Test the Integration

1. Open your form on the frontend
2. Enter a test VIN in the VIN field
3. Wait for the field to reach 17 characters
4. Watch as mapped fields auto-populate
5. Verify the data is correct

### Troubleshooting Tests

**If fields don't populate:**

1. Check browser console for JavaScript errors
2. Verify field mapping is correct
3. Check that API provider is configured
4. Enable logging and check for API errors
5. Verify the VIN field has the correct CSS class

## Advanced Configuration

### Enable Logging

1. Go to **Settings → VIN Decoder → Logging**
2. Check **Enable Logging**
3. Select **Log Level**: Info, Warning, or Error
4. Click **Save Changes**

### View Logs

- Go to **Settings → VIN Decoder → Logging**
- Scroll down to see recent logs
- Logs show API calls, errors, and field population events

### Clear Logs

Click **Clear Logs** button to remove all log entries.

## File Permissions

Ensure these directories are writable:

\`\`\`
/wp-content/uploads/vin-decoder-logs/  (for logging)
/wp-content/plugins/vin-decoder-addon/ (for updates)
\`\`\`

Set permissions to `755` for directories and `644` for files.

## Server Requirements

### Minimum Requirements

- WordPress 6.6+
- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- Gravity Forms (latest version)

### Recommended Requirements

- WordPress 6.7+
- PHP 8.3+
- MySQL 8.0+ or MariaDB 10.6+
- HTTPS enabled
- Modern browser (Chrome, Firefox, Safari, Edge)

### PHP Extensions

Required:
- `json` - For API communication
- `curl` or `allow_url_fopen` - For HTTP requests

Optional:
- `mbstring` - For better string handling

## Uninstallation

### Deactivate Plugin

1. Go to **Plugins**
2. Find "Gravity Forms VIN Decoder Add-On"
3. Click **Deactivate**

### Remove Plugin Data

The plugin preserves settings on deactivation. To completely remove:

1. Deactivate the plugin
2. Click **Delete**
3. Confirm deletion

This will remove:
- Plugin files
- Settings and field mappings
- Log files

## Getting Help

If you encounter issues:

1. Check the [README.md](README.md) for common solutions
2. Enable logging and check for errors
3. Verify all requirements are met
4. Test with a different API provider
5. Contact support with log details

## Next Steps

After installation:

1. ✅ Configure your API provider
2. ✅ Set up field mappings
3. ✅ Enable VIN decoder on form fields
4. ✅ Test with sample VINs
5. ✅ Enable logging for monitoring
6. 🚀 Go live!

---

**Need Help?** Check the main [README.md](README.md) for detailed documentation and developer hooks.
