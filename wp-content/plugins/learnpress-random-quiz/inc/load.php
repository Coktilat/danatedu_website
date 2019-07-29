<?php
/**
 * Plugin load class.
 *
 * @author   ThimPress
 * @package  LearnPress/Random-Quiz/Classes
 * @version  3.0.0
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LP_Addon_Random_Quiz' ) ) {
	/**
	 * Class LP_Addon_Random_Quiz
	 */
	class LP_Addon_Random_Quiz extends LP_Addon {

		/**
		 * @var string
		 */
		public $version = LP_ADDON_RANDOM_QUIZ_VER;

		/**
		 * @var string
		 */
		public $require_version = LP_ADDON_RANDOM_QUIZ_REQUIRE_VER;

		/**
		 * LP_Addon_Random_Quiz constructor.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Define Learnpress Random Quiz constants.
		 *
		 * @since 3.0.0
		 */
		protected function _define_constants() {
			define( 'LP_RANDOM_QUIZ_PATH', dirname( LP_ADDON_RANDOM_QUIZ_FILE ) );
		}

		protected function _init_hooks() {
			add_filter( 'learn_press_quiz_general_meta_box', array( $this, 'admin_meta_box' ) );
			add_filter( 'learn-press/quiz/questions', array( $this, 'random_questions' ), 10, 2 );
			add_action( 'learn-press/user/quiz-redone', array( $this, 'update_user_questions' ), 100, 3 );
		}

		/**
		 * Add random quiz option in quiz meta box.
		 *
		 * @param $meta_boxes
		 *
		 * @return mixed
		 */
		public function admin_meta_box( $meta_boxes ) {
			$random_quiz = array(
				array(
					'name' => __( 'Random Questions', 'learnpress-random-quiz' ),
					'id'   => '_lp_random_mode',
					'type' => 'yes_no',
					'desc' => __( 'Mix all available questions in this quiz.', 'learnpress-random-quiz' ),
					'std'  => 'no'
				)
			);

			foreach ( $random_quiz as $field ) {
				// add prerequisites option on top of admin settings course
				array_unshift( $meta_boxes['fields'], $field );
			}

			return $meta_boxes;
		}

		/**
		 * Update user questions.
		 *
		 * @param $quiz_id
		 * @param $course_id
		 * @param $user_id
		 */
		public function update_user_questions( $quiz_id, $course_id, $user_id ) {
			global $wpdb;
			$item = null;

			switch ( current_action() ) {
				case 'learn-press/user/quiz-redone':
					$item = $wpdb->get_row(
						$wpdb->prepare( "
						SELECT * FROM {$wpdb->prefix}learnpress_user_items
						WHERE item_id = %d
							AND user_id = %d
							AND ref_id = %d
						ORDER BY user_item_id DESC
					", $quiz_id, $user_id, $course_id )
					);
					break;
				case 'learn-press/user/quiz-started':
					break;
			}
			if ( ! $item ) {
				return;
			}
			if ( ! $item->status == 'started' ) {
				return;
			}

			$random_quiz = get_user_meta( $user_id, 'random_quiz', true );
			$quiz        = LP_Quiz::get_quiz( $quiz_id );
			if ( $quiz && $questions = $quiz->get_questions() ) {
				$questions = array_keys( $questions );
				shuffle( $questions );
				$question_id = reset( $questions );

				// set user current question
				$user = learn_press_get_current_user();
				$user_course = $user->get_course_data( $course_id );
				$item_quiz = $user_course->get_item($quiz_id);

				$item_quiz->set_meta( '_current_question', $question_id );
				$item_quiz->update_meta();

				learn_press_update_user_item_meta( $item->user_item_id, 'current_question', $question_id );
				if ( empty( $random_quiz ) ) {
					$random_quiz = array( $quiz_id => $questions );
				} else {
					$random_quiz[ $quiz_id ] = $questions;
				}
				update_user_meta( $user_id, 'random_quiz', $random_quiz );
			}
		}

		/**
		 * Random quiz questions.
		 *
		 * @param $quiz_questions
		 * @param $quiz_id
		 *
		 * @return array
		 */
		public function random_questions( $quiz_questions, $quiz_id ) {

			if ( get_post_meta( $quiz_id, '_lp_random_mode', true ) == 'yes' ) {

				// get user meta random quiz
				$random_quiz = get_user_meta( get_current_user_id(), 'random_quiz', true );
				if ( is_admin() || empty( $random_quiz ) || empty( $random_quiz[ $quiz_id ] ) ) {
					return $quiz_questions;
				}
				$questions = array();
				if ( array_key_exists( $quiz_id, $random_quiz ) && sizeof( $random_quiz[ $quiz_id ] ) == sizeof( $quiz_questions ) ) {
					foreach ( $random_quiz[ $quiz_id ] as $question_id ) {
						if ( $question_id ) {
							$questions[ $question_id ] = $question_id;
						}
					}
				} else {
					$question_ids = array_keys( $quiz_questions );
					shuffle( $question_ids );
					$random_quiz[ $quiz_id ] = $question_ids;
					$questions               = array();
					foreach ( $question_ids as $id ) {
						$questions[ $id ] = $quiz_questions[ $id ];
					}
				}

				return $questions;
			}

			return $quiz_questions;

		}
	}
}

add_action( 'plugins_loaded', array( 'LP_Addon_Random_Quiz', 'instance' ) );
