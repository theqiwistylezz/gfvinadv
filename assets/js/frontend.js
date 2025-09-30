/**
 * VIN Decoder Frontend JavaScript
 *
 * Handles live VIN decoding and field population
 */

;(($) => {
  // Configuration
  const config = {
    vinLength: 17,
    debounceDelay: 500,
    vinFieldSelector: '.vin-field, input[data-vin-decoder="true"]',
  }

  // State
  let decodeTimeout = null
  let isDecoding = false

  // Declare vinDecoderFrontend variable
  const vinDecoderFrontend = {
    ajaxUrl: "your_ajax_url_here",
    nonce: "your_nonce_here",
    strings: {
      decoding: "Decoding...",
      decodingComplete: "Decoding complete!",
      decodingError: "Decoding error.",
    },
  }

  $(document).ready(() => {
    initVinDecoder()
  })

  /**
   * Initialize VIN decoder functionality
   */
  function initVinDecoder() {
    // Find all Gravity Forms on the page
    $(".gform_wrapper").each(function () {
      const $form = $(this)
      const formId = getFormId($form)

      if (!formId) {
        return
      }

      // Find VIN input field
      const $vinField = findVinField($form)

      if ($vinField.length) {
        setupVinField($vinField, formId)
      }
    })
  }

  /**
   * Get form ID from form wrapper
   */
  function getFormId($form) {
    const formIdMatch = $form.attr("id").match(/gform_wrapper_(\d+)/)
    return formIdMatch ? Number.parseInt(formIdMatch[1]) : null
  }

  /**
   * Find VIN input field in form
   */
  function findVinField($form) {
    // Look for field with data attribute
    let $vinField = $form.find('input[data-vin-decoder="true"]')

    // If not found, look for field with "vin" in name or class
    if (!$vinField.length) {
      $vinField = $form
        .find('input[type="text"]')
        .filter(function () {
          const $input = $(this)
          const name = $input.attr("name") || ""
          const className = $input.attr("class") || ""
          const id = $input.attr("id") || ""

          return (
            name.toLowerCase().includes("vin") ||
            className.toLowerCase().includes("vin") ||
            id.toLowerCase().includes("vin")
          )
        })
        .first()
    }

    return $vinField
  }

  /**
   * Setup VIN field with event handlers
   */
  function setupVinField($vinField, formId) {
    // Add visual indicator
    $vinField.attr("data-vin-decoder-active", "true")

    // Create status indicator
    const $statusIndicator = $('<span class="vin-decoder-status"></span>')
    $vinField.after($statusIndicator)

    // Bind input event
    $vinField.on("input", function () {
      const vin = $(this).val().trim()

      // Clear previous timeout
      if (decodeTimeout) {
        clearTimeout(decodeTimeout)
      }

      // Clear status if VIN is too short
      if (vin.length < config.vinLength) {
        $statusIndicator.removeClass("decoding success error").text("")
        return
      }

      // Validate VIN length
      if (vin.length === config.vinLength) {
        // Debounce the decode request
        decodeTimeout = setTimeout(() => {
          decodeVin(vin, formId, $statusIndicator)
        }, config.debounceDelay)
      }
    })

    // Bind blur event for immediate decode
    $vinField.on("blur", function () {
      const vin = $(this).val().trim()

      if (vin.length === config.vinLength && !isDecoding) {
        decodeVin(vin, formId, $statusIndicator)
      }
    })
  }

  /**
   * Decode VIN via AJAX
   */
  function decodeVin(vin, formId, $statusIndicator) {
    if (isDecoding) {
      return
    }

    isDecoding = true

    // Update status
    $statusIndicator.removeClass("success error").addClass("decoding").text(vinDecoderFrontend.strings.decoding)

    // Make AJAX request
    $.ajax({
      url: vinDecoderFrontend.ajaxUrl,
      type: "POST",
      data: {
        action: "vin_decode_and_autofill",
        nonce: vinDecoderFrontend.nonce,
        vin: vin,
        form_id: formId,
      },
      success: (response) => {
        if (response.success && response.data.fields) {
          // Populate fields
          populateFields(response.data.fields, formId)

          // Update status
          $statusIndicator
            .removeClass("decoding error")
            .addClass("success")
            .text(vinDecoderFrontend.strings.decodingComplete)

          // Clear status after 3 seconds
          setTimeout(() => {
            $statusIndicator.fadeOut(function () {
              $(this).removeClass("success").text("").show()
            })
          }, 3000)
        } else {
          showError($statusIndicator, response.data.message || vinDecoderFrontend.strings.decodingError)
        }
      },
      error: (xhr) => {
        let errorMessage = vinDecoderFrontend.strings.decodingError

        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
          errorMessage = xhr.responseJSON.data.message
        }

        showError($statusIndicator, errorMessage)
      },
      complete: () => {
        isDecoding = false
      },
    })
  }

  /**
   * Populate form fields with decoded data
   */
  function populateFields(fields, formId) {
    $.each(fields, (fieldId, value) => {
      // Find the input field
      const $field = $("#input_" + formId + "_" + fieldId)

      if ($field.length) {
        // Set value
        $field.val(value)

        // Trigger change event for any listeners
        $field.trigger("change")

        // Add visual feedback
        $field.addClass("vin-decoder-populated")
        setTimeout(() => {
          $field.removeClass("vin-decoder-populated")
        }, 2000)
      }
    })
  }

  /**
   * Show error message
   */
  function showError($statusIndicator, message) {
    $statusIndicator.removeClass("decoding success").addClass("error").text(message)

    // Clear error after 5 seconds
    setTimeout(() => {
      $statusIndicator.fadeOut(function () {
        $(this).removeClass("error").text("").show()
      })
    }, 5000)
  }

  // Expose API for external use
  window.vinDecoderAPI = {
    decode: (vin, formId, callback) => {
      $.ajax({
        url: vinDecoderFrontend.ajaxUrl,
        type: "POST",
        data: {
          action: "vin_decode_and_autofill",
          nonce: vinDecoderFrontend.nonce,
          vin: vin,
          form_id: formId,
        },
        success: (response) => {
          if (callback) {
            callback(null, response)
          }
        },
        error: (xhr) => {
          if (callback) {
            callback(xhr, null)
          }
        },
      })
    },
  }
})(window.jQuery)
