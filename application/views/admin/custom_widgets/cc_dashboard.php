<div class="panel_s">
  <div class="panel-body">
    <h4 class="tw-font-semibold mb-3">Call Center Lead Dashboard</h4>

    <div class="row mb-3">
      <div class="col-md-4">
        <label for="from_date_filter">From Date</label>
       <input type="date" id="from_date_filter" class="form-control" value="<?= date('Y-m-d') ?>">

      </div>
    </div>

    <div id="task-summary-content">
      <?php $this->load->view('admin/custom_widgets/cc_dashboard_table', ['team_data' => $team_data]); ?>
    </div>
  </div>
</div>



