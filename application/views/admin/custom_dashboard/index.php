<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <h4 class="tw-mb-4"><?php echo _l('custom_dashboard'); ?></h4>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <?php $this->load->view('admin/custom_widgets/team_task_summary', ['team_data' => $team_data]); ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <?php render_custom_dashboard_widgets('left-8'); ?>
      </div>
      <div class="col-md-4">
        <?php render_custom_dashboard_widgets('right-4'); ?>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>
  jQuery(document).ready(function ($) {
    function loadTeamSummary(fromDate) {
      $.post(admin_url + 'custom_dashboard/ajax_team_task_summary', {
        from_date: fromDate
      }).done(function (res) {
        $('#task-summary-content').html(res);
      }).fail(function (xhr) {
        $('#task-summary-content').html('<div class="text-danger text-center">Failed to load data</div>');
        console.error('AJAX error:', xhr.responseText);
      });
    }

    // 1. Trigger load on page load using default date
    var today = $('#from_date_filter').val();
    if (today) {
      loadTeamSummary(today);
    }

    // 2. Also bind on date change
    $('#from_date_filter').on('change', function () {
      var selectedDate = $(this).val();
      loadTeamSummary(selectedDate);
    });
  });
</script>

