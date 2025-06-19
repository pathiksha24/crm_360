<script>
    window.addEventListener('load', function () {
        requestGetJSON('leads_customization/get_services/').done(function(response) {
            if (response.success === true || response.success == 'true') {
                let services = response.services;
                var selectpicker = $('#service');
                $.each(services, function(index, option) {
                    selectpicker.append('<option value="'+ option.id +'">'+ option.name +'</option>');
                });
                selectpicker.selectpicker('refresh');
            }
        }).fail(function(data) {
            alert_float('danger', data.responseText);
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
            "service": '[name="service"]',
        }
        report_from.on('change', function() {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
                if (report_to_val != '') {
                    gen_closed_leads(fnServerParams);
                }
            } else {
                report_to.attr('disabled', true);
            }
        });
        report_to.on('change', function() {
            var val = $(this).val();
            if (val != '') {
                gen_closed_leads(fnServerParams);
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
            gen_closed_leads(fnServerParams);
        });
        $('select[name="service"]').on('change', function() {
            gen_closed_leads(fnServerParams);
        });
        $('.table-closed_leads_report').DataTable().destroy();
        initDataTable('.table-closed_leads_report', admin_url + 'leads_customization/report_table', [1], [2], fnServerParams, [1, 'desc']);
    });
    function gen_closed_leads(fnServerParams){
        $('.table-closed_leads_report').DataTable().destroy();
        initDataTable('.table-closed_leads_report', admin_url + 'leads_customization/report_table', [1], [2], fnServerParams, [1, 'desc']);
    }
</script>