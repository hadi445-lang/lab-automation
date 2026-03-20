 <?php
include 'auth_check.php'; auth_check();
$success = $error = '';

if (isset($_POST['submit'])) {
    $tn = trim($_POST['type_name']    ?? '');
    $dc = trim($_POST['description']  ?? '');
    if (!$tn) {
        $error = "Type name is required.";
    } else {
        $chk = $conn->prepare("SELECT id FROM testing_types WHERE type_name=?");
        $chk->bind_param("s",$tn); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) {
            $error = "<strong>" . htmlspecialchars($tn) . "</strong> already exists.";
        } else {
            $ins = $conn->prepare("INSERT INTO testing_types (type_name,description) VALUES (?,?)");
            $ins->bind_param("ss",$tn,$dc);
            if ($ins->execute()) $success = "<strong>" . htmlspecialchars($tn) . "</strong> added successfully!";
            else $error = "Error: " . $conn->error;
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $uc  = (int)$conn->query("SELECT COUNT(*) c FROM tests WHERE testing_type_id=$did")->fetch_assoc()['c'];
    if ($uc > 0) {
        $error = "Cannot delete: this type is used in $uc test(s).";
    } else {
        $conn->query("DELETE FROM testing_types WHERE id=$did");
        $success = "Type deleted.";
    }
}

$types = $conn->query("SELECT tt.*, (SELECT COUNT(*) FROM tests WHERE testing_type_id=tt.id) tc FROM testing_types tt ORDER BY tt.created_at DESC");
include 'header.php';
?>
<div class="main-content">
  <div class="pg-hd">
    <div class="pg-title"><h2>Test Types</h2><p>Define and manage product testing categories.</p></div>
  </div>

  <?php if ($success) echo "<div class='alert alert-success'><i class='fas fa-circle-check'></i> $success</div>"; ?>
  <?php if ($error)   echo "<div class='alert alert-danger'><i class='fas fa-circle-xmark'></i> $error</div>"; ?>

  <div style="display:grid;grid-template-columns:320px 1fr;gap:18px;align-items:start;">
    <!-- Add Form -->
    <div class="card">
      <div class="card-hd"><h3><i class="fas fa-plus-circle"></i> Add New Type</h3></div>
      <div class="card-bd">
        <form method="POST" action="">
          <div class="fg" style="margin-bottom:16px;">
            <label>Type Name <span style="color:var(--red);">*</span></label>
            <input type="text" name="type_name" placeholder="e.g. Thermal Testing" required>
          </div>
          <div class="fg" style="margin-bottom:20px;">
            <label>Description / Criteria</label>
            <textarea name="description" rows="4" placeholder="Describe the testing methodology…"></textarea>
          </div>
          <button type="submit" name="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <i class="fas fa-plus"></i> Add Type
          </button>
        </form>
      </div>
    </div>

    <!-- Types Table -->
    <div class="card">
      <div class="card-hd">
        <h3><i class="fas fa-tags"></i> All Types
          <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:5px;"><?= $types ? $types->num_rows : 0 ?></span>
        </h3>
      </div>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead><tr><th>#</th><th>Type Name</th><th>Description</th><th>Tests</th><th>Action</th></tr></thead>
          <tbody>
            <?php
            if ($types && $types->num_rows > 0) {
              $i = 1;
              while ($t = $types->fetch_assoc()) {
                $desc = !empty($t['description'])
                  ? htmlspecialchars(substr($t['description'], 0, 58)) . (mb_strlen($t['description'])>58 ? '…' : '')
                  : '<em style="color:#c5cbd5;">—</em>';
                echo "<tr>
                  <td style='color:#c5cbd5;font-size:12px;'>$i</td>
                  <td style='font-weight:600;'>" . htmlspecialchars($t['type_name']) . "</td>
                  <td style='font-size:13px;color:#5b6475;'>$desc</td>
                  <td><span class='badge b-blue'>{$t['tc']}</span></td>
                  <td>
                    <a href='add_type.php?delete={$t['id']}' class='ab ab-del' title='Delete'
                       onclick=\"return confirm('Delete: " . htmlspecialchars($t['type_name'],ENT_QUOTES) . "?');\">
                       <i class='fas fa-trash'></i>
                    </a>
                  </td>
                </tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='5' style='text-align:center;padding:36px;color:#c5cbd5;'>No types yet.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
