<?php
include 'auth_check.php'; auth_check();
$success = $error = '';

if (isset($_POST['submit'])) {
    $pn  = trim($_POST['product_name']         ?? '');
    $pc  = trim($_POST['product_code']          ?? '');
    $pr  = trim($_POST['product_revision']      ?? '');
    $mn  = trim($_POST['manufacturing_number']  ?? '');
    $dc  = trim($_POST['description']           ?? '');

    if (!$pn || !$pc) {
        $error = "Product Name and Code are required.";
    } else {
        // Duplicate product_code check
        $chk = $conn->prepare("SELECT product_code FROM products WHERE product_code = ?");
        $chk->bind_param("s", $pc);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = "Product Code <strong>" . htmlspecialchars($pc) . "</strong> already exists.";
        } else {
            // product_id = product_code ko use karo (teri table mein varchar primary key hai)
            $stmt = $conn->prepare("INSERT INTO products (product_id, product_code, product_name, product_revise, manufacturing_number, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $pc, $pc, $pn, $pr, $mn, $dc);
            // Note: product_id = product_code (dono same value, kyunki teri table mein product_id varchar hai)
            if ($stmt->execute()) {
                $success = "Product <strong>" . htmlspecialchars($pn) . "</strong> added successfully! &nbsp;<a href='view_product.php' style='color:inherit;font-weight:700;'>View Products →</a>";
                // Form clear karne ke liye POST data reset
                $_POST = [];
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}

include 'header.php';
?>
<div class="main-content">
  <div class="pg-hd">
    <div class="pg-title">
      <h2>Add Product</h2>
      <p>Register a new product into the system.</p>
    </div>
    <a href="view_product.php" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </div>

  <?php if ($success) echo "<div class='alert alert-success'><i class='fas fa-circle-check'></i> $success</div>"; ?>
  <?php if ($error)   echo "<div class='alert alert-danger'><i class='fas fa-circle-xmark'></i> $error</div>"; ?>

  <div class="card" style="max-width:820px;">
    <div class="card-hd">
      <h3><i class="fas fa-box-open"></i> Product Details</h3>
    </div>
    <div class="card-bd">
      <form method="POST" action="">

        <!-- Row 1: Product Name + Product Code -->
        <div class="form-row">
          <div class="fg">
            <label>Product Name <span style="color:var(--red);">*</span></label>
            <input type="text" name="product_name"
                   placeholder="e.g. Switch Gear Type A"
                   value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>"
                   required>
          </div>
          <div class="fg">
            <label>Product Code (Unique) <span style="color:var(--red);">*</span></label>
            <input type="text" name="product_code"
                   placeholder="e.g. PROD-001"
                   value="<?= htmlspecialchars($_POST['product_code'] ?? '') ?>"
                   required>
          </div>
        </div>

        <!-- Row 2: Product Revision + Manufacturing Number -->
        <div class="form-row">
          <div class="fg">
            <label>Product Revision</label>
            <input type="text" name="product_revision"
                   placeholder="e.g. Rev-A, v1.2"
                   value="<?= htmlspecialchars($_POST['product_revision'] ?? '') ?>">
          </div>
          <div class="fg">
            <label>Manufacturing Number</label>
            <input type="text" name="manufacturing_number"
                   placeholder="e.g. MFG-2024-001"
                   value="<?= htmlspecialchars($_POST['manufacturing_number'] ?? '') ?>">
          </div>
        </div>

        <!-- Row 3: Description -->
        <div class="fg" style="margin-bottom:20px;">
          <label>Description</label>
          <textarea name="description" rows="4"
                    placeholder="Product specifications, notes, or observations…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <!-- Buttons -->
        <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:15px;border-top:1px solid var(--border2);">
          <button type="reset"  class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</button>
          <button type="submit" name="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</button>
        </div>

      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>