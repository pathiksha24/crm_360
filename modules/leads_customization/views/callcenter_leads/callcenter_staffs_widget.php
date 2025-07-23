<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$callcenter_ids = [59, 55, 14, 72, 163, 216, 34, 20, 214, 225];

$period = $CI->input->get('months-report') ?? 'today';
if ($period === '') {
    $period = 'all';
}

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
        $from_raw = $CI->input->get('report-from');
        $to_raw = $CI->input->get('report-to');
        $from = DateTime::createFromFormat('d-m-Y', $from_raw);
        $to   = DateTime::createFromFormat('d-m-Y', $to_raw);
        if ($from && $to) {
            $start_date = $from->format('Y-m-d');
            $end_date   = $to->format('Y-m-d');
        }
        break;
    case 'all':
        $start_date = null;
        $end_date = null;
        break;
}

// Fetch logs
$CI->db->select('LAL.*');
$CI->db->from(db_prefix() . 'lead_activity_log AS LAL');
$CI->db->join(db_prefix() . 'leads AS TL', 'TL.id = LAL.leadid', 'inner');
$CI->db->where('LAL.description', 'not_lead_activity_assigned_to');
$CI->db->where_in('LAL.staffid', $callcenter_ids);
$CI->db->where('LAL.additional_data !=', '');
$CI->db->where_in('TL.status', [2, 7, 8, 5]);
$CI->db->where_not_in('TL.service', [198, 168]);

if ($start_date && $end_date) {
    $CI->db->where('LAL.date >=', $start_date . ' 00:00:00');
    $CI->db->where('LAL.date <=', $end_date . ' 23:59:59');
}

$logs = $CI->db->get()->result_array();

$assigner_counts = [];

foreach ($logs as $log) {
    $assigner_id = $log['staffid'];
    $data = @unserialize($log['additional_data']);

    if (!is_array($data) || !isset($data[1])) {
        continue;
    }

    if (preg_match('/profile\/(\d+)/', $data[1], $id_match)) {
        $assignee_id = (int)$id_match[1];
    } else {
        continue;
    }

    if ($assigner_id == $assignee_id || in_array($assignee_id, $callcenter_ids)) {
        continue;
    }

    if (!isset($assigner_counts[$assigner_id])) {
        $assigner_counts[$assigner_id] = 0;
    }
    $assigner_counts[$assigner_id]++;
}

$assigner_names = [];
if (!empty($assigner_counts)) {
    $CI->db->where_in('staffid', array_keys($assigner_counts));
    $staff_list = $CI->db->get(db_prefix() . 'staff')->result_array();
    foreach ($staff_list as $staff) {
        $assigner_names[$staff['staffid']] = $staff['firstname'] . ' ' . $staff['lastname'];
    }
}
?>

<div class="widget" id="assigned-to-count-widget" data-name="<?php echo basename(__FILE__, ".php"); ?>">
  <div class="panel_s user-data">
    <div class="panel-body">
      <div class="widget-dragger"></div>

      <!-- Header and Filters -->
      <div class="row">
        <div class="col-md-6">
          <h4 class="bold"><?php echo _l('Call Center Staff Assigned To - Lead Count'); ?></h4>
        </div>
        <div class="col-md-6 text-right">
          <form method="get" id="period-form" class="form-inline">
            <div class="form-group">
              <select class="selectpicker" name="months-report" data-width="200px" onchange="document.getElementById('period-form').submit();">
                <option value="all" <?= $period == 'all' ? 'selected' : '' ?>><?php echo _l('all'); ?></option>
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
                <div class="col-md-12 mtop10 text-right">
                  <button type="submit" class="btn btn-primary"><?php echo _l('filter'); ?></button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <hr class="mtop10"/>
      <!-- Table -->
      <div class="table-responsive mtop15">
        <table class="table table-striped" id="assigned-to-table">
          <thead>
            <tr>
              <th><?php echo _l('Call Center Staff'); ?></th>
              <th><?php echo _l('Total Leads Assigned'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($assigner_counts as $staff_id => $count): ?>
              <tr>
                <td>
                  <?php
                    echo isset($assigner_names[$staff_id])
                      ? htmlspecialchars($assigner_names[$staff_id])
                      : 'Staff ID #' . $staff_id;
                  ?>
                </td>
                <td><?php echo $count; ?></td>
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
    $('#assigned-to-table').DataTable({
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
        $('#period-form').submit();
      }
    });
  });
</script>
