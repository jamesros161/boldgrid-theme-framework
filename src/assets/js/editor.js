( function( $ ) {
	'use strict';

	$( function() {
		tinymce.PluginManager.add( 'boldgrid_theme_framework', function( editor ) {
			editor.on( 'init', function() {
				var $style,
					$iframeHead;

				if ( BOLDGRID_THEME_FRAMEWORK.Editor && BOLDGRID_THEME_FRAMEWORK.Editor.mce_inline_styles ) {
					$style = $( '<style>' );
					$style.html( BOLDGRID_THEME_FRAMEWORK.Editor.mce_inline_styles );
					$iframeHead = $( tinyMCE.activeEditor.iframeElement ).contents().find( 'head' );

					$iframeHead.append( $style );

					// Copy all google fonts into the editor.
					$( 'head link[rel="stylesheet"][href*="fonts.googleapis.com/css"], [id^="kirki-local-webfonts"]' ).each( function() {
						$iframeHead.append( $( this ).addClass( 'webfontjs-loader-styles' ).clone() );
					} );
				}
			} );
		} );

		if ( hasPPBEnabled() ) {
			bindPPB();
		}
	} );

	function bindPPB() {
		var BGE = window.BOLDGRID.EDITOR;

		BGE.$window.on( 'boldgrid_editor_loaded', function() {

			BGE.Service.event.on( 'widgetUpdated', function( node ) {
				var $node = $( node );

				$node.find( '.widget_rss ul' ).addClass( 'media-list' );

				$node.find( '.widget_meta ul, .widget_recent_entries ul, .widget_archive ul, .widget_categories ul, .widget_nav_menu ul, .widget_pages ul' )
					.addClass( 'nav' );
			} );
		} );
	}

	function hasPPBEnabled() {
		var BOLDGRID = window.BOLDGRID;
		return BOLDGRID && BOLDGRID.EDITOR && BOLDGRID.EDITOR.Service && BOLDGRID.EDITOR.$window;
	}

})( jQuery );
