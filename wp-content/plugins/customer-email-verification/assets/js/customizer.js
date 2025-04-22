jQuery( document ).ready(function($) {
	"use strict";

	/**
	 * Sortable Repeater Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */

	// Update the values for all our input fields and initialise the sortable repeater
	$('.sortable_repeater_control').each(function() {
		// If there is an existing customizer value, populate our rows
		var defaultValuesArray = $(this).find('.customize-control-sortable-repeater').val().split(',');
		var numRepeaterItems = defaultValuesArray.length;

		if(numRepeaterItems > 0) {
			// Add the first item to our existing input field
			$(this).find('.repeater-input').val(defaultValuesArray[0]);
			// Create a new row for each new value
			if(numRepeaterItems > 1) {
				var i;
				for (i = 1; i < numRepeaterItems; ++i) {
					skyrocketAppendRow($(this), defaultValuesArray[i]);
				}
			}
		}
	});

	// Make our Repeater fields sortable
	$(this).find('.sortable').sortable({
		update: function(event, ui) {
			skyrocketGetAllInputs($(this).parent());
		}
	});

	// Remove item starting from it's parent element
	$('.sortable').on('click', '.customize-control-sortable-repeater-delete', function(event) {
		event.preventDefault();
		var numItems = $(this).parent().parent().find('.repeater').length;

		if(numItems > 1) {
			$(this).parent().slideUp('fast', function() {
				var parentContainer = $(this).parent().parent();
				$(this).remove();
				skyrocketGetAllInputs(parentContainer);
			})
		}
		else {
			$(this).parent().find('.repeater-input').val('');
			skyrocketGetAllInputs($(this).parent().parent().parent());
		}
	});

	// Add new item
	$('.customize-control-sortable-repeater-add').click(function(event) {
		event.preventDefault();
		skyrocketAppendRow($(this).parent());
		skyrocketGetAllInputs($(this).parent());
	});

	// Refresh our hidden field if any fields change
	$('.sortable').change(function() {
		skyrocketGetAllInputs($(this).parent());
	})

	// Add https:// to the start of the URL if it doesn't have it
	$('.sortable').on('blur', '.repeater-input', function() {
		var url = $(this);
		var val = url.val();
		if(val && !val.match(/^.+:\/\/.*/)) {
			// Important! Make sure to trigger change event so Customizer knows it has to save the field
			url.val('https://' + val).trigger('change');
		}
	});

	// Append a new row to our list of elements
	function skyrocketAppendRow($element, defaultValue = '') {
		var newRow = '<div class="repeater" style="display:none"><input type="text" value="' + defaultValue + '" class="repeater-input" placeholder="https://" /><span class="dashicons dashicons-sort"></span><a class="customize-control-sortable-repeater-delete" href="#"><span class="dashicons dashicons-no-alt"></span></a></div>';

		$element.find('.sortable').append(newRow);
		$element.find('.sortable').find('.repeater:last').slideDown('slow', function(){
			$(this).find('input').focus();
		});
	}

	// Get the values from the repeater input fields and add to our hidden field
	function skyrocketGetAllInputs($element) {
		var inputValues = $element.find('.repeater-input').map(function() {
			return $(this).val();
		}).toArray();
		// Add all the values from our repeater fields to the hidden field (which is the one that actually gets saved)
		$element.find('.customize-control-sortable-repeater').val(inputValues);
		// Important! Make sure to trigger change event so Customizer knows it has to save the field
		$element.find('.customize-control-sortable-repeater').trigger('change');
	}

	/**
	 * Slider Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */

	// Set our slider defaults and initialise the slider
	$('.slider-custom-control').each(function(){
		var sliderValue = $(this).find('.customize-control-slider-value').val();
		var newSlider = $(this).find('.slider');
		var sliderMinValue = parseFloat(newSlider.attr('slider-min-value'));
		var sliderMaxValue = parseFloat(newSlider.attr('slider-max-value'));
		var sliderStepValue = parseFloat(newSlider.attr('slider-step-value'));

		newSlider.slider({
			value: sliderValue,
			min: sliderMinValue,
			max: sliderMaxValue,
			step: sliderStepValue,
			change: function(e,ui){
				// Important! When slider stops moving make sure to trigger change event so Customizer knows it has to save the field
				$(this).parent().find('.customize-control-slider-value').trigger('change');
	      }
		});
	});

	// Change the value of the input field as the slider is moved
	$('.slider').on('slide', function(event, ui) {
		$(this).parent().find('.customize-control-slider-value').val(ui.value);
	});

	// Reset slider and input field back to the default value
	$('.slider-reset').on('click', function() {
		var resetValue = $(this).attr('slider-reset-value');
		$(this).parent().find('.customize-control-slider-value').val(resetValue);
		$(this).parent().find('.slider').slider('value', resetValue);
	});

	// Update slider if the input field loses focus as it's most likely changed
	$('.customize-control-slider-value').blur(function() {
		var resetValue = $(this).val();
		var slider = $(this).parent().find('.slider');
		var sliderMinValue = parseInt(slider.attr('slider-min-value'));
		var sliderMaxValue = parseInt(slider.attr('slider-max-value'));

		// Make sure our manual input value doesn't exceed the minimum & maxmium values
		if(resetValue < sliderMinValue) {
			resetValue = sliderMinValue;
			$(this).val(resetValue);
		}
		if(resetValue > sliderMaxValue) {
			resetValue = sliderMaxValue;
			$(this).val(resetValue);
		}
		$(this).parent().find('.slider').slider('value', resetValue);
	});

	/**
	 * Single Accordion Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */

	$('.single-accordion-toggle').click(function() {
		var $accordionToggle = $(this);
		$(this).parent().find('.single-accordion').slideToggle('slow', function() {
			$accordionToggle.toggleClass('single-accordion-toggle-rotate', $(this).is(':visible'));
		});
	});

	/**
	 * Image Check Box Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */

	$('.multi-image-checkbox').on('change', function () {
	  getAllCheckboxes($(this).parent().parent());
	});

	// Get the values from the checkboxes and add to our hidden field
	function getAllCheckboxes($element) {
	  var inputValues = $element.find('.multi-image-checkbox').map(function() {
	    if( $(this).is(':checked') ) {
	      return $(this).val();
	 //   } else {
	 //     return '';
	    }
	  }).toArray();
	  // Important! Make sure to trigger change event so Customizer knows it has to save the field
	  $element.find('.customize-control-multi-image-checkbox').val(inputValues).trigger('change');
	}

	/**
	 * Dropdown Select2 Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */

	$('.customize-control-dropdown-select2').each(function(){
		$('.customize-control-select2').select2({
			allowClear: true
		});
	});

	$(".customize-control-select2").on("change", function() {
		var select2Val = $(this).val();
		$(this).parent().find('.customize-control-dropdown-select2').val(select2Val).trigger('change');
	});

	/**
	 * Googe Font Select Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */

	$('.google-fonts-list').each(function (i, obj) {
		if (!$(obj).hasClass('select2-hidden-accessible')) {
			$(obj).select2();
		}
	});

	$('.google-fonts-list').on('change', function() {
		var elementRegularWeight = $(this).parent().parent().find('.google-fonts-regularweight-style');
		var elementItalicWeight = $(this).parent().parent().find('.google-fonts-italicweight-style');
		var elementBoldWeight = $(this).parent().parent().find('.google-fonts-boldweight-style');
		var selectedFont = $(this).val();
		var customizerControlName = $(this).attr('control-name');
		var elementItalicWeightCount = 0;
		var elementBoldWeightCount = 0;

		// Clear Weight/Style dropdowns
		elementRegularWeight.empty();
		elementItalicWeight.empty();
		elementBoldWeight.empty();
		// Make sure Italic & Bold dropdowns are enabled
		elementItalicWeight.prop('disabled', false);
		elementBoldWeight.prop('disabled', false);

		// Get the Google Fonts control object
		var bodyfontcontrol = _wpCustomizeSettings.controls[customizerControlName];

		// Find the index of the selected font
		var indexes = $.map(bodyfontcontrol.skyrocketfontslist, function(obj, index) {
			if(obj.family === selectedFont) {
				return index;
			}
		});
		var index = indexes[0];

		// For the selected Google font show the available weight/style variants
		$.each(bodyfontcontrol.skyrocketfontslist[index].variants, function(val, text) {
			elementRegularWeight.append(
				$('<option></option>').val(text).html(text)
			);
			if (text.indexOf("italic") >= 0) {
				elementItalicWeight.append(
					$('<option></option>').val(text).html(text)
				);
				elementItalicWeightCount++;
			} else {
				elementBoldWeight.append(
					$('<option></option>').val(text).html(text)
				);
				elementBoldWeightCount++;
			}
		});

		if(elementItalicWeightCount == 0) {
			elementItalicWeight.append(
				$('<option></option>').val('').html('Not Available for this font')
			);
			elementItalicWeight.prop('disabled', 'disabled');
		}
		if(elementBoldWeightCount == 0) {
			elementBoldWeight.append(
				$('<option></option>').val('').html('Not Available for this font')
			);
			elementBoldWeight.prop('disabled', 'disabled');
		}

		// Update the font category based on the selected font
		$(this).parent().parent().find('.google-fonts-category').val(bodyfontcontrol.skyrocketfontslist[index].category);

		skyrocketGetAllSelects($(this).parent().parent());
	});

	$('.google_fonts_select_control select').on('change', function() {
		skyrocketGetAllSelects($(this).parent().parent());
	});

	function skyrocketGetAllSelects($element) {
		var selectedFont = {
			font: $element.find('.google-fonts-list').val(),
			regularweight: $element.find('.google-fonts-regularweight-style').val(),
			italicweight: $element.find('.google-fonts-italicweight-style').val(),
			boldweight: $element.find('.google-fonts-boldweight-style').val(),
			category: $element.find('.google-fonts-category').val()
		};

		// Important! Make sure to trigger change event so Customizer knows it has to save the field
		$element.find('.customize-control-google-font-selection').val(JSON.stringify(selectedFont)).trigger('change');
	}

	/**
	 * TinyMCE Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */

	$('.customize-control-tinymce-editor').each(function(){
		// Get the toolbar strings that were passed from the PHP Class
		var tinyMCEToolbar1String = _wpCustomizeSettings.controls[$(this).attr('id')].skyrockettinymcetoolbar1;
		var tinyMCEToolbar2String = _wpCustomizeSettings.controls[$(this).attr('id')].skyrockettinymcetoolbar2;
		var tinyMCEMediaButtons = _wpCustomizeSettings.controls[$(this).attr('id')].skyrocketmediabuttons;
		
		wp.editor.initialize( $(this).attr('id'), {
			
			tinymce: {
				wpautop: true,
				toolbar1: tinyMCEToolbar1String,
				toolbar2: tinyMCEToolbar2String
			},
			quicktags: true,
			mediaButtons: tinyMCEMediaButtons
		});
	});
	$(document).on( 'tinymce-editor-init', function( event, editor ) {
		editor.on('change', function(e) {
			tinyMCE.triggerSave();
			$('#'+editor.id).trigger('change');
		});
	});		

});


/*
 * Script run inside a Customizer control sidebar
 *
 * Enable / disable the control title by toggeling its .disabled-control-title style class on or off.
 */
( function( $ ) {
	wp.customize.bind( 'ready', function() { // Ready?

		var customize = this; // Customize object alias.

		//get the toggle controls
		var toggleControls = $('.customize-toogle-label').parent();

		var toggleControlIds = [];

		//Segment in the id of the control that is added by wordpress, but not needed for our purpose
		var idSegment = "customize-control-";

		//fill the id array
		for (var control of toggleControls){
			//remove the segment from the control id
			var controlId = control.id.substring(idSegment.length, control.id.length);
			toggleControlIds.push(controlId);
		}

		$.each( toggleControlIds, function( index, control_name ) {
			customize( control_name, function( value ) {
				var controlTitle = customize.control( control_name ).container.find( '.customize-control-title' ); // Get control  title.
				// 1. On loading.
				controlTitle.toggleClass('disabled-control-title', !value.get() );
				// 2. Binding to value change.
				value.bind( function( to ) {
					controlTitle.toggleClass( 'disabled-control-title', !value.get() );
				} );
			} );
		} );
	} );
} )( jQuery );
