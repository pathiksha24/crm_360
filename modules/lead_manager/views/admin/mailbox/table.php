<?php
defined('BASEPATH') or exit('No direct script access allowed');
$has_permission_delete = has_permission('leads', '', 'delete');
$years = $this->ci->lead_manager_model->get_leads_years();
$months = [1,2,3,4,5,6,7,8,9,10,11,12];
$aColumns = [
    db_prefix() . 'lead_manager_mailbox.id as id',
    'fromName',
    'to_email',
    'subject',
    'mail_date',
    'is_read'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'lead_manager_mailbox';
$join = [
    'LEFT JOIN ' . db_prefix() . 'lead_manager_mailbox_attachments ON ' . db_prefix() . 'lead_manager_mailbox_attachments.staff_id = ' . db_prefix() . 'lead_manager_mailbox.staffid AND '.db_prefix().'lead_manager_mailbox_attachments.mailbox_id='.db_prefix().'lead_manager_mailbox.id' 
];

$where  = [];
$filter = false;


if ($this->ci->input->post('direction')) {
    array_push($where, "AND direction = '" . $this->ci->input->post('direction') . "'");
}
if ($this->ci->input->post('status')) {
    array_push($where, "AND status = '" . $this->ci->input->post('status') . "'");
}


$additionalColumns = ['is_attachment','is_favourite','is_bookmark',db_prefix().'lead_manager_mailbox_attachments.file_name as file','email_size','from_email','status'];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);
$output  = $result['output']; 
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $is_favourite = '<span><a href="javascript:void(0);" data-id="'.$aRow['id'].'" data-action="star" data-table=".table-lm-mailbox" onclick="lm_mb_single_inbox(this); return false;"><i class="fa fa-star" aria-hidden="true"></i></a></span>';
    $is_bookmark = '<span><a href="javascript:void(0);" data-id="'.$aRow['id'].'" data-action="bookmark" data-table=".table-lm-mailbox" onclick="lm_mb_single_inbox(this); return false;"><i class="fa fa-bookmark" aria-hidden="true"></i></a></span>';
    if($aRow['is_favourite']){
        $is_favourite = '<span><a href="javascript:void(0);" data-id="'.$aRow['id'].'" data-action="unstar" data-table=".table-lm-mailbox" onclick="lm_mb_single_inbox(this); return false;"><i class="fa fa-star text-warning" aria-hidden="true"></i></a></span>';
    }if($aRow['is_bookmark']){
        $is_bookmark = '<span><a href="javascript:void(0);" data-id="'.$aRow['id'].'" data-action="unbookmark" data-table=".table-lm-mailbox" onclick="lm_mb_single_inbox(this); return false;"><i class="fa fa-bookmark text-muted" aria-hidden="true"></i></a></span>';
    }
    $row[] = '<div class="checkbox main_icon_check"><input type="checkbox" value="' . $aRow['id'] . '"><label></label>'.$is_favourite.$is_bookmark.'<span><a href="javascript:void(0);" data-id="'.$aRow['id'].'" data-action="delete" data-table=".table-lm-mailbox" onclick="lm_mb_single_inbox(this); return false;"><i class="fa fa-trash text-danger" aria-hidden="true"></i></a></span></div>';
    $fromName = $aRow['fromName'] ?? $aRow['from_email'];
    $row[] = '<a href="javascript:void(0);" onclick="viewMailBoxMail('.$aRow['id'].')">'.$fromName.'</a>';
    $row[] = $aRow['to_email'];
    $subject = $aRow['subject'];
    if($aRow['is_attachment']){
        $subject .= ' <a href="'.admin_url('lead_manager/download_attachemnts/'.$aRow['id']). '" target="_blank"><i class="fa fa-paperclip"></i></a>';
    }
    $subject .= ' <p class="text-muted text-left">'.formatSizeUnits($aRow['email_size']).'</p>';
    $row[] = $subject;
    $mail_date = date('Y-m-d h:i:s',$aRow['mail_date']);
    $row[] = _dt($mail_date);
    $row[] = $aRow['is_read'];
    if ($aRow['is_read'] == 0 && $aRow['status']=='get') {
        $row['DT_RowClass'] = 'alert-info';
    }

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }
    $output['aaData'][] = $row;
}
