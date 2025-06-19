<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Leads_report extends AdminController
{
    public function table(){
        if ($this->input->is_ajax_request()) {
            $eligible = '"Followup", "Call connected", "Walkin (Customer visited office)"';
            $non_eligible = '"Lost", "Call not connected"';
            $closed = '"Closings"';
            $percentage = '"Closings"';
            $total = $eligible.','.$closed.','.$non_eligible;
            /*$select = [
                'CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as name',
                '(SELECT COUNT(*) FROM tblleads JOIN tblleads_status ON tblleads.status = tblleads_status.id WHERE tblleads.assigned = tblstaff.staffid AND tblleads_status.name IN ('.$total.')) as total',
                '(SELECT COUNT(*) FROM tblleads JOIN tblleads_status ON tblleads.status = tblleads_status.id WHERE tblleads.assigned = tblstaff.staffid AND tblleads_status.name IN ('.$eligible.')) as total_eligible_leads',
                '(SELECT COUNT(*) FROM tblleads JOIN tblleads_status ON tblleads.status = tblleads_status.id WHERE tblleads.assigned = tblstaff.staffid AND tblleads_status.name IN ('.$non_eligible.')) as total_non_eligible_leads',
                '(SELECT COUNT(*) FROM tblleads JOIN tblleads_status ON tblleads.status = tblleads_status.id WHERE tblleads.assigned = tblstaff.staffid AND tblleads_status.name IN ('.$closed.')) as total_closed_leads',
                //'(ROUND((SELECT COUNT(*) * 100 / COUNT(tblleads.id) FROM tblleads JOIN tblleads_status ON tblleads.status = tblleads_status.id WHERE tblleads.assigned = tblstaff.staffid AND tblleads_status.name IN ('.$percentage.')), 2)) as percentage',
                //'(ROUND((COUNT(CASE WHEN tblleads_status.name = '.$percentage.' THEN 1 END)/ COUNT(CASE WHEN tblleads_status.name IN ('.$total.') THEN 1 END)* 100), 2)) as percentage',
                '(ROUND(((SELECT COUNT(*) FROM tblleads JOIN tblleads_status ON tblleads.status = tblleads_status.id WHERE tblleads.assigned = tblstaff.staffid AND tblleads_status.name IN ('.$closed.')) / (SELECT COUNT(*) FROM tblleads JOIN tblleads_status ON tblleads.status = tblleads_status.id WHERE tblleads.assigned = tblstaff.staffid AND tblleads_status.name IN ('.$total.'))) * 100, 2)) as percentage',
             ];*/
            $select = [
                'CONCAT(tblstaff.firstname, " ", tblstaff.lastname) as name',
                'COUNT(CASE WHEN tblleads_status.name IN ('.$total.') THEN tblleads.id END) as total',
                'COUNT(CASE WHEN tblleads_status.name IN ('.$eligible.') THEN tblleads.id END) as total_eligible_leads',
                'COUNT(CASE WHEN tblleads_status.name IN ('.$non_eligible.') THEN tblleads.id END) as total_non_eligible_leads',
                'COUNT(CASE WHEN tblleads_status.name IN ('.$closed.') THEN tblleads.id END) as total_closed_leads',
                '(ROUND((COUNT(CASE WHEN tblleads_status.name IN ('.$closed.') THEN tblleads.id END) / COUNT(CASE WHEN tblleads_status.name IN ('.$total.') THEN tblleads.id END)) * 100, 2)) as percentage',
            ];
            $where = [];
            $aColumns     = $select;
            $sIndexColumn = 'staffid';
            $sTable       = 'tblstaff';
            $join         = [
                'LEFT JOIN tblleads ON tblstaff.staffid = tblleads.assigned',
                'LEFT JOIN tblleads_status ON tblleads.status = tblleads_status.id',
            ];
            $groupBu = 'GROUP BY tblstaff.staffid';
            $filter = [];
            $months_report      = $this->input->post('report_months');
            $custom_date_select = '';
            $field = 'tblleads.dateadded';
            if ($months_report!=''){
                $custom_date_select = $this->get_where_report_period('DATE('.$field.')',$months_report);
            }

            if (isset($custom_date_select) and $custom_date_select != '') {
                array_push($filter, $custom_date_select);
            }
            if (count($filter) > 0) {
                array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
            }

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
                'tblstaff.staffid as staffid'
            ], $groupBu);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row = [];
                if (is_admin($aRow['staffid'])) continue;
                $row[] = $aRow['name'];
                $row[] = $aRow['total'];
                $row[] = $aRow['total_eligible_leads'];
                $row[] = $aRow['total_non_eligible_leads'];
                $row[] = $aRow['total_closed_leads'];
                if ($aRow['percentage'] == '') $row[] = '0%';
                else $row[] = $aRow['percentage'].'%';
                $row[] = '';
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