<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <?php echo render_date_input('staff_logs_date', 'staff_logs_filter_by_date', '', [], [], '', 'activity-log-date'); ?>
                    </div>
                    <div class="col-md-8 text-right mtop20">
                        <a class="btn btn-danger _delete"
                            href="<?php echo admin_url('staff_logs/clear_logs'); ?>"><?php echo _l('clear_staff_logs'); ?></a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('description'),
                            _l('date'),
                            _l('staff'),
                        ), 'staff_logs'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="cluster" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('staff_logs/cluster'), ['id' => 'cluster-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('edit_cluster'); ?></span>
                    <span class="add-title"><?php echo _l('new_cluster'); ?></span>
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
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-staff_logs', admin_url + 'staff_logs/table', [], [], undefined, [1, 'desc']);
        
    });
</script>
</body>

</html>