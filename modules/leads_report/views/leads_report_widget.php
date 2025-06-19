<div class="widget" id="widget-<?php echo basename(__FILE__, ".php"); ?>"
     data-name="<?php echo htmlspecialchars(_l('project_roadmap')); ?>">
    <link href="<?php echo module_dir_url('leads_report','assets/css/global.css'); ?>" rel="stylesheet" />
    <div class="panel_s user-data">
        <div class="panel-body">
            <div class="widget-dragger"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <p class="text-dark text-uppercase bold"><?php echo _l('leads_report'); ?></p>
                    </div>
                    <div class="col-md-6 pull-right">
                        <div class="form-group" id="report-time">
                            <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br/>
                            <select class="selectpicker" name="months-report" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""><?php echo _l('all'); ?></option>
                                <option value="this_week"><?php echo _l('this_week'); ?></option>
                                <option value="last_week"><?php echo _l('last_week'); ?></option>
                                <option value="this_month"><?php echo _l('this_month'); ?></option>
                                <option value="last_month"><?php echo _l('last_month'); ?></option>
                                <option value="this_year"><?php echo _l('this_year'); ?></option>
                                <option value="last_year"><?php echo _l('last_year'); ?></option>
                                <option value="custom"><?php echo _l('period_datepicker'); ?></option>
                            </select>
                        </div>
                        <div id="date-range" class="hide mbot15">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo render_date_input('report-from','report_sales_from_date') ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_date_input('report-to','report_sales_to_date') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="clearfix"></div>
                    <hr class="mtop15"/>
                </div>
                <div class="col-md-12">
                    <table class="table table-leads_report dt-table scroll-responsive">
                        <thead>
                        <th><?php echo htmlspecialchars(_l('staff_name')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total_assigned_leads')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total_eligible_leads')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total_non_eligible_leads')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total_closed_leads')); ?></th>
                        <th><?php echo htmlspecialchars(_l('percentage')); ?></th>
                        <th><?php echo htmlspecialchars(_l('net')); ?></th>
                        </thead>
                        <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
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