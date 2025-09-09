<?php
$colors = [
    1   => '#4785C3',   // Khaled (Blue)
    73  => '#F6D8A8',   // Amith (Beige)
    159 => '#A05900',   // Faiza (Brown)
    160 => '#9CC9F2',   // Shivam (Light Blue)
    78  => '#C191B3',   // Ilyas (Lavender)
    94  => '#F29C00',   // Khuwaja (Orange)
    87  => '#B7D774',   // Ziya (Light Green)
];

$grand = ['other' => 0, 'setup' => 0, 'setup_eu' => 0, 'permit' => 0, 'total' => 0];
?>

<div class="table-responsive">
  <table class="table table-bordered text-center" style="border-collapse: collapse;">
    <thead style="background-color: #3A3A3A; color: white;">
      <tr>
        <th style="text-align:center;">Team Leader</th>
        <th style="text-align:center;">Staff Name</th>
        <th style="text-align:center;">Other</th>
        <th style="text-align:center;">Business Setup</th>
        <th style="text-align:center;">Business Setup Europe</th>
        <th style="text-align:center;">Work Permit</th>
        <th style="text-align:center;">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($team_data as $leader_id => $group): ?>
        <?php
          $members = $group['members'];
          $color = $colors[$leader_id] ?? '#ECECEC';
        ?>
        <?php $first = true; $rowspan = count($members); ?>
        <?php foreach ($members as $m): ?>
          <tr>
            <?php if ($first): ?>
              <td rowspan="<?= $rowspan ?>" style="background: <?= $color ?>; font-weight: bold; vertical-align: middle;">
                <?= $group['leader_name'] ?>
              </td>
              <?php $first = false; ?>
            <?php endif; ?>

            <td style="background:#fff;"><?= $m['name'] ?></td>
            <td style="background:#f5f5f5;"><?= $m['other'] ?></td>
            <td style="background:#f5f5f5;"><?= $m['setup'] ?></td>
            <td style="background:#f5f5f5;"><?= $m['setup_eu'] ?></td>
            <td style="background:#f5f5f5;"><?= $m['permit'] ?></td>
            <td style="background: #e6f2ff; font-weight: bold;"><?= $m['total'] ?></td>

            <?php
              $grand['other'] += $m['other'];
              $grand['setup'] += $m['setup'];
              $grand['setup_eu'] += $m['setup_eu'];
              $grand['permit'] += $m['permit'];
              $grand['total'] += $m['total'];
            ?>
          </tr>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr style="background-color: #a5c0ebff; color: white; font-weight: bold;">
        <td colspan="2">Total</td>
        <td><?= $grand['other'] ?></td>
        <td><?= $grand['setup'] ?></td>
        <td><?= $grand['setup_eu'] ?></td>
        <td><?= $grand['permit'] ?></td>
        <td><?= $grand['total'] ?></td>
      </tr>
    </tfoot>
  </table>
</div>
