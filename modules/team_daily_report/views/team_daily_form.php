<?php defined('BASEPATH') or exit('No direct script access allowed');
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <?php
                    $id = '';
                    if ($entry) $id = '/'.$entry['id'];
                    echo form_open(admin_url('team_daily_report/team_daily_form'.$id), ['id' => 'team_daily_form']); ?>

                    <div class="panel-body">
                        <h3><?php echo $title; ?></h3>
                        <hr>
                        <?php if ($entry){ ?>
                            <div class="col-md-6">
                                <?php echo render_datetime_input('date', 'date', $entry['date']); ?>
                            </div>
                            <div class="clearfix"></div>
                        <?php } ?>
<!--                        <div class="col-md-6">-->
<!--                            --><?php //echo render_date_input('date', 'date'); ?>
<!--                        </div>-->
<!--                        <div class="clearfix"></div>-->
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['client_firstname'];
                            echo render_input('client_firstname', 'client_firstname', $value); ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['client_lastname'];
                            echo render_input('client_lastname', 'client_lastname', $value); ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['client_nationality'];
                            echo render_select('client_nationality', $client_nationalities, ['id', 'value'], 'client_nationality', $value); ?>
                        </div>
                        <div class="clearfix"></div>
                         <?php if (!is_admin() && !is_team_leader()){ ?>
                             <div class="col-md-6">
                                 <?php
                                 $value = '';
                                 if ($entry) $value = $entry['team_leader'];
                                 echo render_select('team_leader', $team_leaders, ['id', 'name'], 'team_leader', $value); ?>

                             </div>
                             <div class="clearfix"></div>
                         <?php } ?>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['city'];
                            echo render_select('city', $cities, ['id', 'value'], 'city', $value); ?>

                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['deposit_gross'];
                            echo render_input('deposit_gross', 'deposit_gross', $value, 'number'); ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['net_amount'];
                            echo render_input('net_amount', 'net_amount', $value, 'number'); ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['lead_id'];

                            echo render_select('lead_id', $leads, ['id', 'phonenumber'], 'lead', $value); ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['service_name'];
                            echo render_select('service_name', $services, ['id', 'value'], 'service_name', $value); ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                            <?php
                            $value = '';
                            if ($entry) $value = $entry['closing_source'];
                            echo render_select('closing_source', $sources, ['id', 'value'], 'closing_source', $value); ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <div class="clearfix"></div>
                    </div>

                    <?php echo form_close(); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>

    $(function() {
        appValidateForm($('form'), {
            date: 'required',
            client_firstname: 'required',
            client_lastname: 'required',
            client_nationality: 'required',
            city: 'required',
            deposit_gross: 'required',
            net_amount: 'required',
            phonenumber: 'required',
            service_name: 'required',
            closing_source: 'required',
            team_leader: 'required',
            lead_id: 'required'
        });
    });

</script>
