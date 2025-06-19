<?php

defined('BASEPATH') or exit('No direct script access allowed');

extract($data ?? []);

$aColumns = [
	'name',
	'label_color',
	'text_color',
	'sort_order',
];
$sIndexColumn = 'id';
$sTable = db_prefix().'task_types';

$result = data_tables_init(
	$aColumns,
	$sIndexColumn,
	$sTable,
	[],
	['AND project_id='.$project_id],
	['id', 'editable']
);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	$row[] = '<span class="label" style="background-color:'.$aRow['label_color'].';border:1px solid '.$aRow['label_color'].';color:'.$aRow['text_color'].'">'.$aRow['name'].'</span>';
	$row[] = '<div style="margin-right: 1rem; display: inline-block; min-width: 20px; width: 20px; height: 20px; background-color:'.$aRow['label_color'].';">&nbsp;</div>'.$aRow['label_color'];
	$row[] = '<div style="margin-right: 1rem; display: inline-block; min-width: 20px; width: 20px; height: 20px; background-color:'.$aRow['text_color'].';">&nbsp;</div>'.$aRow['text_color'];
	$row[] = $aRow['sort_order'];

	if ($aRow['editable'])
	{
		$row[] = '<a class="label label-success" data-name="'.$aRow['name'].'" data-label-color="'.$aRow['label_color'].'" data-text-color="'.$aRow['text_color'].'" data-sort-order="'.$aRow['sort_order'].'" onclick="edit_task_type(this,'.$project_id.','.$aRow['id'].')" href="javascript:void(0)">'._l(
				'edit'
			).'</a>&nbsp;<a class="label label-danger" onclick="delete_task_type('.$project_id.','.$aRow['id'].')" href="javascript:void(0)">'._l(
				'delete'
			).'</a>';

	} else
	{
		$row[] = '';
	}

	$row['DT_RowClass'] = 'has-row-options';
	$output['aaData'][] = $row;
}
