jQuery.noConflict();

jQuery( document ).ready( function ( $ ) {

	'use strict';

	if ( ! $( '.ssslideshow' ).length ) {
		return;
	}

	// -------------------------------------------------------------------------
	// Each slideshow is self-contained inside a closure so every variable is
	// private to that instance – this fixes the index/closure bug in the
	// original where all setInterval callbacks shared the loop variable.
	// -------------------------------------------------------------------------
	$( '.ssslideshow' ).each( function () {

		var $show      = $( this );
		var delay      = parseInt( $( '.sssdelay', $show ).text(), 10 ) || 5000;
		var $restLinks = $( '.restslides a', $show );
		var total      = $restLinks.length + 1; // first slide + rest
		var cur        = 0;
		var timer      = null;

		// Slides are stored as plain objects: { src, width, height, title, subtitle, href, linktype }
		var slides = [];

		// -----------------------------------------------------------------------
		// Build slide data from the DOM, then hide the data container.
		// -----------------------------------------------------------------------
		function buildSlideData() {
			var $firstImg  = $( 'img', $show ).eq( 0 );
			var $firstLink = $( '.currentlink', $show );
			var $firstDesc = $( '.slidedescr', $show );

			slides[ 0 ] = {
				src      : $firstImg.attr( 'src' ),
				width    : $firstImg.attr( 'width' ),
				height   : $firstImg.attr( 'height' ),
				title    : $firstImg.attr( 'title' ),
				subtitle : $firstDesc.html() || '',
				href     : $firstLink.attr( 'href' ) || '',
				linktype : $firstLink.attr( 'rel' )  || ''
			};

			$restLinks.each( function ( i ) {
				var $a      = $( this );
				var $opts   = $a.next( 'span.ssoptions' );
				var parts   = $opts.text().split( '|' );
				// parts: [width, height, title, description, href, linktype]
				var w       = parts[ 0 ] || '';
				var h       = parts[ 1 ] || '';
				var title   = parts[ 2 ] || '';
				var descr   = parts[ 3 ] || '';
				var href    = parts[ 4 ] || '';
				var ltype   = parts[ 5 ] || '';

				var caption = '<span>' + title + '</span>';
				if ( descr !== '' ) {
					caption += ' - ' + descr;
				}

				var subtitle;
				if ( href !== '' && ( ltype === 'both' || ltype === 'caption' ) ) {
					subtitle = '<a href="' + href + '" title="' + title + '">' + caption + '</a>';
				} else {
					subtitle = caption;
				}

				slides[ i + 1 ] = {
					src      : $a.attr( 'href' ),
					width    : w,
					height   : h,
					title    : title,
					subtitle : subtitle,
					href     : href,
					linktype : ltype
				};
			} );

			$( '.restslides', $show ).hide();
		}

		// -----------------------------------------------------------------------
		// Auto-height: called each time an image finishes loading.
		// Expands the wrapper if the new image is taller than the current height.
		// Falls back to the explicit sssheight value as the starting minimum.
		// -----------------------------------------------------------------------
		var currentHeight = parseInt( $( '.sssheight', $show ).text(), 10 ) || 0;
		if ( currentHeight > 0 ) {
			$show.height( currentHeight );
		}

		function fitHeight( naturalHeight ) {
			if ( naturalHeight > currentHeight ) {
				currentHeight = naturalHeight;
				$show.height( currentHeight );
			}
		}

		// -----------------------------------------------------------------------
		// Navigation dots.
		// -----------------------------------------------------------------------
		function buildDots() {
			var $dots = $( '.slidedots', $show );
			if ( ! $dots.length ) {
				return;
			}

			$.each( slides, function ( i, slide ) {
				$dots.append( '<a href="" class="dot" title="' + slide.title + '"></a>' );
			} );

			$dots.find( 'a' ).eq( 0 ).addClass( 'active' );

			// Center the dot bar if needed.
			if ( $dots.is( '.top-center, .bottom-center' ) ) {
				$dots.css( 'left', ( $show.width() / 2 ) - ( $dots.width() / 2 ) + 'px' );
			}

			$dots.on( 'click', 'a', function ( e ) {
				e.preventDefault();
				var clicked = $( this ).index();
				if ( clicked === cur ) {
					return;
				}
				cur = clicked - 1; // nextSlide() will increment to the clicked index
				restartTimer();
				nextSlide();
			} );
		}

		// -----------------------------------------------------------------------
		// Update the active dot to match cur.
		// -----------------------------------------------------------------------
		function syncDots() {
			var $dots = $( '.slidedots a', $show );
			if ( ! $dots.length ) {
				return;
			}
			$dots.removeClass( 'active' ).eq( cur ).addClass( 'active' );
		}

		// -----------------------------------------------------------------------
		// Measure and centre the first image once it has loaded.
		// -----------------------------------------------------------------------
		function centerImage() {
			var $img = $( 'img', $show ).eq( 0 );
			$img.css( 'left', ( $show.width() / 2 ) - ( $img.width() / 2 ) + 'px' );
		}

		function initFirstImage() {
			var $img = $( 'img', $show ).eq( 0 );

			function onReady() {
				// Read the rendered height from the DOM so CSS resizing is respected.
				fitHeight( $img.outerHeight() );
				centerImage();
			}

			// If the browser already has it cached the image is already laid out.
			if ( $img[ 0 ].complete && $img[ 0 ].naturalHeight ) {
				onReady();
			} else {
				$img.one( 'load', onReady );
			}
		}

		// -----------------------------------------------------------------------
		// Advance to the next slide, with cross-fade.
		// -----------------------------------------------------------------------
		function nextSlide() {
			cur = ( cur + 1 ) % total;
			var slide = slides[ cur ];
			var img   = new Image();

			$( img )
				.on( 'load', function () {
					var $img = $( this );
					$img.hide();
					$show.append( $img );
					// Read rendered height after appending so CSS has been applied.
					fitHeight( $img.outerHeight() );
					$img.css( 'left', ( $show.width() / 2 ) - ( $img.width() / 2 ) + 'px' );

					// Fade out old, fade in new.
					$( 'img', $show ).not( $img ).fadeOut( 1000 );
					$img.fadeIn( 1000, function () {
						// Clean up old image and its wrapping link (if any).
						$( 'img', $show ).not( $img ).remove();
						$( '.currentlink', $show ).eq( 0 ).remove();

						// Wrap new image in a link if required.
						if ( slide.href !== '' && ( slide.linktype === 'both' || slide.linktype === 'image' ) ) {
							$img.wrap( '<a href="' + slide.href + '" class="currentlink" rel="' + slide.linktype + '"></a>' );
						}

						syncDots();
					} );
				} )
				.on( 'error', function () {
					// Skip broken images silently; still advance the dot.
					syncDots();
				} )
				.attr( {
					src    : slide.src,
					width  : slide.width,
					height : slide.height,
					title  : slide.title,
					alt    : slide.title
				} );

			// Caption updates immediately (no need to wait for image load).
			$( '.slidedescr', $show ).html( slide.subtitle );
		}

		// -----------------------------------------------------------------------
		// Timer helpers.
		// -----------------------------------------------------------------------
		function startTimer() {
			timer = setInterval( nextSlide, delay );
		}

		function stopTimer() {
			clearInterval( timer );
		}

		function restartTimer() {
			stopTimer();
			startTimer();
		}

		// -----------------------------------------------------------------------
		// Arrow controls.
		// Delegated events on $show (not directly on the arrow divs) so clicks
		// register even while the arrows are mid-fade or hidden.
		// -----------------------------------------------------------------------
		function bindArrows() {
			var $arrows = $( '.sssarrow', $show );
			if ( ! $arrows.length ) {
				return;
			}

			$show.hover(
				function () { $arrows.stop( true, true ).fadeIn( 300 ); },
				function () { $arrows.stop( true, true ).fadeOut( 300 ); }
			);

			$show.on( 'click', '.sss-arrow-left', function ( e ) {
				e.preventDefault();
				cur = ( cur - 2 + total ) % total; // nextSlide() adds 1, net = previous slide
				restartTimer();
				nextSlide();
			} );

			$show.on( 'click', '.sss-arrow-right', function ( e ) {
				e.preventDefault();
				// cur stays as-is; nextSlide() adds 1 → next slide
				restartTimer();
				nextSlide();
			} );
		}

		// -----------------------------------------------------------------------
		// Initialise.
		// -----------------------------------------------------------------------
		buildSlideData();
		initFirstImage();
		buildDots();
		bindArrows();
		if ( total > 1 ) {
			startTimer();
		}

	} ); // end each

} );