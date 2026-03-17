<?php
include 'auth_check.php'; auth_check();

// Delete handler
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $tcResult = $conn->query("SELECT COUNT(*) c FROM tests WHERE product_id=$did");
    $tc = $tcResult ? (int)$tcResult->fetch_assoc()['c'] : 0;
    if ($tc > 0) {
        $_SESSION['flash'] = ['type'=>'warning', 'msg'=>"Cannot delete: $tc test(s) linked. Delete tests first."];
    } else {
        $conn->query("DELETE FROM products WHERE product_id=$did");
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Product deleted successfully.'];
    }
    header("Location: view_product.php"); exit();
}

$search = trim($_GET['search'] ?? '');

$where = "WHERE 1=1";
if ($search) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (product_name LIKE '%$s%' OR product_code LIKE '%$s%')";
}

$perPage = 8;
$page    = max(1, (int)($_GET['page'] ?? 1));

$countResult = $conn->query("SELECT COUNT(*) c FROM products $where");
if (!$countResult) {
    die("DB Error: " . $conn->error);
}
$total  = (int)$countResult->fetch_assoc()['c'];
$pages  = max(1, ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

$result = $conn->query("SELECT * FROM products $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
if (!$result) {
    die("DB Error: " . $conn->error);
}

include 'header.php';
?>
<div class="main-content">
  <div class="pg-hd">
    <div class="pg-title"><h2>Product Management</h2><p>All registered products and testing assignments.</p></div>
    <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
  </div>

  <?php if (!empty($_SESSION['flash'])): $fl = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert alert-<?= $fl['type'] ?>">
    <i class="fas fa-<?= $fl['type']==='success' ? 'circle-check' : 'triangle-exclamation' ?>"></i>
    <?= $fl['msg'] ?>
  </div>
  <?php endif; ?>

  <form method="GET" class="fbar">
    <input type="text" name="search" placeholder="Search by name or product code" value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <?php if ($search): ?>
    <a href="view_product.php" class="btn btn-secondary btn-sm">Clear</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <div class="card-hd">
      <h3><i class="fas fa-box-open"></i> Products
        <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:5px;"><?= $total ?></span>
      </h3>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Product Name</th>
            <th>Code</th>
            <th>Revised</th>
            <th>Mfg Number</th>
            <th>Tests</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php $i = $offset + 1; while ($row = $result->fetch_assoc()): ?>
          <?php
            $pid = $row['product_id'];
            $tcR = $conn->query("SELECT COUNT(*) c FROM tests WHERE product_id=$pid");
            $tc  = $tcR ? (int)$tcR->fetch_assoc()['c'] : 0;
          ?>
          <tr>
            <td style="color:#c5cbd5;font-size:12px;"><?= $i ?></td>
            <td style="font-weight:600;"><?= htmlspecialchars($row['product_name']) ?></td>
            <td><code style="font-size:12px;background:#f8fafc;padding:2px 8px;border-radius:5px;border:1px solid #e8edf3;"><?= htmlspecialchars($row['product_code']) ?></code></td>
            <td style="color:#5b6475;font-size:13px;"><?= htmlspecialchars($row['product_revise'] ?? '—') ?></td>
            <td style="color:#5b6475;font-size:13px;"><?= htmlspecialchars($row['manufacturing_number'] ?? '—') ?></td>
            <td><a href="view_tests.php?product_id=<?= $pid ?>" style="color:var(--red);font-weight:700;font-size:13px;"><?= $tc ?> test<?= $tc != 1 ? 's' : '' ?></a></td>
            <td style="color:#94a3b8;font-size:12.5px;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
            <td style="white-space:nowrap;">
              <a href="edit.php?id=<?= $pid ?>" class="ab ab-edit" title="Edit"><i class="fas fa-pen"></i></a>
              <a href="view_product.php?delete=<?= $pid ?>" class="ab ab-del" title="Delete"
                 onclick="return confirm('Delete <?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?>?');"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
          <?php $i++; endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align:center;padding:48px;color:#c5cbd5;">
              <i class="fas fa-box-open" style="font-size:34px;display:block;margin-bottom:10px;opacity:.25;"></i>
              No products found. <a href="add_product.php" style="color:var(--red);">Add one</a>
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
          $qs   = http_build_query(array_filter(['search' => $search]));
          $base = "view_product.php?" . ($qs ? "$qs&" : '');
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