<?php defined('BASEPATH') or exit('No direct script access allowed');
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h3><?php echo $title; ?></h3>
                        <hr>
                        <div class="_buttons">
                            <button class="btn btn-primary mbot15" onclick="new_record()">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
                                <?php echo _l('new_record'); ?>
                            </button>
                            <div class="col-md-4 pull-right">
                                <?php echo render_date_input('created_at', 'created_at'); ?>
                            </div>
                            <?php if (is_admin()) { ?>
                                <div class="col-md-4 pull-right">
                                    <?php echo render_select('manager_id', $managers, ['staffid', ['firstname', 'lastname']], 'managers'); ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <?php
                        $table_data = [
                            _l('agent'),
                            _l('leads'),
                            _l('calls_connected'),
                            _l('calls_not_connected'),
                            _l('international_number'),
                            _l('not_interested'),
                            _l('lost'),
                            _l('followup'),
                            _l('closing_from_walkin'),
                            _l('closing_from_leads'),
                            _l('closing_from_reference'),
                            _l('created_at'),
                            ];
                        if (is_admin()){
                            $table_data[] = _l('manager');
                        }
                        $table_data[] = _l('action');

                        render_datatable($table_data, 'foreign_records');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-sm" id="record" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="add-title"><?php echo _l('add_record'); ?></span>
                    <span class="edit-title"><?php echo _l('edit_record'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row" >
                            <div class="col-md-12" id="flows">
                                <?php echo form_open(admin_url('Foreign Report/crud'),array('id'=>'record_form')); ?>
                                <div class="row">
                                    <?php echo form_hidden('id','') ?>
                                    <div class="col-md-12">
                                        <?php echo render_input('agent','agent','') ?>
                                        <?php echo render_select('related_manager', $managers, ['staffid', ['firstname', 'lastname']], 'manager') ?>
                                        <?php echo render_input('leads','leads','', 'number') ?>
                                        <?php echo render_input('calls_connected','calls_connected','', 'number') ?>
                                        <?php echo render_input('calls_not_connected','calls_not_connected','', 'number') ?>
                                        <?php echo render_input('international_number','international_number','', 'number') ?>
                                        <?php echo render_input('not_interested','not_interested','', 'number') ?>
                                        <?php echo render_input('lost','lost','', 'number') ?>
                                        <?php echo render_input('followup','followup','', 'number') ?>
                                        <?php echo render_input('closing_from_walkin','closing_from_walkin','', 'number') ?>
                                        <?php echo render_input('closing_from_leads','closing_from_leads','', 'number') ?>
                                        <?php echo render_input('closing_from_reference','closing_from_reference','', 'number') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" data-autocomplete="off" data-form="#record_form"><?php echo _l('save'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php init_tail(); ?>
<script>

    $(function() {
        initDataTable('.table-foreign_records', admin_url+'Foreign Report/index', [], [], {}, [0, 'asc']);
        let rules = {
            agent: 'required',
            leads: 'required',
            calls_connected: 'required',
            calls_not_connected: 'required',
            international_number: 'required',
            not_interested: 'required',
            lost: 'required',
            followup: 'required',
            closing_from_walkin: 'required',
            closing_from_leads: 'required',
            closing_from_reference: 'required',
            related_manager: 'required',
        };
        appValidateForm($('#record_form'), rules, manage_records);

        $('select[name="manager_id"]').on('change', function() {
            refresh_records()
        });
        $('input[name="created_at"]').on('change', function() {
            refresh_records()
        });

        function refresh_records(){
            var manager_id = $('select[name="manager_id"]').val();
            let date = $('#created_at').val();
            if (!manager_id) {
                manager_id = 0;
            }
            $('.table-foreign_records').DataTable().destroy()
            initDataTable('.table-foreign_records', admin_url+'Foreign Report/index/'+manager_id+'/'+date, [], [], {}, [0, 'asc']);
        }

        $('.table-foreign_records').on('draw.dt', function() {
            var recordsTable = $(this).DataTable();
            var sums = recordsTable.ajax.json().sums;

            // Check if <tfoot> exists; if not, create and append it
            if ($(this).find('tfoot').length === 0) {
                var tfoot = $('<tfoot><tr>' +
                    '<td></td>' +
                    '<td>' + "<?php echo _l('total_leads'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('total_calls_connected'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('total_calls_not_connected'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('total_international_number'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('total_not_interested'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('total_lost'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('followup'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('closing_from_walkin'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('closing_from_leads'); ?>" + '</td>' +
                    '<td>' + "<?php echo _l('closing_from_reference'); ?>" + '</td>' +
                    '</tr></tfoot>');
                $(this).append(tfoot);
            }

            // Update the <tfoot> content with totals
            var tfoot = $(this).find('tfoot');
            tfoot.addClass('bold');

            // Ensure the tfoot row has enough columns to display all sums
            var row = tfoot.find('tr');
            row.find('td').eq(0).html('Total');
            row.find('td').eq(1).html(sums.total_leads);
            row.find('td').eq(2).html(sums.total_calls_connected);
            row.find('td').eq(3).html(sums.total_calls_not_connected);
            row.find('td').eq(4).html(sums.total_international_number);
            row.find('td').eq(5).html(sums.total_not_interested);
            row.find('td').eq(6).html(sums.total_lost);
            row.find('td').eq(7).html(sums.total_followup);
            row.find('td').eq(8).html(sums.total_closing_from_walkin);
            row.find('td').eq(9).html(sums.total_closing_from_leads);
            row.find('td').eq(10).html(sums.total_closing_from_reference);
        });
    });


    function new_record(){
        $('#record .add-title').show();
        $('#record .edit-title').hide();
        $('#record input[name="id"]').val('');
        $('#record input[name="agent"]').val('');
        $('#record input[name="leads"]').val('');
        $('#record input[name="calls_connected"]').val('');
        $('#record input[name="calls_not_connected"]').val('');
        $('#record input[name="international_number"]').val('');
        $('#record input[name="not_interested"]').val('');
        $('#record input[name="lost"]').val('');
        $('#record input[name="followup"]').val('');
        $('#record input[name="closing_from_walkin"]').val('');
        $('#record input[name="closing_from_leads"]').val('');
        $('#record input[name="closing_from_reference"]').val('');
        $('#record select[name="related_manager"]').val('');
        $('#record select[name="related_manager"]').selectpicker('refresh')
        $('#record').modal();
    }

    function edit_record(invoker){
        $('#record .add-title').hide();
        $('#record .edit-title').show();
        console.log($(invoker).data('id'));
        $('#record input[name="id"]').val($(invoker).data('id'));
        $('#record input[name="agent"]').val($(invoker).data('agent'));
        $('#record input[name="leads"]').val($(invoker).data('leads'));
        $('#record input[name="calls_connected"]').val($(invoker).data('calls_connected'));
        $('#record input[name="calls_not_connected"]').val($(invoker).data('calls_not_connected'));
        $('#record input[name="international_number"]').val($(invoker).data('international_number'));
        $('#record input[name="not_interested"]').val($(invoker).data('not_interested'));
        $('#record input[name="lost"]').val($(invoker).data('lost'));
        $('#record input[name="followup"]').val($(invoker).data('followup'));
        $('#record input[name="closing_from_walkin"]').val($(invoker).data('closing_from_walkin'));
        $('#record input[name="closing_from_leads"]').val($(invoker).data('closing_from_leads'));
        $('#record input[name="closing_from_reference"]').val($(invoker).data('closing_from_reference'));
        $('#record select[name="related_manager"]').val($(invoker).data('related_manager'));
        $('#record select[name="related_manager"]').selectpicker('refresh')
        $('#record').modal();
    }

    function manage_records(form){
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
            }
            $('.table-foreign_records').DataTable().ajax.reload(null, false);
            $('#record').modal('hide');
            $('#record_form').find('button[type="submit"]').button('reset');
        });
        return false;
    }

    function delete_record(id){
        "use strict";
        var url = admin_url + 'Foreign Report/delete_record/'+id;
        if (confirm('Are you sure you want to delete this record?')){
            requestGet(url).done(function(response){
                response = JSON.parse(response);
                if (response.success === true || response.success == 'true') {
                    alert_float('success', response.message);
                    $('.table-foreign_records').DataTable().ajax.reload(null, false);
                }else{
                    alert_float('danger', response.message);
                }
            });
            return false;
        }

    }

</script>
