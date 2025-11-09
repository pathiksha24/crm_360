<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.dashboard-table th,
.dashboard-table td {
    text-align: center;
    vertical-align: middle !important;
    padding: 10px;
    border: 1px solid #dee2e6;
}

.dashboard-table thead {
    background-color: #2c3e50;
    color: white;
}

.dashboard-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.dashboard-table tfoot {
    background-color: #dce6f1;
    font-weight: bold;
    color: #333;
}

.team-leader-cell {
    font-weight: bold;
    color: #fff;
}

.leader-khaled  { background-color: #4785C3; }
.leader-amith   { background-color: #F6D8A8; color: #000; }
.leader-faiza   { background-color: #A05900; }
.leader-shivam  { background-color: #9CC9F2; color: #000; }
.leader-ilyas   { background-color: #C191B3; }
.leader-khuwaja { background-color: #F29C00; }
.leader-ziya    { background-color: #B7D774; color: #000; }

.summary-box {
    margin-bottom: 20px;
}
</style>

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive summary-box">
                <table class="table table-bordered dashboard-table">
                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th>No. of Leads Assigned</th>
                            <th>No. of Transferred Calls</th>
                            <th>No. of Pending Leads</th>
                            <!-- <th>Total</th> -->
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $allowed_staff_ids = [194, 14, 59, 55, 216, 214, 72, 20, 34, 163, 234,229,141,245];

                

                    $grand = ['assigned' => 0, 'transferred' => 0, 'pending' => 0];

                    foreach ($team_data as $leader_id => $group):
                        $members = array_filter($group['members'], function ($m) use ($allowed_staff_ids) {
                            return in_array($m['staff_id'], $allowed_staff_ids);
                        });

                        if (empty($members)) continue;

                        foreach ($members as $m):
                            $assigned    = (int) $m['assigned'];
                            $transferred = (int) $m['transferred'];
                            $pending     = (int) $m['pending'];
                           // $total       = $assigned + $transferred + $pending;

                            $grand['assigned']    += $assigned;
                            $grand['transferred'] += $transferred;
                            $grand['pending']     += $pending;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($m['name'] ?? 'N/A') ?></td>
                        <td><?= $assigned ?></td>
                        <td><?= $transferred ?></td>
                        <td><?= $pending ?></td>
                        <!-- <td><strong></?= $total ?></strong></td> -->
                    </tr>
                    <?php endforeach; endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= $grand['assigned'] ?></td>
                            <td><?= $grand['transferred'] ?></td>
                            <td><?= $grand['pending'] ?></td>
                            <!-- <td></?= $grand['assigned'] + $grand['transferred'] + $grand['pending'] ?></td> -->
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
