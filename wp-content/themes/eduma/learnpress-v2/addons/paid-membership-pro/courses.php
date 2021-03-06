<?php if( wp_is_mobile() ) : ?>
	<?php get_template_part( 'learnpress-v2/addons/paid-membership-pro/courses-mobile' ); ?>
<?php else: ?>
	<?php
	$levels = lp_pmpro_get_all_levels();
	global $current_user;

	$list_courses = array();

	foreach ( $levels as $index => $level ) {
		$the_query = lp_pmpro_query_course_by_level( $level->id );
		if ( !empty( $the_query->posts ) ) {
			foreach ( $the_query->posts as $key => $course ) {
				$list_courses[$course->ID]['link'] = '<a href="' . get_the_permalink( $course->ID ) . '" >' . get_the_title( $course->ID ) . '</a>';
				if ( empty( $list_courses[$course->ID]['level'] ) ) {
					$list_courses[$course->ID]['level'] = array();
				}
				if ( !in_array( $level->id, $list_courses[$course->ID]['level'] ) ) {
					$list_courses[$course->ID]['level'][] = $level->id;
				}

			}
		}

	}
	
	asort( $list_courses );
	
	?>
	<table class="lp-pmpro-membership-list">
		<thead>
		<tr class="lp-pmpro-header">
			<th class="header-list-main list-main"></th>
			<?php

			$class_count = ' has-'.count( $levels);
            $icon = get_option('thim_level_'.$level->id) ? 'background-image: url("'.get_option('thim_level_'.$level->id).'")' : '';

			foreach ( $levels as $index => $level ):
				$current_level = false;
				if ( isset( $current_user->membership_level->ID ) ) {
					if ( $current_user->membership_level->ID == $level->id ) {
						$current_level = true;
					}
				}

				?>

				<th style="<?php echo $icon;?>" class="header-item list-item<?php echo $class_count.' position-'.$index; ?>">
					<h2 class="lp-title"><?php echo esc_html( $level->name ); ?></h2>
					<?php
					if( !empty($level->description)) {
						echo '<div class="lp-desc">'.$level->description.'</div>';
					}
					?>
					<div class="lp-price">
						<?php if ( pmpro_isLevelFree( $level ) ): ?>
							<?php esc_html_e( 'Free', 'eduma' ); ?>
						<?php else: ?>
							<?php
							$cost_text = pmpro_getLevelCost( $level, true, true );
							echo ent2ncr( $cost_text );
							?>
						<?php endif; ?>
					</div>
				</th>
			<?php endforeach; ?>
		</tr>
		</thead>
		<tbody class="lp-pmpro-main">
		<tr class="item-row">
			<td class="list-main item-td item-desc"><?php esc_html_e( 'Number of courses', 'eduma' ); ?></td>
			<?php
			foreach ( $levels as $index => $level ) {
				$the_query = lp_pmpro_query_course_by_level( $level->id );
				$count     = count( $the_query->posts );
				echo '<td class="list-item item-td">' . esc_html( $count ) . '</td>';
			}
			?>
		</tr>

		<tr class="item-row">
			<td class="list-main item-td item-desc"><?php esc_html_e( 'Time', 'eduma' ); ?></td>
			<?php foreach ( $levels as $index => $level ) {
				$expired = '';
				if ( $level->expiration_number ) {
					$expired = sprintf( __( "%d %s", "eduma" ), $level->expiration_number, pmpro_translate_billing_period( $level->expiration_period, $level->expiration_number ) );
				}
				echo '<td class="list-item item-td">' . esc_html( $expired ) . '</td>';
			}
			?>
		</tr>

		<?php
		if ( !empty( $list_courses ) ) {
			foreach ( $list_courses as $key => $course_item ) {
				echo '<tr class="item-row">';
				echo '<td class="list-main item-td">' . $course_item['link'] . '</td>';
				foreach ( $levels as $index => $level ) {
					if ( in_array( $level->id, $course_item['level'] ) ) {
						echo '<td class="list-item item-td item-check "><i class="fa fa-check"></i></td>';
					} else {
						echo '<td class="list-item item-td item-none"><i class="fa fa-remove"></i></td>';
					}
				}
				echo '</tr>';
			}
		}
		?>
		</tbody>

		<tfoot class="lp-pmpro-footer">
		<tr>
			<td class="footer-left-main list-main"></td>
			<?php foreach ( $levels as $index => $level ):
				$current_level = false;

				if ( isset( $current_user->membership_level->ID ) ) {
					if ( $current_user->membership_level->ID == $level->id ) {
						$current_level = true;
					}
				}
				?>
				<td class="list-item">
					<?php if ( empty( $current_user->membership_level->ID ) || !$current_level ) { ?>
						<a class="pmpro_btn pmpro_btn-select" href="<?php echo pmpro_url( 'checkout', '?level=' . $level->id, 'https' ) ?>"><?php _e( 'GET IT NOW', 'eduma' ); ?></a>
					<?php } elseif ( $current_level ) { ?>
						<?php
						if ( pmpro_isLevelExpiringSoon( $current_user->membership_level ) && $current_user->membership_level->allow_signups ) {
							?>
							<a class="pmpro_btn pmpro_btn-select"
							   href="<?php echo pmpro_url( 'checkout', '?level=' . $level->id, 'https' ) ?>"><?php _e( 'Renew', 'eduma' ); ?></a>
							<?php
						} else {
							?>
							<a class="pmpro_btn disabled" href="<?php echo pmpro_url( 'account' ) ?>"><?php _e( 'Your Level', 'eduma' ); ?></a>
							<?php
						}
						?>

					<?php } ?>
				</td>
			<?php endforeach; ?>
		</tr>
		</tfoot>
	</table>
<?php endif;
