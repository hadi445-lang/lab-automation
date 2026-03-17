<?php
include 'auth_check.php'; auth_check();
$q      = trim($_GET['query']  ?? '');
$filter = trim($_GET['filter'] ?? 'all');
$pr = $tr = null;
$pc = $tc = 0;

if ($q) {
    $s = $conn->real_escape_string($q);
    if ($filter === 'all' || $filter === 'product') {
        $pr = $conn->query("SELECT * FROM products WHERE product_name LIKE '%$s%' OR product_code LIKE '%$s%' ORDER BY created_at DESC");
        $pc = $pr ? $pr->num_rows : 0;
    }
    if ($filter === 'all' || $filter === 'test') {
        $tr = $conn->query("SELECT t.*, p.product_name, p.product_code, tt.type_name FROM tests t JOIN products p ON t.product_id=p.id JOIN testing_types tt ON t.testing_type_id=tt.id WHERE t.test_id_unique LIKE '%$s%' OR p.product_name LIKE '%$s%' OR t.tester_name LIKE '%$s%' OR t.result LIKE '%$s%' ORDER BY t.created_at DESC");
        $tc = $tr ? $tr->num_rows : 0;
    }
}
include 'header.php';
?>
<div class="main-content">
  <div class="pg-hd">
    <div class="pg-title">
      <h2>Search</h2>
      <p><?= $q ? "Found <strong>" . ($pc+$tc) . "</strong> result(s) for &ldquo;<strong>" . htmlspecialchars($q) . "</strong>&rdquo;" : "Search across products and tests." ?></p>
    </div>
  </div>

  <!-- Search Box -->
  <div class="card" style="margin-bottom:20px;">
    <div class="card-bd" style="padding:13px 17px;">
      <form method="GET" action="" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <i class="fas fa-magnifying-glass" style="color:#c5cbd5;flex-shrink:0;"></i>
        <input type="text" name="query" value="<?= htmlspecialchars($q) ?>"
               placeholder="Search products, test IDs, tester names…"
               style="flex:1;min-width:180px;border:none;outline:none;font-family:inherit;font-size:14px;background:transparent;color:var(--text);">
        <select name="filter" style="padding:7px 11px;border:1.5px solid #e0e5ed;border-radius:8px;font-family:inherit;font-size:13px;color:var(--text);background:#fff;outline:none;">
          <option value="all"     <?= $filter==='all'     ? 'selected' : '' ?>>All</option>
          <option value="product" <?= $filter==='product' ? 'selected' : '' ?>>Products Only</option>
          <option value="test"    <?= $filter==='test'    ? 'selected' : '' ?>>Tests Only</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
        <?php if ($q): ?><a href="search_results.php" class="btn btn-secondary btn-sm">Clear</a><?php endif; ?>
      </form>
    </div>
  </div>

  <?php if (!$q): ?>
  <div style="text-align:center;padding:80px 20px;color:#c5cbd5;">
    <i class="fas fa-magnifying-glass" style="font-size:46px;display:block;margin-bottom:14px;opacity:.2;"></i>
    <p style="font-size:15px;">Type a keyword above to search.</p>
  </div>

  <?php elseif ($pc + $tc === 0): ?>
  <div style="text-align:center;padding:80px 20px;color:#c5cbd5;">
    <i class="fas fa-file-circle-question" style="font-size:46px;display:block;margin-bottom:14px;opacity:.2;"></i>
    <p style="font-size:15px;">No results for &ldquo;<strong style="color:#374151;"><?= htmlspecialchars($q) ?></strong>&rdquo;.</p>
    <p style="font-size:13px;margin-top:6px;">Try a different keyword or check spelling.</p>
  </div>

  <?php else: ?>

  <!-- Products Results -->
  <?php if ($pr && $pr->num_rows > 0): ?>
  <div class="card" style="margin-bottom:18px;">
    <div class="card-hd">
      <h3><i class="fas fa-box-open"></i> Products
        <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:5px;"><?= $pc ?></span>
      </h3>
      <a href="add_product.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Product</a>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>#</th><th>Product Name</th><th>Code</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php $i=1; while ($p=$pr->fetch_assoc()):
            $bm=['Completed'=>'b-green','Pending'=>'b-yellow','Failed'=>'b-red','In Progress'=>'b-blue'];
            $bc=$bm[$p['status']]??'b-gray';
          ?>
          <tr>
            <td style="color:#c5cbd5;font-size:12px;"><?= $i ?></td>
            <td style="font-weight:600;"><?= htmlspecialchars($p['product_name']) ?></td>
            <td><code style="font-size:12px;background:#f8fafc;padding:2px 8px;border-radius:5px;border:1px solid #e8edf3;"><?= htmlspecialchars($p['product_code']) ?></code></td>
            <td style="color:#5b6475;font-size:13px;"><?= htmlspecialchars($p['testing_type'] ?? '—') ?></td>
            <td><span class="badge <?= $bc ?>"><?= $p['status'] ?></span></td>
            <td style="white-space:nowrap;">
              <a href="edit.php?id=<?= $p['id'] ?>"       class="ab ab-edit" title="Edit"><i class="fas fa-pen"></i></a>
              <a href="view_tests.php?product_id=<?= $p['id'] ?>" class="ab ab-view" title="Tests"><i class="fas fa-vial"></i></a>
            </td>
          </tr>
          <?php $i++; endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Tests Results -->
  <?php if ($tr && $tr->num_rows > 0): ?>
  <div class="card">
    <div class="card-hd">
      <h3><i class="fas fa-vial"></i> Test Records
        <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:5px;"><?= $tc ?></span>
      </h3>
      <a href="add_tests.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Test</a>
    </div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Test ID</th><th>Product</th><th>Type</th><th>Tester</th><th>Date</th><th>Result</th><th>Actions</th></tr></thead>
        <tbody>
          <?php while ($t=$tr->fetch_assoc()):
            $rb=match($t['result']){'Pass'=>'b-green','Fail'=>'b-red',default=>'b-yellow'};
          ?>
          <tr>
            <td><code style="font-size:12px;background:#f8fafc;padding:2px 7px;border-radius:5px;border:1px solid #e8edf3;font-family:monospace;"><?= htmlspecialchars($t['test_id_unique']) ?></code></td>
            <td>
              <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($t['product_name']) ?></div>
              <div style="font-size:11px;color:#94a3b8;"><?= htmlspecialchars($t['product_code']) ?></div>
            </td>
            <td style="color:#5b6475;font-size:13px;"><?= htmlspecialchars($t['type_name']) ?></td>
            <td style="font-size:13px;"><?= htmlspecialchars($t['tester_name']) ?></td>
            <td style="color:#94a3b8;font-size:12.5px;"><?= date('d M Y', strtotime($t['test_date'])) ?></td>
            <td><span class="badge <?= $rb ?>"><?= $t['result'] ?></span></td>
            <td>
              <a href="edit_tests.php?id=<?= $t['id'] ?>" class="ab ab-edit" title="Edit"><i class="fas fa-pen"></i></a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
