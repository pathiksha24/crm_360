<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="language" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('leads_customization/languages_crud'), ['id' => 'languages-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('edit_languages'); ?></span>
                    <span class="add-title"><?php echo _l('new_language'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name', 'name'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>
    window.addEventListener('load', function () {
        appValidateForm($("body").find('#languages-form'), {
            name: 'required'
        }, manage_leads_languages);
        $('#language').on("hidden.bs.modal", function (event) {
            $('#additional').html('');
            $('#status input[name="name"]').val('');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });
    });

    function new_language() {
        $('#language').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_language(invoker, id) {
        $('#additional').append(hidden_input('id', id));
        $('#language input[name="name"]').val($(invoker).data('name'));
        $('#language').modal('show');
        $('.add-title').addClass('hide');
    }

    function manage_leads_languages(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            window.location.reload();
        });
        return false;
    }
</script>
