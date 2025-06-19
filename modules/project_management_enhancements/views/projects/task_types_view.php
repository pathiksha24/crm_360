<a href="#" onclick="new_task_type();return false;" class="btn btn-primary mbot25">
	<i class="fa-regular fa-plus tw-mr-1"></i>
	<?= _l('new_task_type'); ?>
</a>
<div class="panel_s panel-table-full">
	<div class="panel-body">
		<?php
		$this->load->view(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME  . '/projects/task_type');
		render_datatable([
			_l('task_type_name'),
			_l('task_type_label_color'),
			_l('task_type_text_color'),
			_l('task_type_sort_order'),
            '',
		], 'project-task-types'); ?>
	</div>
</div>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- Miles Stones -->
