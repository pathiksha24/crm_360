<div class="modal fade" id="task_type" tabindex="-1" role="dialog">
    <div class="modal-dialog">
		<?= form_open(
			admin_url(PROJECT_MANAGEMENT_ENHANCEMENTS_MODULE_NAME.'/projects/add_task_type'),
			['id' => 'task_type_form']
		); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span
                            class="edit-title"><?= _l('edit_task_type'); ?></span>
                    <span
                            class="add-title"><?= _l('new_task_type'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
						<?= form_hidden('project_id', $project->id); ?>
						<?= render_input(
							'edit_task_type',
							'',
							'',
							'hidden',
							['type' => 'hidden', 'id' => 'additional_task_type']
						); ?>
						<?= render_input('name', _l('project_task_type')); ?>
						<?= render_color_picker('label_color', _l('project_task_type_color'), '#000000'); ?>
						<?= render_color_picker('text_color', _l('project_task_type_text_color'), '#000000'); ?>
						<?= render_input('sort_order', _l('task_type_sort_order'), 0, 'number'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"
                        data-loading-text="<?= _l('wait_text'); ?>"
                        data-autocomplete="off"
                        data-form="#task_type_form"><?= _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
		<?= form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
    function new_task_type() {
        let action = admin_url + "project_management_enhancements/projects/add_task_type";
        $("#task_type_form").attr('action', action);
        $("#task_type .add-title").removeClass("hide");
        $("#task_type .edit-title").addClass("hide");

        $('#task_type_form').trigger('reset');
        $("#task_type").modal("show");
    }

    function delete_task_type(project_id, task_type_id) {
        var result = confirm("Are you sure you want to perform this action?");
        if (result) {
            //Logic to delete the item
            var url = admin_url + "project_management_enhancements/projects/delete_task_type";
            var data = {
                project_id,
                task_type_id
            }
            $.post(url, data).done(function (response) {
                response = JSON.parse(response);
                if (response.success == true) {
                    alert_float("success", response.message);
                } else if (response.message) {
                    alert_float("danger", response.message);
                }
                $(".table-project-task-types").DataTable().ajax.reload(null, false);

                return true;
            });
        }
        return false;
    }

    function edit_task_type(invoker, project_id, task_type_id) {
        let action = admin_url + "project_management_enhancements/projects/edit_task_type";
        $("#task_type_form").attr('action', action);

        $("#edit_task_type").val(task_type_id);
        $('#task_type_form input[name="name"]').val($(invoker).data("name"));
        $('#task_type_form input[name="sort_order"]').val(parseInt($(invoker).data("sort-order")));

        $('#task_type_form input[name="label_color"]').val($(invoker).data("label-color")).trigger('change');

        $('#task_type_form input[name="text_color"]').val($(invoker).data("text-color")).trigger('change');

        $("#task_type").modal("show");
        $("#task_type .add-title").addClass("hide");
        $("#task_type .edit-title").removeClass("hide");
    }
</script>