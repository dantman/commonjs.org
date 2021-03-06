/*
* ext.sf.select2.base.js
*
* Base class to handle autocomplete
* for various input types using Select2 JS library
*
* @file
*
*
* @licence GNU GPL v2+
* @author Jatin Mehta
*
*/

( function ( $, mw, sf ) {
	'use strict';
	/**
	 * Inheritance class for the sf.select2 constructor
	 *
	 *
	 * @class
	 */
	sf.select2 = sf.select2 || {};

	/**
	 * Class constructor
	 *
	 *
	 * @class
	 * @constructor
	 */
	sf.select2.base = function() {

	};

	sf.select2.base.prototype = {
		/*
		 * Applies select2 to the HTML element
		 *
		 * @param {HTMLElement} element
		 *
		 */
		apply: function( element ) {
			this.id = element.attr( "id" );
			var opts = this.setOptions();

			element.select2(opts);
			element.on( "change", this.onChange );
		},
		/*
		 * Used to remove the select2 applied to the HTML element,
		 * the selected value will remain preserved.
		 *
		 * @param {HTMLElement} element
		 *
		 */
		destroy: function( element ) {
			element.select2( "destroy" );
		},
		/*
		 * Returns HTML text to be used by select2 for
		 * showing remote data retrieved
		 *
		 * @param {object} value
		 * @param {object} container
		 * @param {object} query
		 *
		 * @return {string} markup
		 *
		 */
		formatResult: function(value, container, query) {
			var term = query.term;
			var text = value.text;
			var image = value.image;
			var description = value.description;
			var markup = "";

			var text_highlight = sf.select2.base.prototype.textHighlight;
			if ( text !== undefined && image !== undefined && description !== undefined ) {
				markup += "<table class='sf-select2-result'> <tr>";
				markup += "<td class='sf-result-thumbnail'><img src='" + image + "'/></td>";
				markup += "<td class='sf-result-info'><div class='sf-result-title'>" + text_highlight(text, term) + "</div>";
				markup += "<div class='sf-result-description'>" + description + "</div>";
				markup += "</td></tr></table>";
			} else if ( text !== undefined && image !== undefined ) {
				markup += "<img class='sf-icon' src='"+ image +"'/>" + text_highlight(text, term);
			} else if ( text !== undefined && description !== undefined ) {
				markup += "<table class='sf-select2-result'> <tr>";
				markup += "<td class='sf-result-info'><div class='sf-result-title'>" + text_highlight(text, term) + "</div>";
				markup += "<div class='sf-result-description'>" + description + "</div>";
				markup += "</td></tr></table>";
			} else {
				markup += text_highlight(text, term);
			}

			return markup;
		},
		/*
		 * Returns string/HTML text to be used by select2 to
		 * show selection
		 *
		 * @param {object} value (The selected result object)
		 *
		 * @return {string}
		 *
		 */
		formatSelection: function(value) {
			return value.text;
		},
	 	/*
		 * If a field is dependent on some other field in the form
		 * then it returns its name.
		 *
		 * @return {string}
		 *
		 */
		dependentOn: function() {
			var input_id = "#" + this.id;
			var name_attr = this.nameAttr( $(input_id) );
			var name = $(input_id).attr( name_attr );

			var sfgDependentFields = mw.config.get( 'sfgDependentFields' );
			for ( var i = 0; i < sfgDependentFields.length; i++ ) {
				var dependentFieldPair = sfgDependentFields[i];
				if ( dependentFieldPair[1] == name ) {
					 return dependentFieldPair[0];
				}
			}
			return null;
		},
		/*
		 * Returns the array of names of fields in the form which are dependent
		 * on the field passed as a param to this function,
		 *
		 * @param {HTMLElement} element
		 *
		 * @return {associative array} dependent_on_me
		 *
		 */
		dependentOnMe: function( element ) {
			var name_attr = this.nameAttr(element);
			var name = element.attr( name_attr );
			var dependent_on_me = [];

			var sfgDependentFields = mw.config.get( 'sfgDependentFields' );
			for ( var i = 0; i < sfgDependentFields.length; i++ ) {
				var dependentFieldPair = sfgDependentFields[i];
				if ( dependentFieldPair[0] == name ) {
					dependent_on_me.push(dependentFieldPair[1]);
				}
			}

			return dependent_on_me;
		},
		/*
		 * Returns the name attribute of the field depending on
		 * whether it is a part of multiple instance template or not
		 *
		 * @param {HTMLElement} element
		 *
		 * @return {string}
		 *
		 */
		nameAttr: function( element ) {
			return  this.partOfMultiple( element ) ? "origname" : "name";
		},
		/*
		 * Checks whether the field is part of multiple instance template or not
		 *
		 * @param {HTMLElement} element
		 *
		 * @return {boolean}
		 *
		 */
		partOfMultiple: function( element ) {
			return element.attr( "origname" ) !== undefined ? true : false;
		},
		/*
		 * Gives dependent field options which include
		 * property, base property and base value
		 *
		 * @param {string} dep_on
		 *
		 * @return {object} dep_field_opts
		 *
		 */
		getDependentFieldOpts: function( dep_on ) {
			var input_id = "#" + this.id;
			var dep_field_opts = {};
      		var base_element;
			if ( this.partOfMultiple($(input_id)) ) {
				base_element = $(input_id).closest( ".multipleTemplateInstance" )
								.find( '[origname ="' + dep_on + '" ]' );
			} else {
				base_element = $('[name ="' + dep_on + '" ]');
			}
			dep_field_opts.base_value = base_element.val();
			dep_field_opts.base_prop = base_element.attr( "autocompletesettings" );
			dep_field_opts.prop = $(input_id).attr( "autocompletesettings" ).split( "," )[0];

			return dep_field_opts;
		},
		/*
		 * Gives autocomplete options for a field
		 *
		 *
		 * @return {object} autocomplete_opts
		 *
		 */
		getAutocompleteOpts: function() {
			var input_id = "#" + this.id;
			var autocomplete_opts = {};
			autocomplete_opts.autocompletedatatype = $(input_id).attr( "autocompletedatatype" );
			autocomplete_opts.autocompletesettings = $(input_id).attr( "autocompletesettings" );

			return autocomplete_opts;
		},
		/*
		 * Refreshes the field if there is a change
		 * in the autocomplete vlaues
		 *
		 * @param {HTMLElement} element
		 *
		 */
		refresh: function( element ) {
			this.destroy($(element));
			this.apply($(element));
		},
		/*
		 * Removes diacritics from the string and replaces
		 * them with english characters.
		 * This code is basically copied from:
		 * http://jsfiddle.net/potherca/Gtmr2/
		 *
		 * @param {string} text
		 *
		 * @return {string}
		 *
		 */
		removeDiacritics: function( text ) {
			var diacriticsMap = { '??': 'A', '??': 'A', '???': 'A', '???': 'A', '???': 'A', '???': 'A', '???': 'A', '??': 'A', '??': 'A', '???': 'A', '???': 'A', '???': 'A', '???': 'A', '???': 'A', '??': 'A', '??': 'A', '??': 'A', '??': 'A', '???': 'A', '??': 'A', '??': 'A', '???': 'A', '??': 'A', '??': 'A', '??': 'A', '??': 'A', '??': 'A', '???': 'A', '??': 'A', '??': 'A', '???': 'AA', '??': 'AE', '??': 'AE', '??': 'AE', '???': 'AO', '???': 'AU', '???': 'AV', '???': 'AV', '???': 'AY', '???': 'B', '???': 'B', '??': 'B', '???': 'B', '??': 'B', '??': 'B', '??': 'C', '??': 'C', '??': 'C', '???': 'C', '??': 'C', '??': 'C', '??': 'C', '??': 'C', '??': 'D', '???': 'D', '???': 'D', '???': 'D', '???': 'D', '??': 'D', '???': 'D', '??': 'D', '??': 'D', '??': 'D', '??': 'D', '??': 'DZ', '??': 'DZ', '??': 'E', '??': 'E', '??': 'E', '??': 'E', '???': 'E', '??': 'E', '???': 'E', '???': 'E', '???': 'E', '???': 'E', '???': 'E', '???': 'E', '??': 'E', '??': 'E', '???': 'E', '??': 'E', '??': 'E', '???': 'E', '??': 'E', '??': 'E', '???': 'E', '???': 'E', '??': 'E', '??': 'E', '???': 'E', '???': 'E', '???': 'ET', '???': 'F', '??': 'F', '??': 'G', '??': 'G', '??': 'G', '??': 'G', '??': 'G', '??': 'G', '??': 'G', '???': 'G', '??': 'G', '???': 'H', '??': 'H', '???': 'H', '??': 'H', '???': 'H', '???': 'H', '???': 'H', '???': 'H', '??': 'H', '??': 'I', '??': 'I', '??': 'I', '??': 'I', '??': 'I', '???': 'I', '??': 'I', '???': 'I', '??': 'I', '??': 'I', '???': 'I', '??': 'I', '??': 'I', '??': 'I', '??': 'I', '??': 'I', '???': 'I', '???': 'D', '???': 'F', '???': 'G', '???': 'R', '???': 'S', '???': 'T', '???': 'IS', '??': 'J', '??': 'J', '???': 'K', '??': 'K', '??': 'K', '???': 'K', '???': 'K', '???': 'K', '??': 'K', '???': 'K', '???': 'K', '???': 'K', '??': 'L', '??': 'L', '??': 'L', '??': 'L', '???': 'L', '???': 'L', '???': 'L', '???': 'L', '???': 'L', '???': 'L', '??': 'L', '???': 'L', '??': 'L', '??': 'L', '??': 'LJ', '???': 'M', '???': 'M', '???': 'M', '???': 'M', '??': 'N', '??': 'N', '??': 'N', '???': 'N', '???': 'N', '???': 'N', '??': 'N', '??': 'N', '???': 'N', '??': 'N', '??': 'N', '??': 'N', '??': 'NJ', '??': 'O', '??': 'O', '??': 'O', '??': 'O', '???': 'O', '???': 'O', '???': 'O', '???': 'O', '???': 'O', '??': 'O', '??': 'O', '??': 'O', '??': 'O', '???': 'O', '??': 'O', '??': 'O', '??': 'O', '???': 'O', '??': 'O', '???': 'O', '???': 'O', '???': 'O', '???': 'O', '???': 'O', '??': 'O', '???': 'O', '???': 'O', '??': 'O', '???': 'O', '???': 'O', '??': 'O', '??': 'O', '??': 'O', '??': 'O', '??': 'O', '??': 'O', '???': 'O', '???': 'O', '??': 'O', '??': 'OI', '???': 'OO', '??': 'E', '??': 'O', '??': 'OU', '???': 'P', '???': 'P', '???': 'P', '??': 'P', '???': 'P', '???': 'P', '???': 'P', '???': 'Q', '???': 'Q', '??': 'R', '??': 'R', '??': 'R', '???': 'R', '???': 'R', '???': 'R', '??': 'R', '??': 'R', '???': 'R', '??': 'R', '???': 'R', '???': 'C', '??': 'E', '??': 'S', '???': 'S', '??': 'S', '???': 'S', '??': 'S', '??': 'S', '??': 'S', '???': 'S', '???': 'S', '???': 'S', '???': 'SS', '??': 'T', '??': 'T', '???': 'T', '??': 'T', '??': 'T', '???': 'T', '???': 'T', '??': 'T', '???': 'T', '??': 'T', '??': 'T', '???': 'A', '???': 'L', '??': 'M', '??': 'V', '???': 'TZ', '??': 'U', '??': 'U', '??': 'U', '??': 'U', '???': 'U', '??': 'U', '??': 'U', '??': 'U', '??': 'U', '??': 'U', '???': 'U', '???': 'U', '??': 'U', '??': 'U', '??': 'U', '???': 'U', '??': 'U', '???': 'U', '???': 'U', '???': 'U', '???': 'U', '???': 'U', '??': 'U', '??': 'U', '???': 'U', '??': 'U', '??': 'U', '??': 'U', '???': 'U', '???': 'U', '???': 'V', '???': 'V', '??': 'V', '???': 'V', '???': 'VY', '???': 'W', '??': 'W', '???': 'W', '???': 'W', '???': 'W', '???': 'W', '???': 'W', '???': 'X', '???': 'X', '??': 'Y', '??': 'Y', '??': 'Y', '???': 'Y', '???': 'Y', '???': 'Y', '??': 'Y', '???': 'Y', '???': 'Y', '??': 'Y', '??': 'Y', '???': 'Y', '??': 'Z', '??': 'Z', '???': 'Z', '???': 'Z', '??': 'Z', '???': 'Z', '??': 'Z', '???': 'Z', '??': 'Z', '??': 'IJ', '??': 'OE', '???': 'A', '???': 'AE', '??': 'B', '???': 'B', '???': 'C', '???': 'D', '???': 'E', '???': 'F', '??': 'G', '??': 'G', '??': 'H', '??': 'I', '??': 'R', '???': 'J', '???': 'K', '??': 'L', '???': 'L', '???': 'M', '??': 'N', '???': 'O', '??': 'OE', '???': 'O', '???': 'OU', '???': 'P', '??': 'R', '???': 'N', '???': 'R', '???': 'S', '???': 'T', '???': 'E', '???': 'R', '???': 'U', '???': 'V', '???': 'W', '??': 'Y', '???': 'Z', '??': 'a', '??': 'a', '???': 'a', '???': 'a', '???': 'a', '???': 'a', '???': 'a', '??': 'a', '??': 'a', '???': 'a', '???': 'a', '???': 'a', '???': 'a', '???': 'a', '??': 'a', '??': 'a', '??': 'a', '??': 'a', '???': 'a', '??': 'a', '??': 'a', '???': 'a', '??': 'a', '??': 'a', '??': 'a', '???': 'a', '???': 'a', '??': 'a', '??': 'a', '???': 'a', '???': 'a', '??': 'a', '???': 'aa', '??': 'ae', '??': 'ae', '??': 'ae', '???': 'ao', '???': 'au', '???': 'av', '???': 'av', '???': 'ay', '???': 'b', '???': 'b', '??': 'b', '???': 'b', '???': 'b', '???': 'b', '??': 'b', '??': 'b', '??': 'o', '??': 'c', '??': 'c', '??': 'c', '???': 'c', '??': 'c', '??': 'c', '??': 'c', '??': 'c', '??': 'c', '??': 'd', '???': 'd', '???': 'd', '??': 'd', '???': 'd', '???': 'd', '??': 'd', '???': 'd', '???': 'd', '???': 'd', '???': 'd', '??': 'd', '??': 'd', '??': 'd', '??': 'i', '??': 'j', '??': 'j', '??': 'j', '??': 'dz', '??': 'dz', '??': 'e', '??': 'e', '??': 'e', '??': 'e', '???': 'e', '??': 'e', '???': 'e', '???': 'e', '???': 'e', '???': 'e', '???': 'e', '???': 'e', '??': 'e', '??': 'e', '???': 'e', '??': 'e', '??': 'e', '???': 'e', '??': 'e', '??': 'e', '???': 'e', '???': 'e', '???': 'e', '??': 'e', '???': 'e', '??': 'e', '???': 'e', '???': 'e', '???': 'et', '???': 'f', '??': 'f', '???': 'f', '???': 'f', '??': 'g', '??': 'g', '??': 'g', '??': 'g', '??': 'g', '??': 'g', '??': 'g', '???': 'g', '???': 'g', '??': 'g', '???': 'h', '??': 'h', '???': 'h', '??': 'h', '???': 'h', '???': 'h', '???': 'h', '???': 'h', '??': 'h', '???': 'h', '??': 'h', '??': 'hv', '??': 'i', '??': 'i', '??': 'i', '??': 'i', '??': 'i', '???': 'i', '???': 'i', '??': 'i', '??': 'i', '???': 'i', '??': 'i', '??': 'i', '??': 'i', '???': 'i', '??': 'i', '??': 'i', '???': 'i', '???': 'd', '???': 'f', '???': 'g', '???': 'r', '???': 's', '???': 't', '???': 'is', '??': 'j', '??': 'j', '??': 'j', '??': 'j', '???': 'k', '??': 'k', '??': 'k', '???': 'k', '???': 'k', '???': 'k', '??': 'k', '???': 'k', '???': 'k', '???': 'k', '???': 'k', '??': 'l', '??': 'l', '??': 'l', '??': 'l', '??': 'l', '???': 'l', '??': 'l', '???': 'l', '???': 'l', '???': 'l', '???': 'l', '???': 'l', '??': 'l', '??': 'l', '???': 'l', '??': 'l', '??': 'l', '??': 'lj', '??': 's', '???': 's', '???': 's', '???': 's', '???': 'm', '???': 'm', '???': 'm', '??': 'm', '???': 'm', '???': 'm', '??': 'n', '??': 'n', '??': 'n', '???': 'n', '??': 'n', '???': 'n', '???': 'n', '??': 'n', '??': 'n', '???': 'n', '??': 'n', '???': 'n', '???': 'n', '??': 'n', '??': 'n', '??': 'nj', '??': 'o', '??': 'o', '??': 'o', '??': 'o', '???': 'o', '???': 'o', '???': 'o', '???': 'o', '???': 'o', '??': 'o', '??': 'o', '??': 'o', '??': 'o', '???': 'o', '??': 'o', '??': 'o', '??': 'o', '???': 'o', '??': 'o', '???': 'o', '???': 'o', '???': 'o', '???': 'o', '???': 'o', '??': 'o', '???': 'o', '???': 'o', '???': 'o', '??': 'o', '???': 'o', '???': 'o', '??': 'o', '??': 'o', '??': 'o', '??': 'o', '??': 'o', '???': 'o', '???': 'o', '??': 'o', '??': 'oi', '???': 'oo', '??': 'e', '???': 'e', '??': 'o', '???': 'o', '??': 'ou', '???': 'p', '???': 'p', '???': 'p', '??': 'p', '???': 'p', '???': 'p', '???': 'p', '???': 'p', '???': 'p', '???': 'q', '??': 'q', '??': 'q', '???': 'q', '??': 'r', '??': 'r', '??': 'r', '???': 'r', '???': 'r', '???': 'r', '??': 'r', '??': 'r', '???': 'r', '??': 'r', '???': 'r', '??': 'r', '???': 'r', '???': 'r', '??': 'r', '??': 'r', '???': 'c', '???': 'c', '??': 'e', '??': 'r', '??': 's', '???': 's', '??': 's', '???': 's', '??': 's', '??': 's', '??': 's', '???': 's', '???': 's', '???': 's', '??': 's', '???': 's', '???': 's', '??': 's', '??': 'ss', '??': 'g', '???': 'o', '???': 'o', '???': 'u', '??': 't', '??': 't', '???': 't', '??': 't', '??': 't', '???': 't', '???': 't', '???': 't', '???': 't', '??': 't', '???': 't', '???': 't', '??': 't', '??': 't', '??': 't', '???': 'th', '??': 'a', '???': 'ae', '??': 'e', '???': 'g', '??': 'h', '??': 'h', '??': 'h', '???': 'i', '??': 'k', '???': 'l', '??': 'm', '??': 'm', '???': 'oe', '??': 'r', '??': 'r', '??': 'r', '???': 'r', '??': 't', '??': 'v', '??': 'w', '??': 'y', '???': 'tz', '??': 'u', '??': 'u', '??': 'u', '??': 'u', '???': 'u', '??': 'u', '??': 'u', '??': 'u', '??': 'u', '??': 'u', '???': 'u', '???': 'u', '??': 'u', '??': 'u', '??': 'u', '???': 'u', '??': 'u', '???': 'u', '???': 'u', '???': 'u', '???': 'u', '???': 'u', '??': 'u', '??': 'u', '???': 'u', '??': 'u', '???': 'u', '??': 'u', '??': 'u', '???': 'u', '???': 'u', '???': 'ue', '???': 'um', '???': 'v', '???': 'v', '???': 'v', '??': 'v', '???': 'v', '???': 'v', '???': 'v', '???': 'vy', '???': 'w', '??': 'w', '???': 'w', '???': 'w', '???': 'w', '???': 'w', '???': 'w', '???': 'w', '???': 'x', '???': 'x', '???': 'x', '??': 'y', '??': 'y', '??': 'y', '???': 'y', '???': 'y', '???': 'y', '??': 'y', '???': 'y', '???': 'y', '??': 'y', '???': 'y', '??': 'y', '???': 'y', '??': 'z', '??': 'z', '???': 'z', '??': 'z', '???': 'z', '??': 'z', '???': 'z', '??': 'z', '???': 'z', '???': 'z', '???': 'z', '??': 'z', '??': 'z', '??': 'z', '???': 'ff', '???': 'ffi', '???': 'ffl', '???': 'fi', '???': 'fl', '??': 'ij', '??': 'oe', '???': 'st', '???': 'a', '???': 'e', '???': 'i', '???': 'j', '???': 'o', '???': 'r', '???': 'u', '???': 'v', '???': 'x' };

			return text.replace(/[\u007F-\uFFFF]/g, function(key) {
				return diacriticsMap[key] || key;
			});
		},
		textHighlight: function( text, term ) {
			var markup = "";
			var remove_diacritics = sf.select2.base.prototype.removeDiacritics;
			var no_diac_text = remove_diacritics(text);
			var start = no_diac_text.toUpperCase().indexOf(term.toUpperCase());
			if (start !== 0 && !mw.config.get( 'sfgAutocompleteOnAllChars' )) {
				start = no_diac_text.toUpperCase().indexOf(" " + term.toUpperCase());
				if ( start != -1 ) {
					start = start + 1;
				}
			}
			if ( start != -1 ) {
				markup += text.substr(0, start) +
				'<span class="select2-match">' +
				text.substr(start,term.length) +
				'</span>' +
				text.substr(start + term.length, text.length);
			} else {
				markup += text;
			}

			return markup;
		},
	};
} )( jQuery, mediaWiki, semanticforms );