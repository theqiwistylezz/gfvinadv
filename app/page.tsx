export default function Page() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-8">
      <div className="max-w-5xl mx-auto">
        {/* Header */}
        <div className="bg-white rounded-lg shadow-lg p-8 mb-8">
          <div className="flex items-center gap-4 mb-4">
            <div className="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center text-white text-2xl font-bold">
              VIN
            </div>
            <div>
              <h1 className="text-3xl font-bold text-slate-900">Gravity Forms VIN Decoder Add-On</h1>
              <p className="text-slate-600">Production-grade WordPress plugin for automatic VIN decoding</p>
            </div>
          </div>
          <div className="flex gap-2 mt-4">
            <span className="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">v1.0.0</span>
            <span className="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
              WordPress Plugin
            </span>
            <span className="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
              Envato Ready
            </span>
          </div>
        </div>

        {/* Overview */}
        <div className="bg-white rounded-lg shadow-lg p-8 mb-8">
          <h2 className="text-2xl font-bold text-slate-900 mb-4">Overview</h2>
          <p className="text-slate-700 leading-relaxed mb-4">
            This is a complete WordPress plugin that integrates with Gravity Forms to provide automatic Vehicle
            Identification Number (VIN) decoding and field population. The plugin is built following WordPress and
            Envato coding standards, ready for production use.
          </p>
          <div className="grid md:grid-cols-2 gap-4 mt-6">
            <div className="border border-slate-200 rounded-lg p-4">
              <h3 className="font-semibold text-slate-900 mb-2">Core Features</h3>
              <ul className="space-y-2 text-sm text-slate-700">
                <li className="flex items-start gap-2">
                  <span className="text-green-600 mt-0.5">✓</span>
                  <span>Multiple API providers (NHTSA, RapidAPI, CarMD)</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-green-600 mt-0.5">✓</span>
                  <span>Live AJAX-powered VIN decoding</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-green-600 mt-0.5">✓</span>
                  <span>Flexible field mapping system</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-green-600 mt-0.5">✓</span>
                  <span>Comprehensive logging and debugging</span>
                </li>
              </ul>
            </div>
            <div className="border border-slate-200 rounded-lg p-4">
              <h3 className="font-semibold text-slate-900 mb-2">Technical Stack</h3>
              <ul className="space-y-2 text-sm text-slate-700">
                <li className="flex items-start gap-2">
                  <span className="text-blue-600 mt-0.5">•</span>
                  <span>Pure PHP 8.2+ backend</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-blue-600 mt-0.5">•</span>
                  <span>Vanilla JavaScript for AJAX</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-blue-600 mt-0.5">•</span>
                  <span>WordPress 6.6+ compatible</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-blue-600 mt-0.5">•</span>
                  <span>Translation ready (i18n)</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        {/* File Structure */}
        <div className="bg-white rounded-lg shadow-lg p-8 mb-8">
          <h2 className="text-2xl font-bold text-slate-900 mb-4">Plugin Structure</h2>
          <div className="bg-slate-900 text-slate-100 rounded-lg p-6 font-mono text-sm overflow-x-auto">
            <pre>{`/vin-decoder-addon/
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
    └── vin-decoder-addon-en_US.po     # Translation template`}</pre>
          </div>
        </div>

        {/* Installation */}
        <div className="bg-white rounded-lg shadow-lg p-8 mb-8">
          <h2 className="text-2xl font-bold text-slate-900 mb-4">Installation</h2>
          <div className="space-y-4">
            <div className="border-l-4 border-blue-600 pl-4">
              <h3 className="font-semibold text-slate-900 mb-2">Step 1: Download</h3>
              <p className="text-slate-700 text-sm">
                Click the three dots in the top right and select "Download ZIP" to get all plugin files.
              </p>
            </div>
            <div className="border-l-4 border-blue-600 pl-4">
              <h3 className="font-semibold text-slate-900 mb-2">Step 2: Upload to WordPress</h3>
              <p className="text-slate-700 text-sm">
                Upload the plugin folder to{" "}
                <code className="bg-slate-100 px-2 py-1 rounded text-xs">/wp-content/plugins/</code>
              </p>
            </div>
            <div className="border-l-4 border-blue-600 pl-4">
              <h3 className="font-semibold text-slate-900 mb-2">Step 3: Activate</h3>
              <p className="text-slate-700 text-sm">Activate the plugin through the WordPress Plugins menu</p>
            </div>
            <div className="border-l-4 border-blue-600 pl-4">
              <h3 className="font-semibold text-slate-900 mb-2">Step 4: Configure</h3>
              <p className="text-slate-700 text-sm">
                Go to Settings → VIN Decoder to configure API provider and field mappings
              </p>
            </div>
          </div>
        </div>

        {/* Usage */}
        <div className="bg-white rounded-lg shadow-lg p-8 mb-8">
          <h2 className="text-2xl font-bold text-slate-900 mb-4">How to Use</h2>
          <div className="space-y-6">
            <div>
              <h3 className="font-semibold text-slate-900 mb-2 flex items-center gap-2">
                <span className="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm">
                  1
                </span>
                Configure API Provider
              </h3>
              <p className="text-slate-700 text-sm ml-8">
                Choose between NHTSA (free), RapidAPI, or CarMD. Enter your API key if using a premium provider.
              </p>
            </div>
            <div>
              <h3 className="font-semibold text-slate-900 mb-2 flex items-center gap-2">
                <span className="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm">
                  2
                </span>
                Map Form Fields
              </h3>
              <p className="text-slate-700 text-sm ml-8">
                In the Field Mapping tab, select your Gravity Form, choose VIN data fields (Make, Model, Year, etc.),
                and map them to your form field IDs.
              </p>
            </div>
            <div>
              <h3 className="font-semibold text-slate-900 mb-2 flex items-center gap-2">
                <span className="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm">
                  3
                </span>
                Add VIN Field to Form
              </h3>
              <p className="text-slate-700 text-sm ml-8">
                In Gravity Forms editor, add a text field for VIN input. Enable "VIN Decoder" in the field settings.
              </p>
            </div>
            <div>
              <h3 className="font-semibold text-slate-900 mb-2 flex items-center gap-2">
                <span className="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm">
                  4
                </span>
                Test It Out
              </h3>
              <p className="text-slate-700 text-sm ml-8">
                When users enter a 17-character VIN, the plugin automatically decodes it and populates the mapped
                fields!
              </p>
            </div>
          </div>
        </div>

        {/* API Providers */}
        <div className="bg-white rounded-lg shadow-lg p-8 mb-8">
          <h2 className="text-2xl font-bold text-slate-900 mb-4">Supported API Providers</h2>
          <div className="grid md:grid-cols-3 gap-4">
            <div className="border border-slate-200 rounded-lg p-4">
              <h3 className="font-semibold text-slate-900 mb-2">NHTSA</h3>
              <span className="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium mb-2">
                FREE
              </span>
              <p className="text-sm text-slate-700">
                National Highway Traffic Safety Administration. No API key required.
              </p>
            </div>
            <div className="border border-slate-200 rounded-lg p-4">
              <h3 className="font-semibold text-slate-900 mb-2">RapidAPI</h3>
              <span className="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium mb-2">
                PREMIUM
              </span>
              <p className="text-sm text-slate-700">Comprehensive VIN data with enhanced details. Requires API key.</p>
            </div>
            <div className="border border-slate-200 rounded-lg p-4">
              <h3 className="font-semibold text-slate-900 mb-2">CarMD</h3>
              <span className="inline-block px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium mb-2">
                PREMIUM
              </span>
              <p className="text-sm text-slate-700">Detailed vehicle information and diagnostics. Requires API key.</p>
            </div>
          </div>
        </div>

        {/* Requirements */}
        <div className="bg-white rounded-lg shadow-lg p-8">
          <h2 className="text-2xl font-bold text-slate-900 mb-4">Requirements</h2>
          <div className="grid md:grid-cols-2 gap-6">
            <div>
              <h3 className="font-semibold text-slate-900 mb-3">Server Requirements</h3>
              <ul className="space-y-2 text-sm text-slate-700">
                <li className="flex items-center gap-2">
                  <span className="w-2 h-2 bg-slate-400 rounded-full"></span>
                  WordPress 6.6 or higher
                </li>
                <li className="flex items-center gap-2">
                  <span className="w-2 h-2 bg-slate-400 rounded-full"></span>
                  PHP 8.2 or higher
                </li>
                <li className="flex items-center gap-2">
                  <span className="w-2 h-2 bg-slate-400 rounded-full"></span>
                  Gravity Forms plugin (latest version)
                </li>
              </ul>
            </div>
            <div>
              <h3 className="font-semibold text-slate-900 mb-3">Optional</h3>
              <ul className="space-y-2 text-sm text-slate-700">
                <li className="flex items-center gap-2">
                  <span className="w-2 h-2 bg-slate-400 rounded-full"></span>
                  API key for premium providers
                </li>
                <li className="flex items-center gap-2">
                  <span className="w-2 h-2 bg-slate-400 rounded-full"></span>
                  Envato purchase code for license verification
                </li>
              </ul>
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="mt-8 text-center text-slate-600 text-sm">
          <p>Built with WordPress best practices and Envato quality standards</p>
          <p className="mt-2">Ready for production use and CodeCanyon marketplace</p>
        </div>
      </div>
    </div>
  )
}
