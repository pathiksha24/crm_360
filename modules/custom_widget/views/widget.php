<?php
$this->load->model('leads_model');
$this->load->model('staff_model');
$this->load->model('leads_customization/leads_customization_model');
$sources = $this->leads_model->get_source();
$services = $this->leads_customization_model->get_services();;
$team_leaders = $this->staff_model->get('', ['is_team_leader' => true]);;
?>
<div class="widget" id="widget-custom">
    <div class="panel_s user-data">
        <div class="panel-body">
            <div class="widget-dragger"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-3">
                        <p class="text-dark text-uppercase bold"><?php echo _l('leads_report '); ?></p>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="mtop15"/>
                    <div class="col-md-3 pull-right custom_widget">
                        <div class="form-group" id="custom-report-time">
                            <label for="custom-months-report"><?php echo _l('period_datepicker'); ?></label><br/>
                            <select class="selectpicker" name="custom-months-report" data-width="100%"
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
                        <div id="custom-date-range" class="hide mbot15">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo render_date_input('custom-report-from','report_sales_from_date') ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_date_input('custom-report-to','report_sales_to_date') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 leads-filter-column custom_widget">
                        <?php
                        echo render_select('view_source', $sources, ['id', 'name'], 'sources', '', ['data-width' => '100%', 'data-none-selected-text' => _l('leads_source')], [], 'no-mbot');
                        ?>
                    </div>
                    <div class="col-md-3 leads-filter-column custom_widget">
                        <?php
                         echo render_select('view_service[]', $services, ['id', 'name'], 'services', '', ['multiple' => true, 'data-actions-box' => true, 'data-width' => '100%', 'data-none-selected-text' => _l('services')], [], '', '', true);
                        ?>
                    </div>
                    <div class="col-md-3 leads-filter-column custom_widget">
                        <?php
                        echo render_select('team_leader', $team_leaders, ['staffid', ['firstname', 'lastname']], 'team_leader', '', ['data-width' => '100%', 'data-none-selected-text' => _l('team_leader')], [], 'no-mbot');
                        ?>
                    </div>
                    <br>
                    <div class="clearfix"></div>
                    <hr class="mtop15"/>
                </div>
                <div class="col-md-12">
                    <table class="table table-custom_widget dt-table scroll-responsive">
                        <thead>
                        <th><?php echo htmlspecialchars(_l('staff_name')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total_fw_quote_int_fw')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total_closings')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total_cnc')); ?></th>
                        <th><?php echo htmlspecialchars(_l('total_lost')); ?></th>
                        </thead>
                        <tbody>
                        <tr>
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