<?php

class Custom_widget extends AdminController
{
    public function __construct()
    {
        parent::__construct();

    }

    public function table(){
//        if ($this->input->is_ajax_request()) {
//            $total_fw_quote_int_fw = '"Follow up", "Quotation", "International Number Follow up"';
//            $closings = '"Closings"';
//            $cnc = '"Call Not Connected"';
//            $lost = '"Lost"';
//            $total = $total_fw_quote_int_fw.','.$closings.','.$cnc.','.$lost;
//
//            $select = [
//                'CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as name',
//                'COUNT(tblleads.id) as total',
//                'COUNT(CASE WHEN tblleads_status.name IN ('.$total_fw_quote_int_fw.') THEN tblleads.id END) as total_fw_quote_int_fw',
//                'COUNT(CASE WHEN tblleads_status.name IN ('.$closings.') THEN tblleads.id END) as total_closings',
//                'COUNT(CASE WHEN tblleads_status.name IN ('.$cnc.') THEN tblleads.id END) as total_cnc',
//                'COUNT(CASE WHEN tblleads_status.name IN ('.$lost.') THEN tblleads.id END) as total_lost',
//            ];
//            $where = [];
//            $aColumns     = $select;
//            $sIndexColumn = 'staffid';
//            $sTable       = 'tblstaff';
//            $join         = [
//                'LEFT JOIN tblleads ON tblstaff.staffid = tblleads.assigned',
//                'LEFT JOIN tblleads_status ON tblleads.status = tblleads_status.id',
//                'LEFT JOIN tblleads_sources ON tblleads_sources.id = tblleads.source',
//                'LEFT JOIN tblleads_services ON tblleads_services.id = tblleads.service',
//            ];
//            $groupBu = 'GROUP BY tblstaff.staffid';
//            $filter = [];
//            $months_report      = $this->input->post('report_months');
//            $service      = $this->input->post('service');
//            $source      = $this->input->post('source');
//            $team_leader      = $this->input->post('team_leader');
//            $custom_date_select = '';
//            $field = 'tblleads.dateadded';
//            if ($months_report!=''){
//                $custom_date_select = $this->get_where_report_period('DATE('.$field.')',$months_report);
//            }
//            if ($source !=''){
//                array_push($where, 'AND source =' . $source);
//            }
//
//            if ($team_leader !=''){
//                array_push($where, 'AND tblstaff.team_leader =' . $team_leader);
//            }
//
//            if ($service !=''){
//                array_push($where, 'AND service =' . $service);
//            }
//
//            if (isset($custom_date_select) and $custom_date_select != '') {
//                array_push($filter, $custom_date_select);
//            }
//            if (count($filter) > 0) {
//                array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
//            }
//
//            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
//                'tblstaff.staffid as staffid'
//            ], $groupBu);
//
//            $output  = $result['output'];
//            $rResult = $result['rResult'];
//
//            foreach ($rResult as $aRow) {
//                $row = [];
//                $row[] = $aRow['name'];
//                $row[] = $aRow['total'];
//                $row[] = $aRow['total_fw_quote_int_fw'];
//                $row[] = $aRow['total_closings'];
//                $row[] = $aRow['total_cnc'];
//                $row[] = $aRow['total_lost'];
//                $row['DT_RowClass'] = 'has-row-options';
//                $output['aaData'][] = $row;
//            }
//
//            echo json_encode($output);
//            die();
//        }
        if ($this->input->is_ajax_request()) {
            $total_fw_quote_int_fw = '"Follow up", "Quotation", "International Number Follow up"';
            $closings = '"Closings"';
            $cnc = '"Call Not Connected"';
            $lost = '"Lost"';
            $total = $total_fw_quote_int_fw . ',' . $closings . ',' . $cnc . ',' . $lost;

            // Filters
            $where = [];
            $filter = [];
            $months_report = $this->input->post('report_months');
            $service = $this->input->post('service');
            $source = $this->input->post('source');
            $team_leader = $this->input->post('team_leader');
            $field = 'tblleads.dateassigned';

            if ($months_report != '') {
                $custom_date_select = $this->get_where_report_period('DATE(' . $field . ')', $months_report);
                if ($custom_date_select != '') {
                    array_push($filter, $custom_date_select);
                }
            }
            if ($source != '') {
                array_push($where, 'AND source = ' . $source);
            }
            if ($team_leader != '') {
                array_push($where, 'AND tblstaff.team_leader = ' . $team_leader);
            }
            if (!empty($service)) {
                if (is_array($service)) {
                    $serviceList = array_map('intval', $service);
                    $where[] = 'AND service IN (' . implode(',', $serviceList) . ')';
                } else {
                    $where[] = 'AND service = ' . intval($service);
                }
            }
            if (count($filter) > 0) {
                array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
            }

            // Subquery as source table
            $sTable = '(SELECT 
                    tblstaff.staffid,
                    CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as name,
                    COUNT(tblleads.id) as total,
                    COUNT(CASE WHEN tblleads_status.name IN (' . $total_fw_quote_int_fw . ') THEN tblleads.id END) as total_fw_quote_int_fw,
                    COUNT(CASE WHEN tblleads_status.name IN (' . $closings . ') THEN tblleads.id END) as total_closings,
                    COUNT(CASE WHEN tblleads_status.name IN (' . $cnc . ') THEN tblleads.id END) as total_cnc,
                    COUNT(CASE WHEN tblleads_status.name IN (' . $lost . ') THEN tblleads.id END) as total_lost
                FROM tblstaff
                    LEFT JOIN tblleads ON tblstaff.staffid = tblleads.assigned
                    LEFT JOIN tblleads_status ON tblleads.status = tblleads_status.id
                    LEFT JOIN tblleads_sources ON tblleads_sources.id = tblleads.source
                    LEFT JOIN tblleads_services ON tblleads_services.id = tblleads.service
                WHERE 1=1 ' . implode(' ', $where) . '
                GROUP BY tblstaff.staffid
             ) as tblstaff';

            $sIndexColumn = 'staffid';
            $join = []; // already handled in subquery
            $groupBu = ''; // grouping is done in subquery already

            $select = [
                'name',
                'total',
                'total_fw_quote_int_fw',
                'total_closings',
                'total_cnc',
                'total_lost',
            ];

            $aColumns = $select;

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [], $groupBu);

            $output = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row = [];
                $row[] = $aRow['name'];
                $row[] = $aRow['total'];
                $row[] = $aRow['total_fw_quote_int_fw'];
                $row[] = $aRow['total_closings'];
                $row[] = $aRow['total_cnc'];
                $row[] = $aRow['total_lost'];
                $row['DT_RowClass'] = 'has-row-options';
                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    private function get_where_report_period($field = 'date',$months_report='this_month')
    {
        $custom_date_select = '';
        if ($months_report != '') {
            if ($months_report == 'this_week') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d', strtotime('monday this week')) . '" AND "' . date('Y-m-d', strtotime('sunday this week')) . '")';
            } elseif ($months_report == 'last_week') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-d', strtotime('monday last week')) . '" AND "' . date('Y-m-d', strtotime('sunday last week')) . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'last_month') {
                $beginLastMonth = to_sql_date(date('Y-m-01', strtotime('-1 MONTH')) );
                $endLastMonth   = to_sql_date(date('Y-m-t', strtotime('-1 MONTH')));
                $custom_date_select = '  ' . $field . ' BETWEEN "' . ($beginLastMonth) . '" AND "' . ($endLastMonth).'"';
            } elseif ($months_report == 'this_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                    date('Y-m-d', strtotime(date('Y-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = 'AND (' . $field . ' BETWEEN "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                    '" AND "' .
                    date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = 'AND ' . $field . ' = "' . $from_date . '"';
                } else {
                    $custom_date_select = 'AND (' . $field . ' BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            }
        }

        return $custom_date_select;
    }
}