=== Gravity Forms VIN Decoder Add-On ===
Contributors: yourname
Tags: gravity forms, vin decoder, vehicle identification, auto-fill, forms
Requires at least: 6.6
Tested up to: 6.7
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically decode VIN numbers and populate Gravity Forms fields with vehicle information.

== Description ==

The Gravity Forms VIN Decoder Add-On seamlessly integrates with Gravity Forms to provide automatic Vehicle Identification Number (VIN) decoding and field population.

= Features =

* **Multiple API Providers**: Support for NHTSA (free), RapidAPI, and CarMD
* **Live AJAX Decoding**: Instant field population as users type
* **Flexible Field Mapping**: Map any VIN data field to any Gravity Forms field
* **Comprehensive Logging**: Track API calls and errors for debugging
* **Translation Ready**: Fully internationalized and ready for translation
* **Secure & Optimized**: Follows WordPress and Envato coding standards
* **Extensible Architecture**: Built for future integrations and features

= Supported VIN Data Fields =

* Make, Model, Year
* Trim, Engine, Transmission
* Vehicle Type, Body Class
* Manufacturer Information
* Drive Type, Fuel Type
* And many more...

= Requirements =

* WordPress 6.6 or higher
* PHP 8.2 or higher
* Gravity Forms (latest version recommended)
* API key for premium providers (optional)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/vin-decoder-addon/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > VIN Decoder to configure
4. Select your API provider and enter API key (if required)
5. Map VIN data fields to your Gravity Forms fields
6. Add a VIN field to your form and test!

== Frequently Asked Questions ==

= Do I need an API key? =

The NHTSA provider is completely free and doesn't require an API key. Premium providers like RapidAPI and CarMD require API keys for enhanced data.

= How do I map fields? =

Go to Settings > VIN Decoder > Field Mapping tab. Select your form, choose the VIN data field, and map it to the corresponding Gravity Forms field ID.

= Does it work with AJAX forms? =

Yes! The plugin fully supports AJAX-enabled Gravity Forms.

= Can I customize the decoded data? =

Yes, developers can use the provided filters and hooks to customize the data processing and field population.

== Screenshots ==

1. General settings page with API provider selection
2. Field mapping interface
3. Live VIN decoding in action
4. Logging dashboard

== Changelog ==

= 1.0.0 =
* Initial release
* Support for NHTSA, RapidAPI, and CarMD
* Live AJAX field population
* Flexible field mapping system
* Comprehensive logging

== Upgrade Notice ==

= 1.0.0 =
Initial release of the Gravity Forms VIN Decoder Add-On.

== Developer Notes ==

= Hooks & Filters =

`vin_decoder_api_response` - Filter the API response before processing
`vin_decoder_formatted_data` - Filter the formatted VIN data
`vin_decoder_field_value` - Filter individual field values before population

= Code Example =

```php
// Customize VIN data before field population
add_filter('vin_decoder_formatted_data', function($data, $vin) {
    // Add custom processing
    $data['custom_field'] = 'Custom Value';
    return $data;
}, 10, 2);
