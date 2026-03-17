<?php
include 'auth_check.php'; auth_check();

$fr        = trim($_GET['result']     ?? '');
$fpid      = (int)($_GET['product_id'] ?? 0);
$ftype     = (int)($_GET['type_id']   ?? 0);
$date_from = trim($_GET['date_from']  ?? '');
$date_to   = trim($_GET['date_to']    ?? '');

$where = "WHERE 1=1";
if ($fr)        { $r=$conn->real_escape_string($fr);         $where.=" AND t.result='$r'"; }
if ($fpid)      { $where.=" AND t.product_id=$fpid"; }
if ($ftype)     { $where.=" AND t.test_type_id=$ftype"; }
if ($date_from) { $df=$conn->real_escape_string($date_from); $where.=" AND t.testing_date>='$df'"; }
if ($date_to)   { $dt=$conn->real_escape_string($date_to);   $where.=" AND t.testing_date<='$dt'"; }

$join  = "FROM testing_records t 
          JOIN products p ON t.product_id=p.product_id 
          JOIN test_types tt ON t.test_type_id=tt.test_id";

$tests = $conn->query("SELECT t.*, p.product_name, p.product_code, tt.test_name $join $where ORDER BY t.testing_date DESC");
if (!$tests) { die("DB Error: " . $conn->error); }

$total = $tests->num_rows;
$tpass = (int)$conn->query("SELECT COUNT(*) c FROM testing_records t $where AND t.result='Pass'")->fetch_assoc()['c'];
$tfail = (int)$conn->query("SELECT COUNT(*) c FROM testing_records t $where AND t.result='Fail'")->fetch_assoc()['c'];
$pct   = $total > 0 ? number_format($tpass/$total*100, 1) : '0.0';

$prods_dd = $conn->query("SELECT product_id, product_name FROM products ORDER BY product_name");
$types_dd = $conn->query("SELECT test_id, test_name FROM test_types ORDER BY test_name");

include 'header.php';
?>
<div class="main-content">
  <div class="pg-hd">
    <div class="pg-title"><h2>Test Report</h2><p>Filter, preview and print to PDF.</p></div>
    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print / Save PDF</button>
  </div>

  <!-- Filter panel -->
  <div class="card no-print" style="margin-bottom:20px;">
    <div class="card-hd"><h3><i class="fas fa-sliders"></i> Report Filters</h3></div>
    <div class="card-bd">
      <form method="GET" action="" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;align-items:end;">
        <div class="fg">
          <label>Result</label>
          <select name="result">
            <option value="">All Results</option>
            <?php foreach (['Pass','Fail','Inconclusive'] as $r): ?>
            <option value="<?=$r?>" <?=$fr===$r?'selected':''?>><?=$r?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Product</label>
          <select name="product_id">
            <option value="">All Products</option>
            <?php while ($p=$prods_dd->fetch_assoc()): ?>
            <option value="<?=$p['product_id']?>" <?=$fpid==$p['product_id']?'selected':''?>><?=htmlspecialchars($p['product_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="fg">
          <label>Test Type</label>
          <select name="type_id">
            <option value="">All Types</option>
            <?php while ($t=$types_dd->fetch_assoc()): ?>
            <option value="<?=$t['test_id']?>" <?=$ftype==$t['test_id']?'selected':''?>><?=htmlspecialchars($t['test_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="fg">
          <label>Date From</label>
          <input type="date" name="date_from" value="<?=htmlspecialchars($date_from)?>">
        </div>
        <div class="fg">
          <label>Date To</label>
          <input type="date" name="date_to" value="<?=htmlspecialchars($date_to)?>">
        </div>
        <div class="fg" style="display:flex;gap:8px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">Apply</button>
          <a href="test_report.php" class="btn btn-secondary">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- PRINTABLE AREA -->
  <div id="print-area">

    <!-- Print-only header -->
    <div class="print-only" style="display:none;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #e8342a;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
          <div style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:#0d1117;">🧪 Lab Test Report — SRS Electrical Appliances</div>
          <div style="font-size:13px;color:#5b6475;margin-top:3px;">Lab Automation System</div>
        </div>
        <div style="text-align:right;font-size:12px;color:#5b6475;">
          Generated: <?= date('d F Y, h:i A') ?><br>
          By: <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?><br>
          <?php if ($date_from || $date_to) echo "Period: " . ($date_from ?: '—') . " to " . ($date_to ?: date('Y-m-d')); ?>
        </div>
      </div>
    </div>

    <!-- Summary cards -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
      <?php foreach ([
        ['Total Tests',$total,'#3b82f6','fa-vial'],
        ['Passed',$tpass,'#16a34a','fa-circle-check'],
        ['Failed',$tfail,'#e8342a','fa-circle-xmark'],
        ['Pass Rate',$pct.'%','#7c3aed','fa-chart-pie'],
      ] as $c): ?>
      <div style="background:#fff;border:1px solid #e8edf3;border-radius:11px;padding:16px 18px;border-top:3px solid <?=$c[2]?>;-webkit-print-color-adjust:exact;print-color-adjust:exact;">
        <div style="font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;">
          <i class="fas <?=$c[3]?>" style="color:<?=$c[2]?>;margin-right:5px;"></i><?=$c[0]?>
        </div>
        <div style="font-family:'Syne',sans-serif;font-size:26px;font-weight:800;color:#0d1117;"><?=$c[1]?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Test Table -->
    <div class="card">
      <div class="card-hd no-print">
        <h3><i class="fas fa-table-list"></i> Test Entries (<?= $total ?> records)</h3>
      </div>
      <div class="tbl-wrap">
        <table class="tbl" style="font-size:13px;">
          <thead>
            <tr>
              <th>#</th><th>Product</th><th>Test Type</th>
              <th>Tester</th><th>Date</th><th>Result</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($tests->num_rows > 0): ?>
              <?php $i = 1; while ($row = $tests->fetch_assoc()): ?>
              <?php
                $rb = match($row['result']) { 'Pass'=>'b-green', 'Fail'=>'b-red', default=>'b-yellow' };
                $sb = ($row['status']==='Completed') ? 'b-green' : 'b-blue';
              ?>
              <tr>
                <td style="color:#c5cbd5;font-size:12px;"><?= $i ?></td>
                <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($row['product_name']) ?></div>
                  <div style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($row['product_code']) ?></div>
                </td>
                <td style="color:#5b6475;"><?= htmlspecialchars($row['test_name']) ?></td>
                <td><?= htmlspecialchars($row['tested_by']) ?></td>
                <td style="color:#94a3b8;white-space:nowrap;"><?= date('d M Y', strtotime($row['testing_date'])) ?></td>
                <td><span class="badge <?= $rb ?>"><?= $row['result'] ?></span></td>
                <td><span class="badge <?= $sb ?>"><?= $row['status'] ?></span></td>
              </tr>
              <?php $i++; endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:#c5cbd5;">No records match the filters.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="print-only" style="display:none;padding:14px 18px;border-top:1px solid #e8edf3;font-size:11.5px;color:#94a3b8;text-align:center;">
        Lab Automation System — SRS Electrical Appliances &nbsp;|&nbsp; <?= date('d F Y h:i A') ?>
      </div>
    </div>

  </div><!-- /print-area -->
</div>

<style>
@media print {
  @page { size: A4 landscape; margin: 12mm; }
  body  { background: #fff !important; font-family: Arial, sans-serif !important; }
  .sidebar, .topbar, footer, .no-print { display: none !important; }
  .main-content { margin: 0 !important; padding: 0 !important; }
  .card { box-shadow: none !important; }
  .print-only { display: block !important; }
  .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  a { color: inherit !important; text-decoration: none !important; }
  .tbl tbody tr { page-break-inside: avoid; }
}
</style>

<?php include 'footer.php'; ?>