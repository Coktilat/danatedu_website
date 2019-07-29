<?php

class LP_Certificate_Course_Start_Date_Layer extends LP_Certificate_Datetime_Layer {
	public function apply( $data ) {

		$user = learn_press_get_user($data['user_id']);
		$course_data = $user->get_course_data($data['course_id']);
		$course_data->get_start_time();

		$this->options['text'] = ( $course_data ) ? $course_data->get_start_time()->format($this->get_format()) : $this->options['text'];
	}
}