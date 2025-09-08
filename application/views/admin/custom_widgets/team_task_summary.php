<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s" id="team-task-summary-widget">
  <div class="panel-body">

    <!-- Header: title + export dropdown -->
    <div class="row tw-mb-3">
      <div class="col-md-6">
        <h4 class="tw-font-semibold mb-0">Sales Staff Lead Count</h4>
      </div>
      <div class="col-md-6 text-right">
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-download" aria-hidden="true"></i> Export <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right" id="export_menu">
            <li><a href="#" data-format="pdf"><i class="fa fa-file-pdf-o"></i> PDF</a></li>
            <li><a href="#" data-format="print"><i class="fa fa-print"></i> Print</a></li>
            <li><a href="#" data-format="csv"><i class="fa fa-file-text-o"></i> CSV</a></li>
            <li><a href="#" data-format="excel"><i class="fa fa-file-excel-o"></i> Excel (.xlsx)</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-md-4">
        <label for="from_date_filter" class="control-label">From Date</label>
        <input
          type="date"
          id="from_date_filter"
          name="from_date_filter"
          class="form-control"
          value="<?= html_escape(date('Y-m-d')); ?>"
          max="<?= html_escape(date('Y-m-d')); ?>"
          autocomplete="off">
      </div>
    </div>

    <!-- Table container -->
    <div id="task-summary-content" aria-live="polite" aria-busy="false">
      <?php
        // ensure the table view has the table id "task-summary-table"
        $this->load->view('admin/custom_widgets/task_summary_table', [
          'team_data'       => isset($team_data) ? $team_data : [],
          'service_columns' => isset($service_columns) ? $service_columns : (isset($columns) ? $columns : []),
        ]);
      ?>
    </div>

  </div>
</div>
