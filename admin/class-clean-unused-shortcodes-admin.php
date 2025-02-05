<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://amrelarabi.com
 * @since      1.0.0
 *
 * @package    Clean_Unused_Shortcodes
 * @subpackage Clean_Unused_Shortcodes/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Clean_Unused_Shortcodes
 * @subpackage Clean_Unused_Shortcodes/admin
 * @author     Amr Elarabi <contact@amrelarabi.com>
 */
class Clean_Unused_Shortcodes_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name       The name of this plugin.
	 * @param    string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Clean_Unused_Shortcodes_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clean_Unused_Shortcodes_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name . '-admin-styles', CUS_PLUGIN_URL . 'assets/dist/admin-styles.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Clean_Unused_Shortcodes_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clean_Unused_Shortcodes_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, CUS_PLUGIN_URL . 'assets/dist/admin-scripts.min.js', $this->version, array(), true );
		wp_localize_script(
			$this->plugin_name,
			'cus_ajax_object',
			array(
				'admin_ajax'           => admin_url( 'admin-ajax.php' ),
				'cleanShortcodesNonce' => wp_create_nonce( 'clean_shortcodes_nonce' ),
			)
		);
	}

	/**
	 * Plugin tools page.
	 *
	 * @return void
	 */
	public function cus_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'Clean unused shortcodes', 'clean-unused-shortcodes' ),
			__( 'Clean unused shortcodes', 'clean-unused-shortcodes' ),
			'manage_options',
			'clean-unused-shortcodes',
			array( $this, 'cus_tool_page' ),
		);
	}

	/**
	 * Render tool page.
	 *
	 * @return void
	 */
	public function cus_tool_page() {
		add_thickbox();
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);
		require_once CUS_PLUGIN_PATH . 'admin/partials/clean-unused-shortcodes-admin-display.php';
	}

	/**
	 * Clean post shortcodes by ID.
	 *
	 * @param  mixed  $post_id post ID to clean it.
	 * @param  string $content post content to strip.
	 * @return void
	 */
	private function cus_clean_post_shortcodes( $post_id, $content ) {
		global $shortcode_tags;
		$active_shortcodes = ( is_array( $shortcode_tags ) && ! empty( $shortcode_tags ) ) ? array_keys( $shortcode_tags ) : array();
		if ( ! empty( $active_shortcodes ) ) {
			$active_regex    = implode( '|', $active_shortcodes );
			$striped_content = preg_replace( "~(?:\[/?)(?!(?:$active_regex))[^/\]]+/?\]~s", '', $content );
		} else {
			$striped_content = preg_replace( '~(?:\[/?)[^/\]]+/?\]~s', '', $content );
		}
		$data = array(
			'ID'           => $post_id,
			'post_content' => $striped_content,
		);
		wp_update_post( $data );
	}
	/**
	 * Clean shortcodes AJAX.
	 *
	 * @return void
	 */
	public function cus_clean_shortcodes() {
		if ( ! isset( $_REQUEST['nonce'] ) ) {
			wp_send_json_error( __( 'Not valid nonce.', 'clean-unused-shortcodes' ) );
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), 'cus_ajax_nonce' ) ) {
			wp_send_json_error( __( 'Not valid nonce.', 'clean-unused-shortcodes' ) );
		}
		$types = isset( $_REQUEST['types'] ) && ! empty( $_REQUEST['types'] ) && is_array( $_REQUEST['types'] ) ? $_REQUEST['types'] : '';// phpcs:ignore
		$types = in_array( 'all', $types, true ) ? 'any' : $types;
		$args  = array(
			'post_type'      => $types,
			'posts_per_page' => -1,
		);
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->cus_clean_post_shortcodes( get_the_ID(), get_the_content() );
				wp_reset_postdata();
			}
		}
		wp_send_json_success( __( 'Cleaned successfully.', 'clean-unused-shortcodes' ) );
	}

	/**
	 * Clean all shortcodes AJAX.
	 *
	 * @return void
	 */
	public function cus_clean_all_shortcode() {
		check_ajax_referer( 'clean_shortcodes_nonce', '_wpnonce' );
		$posts = get_posts(
			array(
				'post_type'   => 'any',
				'numberposts' => -1,
				'post_status' => 'any',
			)
		);
		foreach ( $posts as $post ) {
			$updated_content = $this->cus_clean_all_shortcode_content( $post->post_content );
			if ( $updated_content !== $post->post_content ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $updated_content,
					)
				);
			}
		}
		wp_send_json_success( __( 'All shortcodes removed successfully!', 'clean-unused-shortcodes' ) );
	}


	/**
	 * Clean all unused shortcodes from the given content.
	 *
	 * @param string $content The content to clean.
	 * @return string The cleaned content.
	 */
	private function cus_clean_all_shortcode_content( $content ) {
		global $shortcode_tags;

		// Get all registered shortcodes.
		$active_shortcodes = is_array( $shortcode_tags ) ? array_keys( $shortcode_tags ) : array();

		// Escape and create regex for active shortcodes.
		$escaped_active_shortcodes = array_map( 'preg_quote', $active_shortcodes, array_fill( 0, count( $active_shortcodes ), '/' ) );
		$active_regex              = implode( '|', $escaped_active_shortcodes );

		// Regex to match unused shortcodes (self-closing and paired).
		$unused_shortcode_pattern = '/\[(?!' . $active_regex . ')\w+(?:[^\[\]]*|(?R))*?\[\/\w+\]|\[(?!' . $active_regex . ')\w+[^\]]*\/\]/';

		// Use a loop to remove all instances of unused shortcodes.
		while ( preg_match( $unused_shortcode_pattern, $content ) ) {
			$content = preg_replace( $unused_shortcode_pattern, '', $content );
		}

		// Clean up any leftover incomplete shortcode tags (opening or closing only).
		$leftover_shortcode_pattern = '/\[(?!' . $active_regex . ')\w+[^\]]*\]|\[\/(?!' . $active_regex . ')\w+\]/';
		$content                    = preg_replace( $leftover_shortcode_pattern, '', $content );

		return trim( $content );
	}


	/**
	 * Fetch shortcodes AJAX.
	 *
	 * @return void
	 */
	public function cus_fetch_shortcodes() {
		check_ajax_referer( 'clean_shortcodes_nonce', '_wpnonce' );
		$used_shortcodes   = array();
		$unused_shortcodes = array();
		global $shortcode_tags;
		$registered_shortcodes = array_keys( $shortcode_tags );
		$posts                 = get_posts(
			array(
				'post_type'   => 'any',
				'numberposts' => -1,
				'post_status' => 'any',
			)
		);
		$shortcode_usage       = array();
		foreach ( $posts as $post ) {
			preg_match_all( '/\[(\w+)[^\]]*\]/', $post->post_content, $matches );
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $shortcode ) {
					if ( ! isset( $shortcode_usage[ $shortcode ] ) ) {
						$shortcode_usage[ $shortcode ] = array();
					}
					$shortcode_usage[ $shortcode ][] = array(
						'title'     => $post->post_title,
						'post_type' => $post->post_type,
						'edit_link' => admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
						'view_link' => get_permalink( $post->ID ),
					);
				}
			}
		}
		foreach ( $shortcode_usage as $shortcode => $locations ) {
			if ( in_array( $shortcode, $registered_shortcodes, true ) ) {
				$used_shortcodes[] = $shortcode;
			} else {
				$unused_shortcodes[] = array(
					'name'      => $shortcode,
					'locations' => $locations,
				);
			}
		}
		wp_send_json_success(
			array(
				'used_shortcodes'   => $used_shortcodes,
				'unused_shortcodes' => $unused_shortcodes,
			)
		);
	}

	/**
	 * Handle AJAX request to clean a shortcode.
	 */
	public function cus_clean_shortcode() {
		// Verify the nonce for security.
		check_ajax_referer( 'clean_shortcodes_nonce', '_wpnonce' );

		// Check if the shortcode parameter is provided.
		if ( ! isset( $_POST['shortcode'] ) || empty( $_POST['shortcode'] ) ) {
			wp_send_json_error( __( 'No shortcode provided.', 'clean-unused-shortcodes' ) );
		}

		$shortcode = sanitize_text_field( wp_unslash( $_POST['shortcode'] ) );

		// Fetch all posts that might contain the shortcode.
		$posts = get_posts(
			array(
				'post_type'   => 'any',
				'numberposts' => -1,
				'post_status' => 'any',
			)
		);

		$updated_posts = 0;

		// Loop through each post and remove the shortcode.
		foreach ( $posts as $post ) {
			if ( strpos( $post->post_content, '[' . $shortcode ) !== false ) {
				$updated_content = $this->cus_clean_shortcode_content( $post->post_content, $shortcode );
				// Only update the post if changes are detected.
				if ( $updated_content !== $post->post_content ) {
					wp_update_post(
						array(
							'ID'           => $post->ID,
							'post_content' => $updated_content,
						)
					);
					$updated_posts++;
				}
			}
		}

		// Send success or error response.
		if ( $updated_posts > 0 ) {
			wp_send_json_success(
				array(
					'message'       => __( 'Shortcode removed successfully!', 'clean-unused-shortcodes' ),
					'updated_posts' => $updated_posts,
				)
			);
		} else {
			wp_send_json_error( __( 'Shortcode not found in any posts.', 'clean-unused-shortcodes' ) );
		}
	}

	/**
	 * Clean shortcodes from the given content.
	 *
	 * @param string $content The content to clean.
	 * @param string $shortcode The shortcode to remove.
	 * @return string The cleaned content.
	 */
	private function cus_clean_shortcode_content( $content, $shortcode ) {
		// Escape special characters in the shortcode name for safe regex usage.
		$escaped_shortcode = preg_quote( $shortcode, '/' );

		// Pattern to handle nested shortcodes and remove them.
		$pattern =
			'/\[' . $escaped_shortcode .
			'(?:[^\[\]]*|(?R))*?' .
			'\[\/' . $escaped_shortcode . '\]/';

		// Use a loop to remove all instances of nested shortcodes.
		while ( preg_match( $pattern, $content ) ) {
			$content = preg_replace( $pattern, '', $content );
		}

		// Remove self-closing shortcodes.
		$self_closing_pattern = '/\[' . $escaped_shortcode . '(?:\s[^\]]*)?\/\]/';
		$content              = preg_replace( $self_closing_pattern, '', $content );

		// Remove any leftover partial opening/closing tags.
		$content = preg_replace( '/\[' . $escaped_shortcode . '[^\]]*\]/', '', $content );
		$content = preg_replace( '/\[\/' . $escaped_shortcode . '\]/', '', $content );

		return trim( $content );
	}

}
