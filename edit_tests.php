<?php
include 'auth_check.php'; auth_check();
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: view_tests.php"); exit(); }
$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT t.*, p.product_name, p.product_code, tt.type_name FROM tests t JOIN products p ON t.product_id=p.id JOIN testing_types tt ON t.testing_type_id=tt.id WHERE t.id=?");
$stmt->bind_param("i",$id); $stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) { header("Location: view_tests.php"); exit(); }

$success = $error = '';
if (isset($_POST['update'])) {
    $rs = $_POST['result'] ?? 'Pass';
    $st = $_POST['status'] ?? 'Completed';
    $rm = trim($_POST['remarks'] ?? '');
    $upd = $conn->prepare("UPDATE tests SET result=?, status=?, remarks=? WHERE id=?");
    $upd->bind_param("sssi",$rs,$st,$rm,$id);
    if ($upd->execute()) {
        $success = "Test record updated successfully!";
        $stmt->execute(); $data = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Error: " . $conn->error;
    }
}
include 'header.php';
?>
<div class="main-content">
  <div class="pg-hd">
    <div class="pg-title"><h2>Update Test</h2><p>ID: <strong><?= htmlspecialchars($data['test_id_unique']) ?></strong></p></div>
    <a href="view_tests.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
  </div>

  <?php if ($success) echo "<div class='alert alert-success'><i class='fas fa-circle-check'></i> $success</div>"; ?>
  <?php if ($error)   echo "<div class='alert alert-danger'><i class='fas fa-circle-xmark'></i> $error</div>"; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
    <div class="card"><div class="card-bd">
      <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Product</p>
      <p style="font-weight:700;"><?= htmlspecialchars($data['product_name']) ?></p>
      <p style="font-size:12px;color:#94a3b8;"><?= htmlspecialchars($data['product_code']) ?></p>
    </div></div>
    <div class="card"><div class="card-bd">
      <p style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Test Info</p>
      <p style="font-weight:700;"><?= htmlspecialchars($data['type_name']) ?></p>
      <p style="font-size:12px;color:#94a3b8;">By <?= htmlspecialchars($data['tester_name']) ?> &mdash; <?= date('d M Y', strtotime($data['test_date'])) ?></p>
    </div></div>
  </div>

  <div class="card" style="max-width:620px;">
    <div class="card-hd"><h3><i class="fas fa-pen"></i> Update Result &amp; Status</h3></div>
    <div class="card-bd">
      <form method="POST" action="">
        <div class="form-row">
          <div class="fg">
            <label>Result</label>
            <select name="result">
              <?php foreach (['Pass','Fail','Inconclusive'] as $r): ?>
              <option value="<?= $r ?>" <?= $data['result']===$r ? 'selected' : '' ?>><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="fg">
            <label>Status</label>
            <select name="status">
              <option value="In Progress" <?= $data['status']==='In Progress' ? 'selected' : '' ?>>In Progress</option>
              <option value="Completed"   <?= $data['status']==='Completed'   ? 'selected' : '' ?>>Completed</option>
            </select>
          </div>
        </div>
        <div class="fg" style="margin-bottom:20px;">
          <label>Remarks / Observations</label>
          <textarea name="remarks" rows="5"><?= htmlspecialchars($data['remarks'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:15px;border-top:1px solid var(--border2);">
          <a href="view_tests.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Update Record</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
