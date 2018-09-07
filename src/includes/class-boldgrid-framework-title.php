<?php
/**
 * Class: Boldgrid_Framework_Title
 *
 * @since      2.0.0
 * @category   Customizer
 * @package    Boldgrid_Framework
 * @subpackage Boldgrid_Framework_Title
 * @author     BoldGrid <support@boldgrid.com>
 * @link       https://boldgrid.com
 */

// If this file is called directly, abort.
defined( 'WPINC' ) ? : die;

/**
 * Boldgrid_Framework_Title Class
 *
 * @since 2.0.0
 */
class Boldgrid_Framework_Title {

	/**
	 * The BoldGrid Theme Framework configurations.
	 *
	 * @since     2.0.0
	 * @access    protected
	 * @var       array $configs The BoldGrid Theme Framework configurations.
	 */
	protected $configs;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.0
	 *
	 * @param array $configs The BoldGrid Theme Framework configurations.
	 */
	public function __construct( $configs ) {
		$this->configs = $configs;
	}

	/**
	 * Enqueue scripts for the customizer.
	 *
	 * @since 2.0.0
	 */
	public function customize_controls_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		/*
		 * Currently, we're enqueuing homepage.js, which handles dynamically adding
		 * "Configure Posts Page" links to Customizer > Design > Homepage.
		 */
		$handle = 'bgtfw-customizer-homepage';
		wp_register_script(
			$handle,
			$this->configs['framework']['js_dir'] . 'customizer/homepage' . $suffix . '.js',
			array(
				'jquery',
			),
			false,
			true
		);
		wp_enqueue_script( $handle );
		wp_localize_script( $handle, 'boldgridFrameworkCustomizerHomepage', array(
			'Configure' => __( 'Configure', 'bgtfw' ),
			'ConfigurePostsPage' => __( 'Configure Posts Page', 'bgtfw' ),
		));
	}

	/**
	 * Get theme mod value for displaying title.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $post_type page or post.
	 * @return string
	 */
	public function get_global( $post_type = null ) {
		$post_type = empty( $post_type ) ? get_post_type() : $post_type;
		$post_type = isset( $this->configs['title'][ 'default_' . $post_type ] ) ? $post_type : 'post';

		return 'show' === get_theme_mod( 'bgtfw_' . $post_type . 's_title_display' ) ? true : false;
	}

	/**
	 * Display post title controls within "Post Attributes" meta box.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post WordPress Post Object.
	 */
	public function meta_box_callback( $post ) {
		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		$is_posts_page = $post->ID === get_option( 'page_for_posts' );

		$post_meta = get_post_meta( $post->ID, $this->configs['title']['hide'], true );
		$global = $this->get_global( $post->post_type );

		$title = sprintf( '%1$s %2$s', 'post' === $post->post_type ? __( 'Post', 'bgtfw' ) : __( 'Page', 'bgtfw' ), __( 'Title', 'bgtfw' ) );

		$options = array(
			array(
				'name' => __( 'Use Global Setting', 'bgtfw' ),
				'value' => 'global',
				'checked' => '' === $post_meta,
				'post_text' => true === $global ? __( 'Show', 'bgtfw' ) : __( 'Hide', 'bgtfw' ),
			),
			array(
				'name' => __( 'Show', 'bgtfw' ),
				'value' => '1',
				'checked' => '1' === $post_meta,
				'post_text' => $this->configs['title']['meta_box'][ $post->post_type ]['show_post_text'],
			),
			array(
				'name' => __( 'Hide', 'bgtfw' ),
				'value' => '0',
				'checked' => '0' === $post_meta,
				'post_text' => $this->configs['title']['meta_box'][ $post->post_type ]['hide_post_text'],
			),
		);

		/*
		 * For posts, the decision to show / hide the title is based on post meta. For the page_for_posts,
		 * the decision is based on a theme mod.
		 *
		 * If we are on the page_for_posts, make necessary adjustments to our $options.
		 */
		if ( $is_posts_page ) {
			$show_title = true === get_theme_mod( $this->configs['title']['page_for_posts'], $this->configs['title']['default_page_for_posts'] );

			array_shift( $options );

			$options[0]['checked'] = $show_title;
			$options[1]['checked'] = ! $show_title;
		}
		?>

		<div class="misc-pub-section bgtfw-misc-pub-section bgtfw-page-title">
			<?php echo esc_html( $title ); ?>: <span class="value-displayed">...</span>
			<a class="edit" href="">
				<span aria-hidden="true"><?php echo __( 'Edit', 'bgtfw' ); ?></span> <span class="screen-reader-text"><?php echo esc_html( $title ); ?></span>
			</a>
			<div class="options">
				<?php foreach ( $options as $option ) {
					$value_displayed = $option['name'] . ( ! empty( $option['post_text'] ) ? sprintf( ' <span class="template-subtitle">%1$s</span>', $option['post_text'] )  : '' );
				?><label>
					<input type="radio" name="<?php echo esc_attr( $this->configs['title']['hide'] ); ?>" value="<?php echo esc_attr( $option['value'] ); ?>" <?php checked( $option['checked'] ); ?> data-default-option="<?php echo $option['checked'] ? '1' : '0'; ?>" data-value-displayed="<?php echo esc_attr( $value_displayed ); ?>" />
					<?php echo $value_displayed; ?>
				</label>
				<?php } ?>
				<p>
					<a href="" class="button">OK</a>
					<a href="" class="button-cancel">Cancel</a>
				</p>
			</div>
		</div><?php
	}

	/**
	 * Update "show title" post meta after a post is updated.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id The ID of the post being updated.
	 */
	public function post_updated( $post_id ) {
		if ( isset( $_POST[ $this->configs['title']['hide'] ] ) ) {

			// Validate "show title" post meta, and abort on failure.
			$value = $_POST[ $this->configs['title']['hide'] ];
			if ( ! in_array( $value, array( '0', '1' ), true ) ) {
				return;
			}

			$is_posts_page = $post_id === (int) get_option( 'page_for_posts' );

			/*
			 * Update our value.
			 *
			 * Posts actually use post meta, and the page_for_posts page uses a theme mod.
			 */
			if( $is_posts_page ) {
				$value = $value ? 'inherit' : 'none';
				set_theme_mod( $this->configs['title']['page_for_posts'], $value );
			} else {
				delete_post_meta( $post_id, $this->configs['title']['hide'] );
				update_post_meta( $post_id, $this->configs['title']['hide'], $value );
			}
		}
	}

	/**
	 * Filter a post's title within the_title filter.
	 *
	 * Determine whether or not to show post title on a post/page.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $title The post title.
	 * @param  int    $id    The post ID.
	 * @return string
	 */
	public function show_title( $title, $id ) {

		// This method only needs to be ran if we're looking at a single page / post.
		$is_single_post = is_page() || is_single();
		if ( ! $is_single_post ) {
			return $title;
		}

		/*
		 * The the_title filter is ran quite often. For example, when displaying nav menus, this filter
		 * is ran and can change a page's title in the nav. We're only interested in adjusting the
		 * title when displaying a post.
		 */
		if ( ! in_the_loop() ) {
			return $title;
		}

		$post_meta = get_post_meta( $id, $this->configs['title']['hide'], true );
		$global = $this->get_global();
		$show = '1' === $post_meta || ( '' === $post_meta && true === $global );

		return $show ? $title : '';
	}
}
