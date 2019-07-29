<?php
/*
Plugin Name: LearnPress - Coming Soon Courses
Plugin URI: http://thimpress.com/learnpress
Description: Set a course is "Coming Soon" and schedule to public.
Author: ThimPress
Version: 3.0.2
Author URI: http://thimpress.com
Tags: learnpress
Text Domain: learnpress-coming-soon-courses
Domain Path: /languages/
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

define( 'LP_ADDON_COMING_SOON_COURSES_FILE', __FILE__ );
define( 'LP_ADDON_COMING_SOON_COURSES_VER', '3.0.0' );
define( 'LP_ADDON_COMING_SOON_COURSES_REQUIRE_VER', '3.0.0' );
define( 'LP_COMING_SOON_VER', '3.0.0' );

/**
 * Class LP_Addon_Coming_Soon_Courses_Preload
 */
class LP_Addon_Coming_Soon_Courses_Preload {

	/**
	 * LP_Addon_Coming_Soon_Courses_Preload constructor.
	 */
	public function __construct() {
		add_action( 'learn-press/ready', array( $this, 'load' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Load addon
	 */
	public function load() {
		LP_Addon::load( 'LP_Addon_Coming_Soon_Courses', 'inc/load.php', __FILE__ );
		remove_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notice
	 */
	public function admin_notices() {
		?>
        <div class="error">
            <p><?php echo wp_kses(
					sprintf(
						__( '<strong>%s</strong> addon version %s requires %s version %s or higher is <strong>installed</strong> and <strong>activated</strong>.', 'learnpress-coming-soon-courses' ),
						__( 'LearnPress Coming Soon Courses', 'learnpress-coming-soon-courses' ),
						LP_ADDON_COMING_SOON_COURSES_VER,
						sprintf( '<a href="%s" target="_blank"><strong>%s</strong></a>', admin_url( 'plugin-install.php?tab=search&type=term&s=learnpress' ), __( 'LearnPress', 'learnpresscoming-soon-courses' ) ),
						LP_ADDON_COMING_SOON_COURSES_REQUIRE_VER
					),
					array(
						'a'      => array(
							'href'  => array(),
							'blank' => array()
						),
						'strong' => array()
					)
				); ?>
            </p>
        </div>
		<?php
	}
}

new LP_Addon_Coming_Soon_Courses_Preload();