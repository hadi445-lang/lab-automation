<?php
include 'auth_check.php'; auth_check();
$success = $error = '';

if (isset($_POST['submit'])) {
    $pid = (int)($_POST['product_id']      ?? 0);
    $tid = (int)($_POST['testing_type_id'] ?? 0);
    $td  = trim($_POST['test_date']   ?? '');
    $tn  = trim($_POST['tester_name'] ?? '');
    $rs  = trim($_POST['result']      ?? 'Pass');
    $rm  = trim($_POST['remarks']     ?? '');
    $st  = trim($_POST['status']      ?? 'In Progress');

    if (!$pid || !$tid || !$td || !$tn) {
        $error = "Please fill in all required fields.";
    } else {
        $pr = $conn->prepare("SELECT product_code FROM products WHERE id=?");
        $pr->bind_param("i",$pid); $pr->execute();
        $prow = $pr->get_result()->fetch_assoc();

        if (!$prow) {
            $error = "Selected product not found.";
        } else {
            // Generate unique 12-char Test ID
            $pcode = strtoupper(preg_replace('/[^A-Z0-9]/', '', strtoupper($prow['product_code'])));
            $uid   = strtoupper(substr($pcode . str_pad($tid,3,'0',STR_PAD_LEFT) . date('md') . rand(10,99), 0, 12));
            for ($a = 0; $a < 10; $a++) {
                $chk = $conn->prepare("SELECT id FROM tests WHERE test_id_unique=?");
                $chk->bind_param("s",$uid); $chk->execute(); $chk->store_result();
                if ($chk->num_rows === 0) break;
                $uid = strtoupper(substr($pcode . str_pad($tid,3,'0',STR_PAD_LEFT) . rand(1000,9999), 0, 12));
            }

            $ins = $conn->prepare("INSERT INTO tests (test_id_unique,product_id,testing_type_id,test_date,tester_name,result,remarks,status) VALUES (?,?,?,?,?,?,?,?)");
            $ins->bind_param("siisssss", $uid, $pid, $tid, $td, $tn, $rs, $rm, $st);
            if ($ins->execute()) {
                $success = "Test saved! ID: <strong>$uid</strong> &nbsp;<a href='view_tests.php' style='color:inherit;font-weight:700;'>View Records →</a>";
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}

$prods = $conn->query("SELECT id, product_name, product_code FROM products ORDER BY product_name ASC");
$types = $conn->query("SELECT id, type_name FROM testing_types ORDER BY type_name ASC");
include 'header.php';
?>
<div class="main-content">
  <div class="pg-hd">
    <div class="pg-title"><h2>Conduct Test</h2><p>Log a new test. Unique 12-digit Test ID generated automatically.</p></div>
    <a href="view_tests.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
  </div>

  <?php if ($success) echo "<div class='alert alert-success'><i class='fas fa-circle-check'></i> $success</div>"; ?>
  <?php if ($error)   echo "<div class='alert alert-danger'><i class='fas fa-circle-xmark'></i> $error</div>"; ?>

  <div class="card" style="max-width:820px;">
    <div class="card-hd">
      <h3><i class="fas fa-vial"></i> Test Entry Form</h3>
      <span style="font-size:12px;color:#94a3b8;background:#f8fafc;padding:4px 10px;border-radius:6px;border:1px solid var(--border);"><i class="fas fa-wand-magic-sparkles" style="color:var(--red);margin-right:4px;"></i>Auto Test ID</span>
    </div>
    <div class="card-bd">
      <form method="POST" action="">
        <div class="form-row">
          <div class="fg">
            <label>Select Product <span style="color:var(--red);">*</span></label>
            <select name="product_id" required>
              <option value="">-- Select Product --</option>
              <?php
              if ($prods && $prods->num_rows > 0) {
                while ($p = $prods->fetch_assoc()) {
                  $sel = (isset($_POST['product_id']) && (int)$_POST['product_id'] === $p['id']) ? 'selected' : '';
                  echo "<option value='{$p['id']}' $sel>" . htmlspecialchars($p['product_name']) . " (" . htmlspecialchars($p['product_code']) . ")</option>";
                }
              } else {
                echo "<option disabled>No products found — add one first</option>";
              }
              ?>
            </select>
          </div>
          <div class="fg">
            <label>Testing Type <span style="color:var(--red);">*</span></label>
            <select name="testing_type_id" required>
              <option value="">-- Select Type --</option>
              <?php
              if ($types && $types->num_rows > 0) {
                while ($t = $types->fetch_assoc()) {
                  $sel = (isset($_POST['testing_type_id']) && (int)$_POST['testing_type_id'] === $t['id']) ? 'selected' : '';
                  echo "<option value='{$t['id']}' $sel>" . htmlspecialchars($t['type_name']) . "</option>";
                }
              } else {
                echo "<option disabled>No types — add from sidebar</option>";
              }
              ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="fg">
            <label>Test Date <span style="color:var(--red);">*</span></label>
            <input type="date" name="test_date" value="<?= isset($_POST['test_date']) ? htmlspecialchars($_POST['test_date']) : date('Y-m-d') ?>" required>
          </div>
          <div class="fg">
            <label>Tester Name <span style="color:var(--red);">*</span></label>
            <input type="text" name="tester_name" placeholder="Full name of the technician"
                   value="<?= isset($_POST['tester_name']) ? htmlspecialchars($_POST['tester_name']) : '' ?>" required>
          </div>
        </div>
        <div class="form-row">
          <div class="fg">
            <label>Result <span style="color:var(--red);">*</span></label>
            <select name="result">
              <?php foreach (['Pass','Fail','Inconclusive'] as $r): ?>
              <option value="<?= $r ?>" <?= (isset($_POST['result']) && $_POST['result']===$r) ? 'selected' : '' ?>><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="fg">
            <label>Status</label>
            <select name="status">
              <option value="In Progress" <?= (!isset($_POST['status']) || $_POST['status']==='In Progress') ? 'selected' : '' ?>>In Progress</option>
              <option value="Completed"   <?= (isset($_POST['status']) && $_POST['status']==='Completed')   ? 'selected' : '' ?>>Completed</option>
            </select>
          </div>
        </div>
        <div class="fg" style="margin-bottom:20px;">
          <label>Observations / Remarks</label>
          <textarea name="remarks" rows="4" placeholder="Document measurements, observations, anomalies…"><?= isset($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : '' ?></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:15px;border-top:1px solid var(--border2);">
          <button type="reset"  class="btn btn-secondary"><i class="fas fa-undo"></i> Clear</button>
          <button type="submit" name="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Test Record</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
