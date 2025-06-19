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
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="javascript:void(0)" onclick="add_new_widget()" class="btn btn-info pull-left">
                                        <?php echo _l('add_new_widget'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
<!--                        <div class="_buttons">-->
<!--                            <div class="col-md-3 pull-right">-->
<!--                                <div class="form-group" id="report-time">-->
<!--                                    <label for="months-report">--><?php //echo _l('period_datepicker'); ?><!--</label><br/>-->
<!--                                    <select class="selectpicker" name="months-report" data-width="100%"-->
<!--                                            data-none-selected-text="--><?php //echo _l('dropdown_non_selected_tex'); ?><!--">-->
<!--                                        <option value="today">--><?php //echo _l('today'); ?><!--</option>-->
<!--                                        <option value="">--><?php //echo _l('all'); ?><!--</option>-->
<!--                                        <option value="this_week">--><?php //echo _l('this_week'); ?><!--</option>-->
<!--                                        <option value="last_week">--><?php //echo _l('last_week'); ?><!--</option>-->
<!--                                        <option value="this_month">--><?php //echo _l('this_month'); ?><!--</option>-->
<!--                                        <option value="last_month">--><?php //echo _l('last_month'); ?><!--</option>-->
<!--                                        <option value="this_year">--><?php //echo _l('this_year'); ?><!--</option>-->
<!--                                        <option value="last_year">--><?php //echo _l('last_year'); ?><!--</option>-->
<!--                                        <option value="custom">--><?php //echo _l('period_datepicker'); ?><!--</option>-->
<!--                                    </select>-->
<!--                                </div>-->
<!--                                <div id="date-range" class="hide mbot15">-->
<!--                                    <div class="row">-->
<!--                                        <div class="col-md-6">-->
<!--                                            --><?php //echo render_date_input('report-from','report_sales_from_date') ?>
<!--                                        </div>-->
<!--                                        <div class="col-md-6">-->
<!--                                            --><?php //echo render_date_input('report-to','report_sales_to_date') ?>
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="col-md-3 pull-right">-->
<!--                                --><?php //echo render_select('staff[]', $staff, ['staffid', ['firstname', 'lastname']], 'staff', '', ['data-width' => '100%', 'data-none-selected-text' => _l('staff'), 'multiple' => true], [], 'no-mbot', '', false); ?>
<!---->
<!--                            </div>-->
<!--                            --><?php //if (is_admin()) { ?>
<!--                                <div class="col-md-3 pull-right">-->
<!--                                    --><?php //echo render_select('team_leader', $team_leaders, ['staffid', ['firstname', 'lastname']], 'team_leader'); ?>
<!--                                </div>-->
<!--                            --><?php //} ?>
<!---->
<!--                            <div class="col-md-3 pull-right">-->
<!--                                --><?php //echo render_select('service_name[]', $services, ['id', 'value'], 'services', '', ['data-width' => '100%', 'data-none-selected-text' => _l('services'), 'multiple' => true], [], 'no-mbot', '', false); ?>
<!--                            </div>-->
<!--                        </div>-->
                        <div class="clearfix"></div>
                        <table class="table table-team_daily_report_widgets dt-table scroll-responsive">
                            <thead>
                            <th><?php echo htmlspecialchars(_l('id')); ?></th>
                            <th><?php echo htmlspecialchars(_l('widget_name')); ?></th>
                            <th><?php echo htmlspecialchars(_l('filters')); ?></th>
                            <th><?php echo htmlspecialchars(_l('action')); ?></th>
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

<div class="modal fade" id="widget_creator_modal" tabindex="-1" role="dialog" aria-labelledby="widget_creator_modal"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-m" role="document">
        <?php echo form_open(admin_url('team_daily_report/widget_creator')) ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _l('widget_creator'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo render_input('widget_name','widget_name','', 'widget_name'); ?>
                <div class="mtop5"></div>

                <?php echo render_select('team_leader[]', $team_leaders, ['staffid', ['firstname', 'lastname']], 'team_leader', '',['data-width' => '100%', 'data-none-selected-text' => _l('services'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                <div class="mtop5"></div>

                <?php echo render_select('staff[]', $staff, ['staffid', ['firstname', 'lastname']], 'staff', '', ['data-width' => '100%', 'data-none-selected-text' => _l('staff'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                <div class="mtop5"></div>

                <?php echo render_select('service_name[]', $services, ['id', 'value'], 'services', '', ['data-width' => '100%', 'data-none-selected-text' => _l('services'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                <div class="mtop5"></div>

                <div class="form-group" id="report-time">
                    <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br/>
                    <select class="selectpicker" name="months-report" data-width="100%"
                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value="today"><?php echo _l('today'); ?></option>
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
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
        <?php echo form_close(); ?>

    </div>
</div>

<?php init_tail(); ?>
<script>

    function add_new_widget(){
        $('#widget_creator_modal').modal('show');
    }
    $(function() {
        appValidateForm($('form'), {
            widget_name: 'required',
            
        });
        init_datepicker();
        var report_from_choose = $('#report-time');
        var report_from = $('input[name="report-from"]');
        var report_to = $('input[name="report-to"]');
        var date_range = $('#date-range');
        var fnServerParams = {
            "report_months": '[name="months-report"]',
            "report_from": '[name="report-from"]',
            "report_to": '[name="report-to"]',
            "team_leader": '[name="team_leader"]',
            "staff": '[name="staff[]"]',
            "service_name": "[name='service_name[]']",
            "date_by": "[name='date_by']"
        }
        report_from.on('change', function() {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
                if (report_to_val != '') {
                    gen_team_daily_report_widgets(fnServerParams);
                }
            } else {
                report_to.attr('disabled', true);
            }
        });
        report_to.on('change', function() {
            var val = $(this).val();
            if (val != '') {
                gen_team_daily_report_widgets(fnServerParams);
            }
        });
        $('select[name="months-report"]').on('change', function() {
            var val = $(this).val();
            report_to.attr('disabled', true);
            report_to.val('');
            report_from.val('');
            if (val == 'custom') {
                date_range.addClass('fadeIn').removeClass('hide');
                return;
            } else {
                if (!date_range.hasClass('hide')) {
                    date_range.removeClass('fadeIn').addClass('hide');
                }
            }
            gen_team_daily_report_widgets(fnServerParams);
        });

        $('select[name="date_by"]').on('change', function() {
            gen_team_daily_report_widgets(fnServerParams);
        });

        $('select[name="team_leader"]').on('change', function() {
            gen_team_daily_report_widgets(fnServerParams);
        });

        $('select[name="service_name[]"]').on('change', function() {
            gen_team_daily_report_widgets(fnServerParams);
        });

        $('select[name="staff[]"]').on('change', function() {
            gen_team_daily_report_widgets(fnServerParams);
        });

        $('.table-team_daily_report_widgets').DataTable().destroy();
        initDataTable('.table-team_daily_report_widgets', admin_url + 'team_daily_report/team_daily_report_widgets_table', [1,2,3], [], fnServerParams, [0, 'desc']);


        function gen_team_daily_report_widgets(fnServerParams){
            // $('.table-team_daily_report_widgets').DataTable().destroy();
            // initDataTable('.table-team_daily_report_widgets', admin_url + 'team_daily_report/team_daily_report_widgets_table', [1,2,3], [], fnServerParams, [0, 'desc']);
        }


        //$('.table-team_daily_report').on('draw.dt', function() {
        //    var recordsTable = $(this).DataTable();
        //    var sums = recordsTable.ajax.json().sums;
        //
        //    // Check if <tfoot> exists; if not, create and append it
        //    if ($(this).find('tfoot').length === 0) {
        //        var tfoot = $('<tfoot><tr>' +
        //            '<td></td>' +
        //            '<td>' + "<?php //echo _l('total_leads'); ?>//" + '</td>' +
        //            '<td>' + "<?php //echo _l('total_calls_connected'); ?>//" + '</td>' +
        //            '<td>' + "<?php //echo _l('total_international_number'); ?>//" + '</td>' +
        //            '<td>' + "<?php //echo _l('total_lost_leads'); ?>//" + '</td>' +
        //            '<td>' + "<?php //echo _l('followup'); ?>//" + '</td>' +
        //            '<td>' + "<?php //echo _l('closing_from_walkin'); ?>//" + '</td>' +
        //            '<td>' + "<?php //echo _l('closing_from_leads'); ?>//" + '</td>' +
        //            '<td>' + "<?php //echo _l('closing_from_reference'); ?>//" + '</td>' +
        //            '</tr></tfoot>');
        //        $(this).append(tfoot);
        //    }
        //
        //    // Update the <tfoot> content with totals
        //    var tfoot = $(this).find('tfoot');
        //    tfoot.addClass('bold');
        //
        //    // Ensure the tfoot row has enough columns to display all sums
        //    var row = tfoot.find('tr');
        //    row.find('td').eq(0).html('Total');
        //    row.find('td').eq(1).html(sums.total_leads);
        //    row.find('td').eq(2).html(sums.total_calls_connected);
        //    row.find('td').eq(3).html(sums.total_international_number);
        //    row.find('td').eq(4).html(sums.total_lost);
        //    row.find('td').eq(5).html(sums.total_followup);
        //    row.find('td').eq(6).html(sums.total_closing_from_walkin);
        //    row.find('td').eq(7).html(sums.total_closing_from_leads);
        //    row.find('td').eq(8).html(sums.total_closing_from_reference);
        //});
    });

</script>
