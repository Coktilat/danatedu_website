<?php
/**
 * Restrict lesson content template.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/content-drip/restrict-content.php.
 *
 * @author  ThimPress
 * @package LearnPress/Addon/Template
 * @version 3.0.6
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

global $course;
$drip_type = get_post_meta( $course->get_id(), '_lp_content_drip_drip_type', true );

$message = '';

/**
 * @var $drip_item
 */

if ( isset( $drip_item['open_item'] ) ) {
	$message = sprintf( __( 'Sorry! You can not view this item right now. It will become available on <strong>%s</strong>.', 'learnpress-content-drip' ), $drip_item['open_item'] );
} else if ( isset( $drip_item['depends'] ) && $drip_type == 'prerequisite' ) {

	$message = __( 'Sorry! You can not view this item right now. It will become available when you completed ', 'learnpress-content-drip' );
	$message .= '<ul>';

	foreach ( $drip_item['depends']['prerequisite'] as $prerequisite_item_id ) {
		/**
		 * @var $open_time LP_Datetime
		 */
		$open_time = $drip_item['depends']['open_time'][ $prerequisite_item_id ];

		// course item
		$item = $course->get_item( $prerequisite_item_id );

		$message .= '<li><a href="' . $item->get_permalink() . '">' . ' ' . get_the_title( $prerequisite_item_id ) . '</a></li>';
	}
	$message .= '</ul>';
	if ( isset( $drip_item['depends']['type'] ) && $drip_item['depends']['type'] != 'immediately' ) {
		$message .= __( 'and ', 'learnpress-content-drip' ) . ( $drip_item['depends']['type'] == 'interval' ? __( 'after ', 'learnpress-content-drip' ) . $drip_item['depends']['interval'][0] . ' ' . $drip_item['depends']['interval'][1] . __( '(s)', 'learnpress-content-drip' ) : __( 'on ', 'learnpress-content-drip' ) . date( get_option( 'date_format' ) . ' H:i', $drip_item['depends']['date'] ) );
	}
} else if ( $drip_item != 'prerequisite' ) {
	$message = __( 'Sorry! You can not view this item right now. It will become available when you completed ', 'learnpress-content-drip' );
	foreach ( $drip_item['depends']['open_time'] as $item_id => $time ) {
		$item    = $course->get_item( $item_id );
		$message .= '<strong><a href="' . $item->get_permalink() . '">' . ' ' . get_the_title( $item_id ) . '</a></strong>';
	}

	if ( $drip_item['depends']['type'] != 'immediately' ) {
		$message .= __( ' and ', 'learnpress-content-drip' ) . ( $drip_item['depends']['type'] == 'interval' ? __( 'after ', 'learnpress-content-drip' ) . $drip_item['depends']['interval'][0] . ' ' . $drip_item['depends']['interval'][1] . __( '(s)', 'learnpress-content-drip' ) : __( 'on ', 'learnpress-content-drip' ) . date( get_option( 'date_format' ) . ' H:i', $drip_item['depends']['date'] ) );
	}

} else if ( isset( $drip_item['dependency'] ) ) {
	// for old version
	$pre_item_id = $drip_item['dependency']['pre_item_id'];
	$pre_item    = LP_Course_Item::get_item( $pre_item_id );

	$message = sprintf( wp_kses( __( 'Sorry! You can not view this item right now. It will become available when you complete <strong><a href="%s">%s</a></strong> %s. <br>', 'learnpress-content-drip' ), array(
		'a'      => array(
			'href' => array()
		),
		'strong' => array(),
		'br'     => array()
	) ), $pre_item->get_permalink(), get_the_title( $pre_item_id ), $drip_item['dependency']['item']['type'] == 'immediately' ? '' : ( $drip_item['dependency']['item']['type'] == 'specific' ? __( ' and after ', 'learnpress-content-drip' ) . date( get_option( 'date_format' ) . ' H:i', $drip_item['dependency']['item']['date'] ) : ( $drip_item['dependency']['item']['interval'][0] . ' ' . $drip_item['dependency']['item']['interval'][1] . '(s)' ) ) );

	if ( isset( $drip_item['dependency']['end_time'] ) ) {
		$message .= __( 'Complete time: ', 'learnpress-content-drip' ) . $drip_item['dependency']['end_time'];
	}
} ?>

<div class="learn-press-content-protected-message content-item-block">
	<?php echo $message; ?>
</div>

