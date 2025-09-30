/**
 * VIN Decoder Admin JavaScript
 *
 * Handles admin page interactivity
 */

;(($) => {
  const vinDecoderAdmin = {
    strings: {
      confirmDelete: "Are you sure you want to delete this mapping?",
    },
  }

  $(document).ready(() => {
    // Field Mapping functionality
    initFieldMapping()

    // API Provider change handler
    initApiProviderHandler()
  })

  /**
   * Initialize field mapping functionality
   */
  function initFieldMapping() {
    let mappingIndex = $("#field-mapping-table tbody tr").length

    // Add new mapping row
    $("#add-mapping").on("click", (e) => {
      e.preventDefault()

      // Remove "no mappings" message if present
      $("#field-mapping-table tbody .no-mappings").remove()

      // Get template and replace index placeholder
      const template = $("#mapping-row-template").html()
      const newRow = template.replace(/__INDEX__/g, mappingIndex)

      // Append new row
      $("#field-mapping-table tbody").append(newRow)

      mappingIndex++
    })

    // Remove mapping row
    $(document).on("click", ".remove-mapping", function (e) {
      e.preventDefault()

      if (confirm(vinDecoderAdmin.strings.confirmDelete)) {
        $(this).closest("tr").remove()

        // Show "no mappings" message if table is empty
        if ($("#field-mapping-table tbody tr").length === 0) {
          $("#field-mapping-table tbody").append(
            '<tr class="no-mappings"><td colspan="4">' + "No field mappings configured yet." + "</td></tr>",
          )
        }
      }
    })

    // Form submission handler
    $("#field-mapping-form").on("submit", () => {
      // Remove empty rows before submission
      $("#field-mapping-table tbody tr").each(function () {
        const formId = $(this).find('select[name*="[form_id]"]').val()
        const vinField = $(this).find('select[name*="[vin_field]"]').val()
        const gfFieldId = $(this).find('input[name*="[gf_field_id]"]').val()

        if (!formId || !vinField || !gfFieldId) {
          $(this).remove()
        }
      })
    })
  }

  /**
   * Initialize API provider change handler
   */
  function initApiProviderHandler() {
    const $apiProvider = $("#api_provider")
    const $apiKeyField = $("#api_key").closest("tr")

    if ($apiProvider.length && $apiKeyField.length) {
      // Initial state
      toggleApiKeyField()

      // On change
      $apiProvider.on("change", toggleApiKeyField)
    }

    function toggleApiKeyField() {
      const provider = $apiProvider.val()

      // NHTSA doesn't require API key
      if (provider === "nhtsa") {
        $apiKeyField.hide()
      } else {
        $apiKeyField.show()
      }
    }
  }
})(window.jQuery)
