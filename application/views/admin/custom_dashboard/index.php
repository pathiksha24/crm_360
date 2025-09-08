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

<!-- Client-side PDF + Excel/CSV (no navigation/new tab) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
  jQuery(document).ready(function ($) {
    var baseUrl = (typeof admin_url !== 'undefined' ? admin_url : '<?= admin_url(); ?>');

    function loadTeamSummary(fromDate) {
      $.post(baseUrl + 'custom_dashboard/ajax_team_task_summary', {
        from_date: fromDate
      }).done(function (res) {
        $('#task-summary-content').html(res);
      }).fail(function (xhr) {
        $('#task-summary-content').html('<div class="text-danger text-center">Failed to load data</div>');
        console.error('AJAX error:', xhr.responseText);
      });
    }

    // initial load
    var todayVal = $('#from_date_filter').val();
    if (todayVal) loadTeamSummary(todayVal);

    // change load
    $('#from_date_filter').on('change', function () {
      loadTeamSummary($(this).val());
    });

    // ---------- Export handlers ----------
    $('#export_menu').on('click', 'a[data-format]', function (e) {
      e.preventDefault();
      var fmt = $(this).data('format');
      var dateStr = ($('#from_date_filter').val() || '<?= date('Y-m-d'); ?>');

      if (fmt === 'pdf')   return exportPDF(dateStr);
      if (fmt === 'print') return printTable();
      if (fmt === 'csv')   return exportCSV(dateStr);
      if (fmt === 'excel') return exportExcel(dateStr);
    });

    function exportPDF(dateStr) {
      var el = document.getElementById('task-summary-content');
      if (!el) return;
      var opt = {
        margin:       10,
        filename:     'sales_staff_lead_count_' + dateStr + '.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
        pagebreak:    { mode: ['avoid-all','css','legacy'] }
      };
      html2pdf().set(opt).from(el).save();
    }

    function printTable() {
      var table = document.getElementById('task-summary-table');
      if (!table) return;
      var w = window.open('', '_blank', 'width=1200,height=800');
      w.document.write('<html><head><title>Print</title>');
      w.document.write('<style>table{width:100%;border-collapse:collapse} th,td{border:1px solid #ccc;padding:6px;text-align:center} thead{background:#3A3A3A;color:#fff}</style>');
      w.document.write('</head><body>' + table.outerHTML + '</body></html>');
      w.document.close();
      w.focus();
      w.print();
      w.close();
    }

    function exportExcel(dateStr) {
      var table = document.getElementById('task-summary-table');
      if (!table) return;
      var wb = XLSX.utils.table_to_book(table, {sheet: 'Sales Staff Lead Count'});
      XLSX.writeFile(wb, 'sales_staff_lead_count_' + dateStr + '.xlsx');
    }

    function exportCSV(dateStr) {
      var table = document.getElementById('task-summary-table');
      if (!table) return;
      var ws = XLSX.utils.table_to_sheet(table);
      var csv = XLSX.utils.sheet_to_csv(ws);
      downloadBlob(new Blob([csv], {type: 'text/csv;charset=utf-8;'}), 'sales_staff_lead_count_' + dateStr + '.csv');
    }

    function downloadBlob(blob, filename) {
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      setTimeout(function(){
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
      }, 100);
    }
  });
</script>

