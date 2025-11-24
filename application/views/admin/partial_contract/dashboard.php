<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">

                        <h4 class="no-margin">Partial Contract Dashboard</h4>
                        <hr>

                        <div class="row">

                            <div class="col-md-3">
                                <label>Team Leader</label>
                                <select id="filter_team_leader" class="selectpicker" data-live-search="true" data-width="100%">
                                    <option value=""></option>
                                    <?php foreach ($team_leaders as $tl) { ?>
                                        <option value="<?= $tl->agent_id ?>"><?= $tl->agent_name ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Agent</label>
                                <select id="filter_agent" class="selectpicker" data-live-search="true" data-width="100%">
                                    <option value=""></option>
                                    <?php foreach ($agents as $ag) { ?>
                                        <option value="<?= $ag->agent_id ?>"><?= $ag->agent_name ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Period</label>
                                <select id="filter_period" class="selectpicker" data-width="100%">
                                    <option value="all">All</option>
                                    <option value="today">Today</option>
                                    <option value="this_week">This Week</option>
                                    <option value="this_month" selected>This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="this_year">This Year</option>
                                    <option value="period">Custom Period</option>
                                </select>
                            </div>

                            <div class="col-md-3" id="range_box" style="display:none;">
                                <label>Date Range</label>
                                <input type="text" class="form-control datepicker" id="date_from" placeholder="From">
                                <input type="text" class="form-control datepicker mtop10" id="date_to" placeholder="To">
                            </div>

                        </div>

                        <hr>

                        <table class="table table-partial-contract-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Team Leader</th>
                                    <th>Agent</th>
                                    <th>Customer</th>
                                    <th>ID Number</th>
                                    <th>Total Payment</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function(){

    if (typeof init_datepicker === 'function') init_datepicker();

    $('#filter_period').on('change', function(){
        if ($(this).val() === 'period') $('#range_box').show();
        else $('#range_box').hide();
    });

    var params = {
        team_leader_id: "#filter_team_leader",
        agent_id: "#filter_agent",
        period: "#filter_period",
        date_from: "#date_from",
        date_to: "#date_to",
    };

    var table = initDataTable(
        '.table-partial-contract-table',
        admin_url + 'partial_contract/table',
        [[0, 'desc']],
        [],
        params
    );

  // Disable server-side search AFTER table is loaded
    table.on('xhr.dt', function () {
        table.settings()[0].oFeatures.bServerSide = false;
    });



    $('select, #date_from, #date_to').on('change', function(){
        table.ajax.reload();
    });

});
</script>

</body>
</html>
