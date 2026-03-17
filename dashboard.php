<?php
include 'auth_check.php';
auth_check();
include 'header.php';

function getSafeCount($conn,$sql){
    try{$r=$conn->query($sql);if($r&&$row=$r->fetch_assoc())return(int)$row['c'];}catch(Exception $e){}return 0;
}

$s_prod      = getSafeCount($conn,"SELECT COUNT(*) c FROM products");
$s_tests     = getSafeCount($conn,"SELECT COUNT(*) c FROM tests");
$s_pass      = getSafeCount($conn,"SELECT COUNT(*) c FROM tests WHERE result='Pass'");
$s_fail      = getSafeCount($conn,"SELECT COUNT(*) c FROM tests WHERE result='Fail'");
$s_inconc    = getSafeCount($conn,"SELECT COUNT(*) c FROM tests WHERE result='Inconclusive'");
$s_types     = getSafeCount($conn,"SELECT COUNT(*) c FROM testing_types");
$s_pending   = getSafeCount($conn,"SELECT COUNT(*) c FROM products WHERE status='Pending'");
$s_completed = getSafeCount($conn,"SELECT COUNT(*) c FROM products WHERE status='Completed'");
$s_inprog    = getSafeCount($conn,"SELECT COUNT(*) c FROM products WHERE status='In Progress'");
$s_failed_p  = getSafeCount($conn,"SELECT COUNT(*) c FROM products WHERE status='Failed'");
$s_today     = getSafeCount($conn,"SELECT COUNT(*) c FROM tests WHERE test_date=CURDATE()");

$pass_pct = $s_tests > 0 ? round($s_pass/$s_tests*100) : 0;
$circ = round(2 * 3.14159 * 38);

// 7-day trend
$trend = [];
for ($d = 6; $d >= 0; $d--) {
    $date = date('Y-m-d',strtotime("-$d days"));
    $cnt = getSafeCount($conn,"SELECT COUNT(*) c FROM tests WHERE test_date='$date'");
    $trend[] = ['date'=>date('D',strtotime($date)),'count'=>$cnt,'full'=>date('d M',strtotime($date))];
}

$top_testers = $conn->query("SELECT tester_name,COUNT(*) tc,SUM(result='Pass') pc FROM tests GROUP BY tester_name ORDER BY tc DESC LIMIT 5");
$rec = $conn->query("SELECT t.test_id_unique,p.product_name,t.result,t.test_date,t.tester_name,tt.type_name FROM tests t JOIN products p ON t.product_id=p.id JOIN testing_types tt ON t.testing_type_id=tt.id ORDER BY t.created_at DESC LIMIT 6");
$by_type = $conn->query("SELECT tt.type_name,COUNT(t.id) tc FROM testing_types tt LEFT JOIN tests t ON tt.id=t.testing_type_id GROUP BY tt.id ORDER BY tc DESC");
?>

<style>
/* ── Dashboard Specific ── */
.hero{
  background:linear-gradient(125deg,#060a10 0%,#0f1824 50%,#1a0b0a 100%);
  border-radius:14px;padding:28px 30px;margin-bottom:20px;
  display:flex;justify-content:space-between;align-items:center;
  overflow:hidden;position:relative;border:1px solid rgba(255,255,255,.055);
}
.hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:48px 48px;}
.hero-glow1{position:absolute;top:-80px;right:60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(232,52,42,.14),transparent 65%);pointer-events:none;}
.hero-glow2{position:absolute;bottom:-70px;left:220px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(59,130,246,.07),transparent 65%);pointer-events:none;}

.hero-left{position:relative;z-index:1;}
.hero-tag{font-size:10px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(232,52,42,.75);margin-bottom:9px;display:flex;align-items:center;gap:6px;}
.hero-tag::before{content:'';display:inline-block;width:12px;height:2px;background:rgba(232,52,42,.7);border-radius:2px;}
.hero-title{font-family:'Outfit',sans-serif;font-size:24px;font-weight:900;color:#fff;margin-bottom:7px;line-height:1.2;}
.hero-title span{color:#e8342a;}
.hero-date{color:rgba(255,255,255,.38);font-size:12.5px;margin-bottom:18px;display:flex;align-items:center;gap:14px;}
.hero-date i{color:rgba(255,255,255,.2);margin-right:4px;}
.hero-actions{display:flex;gap:9px;flex-wrap:wrap;}
.ha-btn{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:8px;font-family:'Space Grotesk',sans-serif;font-size:12.5px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .15s;border:none;}
.ha-primary{background:var(--red);color:#fff;}
.ha-primary:hover{background:var(--red2);box-shadow:0 5px 16px rgba(232,52,42,.3);}
.ha-ghost{background:rgba(255,255,255,.07);color:#fff;border:1px solid rgba(255,255,255,.12);}
.ha-ghost:hover{background:rgba(255,255,255,.12);}

/* KPI ring */
.hero-right{position:relative;z-index:1;text-align:center;flex-shrink:0;}
.kpi-ring svg{transform:rotate(-90deg);}
.kpi-inner{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.kpi-pct{font-family:'Outfit',sans-serif;font-size:20px;font-weight:900;color:#fff;line-height:1;}
.kpi-lbl{font-size:9px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1px;margin-top:2px;}

/* Stat grid */
.stat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;}

/* Main layout */
.dash-grid{display:grid;grid-template-columns:1.65fr 1fr;gap:18px;}
.col{display:flex;flex-direction:column;gap:16px;}

/* Chart */
.chart-wrap{padding:18px 20px;}
.bar-chart{display:flex;align-items:flex-end;gap:8px;height:80px;}
.bar{
  flex:1;border-radius:5px 5px 0 0;min-height:4px;
  background:linear-gradient(180deg,var(--red),rgba(232,52,42,.55));
  position:relative;cursor:pointer;transition:opacity .15s;
}
.bar:hover{opacity:.8;}
.bar-tip{
  position:absolute;top:-30px;left:50%;transform:translateX(-50%);
  background:#0a0d13;color:#fff;font-size:10px;padding:3px 7px;border-radius:5px;
  white-space:nowrap;opacity:0;transition:opacity .15s;pointer-events:none;
}
.bar:hover .bar-tip{opacity:1;}
.bar-labels{display:flex;gap:8px;margin-top:6px;}
.bar-labels span{flex:1;text-align:center;font-size:10px;color:#94a3b8;}

/* Progress bars */
.prog-row{display:flex;align-items:center;gap:10px;margin-bottom:11px;}
.prog-label{font-size:12.5px;font-weight:600;color:#374151;min-width:94px;}
.prog-bar{flex:1;height:7px;border-radius:4px;background:#f0f4f9;overflow:hidden;}
.prog-fill{height:100%;border-radius:4px;transition:width 1.1s ease;}
.prog-val{font-size:11px;color:#94a3b8;min-width:28px;text-align:right;font-weight:600;}

/* Activity row */
.act-row{display:flex;align-items:center;gap:11px;padding:10px 0;border-bottom:1px solid #f0f4f9;}
.act-row:last-child{border-bottom:none;}

/* Tester rank */
.rank-num{width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;}
.mini-bar{height:5px;border-radius:3px;background:#f0f4f9;overflow:hidden;margin-top:5px;}
.mini-bar-in{height:100%;border-radius:3px;transition:width 1.2s ease;}

/* Quick actions */
.qa-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px;}
.qa-btn{
  display:flex;align-items:center;gap:10px;padding:13px 14px;
  background:#fafbfd;border:1.5px solid var(--border);border-radius:10px;
  text-decoration:none;transition:all .15s;cursor:pointer;
}
.qa-btn:hover{border-color:var(--red);background:#fff5f4;transform:translateY(-1px);}
.qa-ic{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}
.qa-btn .qa-t{font-size:13px;font-weight:600;color:var(--text);}
.qa-btn .qa-d{font-size:11px;color:var(--text2);margin-top:1px;}

@keyframes countUp{from{opacity:0;transform:translateY(7px);}to{opacity:1;transform:translateY(0);}}
.stat-card{animation:countUp .35s ease both;}
.stat-card:nth-child(1){animation-delay:.04s;}.stat-card:nth-child(2){animation-delay:.08s;}.stat-card:nth-child(3){animation-delay:.12s;}.stat-card:nth-child(4){animation-delay:.16s;}.stat-card:nth-child(5){animation-delay:.20s;}

@media(max-width:1200px){.stat-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:900px){.dash-grid{grid-template-columns:1fr;}}
@media(max-width:640px){.stat-grid{grid-template-columns:1fr 1fr;}.hero{flex-direction:column;gap:22px;}.hero-right{display:none;}}
</style>

<div class="main-content">

<!-- Hero Banner -->
<div class="hero">
  <div class="hero-glow1"></div>
  <div class="hero-glow2"></div>
  <div class="hero-left">
    <p class="hero-tag"><i class="fas fa-flask"></i>Lab Automation System</p>
    <h1 class="hero-title">
      Good <?= date('H')<12?'Morning':(date('H')<17?'Afternoon':'Evening') ?>,
      <span><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?></span>
    </h1>
    <p class="hero-date">
      <span><i class="fas fa-calendar-days"></i><?= date('l, d F Y') ?></span>
      <span><i class="fas fa-clock"></i><span id="ltime">--:--:--</span></span>
    </p>
    <div class="hero-actions">
      <a href="add_product.php" class="ha-btn ha-primary"><i class="fas fa-plus"></i> Add Product</a>
      <a href="add_tests.php"   class="ha-btn ha-ghost"><i class="fas fa-vial"></i> Conduct Test</a>
      <a href="test_report.php" class="ha-btn ha-ghost"><i class="fas fa-file-pdf"></i> Report</a>
    </div>
  </div>
  <div class="hero-right">
    <div style="position:relative;display:inline-block;">
      <svg width="100" height="100" viewBox="0 0 100 100">
        <circle cx="50" cy="50" r="38" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="8"/>
        <circle cx="50" cy="50" r="38" fill="none" stroke="#e8342a" stroke-width="8"
          stroke-dasharray="<?= round($circ*$pass_pct/100) ?> <?= $circ ?>"
          stroke-linecap="round"/>
      </svg>
      <div class="kpi-inner">
        <div class="kpi-pct"><?= $pass_pct ?>%</div>
        <div class="kpi-lbl">Pass Rate</div>
      </div>
    </div>
    <div style="color:rgba(255,255,255,.32);font-size:11px;margin-top:6px;"><?= $s_pass ?> / <?= $s_tests ?> passed</div>
  </div>
</div>

<!-- Stat Grid -->
<div class="stat-grid">
  <?php
  $cards = [
    ['Products',     $s_prod,    'fa-box-open',       'ic-blue',   'view_product.php'],
    ['Total Tests',  $s_tests,   'fa-clipboard-list', 'ic-purple', 'view_tests.php'],
    ['Passed',       $s_pass,    'fa-circle-check',   'ic-green',  'view_tests.php?result=Pass'],
    ['Failed',       $s_fail,    'fa-circle-xmark',   'ic-red',    'view_tests.php?result=Fail'],
    ['Today\'s Tests',$s_today,  'fa-calendar-day',   'ic-teal',   'view_tests.php'],
  ];
  foreach ($cards as $c):
  ?>
  <a href="<?= $c[4] ?>" style="text-decoration:none;">
    <div class="stat-card">
      <div class="st-ic <?= $c[3] ?>"><i class="fas <?= $c[2] ?>"></i></div>
      <div style="flex:1;min-width:0;">
        <div class="st-val"><?= $c[1] ?></div>
        <div class="st-lbl"><?= $c[0] ?></div>
      </div>
      <i class="fas fa-arrow-up-right-from-square" style="color:#d1d9e6;font-size:10px;"></i>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Main Dashboard Grid -->
<div class="dash-grid">

  <!-- LEFT COLUMN -->
  <div class="col">

    <!-- 7-Day Trend -->
    <div class="card">
      <div class="card-hd">
        <h3><i class="fas fa-chart-line"></i> 7-Day Test Activity</h3>
        <span style="font-size:11px;color:#94a3b8;background:#f8fafc;padding:3px 10px;border-radius:6px;border:1px solid #e8edf3;">Last 7 Days</span>
      </div>
      <div class="chart-wrap">
        <?php $maxC = max(max(array_column($trend,'count')),1); ?>
        <div class="bar-chart">
          <?php foreach($trend as $d): ?>
          <div class="bar" style="height:<?= max(round($d['count']/$maxC*100),5) ?>%;">
            <div class="bar-tip"><?= $d['count'] ?> tests — <?= $d['full'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="bar-labels">
          <?php foreach($trend as $d): ?><span><?= $d['date'] ?></span><?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Recent Tests -->
    <div class="card">
      <div class="card-hd">
        <h3><i class="fas fa-clock-rotate-left"></i> Recent Tests</h3>
        <a href="view_tests.php" class="btn btn-ghost btn-sm">View All →</a>
      </div>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>Test ID</th><th>Product</th><th>Type</th><th>Date</th><th>Result</th></tr></thead>
          <tbody>
            <?php
            if ($rec && $rec->num_rows > 0) {
              while ($r = $rec->fetch_assoc()) {
                $b = $r['result']==='Pass'?'b-green':($r['result']==='Fail'?'b-red':'b-yellow');
                echo "<tr>
                  <td><code style='font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:5px;font-family:monospace;'>{$r['test_id_unique']}</code></td>
                  <td style='font-weight:600;font-size:13px;'>".htmlspecialchars($r['product_name'])."</td>
                  <td style='color:#5a6478;font-size:12px;'>".htmlspecialchars($r['type_name'])."</td>
                  <td style='color:#94a3b8;font-size:12px;'>".date('d M',strtotime($r['test_date']))."</td>
                  <td><span class='badge $b'>{$r['result']}</span></td>
                </tr>";
              }
            } else {
              echo "<tr><td colspan='5' style='text-align:center;padding:32px;color:#c5cbd5;'>No tests yet. <a href='add_tests.php' style='color:var(--red);'>Conduct first test →</a></td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- RIGHT COLUMN -->
  <div class="col">

    <!-- Quick Actions -->
    <div class="card">
      <div class="card-hd"><h3><i class="fas fa-bolt"></i> Quick Actions</h3></div>
      <div class="card-bd">
        <div class="qa-grid">
          <a href="add_product.php" class="qa-btn">
            <div class="qa-ic ic-blue"><i class="fas fa-plus"></i></div>
            <div><div class="qa-t">Add Product</div><div class="qa-d">Register new</div></div>
          </a>
          <a href="add_tests.php" class="qa-btn">
            <div class="qa-ic ic-purple"><i class="fas fa-vial"></i></div>
            <div><div class="qa-t">New Test</div><div class="qa-d">Log entry</div></div>
          </a>
          <a href="test_report.php" class="qa-btn">
            <div class="qa-ic ic-red"><i class="fas fa-print"></i></div>
            <div><div class="qa-t">Print PDF</div><div class="qa-d">Export report</div></div>
          </a>
          <a href="add_type.php" class="qa-btn">
            <div class="qa-ic ic-teal"><i class="fas fa-tags"></i></div>
            <div><div class="qa-t">Test Types</div><div class="qa-d">Manage</div></div>
          </a>
        </div>
      </div>
    </div>

    <!-- Product Status -->
    <div class="card">
      <div class="card-hd"><h3><i class="fas fa-chart-pie"></i> Product Status</h3></div>
      <div class="card-bd">
        <?php
        $statuses = [
          ['Completed',   $s_completed, '#16a34a'],
          ['In Progress', $s_inprog,    '#2563eb'],
          ['Pending',     $s_pending,   '#d97706'],
          ['Failed',      $s_failed_p,  '#e8342a'],
        ];
        foreach ($statuses as $st):
          $pct = $s_prod > 0 ? round($st[1]/$s_prod*100) : 0;
        ?>
        <div class="prog-row">
          <span class="prog-label"><?= $st[0] ?></span>
          <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%;background:<?= $st[2] ?>;"></div></div>
          <span class="prog-val"><?= $st[1] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Top Testers -->
    <div class="card">
      <div class="card-hd"><h3><i class="fas fa-medal"></i> Top Testers</h3></div>
      <div class="card-bd" style="padding:12px 18px;">
        <?php
        if ($top_testers && $top_testers->num_rows > 0):
          $rank = 1;
          while ($t = $top_testers->fetch_assoc()):
            $rate  = $t['tc']>0 ? round($t['pc']/$t['tc']*100) : 0;
            $cols  = ['#e8342a','#f97316','#3b82f6','#8b5cf6','#6b7280'];
            $col   = $cols[$rank-1] ?? '#6b7280';
        ?>
        <div class="act-row">
          <div class="rank-num" style="background:<?= $col ?>18;color:<?= $col ?>;"><?= $rank ?></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:var(--text);"><?= htmlspecialchars($t['tester_name']) ?></div>
            <div class="mini-bar"><div class="mini-bar-in" style="width:<?= $rate ?>%;background:<?= $col ?>;"></div></div>
          </div>
          <div style="text-align:right;flex-shrink:0;">
            <div style="font-size:12.5px;font-weight:700;"><?= $t['tc'] ?><span style="font-weight:400;color:#94a3b8;font-size:11px;"> tests</span></div>
            <div style="font-size:10.5px;color:<?= $col ?>;"><?= $rate ?>% pass</div>
          </div>
        </div>
        <?php $rank++; endwhile; else: ?>
          <p style="color:#94a3b8;text-align:center;padding:20px 0;">No data yet.</p>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
</div>

<script>
// Live clock
(function tick(){
  const n=new Date(),pad=n=>String(n).padStart(2,'0');
  document.getElementById('ltime').textContent=pad(n.getHours())+':'+pad(n.getMinutes())+':'+pad(n.getSeconds());
  setTimeout(tick,1000);
})();
// Animate progress bars
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.prog-fill,.mini-bar-in').forEach(function(el){
    const w=el.style.width; el.style.width='0';
    setTimeout(()=>{ el.style.width=w; },150);
  });
});
</script>

<?php include 'footer.php'; ?>
