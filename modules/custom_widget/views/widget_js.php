<script>
    window.addEventListener('load', function () {
        init_datepicker();
        var report_from = $('.custom_widget input[name="custom-report-from"]');
        var report_to = $('.custom_widget input[name="custom-report-to"]');
        var date_range = $('.custom_widget #custom-date-range');
        var source = $('.custom_widget select[name="view_source"]');
        var service = $('.custom_widget select[name="view_service[]"]');
        var team_leader = $('.custom_widget select[name="team_leader"]');

        var fnServerParams = {
            "report_months": '[name="custom-months-report"]',
            "report_from": '[name="custom-report-from"]',
            "report_to": '[name="custom-report-to"]',
            "source": "[name='view_source']",
            "service": "[name='view_service[]']",
            "team_leader": "[name='team_leader']",
        }
        report_from.on('change', function() {
            var val = $(this).val();
            var report_to_val = report_to.val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
                if (report_to_val != '') {
                    gen_widget_leads(fnServerParams);
                }
            } else {
                report_to.attr('disabled', true);
            }
        });
        report_to.on('change', function() {
            var val = $(this).val();
            if (val != '') {
                gen_widget_leads(fnServerParams);
            }
        });
        $('.custom_widget select[name="custom-months-report"]').on('change', function() {
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
            gen_widget_leads(fnServerParams);
        });

        source.on('change', function() {
            gen_widget_leads(fnServerParams);
        });
        service.on('change', function() {
            gen_widget_leads(fnServerParams);
        });
        team_leader.on('change', function() {
            gen_widget_leads(fnServerParams);
        });

        $('.table-custom_widget').DataTable().destroy();
        initDataTable('.table-custom_widget', admin_url + 'custom_widget/table', [], [], fnServerParams, [1, 'desc']);
    });
    function gen_widget_leads(fnServerParams){
        $('.table-custom_widget').DataTable().destroy();
        initDataTable('.table-custom_widget', admin_url + 'custom_widget/table', [], [], fnServerParams, [1, 'desc']);
    }
</script>