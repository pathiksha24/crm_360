<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$callcenter_ids = [59, 55, 14, 72, 163, 216, 34, 20, 214];

$period = $CI->input->get('months-report');
$start_date = null;
$end_date = date('Y-m-d');

switch ($period) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date   = date('Y-m-d');
        break;
    case 'yesterday':
        $start_date = date('Y-m-d', strtotime('-1 day'));
        $end_date   = date('Y-m-d', strtotime('-1 day'));
        break;
    case 'this_week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        break;
    case 'last_week':
        $start_date = date('Y-m-d', strtotime('monday last week'));
        $end_date   = date('Y-m-d', strtotime('sunday last week'));
        break;
    case 'this_month':
        $start_date = date('Y-m-01');
        break;
    case 'last_month':
        $start_date = date('Y-m-01', strtotime('first day of last month'));
        $end_date   = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'this_year':
        $start_date = date('Y-01-01');
        break;
    case 'last_year':
        $start_date = date('Y-01-01', strtotime('-1 year'));
        $end_date   = date('Y-12-31', strtotime('-1 year'));
        break;
    case 'custom':
        $start_date = $CI->input->get('report-from');
        $end_date   = $CI->input->get('report-to');
        break;
}

$CI->db->select('addedfrom, assigned, dateadded');
$CI->db->from(db_prefix() . 'leads');
$CI->db->where_in('addedfrom', $callcenter_ids);
$CI->db->where('assigned !=', 0);
if ($start_date && $end_date) {
    $CI->db->where('dateadded >=', $start_date . ' 00:00:00');
    $CI->db->where('dateadded <=', $end_date . ' 23:59:59');
}

$results = $CI->db->get()->result_array();
$staff_lead_count = [];
foreach ($results as $row) {
    $from = (int)$row['addedfrom'];
    $to   = (int)$row['assigned'];
    if ($from === $to) continue;

    if (!isset($staff_lead_count[$from])) {
        $staff_lead_count[$from] = 0;
    }
    $staff_lead_count[$from]++;
}
?>

<div class="widget" id="callcenter-assigned-to-staff" data-name="<?php echo basename(__FILE__, ".php"); ?>">
  <div class="panel_s user-data">
    <div class="panel-body">
      <div class="widget-dragger"></div>

      <!-- Header and Filters -->
      <div class="row">
        <div class="col-md-6">
          <h4 class="bold"><?php echo _l('Call Center Staff Assigned List'); ?></h4>
        </div>
        <div class="col-md-6 text-right">
          <form method="get" id="period-form" class="form-inline">
            <div class="form-group">
              <select class="selectpicker" name="months-report" data-width="200px" onchange="document.getElementById('period-form').submit();">
                <option value=""><?php echo _l('all'); ?></option>
                <option value="today" <?= $period == 'today' ? 'selected' : '' ?>><?php echo _l('today'); ?></option>
                <option value="yesterday" <?= $period == 'yesterday' ? 'selected' : '' ?>><?php echo _l('yesterday'); ?></option>
                <option value="this_week" <?= $period == 'this_week' ? 'selected' : '' ?>><?php echo _l('this_week'); ?></option>
                <option value="last_week" <?= $period == 'last_week' ? 'selected' : '' ?>><?php echo _l('last_week'); ?></option>
                <option value="this_month" <?= $period == 'this_month' ? 'selected' : '' ?>><?php echo _l('this_month'); ?></option>
                <option value="last_month" <?= $period == 'last_month' ? 'selected' : '' ?>><?php echo _l('last_month'); ?></option>
                <option value="this_year" <?= $period == 'this_year' ? 'selected' : '' ?>><?php echo _l('this_year'); ?></option>
                <option value="last_year" <?= $period == 'last_year' ? 'selected' : '' ?>><?php echo _l('last_year'); ?></option>
                <option value="custom" <?= $period == 'custom' ? 'selected' : '' ?>><?php echo _l('custom'); ?></option>
              </select>
            </div>

            <div id="date-range" class="<?= $period != 'custom' ? 'hide' : ''; ?> mtop10">
              <div class="row">
                <div class="col-md-6">
                  <?php echo render_date_input('report-from', 'report_sales_from_date', $CI->input->get('report-from')); ?>
                </div>
                <div class="col-md-6">
                  <?php echo render_date_input('report-to', 'report_sales_to_date', $CI->input->get('report-to')); ?>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <hr class="mtop10"/>

      <!-- Table -->
      <div class="table-responsive mtop15">
        <table class="table table-striped" id="callcenter-leads-table">
          <thead>
            <tr>
              <th><?php echo _l('Call Center Staff'); ?></th>
              <th><?php echo _l('Total Leads Assigned'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($callcenter_ids as $staff_id): ?>
              <?php
                if (!isset($staff_lead_count[$staff_id]) || $staff_lead_count[$staff_id] == 0) continue;
                $staff_name = get_staff_full_name($staff_id);
                if (trim($staff_name) === '') continue;
              ?>
              <tr>
                <td><?= $staff_name; ?></td>
                <td><?= $staff_lead_count[$staff_id]; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    $('#callcenter-leads-table').DataTable({
      dom: '<"row"<"col-md-6"l><"col-md-6 text-right"f>>rt<"bottom"ip>',
      pageLength: 10,
      order: [[1, 'desc']],
      responsive: true
    });

    $('select[name="months-report"]').on('change', function () {
      if ($(this).val() === 'custom') {
        $('#date-range').removeClass('hide');
      } else {
        $('#date-range').addClass('hide');
      }
    });
  });
</script>
