<?php defined('BASEPATH') or exit('No direct script access allowed');
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h3><?php echo $title; ?></h3>
                        <hr>
                        <div class="_buttons">
                            <?php if (is_admin()) {
                                echo form_open(admin_url('auto_assign_leads/auto_assign'), ['id' => 'auto_assign__form']);
                            ?>
                                <div class="col-md-2">
                                    <?php echo render_select('staff_ids[]', $staff, ['staffid', ['firstname', 'lastname']], 'staff', '', ['data-width' => '100%', 'data-none-selected-text' => _l('staff'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_select('service_ids[]', $services, ['id', 'name'], 'services', '', ['data-width' => '100%', 'data-none-selected-text' => _l('services'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_select('source_ids[]', $sources, ['id', 'name'], 'sources', '', ['data-width' => '100%', 'data-none-selected-text' => _l('sources'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_input('max_leads_to_distribute', 'max_leads_to_distribute', get_option('max_leads_to_distribute'), 'number'); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo render_input('max_leads_for_each_staff', 'max_leads_for_each_staff', get_option('max_leads_for_each_staff'), 'number'); ?>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" style="margin-top: 24px" class="btn btn-success _delete">Auto Assign</button>
                                </div>
                            <?php echo form_close();
                            } ?>
                            <?php if(get_option('auto_assign_leads')){ echo form_open(admin_url('auto_assign_leads/revert_last_auto_assign'), ['id' => 'auto_assign__form']); ?>
                                <div class="col-md-1">
                                    <button type="submit" style="margin-top: 24px;margin-left: 10px" class="btn btn-danger _delete">Undo</button>
                                </div>
                            <?php echo form_close(); } ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr>

                        <div class="col-md-3 leads-filter-column">
                            <?php echo render_date_input('lead_log_date', 'utility_activity_log_filter_by_date', '', array(), array(), '', 'activity-log-date'); ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <table class="table table-auto_assign_leads dt-table scroll-responsive">
                            <thead>
                                <th><?php echo htmlspecialchars(_l('staff')); ?></th>
                                <th><?php echo htmlspecialchars(_l('service')); ?></th>
                                <th><?php echo htmlspecialchars(_l('total_assigned_leads')); ?></th>
                                <th><?php echo htmlspecialchars(_l('date')); ?></th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function() {

        $('.table-auto_assign_leads').DataTable().destroy();
        initDataTable('.table-auto_assign_leads', admin_url + 'auto_assign_leads/table');

        let rules = {
            'staff_ids[]': 'required',
            'service_ids[]': 'required',
            'source_ids[]': 'required',
            'max_leads_to_distribute': 'required',
            'max_leads_for_each_staff': 'required',
        }

        appValidateForm($('#auto_assign__form'), rules);

    });

    $("body").on("change", 'input[name="lead_log_date"]', function() {

        $('.table-auto_assign_leads').DataTable().destroy();

        var ActivityLogServerParams = [];
        ActivityLogServerParams["lead_log_date"] = '[name="lead_log_date"]';

        initDataTable('.table-auto_assign_leads', admin_url + 'auto_assign_leads/table', [], [], ActivityLogServerParams, [1, 'desc']);
    });
</script>