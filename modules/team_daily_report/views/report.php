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
                            <div class="col-md-2 pull-right">
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

<!--                                <div class="col-md-2 pull-right">-->
<!--                                    <label for="date_by" class="control-label"><span class="control-label">--><?php //echo _l('si_lf_lead_filter_by_date'); ?><!--</span></label>-->
<!--                                    <select name="date_by" id="date_by" class="selectpicker no-margin" data-width="100%" >-->
<!--                                        <option value="changeddate" selected>--><?php //echo _l('changeddate'); ?><!--</option>-->
<!--                                        <option value="dateassigned">--><?php //echo _l('dateassigned'); ?><!--</option>-->
<!--                                    </select>-->
<!--                                </div>-->
                            <div class="col-md-2 pull-right">
                                <?php echo render_select('staff[]', $staff, ['staffid', ['firstname', 'lastname']], 'staff', '', ['data-width' => '100%', 'data-none-selected-text' => _l('staff'), 'multiple' => true], [], 'no-mbot', '', false); ?>

                            </div>
                            <div class="col-md-2">
                                <?php
                                $value = '';
                                echo render_select('closing_source[]', $sources, ['id', 'value'], 'closing_source', '', ['data-width' => '100%', 'data-none-selected-text' => _l('closing_source'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                            </div>
                            <?php if (is_admin()) { ?>
                                <div class="col-md-4 pull-right">
                                    <?php echo render_select('team_leader', $team_leaders, ['staffid', ['firstname', 'lastname']], 'team_leader'); ?>
                                </div>
                            <?php } ?>

                            <div class="col-md-2 pull-right">
                                <?php echo render_select('service_name[]', $services, ['id', 'value'], 'services', '', ['data-width' => '100%', 'data-none-selected-text' => _l('services'), 'multiple' => true], [], 'no-mbot', '', false); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <table class="table table-team_daily_report dt-table scroll-responsive">
                            <thead>
                            <th><?php echo htmlspecialchars(_l('id')); ?></th>
                            <th><?php echo htmlspecialchars(_l('staff')); ?></th>
                            <th><?php echo htmlspecialchars(_l('team_leader')); ?></th>
                            <th><?php echo htmlspecialchars(_l('customer')); ?></th>
                            <th><?php echo htmlspecialchars(_l('service_name')); ?></th>
                            <th><?php echo htmlspecialchars(_l('closing_source')); ?></th>
                            <th><?php echo htmlspecialchars(_l('client_nationality')); ?></th>
                            <th><?php echo htmlspecialchars(_l('city')); ?></th>
                            <th><?php echo htmlspecialchars(_l('deposit_gross')); ?></th>
                            <th><?php echo htmlspecialchars(_l('net_amount')); ?></th>
                            <th><?php echo htmlspecialchars(_l('lead')); ?></th>
                            <th><?php echo htmlspecialchars(_l('date')); ?></th>
                            <th><?php echo htmlspecialchars(_l('action')); ?></th>
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
</div>

<?php init_tail(); ?>
<script>

    $(function() {
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
            "closing_source": '[name="closing_source[]"]',
            "service_name": "[name='service_name[]']",
            "date_by": "[name='date_by']"
        }
        report_from.on('change', function() {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
                if (report_to_val != '') {
                    gen_team_daily_report(fnServerParams);
                }
            } else {
                report_to.attr('disabled', true);
            }
        });
        report_to.on('change', function() {
            var val = $(this).val();
            if (val != '') {
                gen_team_daily_report(fnServerParams);
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
            gen_team_daily_report(fnServerParams);
        });

        $('select[name="date_by"]').on('change', function() {
            gen_team_daily_report(fnServerParams);
        });

        $('select[name="team_leader"]').on('change', function() {
            gen_team_daily_report(fnServerParams);
        });

        $('select[name="service_name[]"]').on('change', function() {
            gen_team_daily_report(fnServerParams);
        });

        $('select[name="staff[]"]').on('change', function() {
            gen_team_daily_report(fnServerParams);
        });
        
        $('select[name="closing_source[]"]').on('change', function() {
            gen_team_daily_report(fnServerParams);
        });

        $('.table-team_daily_report').DataTable().destroy();
        initDataTable('.table-team_daily_report', admin_url + 'team_daily_report/team_daily_report_table', [1,2,3,4,5,6], [], fnServerParams, [9, 'desc']);


        function gen_team_daily_report(fnServerParams){
            $('.table-team_daily_report').DataTable().destroy();
            initDataTable('.table-team_daily_report', admin_url + 'team_daily_report/team_daily_report_table', [1,2,3,4,5,6], [], fnServerParams, [9, 'desc']);
        }

        $('.table-team_daily_report').on('draw.dt', function() {
            var recordsTable = $(this).DataTable();
            var sums = recordsTable.ajax.json().sums;

            // Check if <tfoot> exists; if not, create and append it
            if ($(this).find('tfoot').length === 0) {
                var tfoot = $('<tfoot><tr>' +
                    '<td></td>' +
                    '<td>' + "<?php echo _l('total_closing'); ?>" + '</td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td>' + "<?php echo _l('total_gross'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('total_net'); ?>" + '</td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '</tr></tfoot>');
                $(this).append(tfoot);
            }

            // Update the <tfoot> content with totals
            var tfoot = $(this).find('tfoot');
            tfoot.addClass('bold');

            // Ensure the tfoot row has enough columns to display all sums
            var row = tfoot.find('tr');
            row.find('td').eq(0).html('Total Closing');
            row.find('td').eq(1).html(sums.total_closing);
            row.find('td').eq(8).html(sums.total_gross);
            row.find('td').eq(9).html(sums.total_net);
        });
    });

</script>
