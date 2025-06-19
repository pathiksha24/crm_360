<script>
    window.addEventListener('load', function () {

        init_datepicker();
        var report_from_choose = $('#report-time');
        var report_from = $('input[name="report-from"]');
        var report_to = $('input[name="report-to"]');
        var date_range = $('#date-range');
        var fnServerParams = {
            "report_months": '[name="months-report"]',
            "report_from": '[name="report-from"]',
            "report_to": '[name="report-to"]',
        }
        report_from.on('change', function() {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
                if (report_to_val != '') {
                    gen_leads(fnServerParams);
                }
            } else {
                report_to.attr('disabled', true);
            }
        });
        report_to.on('change', function() {
            var val = $(this).val();
            if (val != '') {
                gen_leads(fnServerParams);
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
            gen_leads(fnServerParams);
        });
        $('select[name="field"]').on('change', function() {
            gen_leads(fnServerParams);
        });
        $('.table-leads_report').DataTable().destroy();
        initDataTable('.table-leads_report', admin_url + 'leads_report/table', [1,2,3,4,5,6], [6], fnServerParams, [4, 'desc']);
    });
    function gen_leads(fnServerParams){
        $('.table-leads_report').DataTable().destroy();
        initDataTable('.table-leads_report', admin_url + 'leads_report/table', [1,2,3,4,5,6], [6], fnServerParams, [4, 'desc']);
    }
</script>