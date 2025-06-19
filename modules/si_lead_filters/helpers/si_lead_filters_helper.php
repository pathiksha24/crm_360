<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Function that format lead status for the final user
 * @param  string  $id    status id
 * @param  boolean $text
 * @param  boolean $clean
 * @return string
 */
function si_format_lead_status($status, $text = false, $clean = false)
{
	if (!is_array($status)) {
		$status = si_get_lead_status_by_id($status);
	}

	$status_name = $status['name'];

	if ($clean == true) {
		return $status_name;
	}

	$style = '';
	$class = '';
	if ($text == false) {
		$style = 'border: 1px solid ' . $status['color'] . ';color:' . $status['color'] . ';';
		$class = 'label';
	} else {
		$style = 'color:' . $status['color'] . ';';
	}

	return '<span class="' . $class . '" style="' . $style . '">' . $status_name . '</span>';
}

/**
 * Get lead status by passed lead id
 * @param  mixed $id lead id
 * @return array
 */
function si_get_lead_status_by_id($id)
{
	$CI       = &get_instance();
	$statuses = $CI->leads_model->get_status();

	$status = [
		'id'         => 0,
		'bg_color'   => '#333',
		'text_color' => '#333',
		'name'       => '[Status Not Found]',
		'order'      => 1,
	];

	foreach ($statuses as $s) {
		if ($s['id'] == $id) {
			$status = $s;
			
		break;
		}
	}

	return $status;
}



