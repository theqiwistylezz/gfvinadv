# Gravity Forms VIN Decoder Add-On

A production-grade WordPress plugin that integrates with Gravity Forms to provide automatic Vehicle Identification Number (VIN) decoding and field population.

## 🚀 Features

- **Multiple API Providers**: Support for NHTSA (free), RapidAPI, and CarMD
- **Live AJAX Decoding**: Instant field population as users type
- **Flexible Field Mapping**: Map any VIN data field to any Gravity Forms field
- **Comprehensive Logging**: Track API calls and errors for debugging
- **Translation Ready**: Fully internationalized with i18n support
- **Secure & Optimized**: Follows WordPress and Envato coding standards
- **Extensible Architecture**: Built for future integrations and features

## 📋 Requirements

- WordPress 6.6 or higher
- PHP 8.2 or higher
- Gravity Forms plugin (latest version recommended)
- API key for premium providers (optional)

## 📦 Installation

### Method 1: Download from v0

1. Click the three dots (⋮) in the top right corner
2. Select "Download ZIP"
3. Extract the downloaded file
4. Upload the `vin-decoder-addon` folder to `/wp-content/plugins/`
5. Activate the plugin through the WordPress Plugins menu

### Method 2: Manual Installation

1. Clone or download this repository
2. Copy the plugin files to `/wp-content/plugins/vin-decoder-addon/`
3. Activate the plugin in WordPress admin

## ⚙️ Configuration

### 1. Configure API Provider

1. Go to **Settings → VIN Decoder**
2. Select your API provider:
   - **NHTSA** (free, no API key required)
   - **RapidAPI** (requires API key)
   - **CarMD** (requires API key)
3. Enter your API key if using a premium provider
4. Click **Save Changes**

### 2. Map Form Fields

1. Go to **Settings → VIN Decoder → Field Mapping**
2. Click **Add Mapping**
3. Select your Gravity Form
4. Choose a VIN data field (Make, Model, Year, etc.)
5. Enter the corresponding Gravity Forms field ID
6. Repeat for all fields you want to auto-populate
7. Click **Save Field Mappings**

### 3. Enable VIN Decoder on Form Field

1. Edit your Gravity Form
2. Add or select a text field for VIN input
3. In the field settings, check **Enable VIN Decoder**
4. Save the form

## 🎯 Usage

Once configured, the plugin works automatically:

1. User opens a form with a VIN field
2. User enters a 17-character VIN
3. Plugin decodes the VIN via API
4. Mapped fields are automatically populated
5. User can review and submit the form

## 📁 Plugin Structure

\`\`\`
/vin-decoder-addon/
├── vin-decoder-addon.php          # Main plugin bootstrap
├── readme.txt                      # WordPress plugin readme
│
├── /includes/                      # PHP classes
│   ├── class-vin-decoder-addon.php    # Main add-on logic
│   ├── class-vin-api-client.php       # API integration
│   ├── class-vin-autofill.php         # Field population
│   ├── class-admin-settings.php       # Admin interface
│   ├── class-ajax-handler.php         # AJAX endpoints
│   ├── class-logger.php               # Logging system
│   ├── class-license-verifier.php     # Envato license
│   └── helpers.php                    # Utility functions
│
├── /assets/                        # Frontend assets
│   ├── /css/
│   │   ├── admin.css                  # Admin styles
│   │   └── frontend.css               # Frontend styles
│   └── /js/
│       ├── admin.js                   # Admin JavaScript
│       └── frontend.js                # VIN decoder AJAX
│
└── /languages/                     # Translations
    └── vin-decoder-addon-en_US.po     # Translation template
\`\`\`

## 🔌 API Providers

### NHTSA (Free)
- **Provider**: National Highway Traffic Safety Administration
- **API Key**: Not required
- **Endpoint**: `https://vpic.nhtsa.dot.gov/api/`
- **Best For**: Basic VIN decoding without cost

### RapidAPI (Premium)
- **Provider**: RapidAPI VIN Decoder
- **API Key**: Required
- **Best For**: Comprehensive VIN data with enhanced details
- **Get API Key**: [RapidAPI Marketplace](https://rapidapi.com/)

### CarMD (Premium)
- **Provider**: CarMD
- **API Key**: Required
- **Best For**: Detailed vehicle information and diagnostics
- **Get API Key**: [CarMD API](https://api.carmd.com/)

## 🛠️ Developer Hooks

### Filters

\`\`\`php
// Modify API response before processing
add_filter('vin_decoder_api_response', function($response, $vin) {
    // Your custom logic
    return $response;
}, 10, 2);

// Modify formatted VIN data
add_filter('vin_decoder_formatted_data', function($data, $raw_data, $provider) {
    // Add custom fields or modify existing ones
    $data['custom_field'] = 'Custom Value';
    return $data;
}, 10, 3);

// Modify individual field values before population
add_filter('vin_decoder_field_value', function($value, $vin_field, $gf_field_id, $decoded_data) {
    // Transform the value
    return strtoupper($value);
}, 10, 4);
\`\`\`

## 🐛 Debugging

### Enable Logging

1. Go to **Settings → VIN Decoder → Logging**
2. Check **Enable Logging**
3. Select log level (Info, Warning, or Error)
4. Click **Save Changes**

### View Logs

- Logs are displayed in the **Logging** tab
- Log files are stored in `/wp-content/uploads/vin-decoder-logs/`
- Each day creates a new log file

### Clear Logs

Click the **Clear Logs** button in the Logging tab to remove all log entries.

## 🔒 Security

The plugin follows WordPress security best practices:

- ✅ Nonce verification on all forms
- ✅ Capability checks for admin functions
- ✅ Data sanitization and escaping
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ CSRF protection

## 🌍 Translation

The plugin is translation-ready. To translate:

1. Copy `/languages/vin-decoder-addon-en_US.po`
2. Translate strings using Poedit or similar tool
3. Save as `vin-decoder-addon-{locale}.po` and `.mo`
4. Place in the `/languages/` folder

## 📝 Changelog

### Version 1.0.0
- Initial release
- Support for NHTSA, RapidAPI, and CarMD
- Live AJAX field population
- Flexible field mapping system
- Comprehensive logging
- Translation ready

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 🤝 Support

For support, feature requests, or bug reports:
- Open an issue on GitHub
- Contact via email
- Visit the support forum

## 👨‍💻 Author

Built with WordPress best practices and Envato quality standards.

---

**Note**: This is a WordPress plugin. The Next.js preview you see is for documentation purposes only. Download the plugin files and install them in your WordPress site to use the actual functionality.
