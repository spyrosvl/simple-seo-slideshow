<?php
/**
 * Plugin Name:       Simple SEO Slideshow
 * Plugin URI:        https://nitroweb.gr
 * Description:       Create Simple Slideshow from Post/Page Gallery (W3C Valid)
 * Author:            Nitroweb Development LTD
 * Version:           1.2.9
 * Author URI:        https://nitroweb.gr/
 * Requires PHP:      7.4
 * Requires at least: 5.2
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
 
// ---------------------------------------------------------------------------
// Allow-lists used for validation throughout the plugin.
// ---------------------------------------------------------------------------
 
/**
 * Valid values for yes/no toggle attributes.
 *
 * @return string[]
 */
function sss_valid_yesno() {
	return array( 'yes', 'no' );
}
 
/**
 * Valid position values for bullets / caption.
 *
 * @return string[]
 */
function sss_valid_positions() {
	return array(
		'top-left',
		'top-center',
		'top-right',
		'bottom-left',
		'bottom-center',
		'bottom-right',
	);
}
 
/**
 * Valid values for the "link what" attribute.
 *
 * @return string[]
 */
function sss_valid_linkwhat() {
	return array( 'image', 'caption', 'none', 'both' );
}
 
/**
 * Sanitise a value against an allow-list; return the default if not found.
 *
 * @param string   $value    Raw value.
 * @param string[] $allowed  Allow-list.
 * @param string   $default  Fallback.
 * @return string
 */
function sss_sanitize_allowed( $value, array $allowed, $default = '' ) {
	$value = sanitize_text_field( $value );
	return in_array( $value, $allowed, true ) ? $value : $default;
}
 
// ---------------------------------------------------------------------------
// Widget class
// ---------------------------------------------------------------------------
 
/**
 * Simple SEO Slideshow Widget.
 */
class SimpleSEOSlideshowWidget extends WP_Widget {
 
	/**
	 * Constructor – registers the widget.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'simpleSlideshowWidget',
			'description' => 'Displays a slideshow from post/page gallery',
		);
		parent::__construct( 'simpleSlideshowWidget', 'Post/Page Slideshow', $widget_ops );
	}
 
	/**
	 * Admin form.
	 *
	 * @param array $instance Current widget settings.
	 * @return void
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'          => '',
				'pid'            => '',
				'delay'          => 5,
				'ssheight'       => 100,
				'sssdisplaybul'  => 'yes',
				'sssdisplayarr'  => 'yes',
				'sssdisplaycap'  => 'yes',
				'sssbulpos'      => 'bottom-right',
				'ssscappos'      => 'bottom-left',
				'sssexclude'     => '',
				'sssrandomize'   => 'no',
				'ssslinkwhat'    => 'both',
			)
		);
 
		// Sanitise before rendering.
		$title         = esc_attr( sanitize_text_field( $instance['title'] ) );
		$pid           = absint( $instance['pid'] );
		$delay         = absint( $instance['delay'] );
		$ssheight      = absint( $instance['ssheight'] );
		$sssdisplaybul = sss_sanitize_allowed( $instance['sssdisplaybul'], sss_valid_yesno(), 'yes' );
		$sssdisplayarr = sss_sanitize_allowed( $instance['sssdisplayarr'], sss_valid_yesno(), 'yes' );
		$sssdisplaycap = sss_sanitize_allowed( $instance['sssdisplaycap'], sss_valid_yesno(), 'yes' );
		$sssbulpos     = sss_sanitize_allowed( $instance['sssbulpos'], sss_valid_positions(), 'bottom-right' );
		$ssscappos     = sss_sanitize_allowed( $instance['ssscappos'], sss_valid_positions(), 'bottom-left' );
		$sssexclude    = esc_attr( sanitize_text_field( $instance['sssexclude'] ) );
		$sssrandomize  = sss_sanitize_allowed( $instance['sssrandomize'], sss_valid_yesno(), 'no' );
		$ssslinkwhat   = sss_sanitize_allowed( $instance['ssslinkwhat'], sss_valid_linkwhat(), 'both' );
		?>
 
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'simple-seo-slideshow' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $title ); ?>"
			/>
		</p>
 
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'pid' ) ); ?>">
				<?php esc_html_e( 'Page/Post ID:', 'simple-seo-slideshow' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'pid' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'pid' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $pid ); ?>"
			/>
		</p>
 
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'delay' ) ); ?>">
				<?php esc_html_e( 'Delay in seconds:', 'simple-seo-slideshow' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'delay' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'delay' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $delay ); ?>"
			/>
		</p>
 
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ssheight' ) ); ?>">
				<?php esc_html_e( 'Slideshow Height:', 'simple-seo-slideshow' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'ssheight' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'ssheight' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $ssheight ); ?>"
			/>
		</p>
 
		<?php
		// Helper: render a yes/no <select>.
		$yesno_fields = array(
			'sssdisplaybul' => array( 'label' => 'Display Bullets:', 'value' => $sssdisplaybul ),
			'sssdisplayarr' => array( 'label' => 'Display Arrows:', 'value' => $sssdisplayarr ),
			'sssdisplaycap' => array( 'label' => 'Display Caption:', 'value' => $sssdisplaycap ),
			'sssrandomize'  => array( 'label' => 'Randomize image sorting:', 'value' => $sssrandomize ),
		);
		foreach ( $yesno_fields as $field_key => $field ) {
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( $field_key ) ); ?>">
					<?php echo esc_html( $field['label'] ); ?>
				</label>
				<select
					id="<?php echo esc_attr( $this->get_field_id( $field_key ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( $field_key ) ); ?>"
				>
					<?php foreach ( sss_valid_yesno() as $opt ) { ?>
						<option value="<?php echo esc_attr( $opt ); ?>"
							<?php selected( $field['value'], $opt ); ?>>
							<?php echo esc_html( $opt ); ?>
						</option>
					<?php } ?>
				</select>
			</p>
			<?php
		}
 
		// Position selects.
		$pos_fields = array(
			'sssbulpos' => array( 'label' => 'Bullets Position:', 'value' => $sssbulpos ),
			'ssscappos' => array( 'label' => 'Caption Position:', 'value' => $ssscappos ),
		);
		foreach ( $pos_fields as $field_key => $field ) {
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( $field_key ) ); ?>">
					<?php echo esc_html( $field['label'] ); ?>
				</label>
				<select
					id="<?php echo esc_attr( $this->get_field_id( $field_key ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( $field_key ) ); ?>"
				>
					<?php foreach ( sss_valid_positions() as $opt ) { ?>
						<option value="<?php echo esc_attr( $opt ); ?>"
							<?php selected( $field['value'], $opt ); ?>>
							<?php echo esc_html( $opt ); ?>
						</option>
					<?php } ?>
				</select>
			</p>
			<?php
		}
		?>
 
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'sssexclude' ) ); ?>">
				<?php esc_html_e( 'Exclude images by ID:', 'simple-seo-slideshow' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'sssexclude' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'sssexclude' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $sssexclude ); ?>"
			/>
			<?php esc_html_e( '(separate by comma)', 'simple-seo-slideshow' ); ?>
		</p>
 
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'ssslinkwhat' ) ); ?>">
				<?php esc_html_e( 'Choose what to link:', 'simple-seo-slideshow' ); ?>
			</label>
			<select
				id="<?php echo esc_attr( $this->get_field_id( 'ssslinkwhat' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'ssslinkwhat' ) ); ?>"
			>
				<?php foreach ( sss_valid_linkwhat() as $opt ) { ?>
					<option value="<?php echo esc_attr( $opt ); ?>"
						<?php selected( $ssslinkwhat, $opt ); ?>>
						<?php echo esc_html( $opt ); ?>
					</option>
				<?php } ?>
			</select>
		</p>
 
		<?php
	}
 
	/**
	 * Save widget settings.
	 *
	 * @param array $new_instance New settings from the form.
	 * @param array $old_instance Old settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                  = $old_instance;
		$instance['title']         = sanitize_text_field( $new_instance['title'] );
		$instance['pid']           = absint( $new_instance['pid'] );
		$instance['delay']         = absint( $new_instance['delay'] );
		$instance['ssheight']      = absint( $new_instance['ssheight'] );
		$instance['sssdisplaybul'] = sss_sanitize_allowed( $new_instance['sssdisplaybul'], sss_valid_yesno(), 'yes' );
		$instance['sssdisplayarr'] = sss_sanitize_allowed( $new_instance['sssdisplayarr'], sss_valid_yesno(), 'yes' );
		$instance['sssdisplaycap'] = sss_sanitize_allowed( $new_instance['sssdisplaycap'], sss_valid_yesno(), 'yes' );
		$instance['sssbulpos']     = sss_sanitize_allowed( $new_instance['sssbulpos'], sss_valid_positions(), 'bottom-right' );
		$instance['ssscappos']     = sss_sanitize_allowed( $new_instance['ssscappos'], sss_valid_positions(), 'bottom-left' );
		$instance['sssexclude']    = sanitize_text_field( $new_instance['sssexclude'] );
		$instance['sssrandomize']  = sss_sanitize_allowed( $new_instance['sssrandomize'], sss_valid_yesno(), 'no' );
		$instance['ssslinkwhat']   = sss_sanitize_allowed( $new_instance['ssslinkwhat'], sss_valid_linkwhat(), 'both' );
		return $instance;
	}
 
	/**
	 * Front-end output.
	 *
	 * @param array $args     Theme wrapper arguments.
	 * @param array $instance Saved widget settings.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is built with escaping helpers inside ssoutput().
		echo ssoutput( $args, $instance );
	}
}
 
// ---------------------------------------------------------------------------
// Shared slideshow output builder (used by both widget and shortcode).
// ---------------------------------------------------------------------------
 
/**
 * Build and return the slideshow HTML.
 *
 * @param array      $args     Widget sidebar args or shortcode attributes.
 * @param array|null $instance Widget instance data (null when called via shortcode).
 * @return string
 */
function ssoutput( $args, $instance = null ) {
	global $post;
 
	if ( null === $instance ) {
		$instance = array();
	}
 
	// ------------------------------------------------------------------
	// Merge shortcode attributes (already validated) with widget values.
	// ------------------------------------------------------------------
	$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
	$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';
	$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : '';
	$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : '';
	$widget_id     = isset( $args['widget_id'] ) ? $args['widget_id'] : 'sss0';
 
	// Shortcode defaults (already validated when coming from the shortcode handler).
	// Read $post->ID here (lazily, inside the callback) so the global is already
	// set by the time the shortcode fires during the_content processing.
	$queried_pid    = ( $post instanceof WP_Post ) ? $post->ID : 0;
	$sc_title       = isset( $args['sctitle'] ) ? $args['sctitle'] : '';
	// scpid of 0 means "use the current post" – preserve that intent.
	$sc_pid         = ( isset( $args['scpid'] ) && absint( $args['scpid'] ) > 0 )
		? absint( $args['scpid'] )
		: $queried_pid;
	$sc_delay       = isset( $args['scdelay'] ) ? $args['scdelay'] : 5;
	$sc_height      = isset( $args['scheight'] ) ? $args['scheight'] : 100;
	$sc_displaybul  = isset( $args['scdisplaybul'] ) ? $args['scdisplaybul'] : 'yes';
	$sc_displayarr  = isset( $args['scdisplayarr'] ) ? $args['scdisplayarr'] : 'yes';
	$sc_randomize   = isset( $args['scrandomize'] ) ? $args['scrandomize'] : 'no';
	$sc_linkwhat    = isset( $args['sclinkwhat'] ) ? $args['sclinkwhat'] : 'both';
	$sc_displaycap  = isset( $args['scdisplaycap'] ) ? $args['scdisplaycap'] : 'yes';
	$sc_bulpos      = isset( $args['scbulpos'] ) ? $args['scbulpos'] : 'bottom-right';
	$sc_cappos      = isset( $args['sccappos'] ) ? $args['sccappos'] : 'bottom-left';
	$sc_exclude     = isset( $args['scexclude'] ) ? $args['scexclude'] : '';
 
	// Resolve final values (widget overrides shortcode defaults).
	// Note: do NOT use empty() for $pid – it treats 0 as empty, which would
	// silently discard a valid "use current post" value.
	$title    = ( ! isset( $instance['title'] ) || '' === $instance['title'] )
		? $sc_title
		: apply_filters( 'widget_title', $instance['title'] );
	$pid      = ( ! isset( $instance['pid'] ) || 0 === absint( $instance['pid'] ) )
		? absint( $sc_pid )
		: absint( $instance['pid'] );
	$delay        = empty( $instance['delay'] ) ? absint( $sc_delay ) : absint( $instance['delay'] );
	$ssheight     = empty( $instance['ssheight'] ) ? absint( $sc_height ) : absint( $instance['ssheight'] );
 
	$sssdisplaybul = empty( $instance['sssdisplaybul'] )
		? sss_sanitize_allowed( $sc_displaybul, sss_valid_yesno(), 'yes' )
		: sss_sanitize_allowed( $instance['sssdisplaybul'], sss_valid_yesno(), 'yes' );
 
	$sssdisplayarr = empty( $instance['sssdisplayarr'] )
		? sss_sanitize_allowed( $sc_displayarr, sss_valid_yesno(), 'yes' )
		: sss_sanitize_allowed( $instance['sssdisplayarr'], sss_valid_yesno(), 'yes' );
 
	$sssrandomize = empty( $instance['sssrandomize'] )
		? sss_sanitize_allowed( $sc_randomize, sss_valid_yesno(), 'no' )
		: sss_sanitize_allowed( $instance['sssrandomize'], sss_valid_yesno(), 'no' );
 
	$ssslinkwhat = empty( $instance['ssslinkwhat'] )
		? sss_sanitize_allowed( $sc_linkwhat, sss_valid_linkwhat(), 'both' )
		: sss_sanitize_allowed( $instance['ssslinkwhat'], sss_valid_linkwhat(), 'both' );
 
	$sssdisplaycap = empty( $instance['sssdisplaycap'] )
		? sss_sanitize_allowed( $sc_displaycap, sss_valid_yesno(), 'yes' )
		: sss_sanitize_allowed( $instance['sssdisplaycap'], sss_valid_yesno(), 'yes' );
 
	$sssbulpos = empty( $instance['sssbulpos'] )
		? sss_sanitize_allowed( $sc_bulpos, sss_valid_positions(), 'bottom-right' )
		: sss_sanitize_allowed( $instance['sssbulpos'], sss_valid_positions(), 'bottom-right' );
 
	$ssscappos = empty( $instance['ssscappos'] )
		? sss_sanitize_allowed( $sc_cappos, sss_valid_positions(), 'bottom-left' )
		: sss_sanitize_allowed( $instance['ssscappos'], sss_valid_positions(), 'bottom-left' );
 
	$sssexclude_raw = empty( $instance['sssexclude'] ) ? $sc_exclude : $instance['sssexclude'];
	// Turn the comma-separated exclude list into an array of integers.
	$sssexclude = array_map( 'absint', explode( ',', sanitize_text_field( $sssexclude_raw ) ) );
 
	$sssbelow  = ( $sssbulpos === $ssscappos ) ? 'sssbelow' : '';
	$ssswidgid = esc_attr( str_replace( '-', '', $widget_id ) );
 
	// ------------------------------------------------------------------
	// Build output.
	// ------------------------------------------------------------------
	$sssout = $before_widget;
 
	if ( ! empty( $title ) ) {
		$sssout .= $before_title . esc_html( $title ) . $after_title;
	}
 
	// get_children() with post_parent=0 would return ALL unattached media site-wide.
	// Bail early if we still have no valid post ID.
	if ( 0 === $pid ) {
		return '';
	}
 
	$photos = get_children(
		array(
			'post_parent'    => $pid,
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'order'          => 'ASC',
			'orderby'        => 'menu_order ID',
		)
	);
 
	if ( 'yes' === $sssrandomize && is_array( $photos ) ) {
		shuffle( $photos );
	}
 
	$actslide   = '';
	$slidedescr = '';
	$restslides = '';
 
	if ( $photos ) {
		$ssswi = 0;
		foreach ( $photos as $photo ) {
			$photoid = absint( $photo->ID );
			if ( in_array( $photoid, $sssexclude, true ) ) {
				continue;
			}
 
			$photosrc      = wp_get_attachment_image_src( $photo->ID, 'full' );
			$photoalt      = esc_attr( get_post_meta( $photo->ID, '_wp_attachment_image_alt', true ) );
			$phototitle    = esc_attr( get_the_title( $photo->ID ) );
			$photocustlink = esc_url( get_post_meta( $photo->ID, '_ssscustomlink', true ) );
			$photodescr    = esc_html( $photo->post_content );
			$img_w         = isset( $photosrc[1] ) ? absint( $photosrc[1] ) : 0;
			$img_h         = isset( $photosrc[2] ) ? absint( $photosrc[2] ) : 0;
			$img_src       = isset( $photosrc[0] ) ? esc_url( $photosrc[0] ) : '';
 
			$ssscaption = '<span>' . esc_html( get_the_title( $photo->ID ) ) . '</span>';
			if ( '' !== $photodescr ) {
				$ssscaption .= ' - ' . $photodescr;
			}
 
			if ( 0 === $ssswi ) {
				$actslide = sprintf(
					'<img src="%s" alt="%s" title="%s" width="%d" height="%d" />',
					$img_src,
					$photoalt,
					$phototitle,
					$img_w,
					$img_h
				);
 
				if ( ( 'both' === $ssslinkwhat || 'image' === $ssslinkwhat ) && '' !== $photocustlink ) {
					$actslide = sprintf(
						'<a href="%s" title="%s" class="currentlink" rel="%s">%s</a>',
						$photocustlink,
						$phototitle,
						esc_attr( $ssslinkwhat ),
						$actslide
					);
				}
 
				if ( '' !== $photocustlink && ( 'both' === $ssslinkwhat || 'caption' === $ssslinkwhat ) ) {
					$slidedescr = sprintf(
						'<a href="%s" title="%s">%s</a>',
						$photocustlink,
						$phototitle,
						$ssscaption
					);
				} else {
					$slidedescr = $ssscaption;
				}
			} else {
				$restslides .= sprintf(
					'<a href="%s" title="%s">%s - %s</a>',
					$img_src,
					$phototitle,
					esc_html( get_the_title( $photo->ID ) ),
					$photodescr
				);
				$restslides .= sprintf(
					'<span class="ssoptions">%d|%d|%s|%s|%s|%s</span>',
					$img_w,
					$img_h,
					esc_html( get_the_title( $photo->ID ) ),
					$photodescr,
					$photocustlink,
					esc_html( $ssslinkwhat )
				);
			}
 
			++$ssswi;
		}
	}
 
	// Delay is already absint; multiply is safe.
	$delay_ms = $delay * 1000;
 
	$sssout .= '<div class="' . $ssswidgid . ' ssslideshow ssswcustom">' . "\n";
	$sssout .= $actslide . "\n";
 
	if ( 'no' !== $sssdisplayarr ) {
		$sssout .= '<div class="sss-arrow-left sssarrow"></div>' . "\n";
		$sssout .= '<div class="sss-arrow-right sssarrow"></div>' . "\n";
	}
 
	if ( 'no' !== $sssdisplaybul ) {
		$sssout .= '<div class="' . $ssswidgid . ' slidedots '
			. esc_attr( $sssbulpos ) . ' ' . esc_attr( $sssbelow ) . '"></div>' . "\n";
	}
 
	if ( 'no' !== $sssdisplaycap ) {
		$sssout .= '<div class="' . $ssswidgid . ' slidedescr '
			. esc_attr( $ssscappos ) . '">' . $slidedescr . '</div>' . "\n";
	}
 
	$sssout .= '<div class="' . $ssswidgid . ' restslides">' . "\n" . $restslides . "\n" . '</div>' . "\n";
	$sssout .= '<div class="sssdelay">' . absint( $delay_ms ) . '</div>' . "\n";
	$sssout .= '<div class="sssheight">' . absint( $ssheight ) . '</div>' . "\n";
	$sssout .= '</div>' . "\n";
 
	$sssout .= $after_widget;
 
	return $sssout;
}
 
// ---------------------------------------------------------------------------
// Shortcode handler – validates all attributes before passing to ssoutput().
// ---------------------------------------------------------------------------
 
/**
 * Shortcode callback for [simpleslideshow].
 *
 * @param array $atts Raw shortcode attributes.
 * @return string
 */
function sss_shortcode_handler( $atts ) {
	// Do NOT resolve $post->ID here as the default – shortcode_atts() runs at
	// parse time and $post may not be set yet.  Use 0 as a sentinel meaning
	// "use the current post", which ssoutput() resolves lazily via its own
	// `global $post` read inside the callback.
	$raw = shortcode_atts(
		array(
			'sctitle'      => '',
			'scpid'        => 0,   // 0 = "use current post" sentinel
			'scdelay'      => 5,
			'scheight'     => 100,
			'scdisplaybul' => 'yes',
			'scdisplayarr' => 'yes',
			'scrandomize'  => 'no',
			'sclinkwhat'   => 'both',
			'scdisplaycap' => 'yes',
			'scbulpos'     => 'bottom-right',
			'sccappos'     => 'bottom-left',
			'scexclude'    => '',
		),
		$atts,
		'simpleslideshow'
	);
 
	// Validate / sanitise every attribute here so ssoutput() receives clean data.
	$validated = array(
		'sctitle'      => sanitize_text_field( $raw['sctitle'] ),
		'scpid'        => absint( $raw['scpid'] ),
		'scdelay'      => absint( $raw['scdelay'] ),
		'scheight'     => absint( $raw['scheight'] ),
		'scdisplaybul' => sss_sanitize_allowed( $raw['scdisplaybul'], sss_valid_yesno(), 'yes' ),
		'scdisplayarr' => sss_sanitize_allowed( $raw['scdisplayarr'], sss_valid_yesno(), 'yes' ),
		'scrandomize'  => sss_sanitize_allowed( $raw['scrandomize'], sss_valid_yesno(), 'no' ),
		'sclinkwhat'   => sss_sanitize_allowed( $raw['sclinkwhat'], sss_valid_linkwhat(), 'both' ),
		'scdisplaycap' => sss_sanitize_allowed( $raw['scdisplaycap'], sss_valid_yesno(), 'yes' ),
		'scbulpos'     => sss_sanitize_allowed( $raw['scbulpos'], sss_valid_positions(), 'bottom-right' ),
		'sccappos'     => sss_sanitize_allowed( $raw['sccappos'], sss_valid_positions(), 'bottom-left' ),
		'scexclude'    => sanitize_text_field( $raw['scexclude'] ),
		// Provide stub widget-wrapper keys so ssoutput() does not emit notices.
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
		'widget_id'     => 'sss-shortcode',
	);
 
	return ssoutput( $validated, array() );
}
add_shortcode( 'simpleslideshow', 'sss_shortcode_handler' );
 
// ---------------------------------------------------------------------------
// TinyMCE button.
// ---------------------------------------------------------------------------
 
/**
 * Register TinyMCE plugin and button for users with editing caps.
 *
 * @return void
 */
function sss_add_button() {
	if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
		add_filter( 'mce_external_plugins', 'sss_add_plugin' );
		add_filter( 'mce_buttons', 'sss_register_button' );
	}
}
add_action( 'init', 'sss_add_button' );
 
/**
 * Add the slideshow button to the TinyMCE toolbar.
 *
 * @param array $buttons Existing buttons.
 * @return array
 */
function sss_register_button( $buttons ) {
	array_push( $buttons, '|', 'ssslide' );
	return $buttons;
}
 
/**
 * Register the TinyMCE plugin JS file.
 *
 * @param array $plugin_array Existing plugins.
 * @return array
 */
function sss_add_plugin( $plugin_array ) {
	$plugin_array['ssslide'] = plugins_url( '/simple-seo-slideshow/js/simpleslideshowmce.js' );
	return $plugin_array;
}
 
/**
 * Force TinyMCE to reload after plugin changes.
 *
 * @param int $ver Current TinyMCE version token.
 * @return int
 */
function sss_refresh_mce( $ver ) {
	return $ver + 3;
}
add_filter( 'tiny_mce_version', 'sss_refresh_mce' );
 
// ---------------------------------------------------------------------------
// Attachment custom link field.
// ---------------------------------------------------------------------------
 
/**
 * Add a "Custom Link" field to the attachment edit form.
 *
 * @param array   $form_fields Existing fields.
 * @param WP_Post $post        Attachment post.
 * @return array
 */
function sss_attachment_fields_to_edit( $form_fields, $post ) {
	$form_fields['ssscustomlink'] = array(
		'label' => __( 'Custom Link', 'simple-seo-slideshow' ),
		'input' => 'text',
		'value' => get_post_meta( $post->ID, '_ssscustomlink', true ),
		'helps' => __( 'Fill this only if you want the slideshow image caption to link to a page', 'simple-seo-slideshow' ),
	);
	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'sss_attachment_fields_to_edit', null, 2 );
 
/**
 * Save the custom link field value.
 *
 * @param array $post       Post data.
 * @param array $attachment Attachment POST data.
 * @return array
 */
function sss_attachment_fields_to_save( $post, $attachment ) {
	if ( isset( $attachment['ssscustomlink'] ) ) {
		update_post_meta( $post['ID'], '_ssscustomlink', esc_url_raw( $attachment['ssscustomlink'] ) );
	}
	return $post;
}
add_filter( 'attachment_fields_to_save', 'sss_attachment_fields_to_save', null, 2 );
 
// ---------------------------------------------------------------------------
// Bootstrap.
// ---------------------------------------------------------------------------
 
/**
 * Register the widget.
 *
 * @return void
 */
function sss_register_widget() {
	register_widget( 'SimpleSEOSlideshowWidget' );
}
add_action( 'widgets_init', 'sss_register_widget' );
 
/**
 * Enqueue front-end assets.
 *
 * @return void
 */
function sss_enqueue_assets() {
	wp_enqueue_script(
		'simpleslideshowjs',
		plugins_url( '/simple-seo-slideshow/simpleslideshow.js' ),
		array( 'jquery' ),
		'1.2.9',
		false
	);
	wp_enqueue_style(
		'simpleslideshowcss',
		plugins_url( '/simple-seo-slideshow/simpleslideshow.css' ),
		array(),
		'1.2.9',
		'all'
	);
}
add_action( 'wp_enqueue_scripts', 'sss_enqueue_assets' );
