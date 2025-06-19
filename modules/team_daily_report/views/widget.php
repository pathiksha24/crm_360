<?php
$team_daily_report_widgets = $this->db->get_where(db_prefix().'team_daily_report_widgets')->result_array(); ?>

<div class="widget" id="widget-team_daily_report_widget" data-name="team_daily_report_widget">
    <div class="panel_s user-data">
        <div class="panel-body">
            <div class="widget-dragger"></div>
            <?php foreach ($team_daily_report_widgets as $team_daily_report_widget) {
                $filters = json_decode($team_daily_report_widget['filters'], true);
                $widget_detail = [];
                $allowed_team_leaders = [];

                foreach ($filters as $key => $value) {
                    switch ($key) {
                        case 'staff':
                            if (!empty($value) && is_array($value)) {
                                $staff_names = array_map('get_staff_full_name', $value);
                                $widget_detail[] = '<b class="text-success">Staff :</b> ' . implode(', ', $staff_names);
                            }
                            break;

                        case 'service_name':
                            if (!empty($value)) {
                                $widget_detail[] = '<b class="text-success">Service Name :</b> ' . implode(', ', $value);
                            }
                            break;

                        case 'team_leader':
                            if (!empty($value) && is_array($value)) {
                                $allowed_team_leaders = $value;
                                $team_leaders_names = array_map('get_staff_full_name', $value);
                                $widget_detail[] = '<b class="text-success">Team Leader :</b> ' . implode(', ', $team_leaders_names);
                            }
                            break;

                        case 'date':
                            if (!empty($value) && is_array($value)) {
                                if (isset($value['months-report'])) {
                                    if ($value['months-report'] === 'custom') {
                                        $widget_detail[] = '<b class="text-success">Custom Date :</b> ' . $value['report-from'] . ' - ' . $value['report-to'];
                                    } else {
                                        $string = str_replace('_', ' ', $value['months-report']);
                                        $string = ucwords($string);
                                        $widget_detail[] = '<b class="text-success">Custom Date :</b> ' . $string;
                                    }
                                }
                            }
                            break;

                        default:
                            $string = str_replace('_', ' ', $key);
                            $string = ucwords($string);
                            $widget_detail[] = '<b>' . $string . ':</b> ' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                            break;
                    }
                }
                if (!in_array(get_staff_user_id(), $allowed_team_leaders) && !is_admin()) {
                    continue;
                }
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <h4 class="text-dark text-uppercase bold"><?php echo htmlspecialchars(_l('Widget')).': '.htmlspecialchars($team_daily_report_widget['widget_name']); ?></h4>
                            <p class="text-dark text-uppercase bold"><?php echo implode(' | ', $widget_detail) ?></p>
                        </div>
                        <div class="clearfix"></div>
                        <br>
                        <hr class="mtop15" />
                    </div>
                    <div class="col-md-12">
                        <table class="table table-team_daily_report_widget-<?php echo htmlspecialchars($team_daily_report_widget['id']); ?> dt-table">
                            <thead>
                            <th><?php echo htmlspecialchars(_l('staff')); ?></th>
                            <th><?php echo htmlspecialchars(_l('total_closing')); ?></th>
                            <th><?php echo htmlspecialchars(_l('gross')); ?></th>
                            <th><?php echo htmlspecialchars(_l('net')); ?></th>
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
                        <br>
                        <hr class="mtop15" />
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <script>
        window.addEventListener('load',function(){
            <?php
            foreach ($team_daily_report_widgets as $team_daily_report_widget) {
                $id = $team_daily_report_widget['id'];
                ?>
                if ($.fn.DataTable.isDataTable('.table-team_daily_report_widget-<?php echo htmlspecialchars($id); ?>')) {
                    $('.table-team_daily_report_widget-<?php echo htmlspecialchars($id); ?>').DataTable().destroy();
                }
                initDataTable('.table-team_daily_report_widget-<?php echo htmlspecialchars($id); ?>', admin_url+'team_daily_report/widget_table/<?php echo htmlspecialchars($id); ?>', false, false);
                $('.table-team_daily_report_widget-<?php echo htmlspecialchars($id); ?>').on('draw.dt', function() {
                var recordsTable = $(this).DataTable();
                var sums = recordsTable.ajax.json().sums;

                // Check if <tfoot> exists; if not, create and append it
                if ($(this).find('tfoot').length === 0) {
                    var tfoot = $('<tfoot><tr>' +
                        '<td></td>' +
                        '<td>' + "<?php echo _l('total_closing'); ?>" + '</td>' +
                        '<td>' + "<?php echo _l('total_gross'); ?>" + '</td>' +
                        '<td>' + "<?php echo _l('total_net'); ?>" + '</td>' +
                        '</tr></tfoot>');
                    $(this).append(tfoot);
                }

                // Update the <tfoot> content with totals
                var tfoot = $(this).find('tfoot');
                tfoot.addClass('bold');
                var row = tfoot.find('tr');
                row.find('td').eq(0).html('Total :');
                row.find('td').eq(1).html(sums.total_closing);
                row.find('td').eq(2).html(sums.total_gross);
                row.find('td').eq(3).html(sums.total_net);
            });
            <?php } ?>
        });
    </script>
</div>

