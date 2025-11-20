<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="row mbot20">
                            <div class="col-md-6">
                                <h4 class="no-margin">Contract Webapp Dashboard</h4>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="row mbot15">

                            <!-- Agent -->
                                                        <!-- Team Leader -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_team_leader">Team Leader</label>
                                    <select id="filter_team_leader"
                                            name="team_leader_id"
                                            class="selectpicker"
                                            data-live-search="true"
                                            data-width="100%"
                                            data-none-selected-text="All Team Leaders">
                                        <option value=""></option>
                                           <?php foreach ($team_leaders as $ag) : ?>
                                            <option value="<?php echo $ag->agent_id; ?>">
                                                <?php echo $ag->agent_name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_agent">Agent</label>
                                    <select id="filter_agent"
                                            name="agent_id"
                                            class="selectpicker"
                                            data-live-search="true"
                                            data-width="100%"
                                            data-none-selected-text="All Agents">
                                        <option value=""></option>
                                        <?php foreach ($agents as $ag) : ?>
                                            <option value="<?php echo $ag->agent_id; ?>">
                                                <?php echo $ag->agent_name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Service -->
                      <div class="col-md-3">
                    <div class="form-group">
                        <label for="filter_service">Service</label>
                        <select id="filter_service"
                                name="service_type[]"
                                class="selectpicker"
                                multiple
                                data-actions-box="true"
                                data-live-search="true"
                                data-width="100%"
                                data-none-selected-text="All Services">
                            <?php foreach ($services as $srv): ?>
                                <option value="<?php echo html_escape($srv->id); ?>">
                                    <?php echo html_escape($srv->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>


                            <!-- Period -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_period">Period</label>
                                    <select id="filter_period"
                                            name="period"
                                            class="selectpicker"
                                            data-width="100%">
                                        <option value="all">All</option>
                                        <option value="today">Today</option>
                                        <option value="this_week">This Week</option>
                                        <option value="last_week">Last Week</option>
                                        <option value="this_month" selected>This Month</option>
                                        <option value="last_month">Last Month</option>
                                        <option value="this_year">This Year</option>
                                        <option value="last_year">Last Year</option>
                                        <option value="period">Custom Period</option>
                                    </select>
                                </div>

                                <!-- Custom date range -->
                                <div id="date_range_wrapper" style="display:none; margin-top:-5px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_from">From Date</label>
                                                <div class="input-group">
                                                    <input type="text"
                                                           id="date_from"
                                                           name="date_from"
                                                           class="form-control datepicker"
                                                           autocomplete="off">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_to">To Date</label>
                                                <div class="input-group">
                                                    <input type="text"
                                                           id="date_to"
                                                           name="date_to"
                                                           class="form-control datepicker"
                                                           autocomplete="off">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- /date_range_wrapper -->
                            </div>
                        </div>

                        <hr class="mbot15"/>

                        <!-- DataTable -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <?php
                                    $table_data = [
                                        'ID',
                                        'Team Leader',  
                                        'Agent',
                                        'Customer',
                                        'Service',
                                        'Total Amount',
                                        'Date',
                                    ];
                                    render_datatable($table_data, 'contract_webapp_report');
                                    ?>
                                </div>
                            </div>
                        </div>

                    </div><!-- /panel-body -->
                </div><!-- /panel_s -->
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function () {

    if (typeof init_datepicker === 'function') {
        init_datepicker();
    }

    function toggleDateRange() {
        if ($('#filter_period').val() === 'period') {
            $('#date_range_wrapper').show();
        } else {
            $('#date_range_wrapper').hide();
            $('#date_from').val('');
            $('#date_to').val('');
        }
    }

    toggleDateRange();

    var ContractsServerParams = {
        team_leader_id: '[name="team_leader_id"]', 
        agent_id:     '[name="agent_id"]',
        service_type: '[name="service_type[]"]',
        period:       '[name="period"]',
        date_from:    '[name="date_from"]',
        date_to:      '[name="date_to"]',
    };

    var tableContracts = initDataTable(
        '.table-contract_webapp_report',
        admin_url + 'contract_webapp/table',
        [[5, 'desc']], // Date column, newest first
        [],
        ContractsServerParams
    );
   
        $('#filter_team_leader, #filter_agent, #filter_service')
        .on('change changed.bs.select', function () {
            tableContracts.ajax.reload();
        });

    $('#filter_period')
        .on('change changed.bs.select', function () {
            toggleDateRange();
            tableContracts.ajax.reload();
        });

    $('#date_from, #date_to').on('change', function () {
        if ($('#filter_period').val() === 'period') {
            tableContracts.ajax.reload();
        }
    });

});
</script>

</body>
</html>
