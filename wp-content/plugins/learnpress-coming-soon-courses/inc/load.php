<?php
/**
 * Plugin load class.
 *
 * @author   ThimPress
 * @package  LearnPress/Coming-Soon-Courses/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Addon_Coming_Soon_Courses' ) ) {
	/**
	 * Class LP_Addon_Coming_Soon_Courses.
	 */
	class LP_Addon_Coming_Soon_Courses extends LP_Addon {

		/**
		 * @var RW_Meta_Box
		 */
		public $metabox = null;

		/**
		 * Hold the course ids is coming soon
		 *
		 * @var array
		 */
		protected $_coming_soon_courses = array();

		/**
		 * @var null
		 */
		protected $_course_coming_soon = null;

		/**
		 * @var string
		 */
		public $version = LP_ADDON_COMING_SOON_COURSES_VER;

		/**
		 * @var string
		 */
		public $require_version = LP_ADDON_COMING_SOON_COURSES_REQUIRE_VER;

		/**
		 * LP_Addon_Coming_Soon_Courses constructor.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Define constants.
		 */
		protected function _define_constants() {
			define( 'LP_ADDON_COMING_SOON_COURSES_PATH', dirname( LP_ADDON_COMING_SOON_COURSES_FILE ) );
			define( 'LP_ADDON_COMING_SOON_COURSES_TEMP', LP_ADDON_COMING_SOON_COURSES_PATH . '/templates/' );
		}

		/**
		 * Includes files.
		 */
		protected function _includes() {
			require_once LP_ADDON_COMING_SOON_COURSES_PATH . '/inc/functions.php';
		}

		/**
		 * Init hooks.
		 */
		protected function _init_hooks() {

			add_filter( 'learn-press/admin-course-tabs', array( $this, 'add_course_tab' ) );

			add_filter( 'learn_press_get_template', array( $this, 'change_default_template' ), 100, 5 );
			add_filter( 'learn_press_get_template_part', array( $this, 'change_content_course_template' ), 100, 3 );

			add_action( 'learn_press_content_coming_soon_meta_details', array(
				$this,
				'coming_soon_meta_details'
			), 10 );
			add_action( 'learn_press_content_coming_soon_content_tabs', array(
				$this,
				'coming_soon_content_tabs'
			), 10 );
			add_action( 'learn_press_content_coming_soon_enroll_button', array(
				$this,
				'coming_soon_enroll_button'
			), 10 );
			add_action( 'learn_press_content_coming_soon_message', array( $this, 'coming_soon_message' ), 10 );
			add_action( 'learn_press_content_coming_soon_countdown', array( $this, 'coming_soon_countdown' ), 10 );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Assets.
		 */
		public function enqueue_scripts() {

			wp_enqueue_style( 'lp-coming-soon-course', $this->get_plugin_url( 'assets/css/coming-soon-course.css' ) );
			wp_enqueue_script( 'lp-jquery-mb-coming-soon', $this->get_plugin_url( 'assets/js/jquery.mb-coming-soon.min.js' ), array( 'jquery' ) );
			wp_enqueue_script( 'lp-coming-soon-course', $this->get_plugin_url( 'assets/js/coming-soon-course.js' ) );

			$course = learn_press_get_course();
			if ( ! $course ) {
				return;
			}
			$course_id = $course->get_id();
            $translation_array = array(
                'days'    => __( 'days', 'learnpress-coming-soon-courses' ),
                'hours'   => __( 'hours', 'learnpress-coming-soon-courses' ),
                'minutes' => __( 'minutes', 'learnpress-coming-soon-courses' ),
                'seconds' => __( 'seconds', 'learnpress-coming-soon-courses' ),
            );
			if ( $this->is_coming_soon( $course_id ) ) {
				wp_localize_script( 'lp-coming-soon-course', 'lp_coming_soon_translation', $translation_array );
			}elseif( learn_press_is_courses() ){
			    $all_courses = learn_press_get_all_courses();
			    if( count($all_courses) ){
			        foreach( $all_courses as $course_item ){
			            if( learn_press_is_coming_soon($course_item) ){
                            wp_localize_script( 'lp-coming-soon-course', 'lp_coming_soon_translation', $translation_array );
                            break;
                        }
                    }
                }
            }
		}

		/**
		 * Add Coming soon tab in admin course.
		 *
		 * @param $tabs
		 *
		 * @return array
		 */
		public function add_course_tab( $tabs ) {
			$forum = array( 'course_coming_soon' => new RW_Meta_Box( self::course_coming_soon_meta_box() ) );

			return array_merge( $tabs, $forum );
		}

		/**
		 * Coming soon course meta box.
		 *
		 * @return mixed
		 */
		public function course_coming_soon_meta_box() {

			// for dependent options
			$visibility = array(
				'state'       => 'show',
				'conditional' => array(
					array(
						'field'   => '_lp_coming_soon',
						'compare' => '==',
						'value'   => 'yes'
					)
				)
			);

			$meta_box = array(
				'id'       => 'course_coming_soon',
				'title'    => __( 'Coming soon', 'learnpress-coming-soon-courses' ),
				'icon'     => 'dashicons-clock',
				'priority' => 'high',
				'pages'    => array( LP_COURSE_CPT ),
				'fields'   => array(
					array(
						'name' => __( 'Enable', 'learnpress-coming-soon-courses' ),
						'id'   => '_lp_coming_soon',
						'type' => 'yes-no',
						'desc' => __( 'Enable coming soon mode.', 'learnpress-coming-soon-courses' ),
						'std'  => 'no',
					),
					array(
						'name'       => __( 'Message', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_msg',
						'type'       => 'wysiwyg',
						'editor'     => true,
						'desc'       => __( 'The coming soon message will show in single course page.', 'learnpress-coming-soon-courses' ),
						'std'        => __( 'This course will coming soon', 'learnpress-coming-soon-courses' ),
						'visibility' => $visibility
					),
					array(
						'name'       => __( 'Coming soon end time', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_end_time',
						'type'       => 'datetime',
						'desc'       => __( 'Set end time coming soon.', 'learnpress-coming-soon-courses' ),
						'visibility' => $visibility
					),
					array(
						'name'       => __( 'Show Countdown', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_countdown',
						'type'       => 'yes-no',
						'desc'       => __( 'Show countdown counter.', 'learnpress-coming-soon-courses' ),
						'std'        => 'no',
						'visibility' => $visibility
					),
					array(
						'name'       => __( 'Show DateTime Text', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_showtext',
						'type'       => 'yes-no',
						'desc'       => __( 'Show date and time text (days, hours, minutes, seconds) on single course page.', 'learnpress-coming-soon-courses' ),
						'std'        => 'no',
						'visibility' => $visibility
					),
					array(
						'name'       => __( 'Show Meta', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_metadata',
						'type'       => 'yes-no',
						'desc'       => __( 'Show meta data (such as info about Instructor, price, so on) of the course.', 'learnpress-coming-soon-courses' ),
						'std'        => 'no',
						'visibility' => $visibility
					),
					array(
						'name'       => __( 'Show Details', 'learnpress-coming-soon-courses' ),
						'id'         => '_lp_coming_soon_details',
						'type'       => 'yes-no',
						'desc'       => __( 'Show details content of the course.', 'learnpress-coming-soon-courses' ),
						'std'        => 'no',
						'visibility' => $visibility
					)
				)
			);

			return apply_filters( 'learn-press/course-coming-soon/settings-meta-box-args', $meta_box );
		}

		/**
		 * @param $located
		 * @param $template_name
		 * @param $args
		 * @param $template_path
		 * @param $default_path
		 *
		 * @return string
		 */
		public function change_default_template( $located, $template_name, $args, $template_path, $default_path ) {
			remove_filter( 'learn_press_get_template', array( $this, 'change_default_template' ), 100, 5 );
			if ( $template_name == 'content-single-course.php' ) {
				if($course    = learn_press_get_course()) {
					$course_id = $course->get_id();
					if ( $this->is_coming_soon( $course_id ) ) {
						$located = learn_press_coming_soon_course_locate_template( $template_name );
					}
				}
			}
			add_filter( 'learn_press_get_template', array( $this, 'change_default_template' ), 100, 5 );

			return $located;
		}

		/**
		 * @param $template
		 * @param $slug
		 * @param $name
		 *
		 * @return string
		 */
		public function change_content_course_template( $template, $slug, $name ) {
			if ( $slug == 'content' && $name == 'course' ) {
				$course    = learn_press_get_course();
				$course_id = $course->get_id();
				if ( $this->is_coming_soon( $course_id ) ) {
					remove_filter( 'learn_press_get_template_part', array(
						$this,
						'change_content_course_template'
					), 100, 3 );
					$template = learn_press_coming_soon_course_locate_template( "content-course.php" );
					add_filter( 'learn_press_get_template_part', array(
						$this,
						'change_content_course_template'
					), 100, 3 );
				}
			}

			return $template;
		}


		/**
		 * Display coming soon message
		 */
		public function coming_soon_message() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			if ( $this->is_coming_soon( $course_id ) && '' !== ( $message = get_post_meta( $course_id, '_lp_coming_soon_msg', true ) ) ) {
				// enable shortcode in coming message
				$message = do_shortcode( $message );
				learn_press_coming_soon_course_template( 'single-course/message.php', array( 'message' => $message ) );
			}
		}

		/**
		 * Display meta data of the course
		 */
		public function coming_soon_meta_details() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			if ( $this->is_coming_soon( $course_id ) && 'no' !== ( $details = get_post_meta( $course_id, '_lp_coming_soon_metadata', true ) ) ) {
				learn_press_course_meta_start_wrapper();
				learn_press_course_price();
				learn_press_course_instructor();
				learn_press_course_students();
				learn_press_course_meta_end_wrapper();
			}
		}

		/**
		 * Display content tabs of the course
		 */
		public function coming_soon_content_tabs() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			if ( $this->is_coming_soon( $course_id ) && 'no' !== ( $details = get_post_meta( $course_id, '_lp_coming_soon_details', true ) ) ) {
				learn_press_coming_soon_course_template( 'single-course/content-tabs.php', array() );
			}
		}

		/**
		 * Display Enroll button of the course. This need to be checked more!
		 */
		public function coming_soon_enroll_button() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			if ( $this->is_coming_soon( $course_id ) && 'no' !== ( $details = get_post_meta( $course_id, '_lp_coming_soon_enroll_button', true ) ) ) {
				learn_press_course_buttons();
			}
		}

		/**
		 * Display coming soon countdown
		 */
		public function coming_soon_countdown() {
			$course    = learn_press_get_course();
			$course_id = $course->get_id();
			$end_time  = $this->get_coming_soon_end_time( $course_id, 'Y-m-d H:i:s' );
			$datetime  = new DateTime( $end_time );
			$timezone  = get_option( 'gmt_offset' );
			$showtext  = get_post_meta( $course_id, '_lp_coming_soon_showtext', true );
			learn_press_coming_soon_course_template( 'single-course/countdown.php', array(
				'datetime' => $datetime,
				'timezone' => $timezone,
				'showtext' => $showtext
			) );
		}

		/**
		 * Check all options and return TRUE if a course has 'Coming Soon'
		 *
		 * @param int $course_id
		 *
		 * @return mixed
		 */
		public function is_coming_soon( $course_id = 0 ) {
			if ( ! $course_id && LP_COURSE_CPT == get_post_type() ) {
				$course_id = get_the_ID();
			}
			if ( empty( $this->_coming_soon_courses[ $course_id ] ) ) {
				$this->_coming_soon_courses[ $course_id ] = false;
				if ( $this->is_enable_coming_soon( $course_id ) ) {
					$end_time     = $this->get_coming_soon_end_time( $course_id );
					$current_time = current_time( 'timestamp' );

					if ( $end_time == 0 || $end_time > $current_time ) {
						$this->_coming_soon_courses[ $course_id ] = true;
					}
				}
			}

			return $this->_coming_soon_courses[ $course_id ];
		}

		/**
		 * Return TRUE if 'Coming Soon' is enabled
		 *
		 * @param int $course_id
		 *
		 * @return bool
		 */
		public function is_enable_coming_soon( $course_id = 0 ) {
			if ( ! $course_id && LP_COURSE_CPT == get_post_type() ) {
				$course_id = get_the_ID();
			}

			return 'yes' == get_post_meta( $course_id, '_lp_coming_soon', true );
		}

		/**
		 * Return expiration time of 'Coming Soon'
		 *
		 * @param int $course_id
		 * @param     string
		 *
		 * @return int
		 */
		public function get_coming_soon_end_time( $course_id = 0, $format = 'timestamp' ) {
			if ( ! $course_id && LP_COURSE_CPT == get_post_type() ) {
				$course_id = get_the_ID();
			}
			$end_time = 0;
			if ( $this->is_enable_coming_soon( $course_id ) ) {
				$end_time           = get_post_meta( $course_id, '_lp_coming_soon_end_time', true );
				$current_time       = current_time( 'timestamp' );
				$end_time_timestamp = strtotime( $end_time, $current_time );
				if ( $format == 'timestamp' ) {
					$end_time = $end_time_timestamp;
				} elseif ( $format ) {
					$end_time = date( $format, $end_time_timestamp );
				}
			}

			return $end_time;
		}

		/**
		 * Return TRUE if a course is enabled countdown
		 *
		 * @param int $course_id
		 *
		 * @return bool
		 */
		public function is_show_coming_soon_countdown( $course_id = 0 ) {
			if ( ! $course_id && LP_COURSE_CPT == get_post_type() ) {
				$course_id = get_the_ID();
			}

			return 'yes' == get_post_meta( $course_id, '_lp_coming_soon_countdown', true );
		}
	}
}

add_action( 'learn_press_ready', array( 'LP_Addon_Coming_Soon_Courses', 'instance' ) );