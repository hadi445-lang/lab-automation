<?php
include 'auth_check.php'; auth_check();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $conn->query("DELETE FROM testing_records WHERE testing_id=" . (int)$_GET['delete']);
    $_SESSION['flash'] = ['type'=>'success','msg'=>'Test record deleted.'];
    header("Location: view_tests.php"); exit();
}

$search = trim($_GET['search'] ?? '');
$fr     = trim($_GET['result'] ?? '');
$fpid   = (int)($_GET['product_id'] ?? 0);

$where = "WHERE 1=1";
if ($search) { $s=$conn->real_escape_string($search); $where.=" AND (p.product_name LIKE '%$s%' OR t.tested_by LIKE '%$s%')"; }
if ($fr)     { $r=$conn->real_escape_string($fr);     $where.=" AND t.result='$r'"; }
if ($fpid)   { $where.=" AND t.product_id=$fpid"; }

$join = "FROM testing_records t 
         JOIN products p ON t.product_id=p.product_id 
         JOIN test_types tt ON t.test_type_id=tt.test_id";

$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));

$countQ = $conn->query("SELECT COUNT(*) c $join $where");
if (!$countQ) { die("DB Error: " . $conn->error); }
$total  = (int)$countQ->fetch_assoc()['c'];
$pages  = max(1, ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$result = $conn->query("SELECT t.*, p.product_name, p.product_code, tt.test_name $join $where ORDER BY t.created_at DESC LIMIT $perPage OFFSET $offset");
if (!$result) { die("DB Error: " . $conn->error); }

$tall  = (int)$conn->query("SELECT COUNT(*) c FROM testing_records")->fetch_assoc()['c'];
$tpass = (int)$conn->query("SELECT COUNT(*) c FROM testing_records WHERE result='Pass'")->fetch_assoc()['c'];
$tfail = (int)$conn->query("SELECT COUNT(*) c FROM testing_records WHERE result='Fail'")->fetch_assoc()['c'];

include 'header.php';
?>
<div class="main-content">
  <div class="pg-hd">
    <div class="pg-title"><h2>Test Records</h2><p>All test entries with result tracking.</p></div>
    <div style="display:flex;gap:9px;">
      <a href="test_report.php" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf"></i> Export PDF</a>
      <a href="add_tests.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Test</a>
    </div>
  </div>

  <?php if (!empty($_SESSION['flash'])): $fl=$_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert alert-<?= $fl['type'] ?>"><i class="fas fa-circle-check"></i> <?= $fl['msg'] ?></div>
  <?php endif; ?>

  <!-- Mini stats -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
    <?php foreach([['Total Tests',$tall,'ic-blue','fa-vial'],['Passed',$tpass,'ic-green','fa-circle-check'],['Failed',$tfail,'ic-red','fa-circle-xmark']] as $m): ?>
    <div class="stat-card" style="padding:15px 17px;">
      <div class="st-ic <?= $m[2] ?>" style="width:40px;height:40px;border-radius:9px;"><i class="fas <?= $m[3] ?>"></i></div>
      <div><div class="st-val" style="font-size:23px;"><?= $m[1] ?></div><div class="st-lbl"><?= $m[0] ?></div></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Filter -->
  <form method="GET" class="fbar">
    <input type="text" name="search" placeholder="Search product or tester…" value="<?= htmlspecialchars($search) ?>">
    <select name="result">
      <option value="">All Results</option>
      <?php foreach(['Pass','Fail','Inconclusive'] as $r): ?>
      <option value="<?= $r ?>" <?= $fr===$r?'selected':'' ?>><?= $r ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <?php if($search||$fr||$fpid): ?>
    <a href="view_tests.php" class="btn btn-secondary btn-sm">Clear</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <div class="card-hd">
      <h3><i class="fas fa-clipboard-list"></i> Tests
        <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:5px;"><?= $total ?></span>
      </h3>
      <a href="test_report.php" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Print</a>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Product</th>
            <th>Test Type</th>
            <th>Tester</th>
            <th>Date</th>
            <th>Result</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php $i = $offset + 1; while ($row = $result->fetch_assoc()): ?>
          <?php
            $rb = match($row['result']) { 'Pass'=>'b-green', 'Fail'=>'b-red', default=>'b-yellow' };
            $sb = ($row['status']==='Completed') ? 'b-green' : 'b-blue';
          ?>
          <tr>
            <td style="color:#c5cbd5;font-size:12px;"><?= $i ?></td>
            <td>
              <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($row['product_name']) ?></div>
              <div style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($row['product_code']) ?></div>
            </td>
            <td style="color:#5b6475;font-size:13px;"><?= htmlspecialchars($row['test_name']) ?></td>
            <td style="font-size:13px;"><?= htmlspecialchars($row['tested_by']) ?></td>
            <td style="color:#94a3b8;font-size:12.5px;"><?= date('d M Y', strtotime($row['testing_date'])) ?></td>
            <td><span class="badge <?= $rb ?>"><?= $row['result'] ?></span></td>
            <td><span class="badge <?= $sb ?>"><?= $row['status'] ?></span></td>
            <td style="white-space:nowrap;">
              <a href="edit_tests.php?id=<?= $row['testing_id'] ?>" class="ab ab-edit" title="Edit"><i class="fas fa-pen"></i></a>
              <a href="view_tests.php?delete=<?= $row['testing_id'] ?>" class="ab ab-del" title="Delete"
                 onclick="return confirm('Delete this test?');"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
          <?php $i++; endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align:center;padding:48px;color:#c5cbd5;">
              <i class="fas fa-vial" style="font-size:34px;display:block;margin-bottom:10px;opacity:.25;"></i>
              No records found. <a href="add_tests.php" style="color:var(--red);">Conduct a test</a>
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="pag">
      <span class="pag-info">Showing <?= $offset+1 ?>–<?= min($offset+$perPage,$total) ?> of <?= $total ?></span>
      <div class="pag-links">
        <?php
          $qs   = http_build_query(array_filter(['search'=>$search,'result'=>$fr,'product_id'=>$fpid?:null]));
          $base = "view_tests.php?" . ($qs ? "$qs&" : '');
          echo "<a href='{$base}page=" . max(1,$page-1) . "' class='pg-btn " . ($page<=1?'off':'') . "'><i class='fas fa-chevron-left'></i></a>";
          for ($p = max(1,$page-2); $p <= min($pages,$page+2); $p++)
            echo "<a href='{$base}page=$p' class='pg-btn " . ($p==$page?'on':'') . "'>$p</a>";
          echo "<a href='{$base}page=" . min($pages,$page+1) . "' class='pg-btn " . ($page>=$pages?'off':'') . "'><i class='fas fa-chevron-right'></i></a>";
        ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include 'footer.php'; ?>