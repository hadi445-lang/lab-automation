<?php // edit.php - FINAL
include 'auth_check.php'; auth_check();
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: view_product.php"); exit(); }
$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i",$id); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) { header("Location: view_product.php"); exit(); }
$success = $error = '';
if (isset($_POST['update'])) {
    $pn=$_POST['product_name']??''; $pc=$_POST['product_code']??'';
    $tt=$_POST['testing_type']??''; $st=$_POST['status']??'Pending'; $rm=$_POST['remarks']??'';
    $chk=$conn->prepare("SELECT id FROM products WHERE product_code=? AND id!=?");
    $chk->bind_param("si",$pc,$id); $chk->execute(); $chk->store_result();
    if ($chk->num_rows>0) { $error="Code already used by another product."; }
    else {
        $upd=$conn->prepare("UPDATE products SET product_name=?,product_code=?,testing_type=?,status=?,remarks=? WHERE id=?");
        $upd->bind_param("sssssi",$pn,$pc,$tt,$st,$rm,$id);
        if ($upd->execute()) { $success="Updated!"; $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); }
        else { $error="Error: ".$conn->error; }
    }
}
$types=$conn->query("SELECT id,type_name FROM testing_types ORDER BY type_name");
include 'header.php';
?>
<div class="main-content">
<div class="pg-hd">
  <div class="pg-title"><h2>Edit Product</h2><p>Updating: <strong><?=htmlspecialchars($row['product_name'])?></strong></p></div>
  <a href="view_product.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<?php if($success) echo "<div class='alert alert-success'><i class='fas fa-circle-check'></i> $success</div>"; ?>
<?php if($error)   echo "<div class='alert alert-danger'><i class='fas fa-circle-xmark'></i> $error</div>"; ?>
<div class="card" style="max-width:820px;">
  <div class="card-hd"><h3><i class="fas fa-pen"></i> Edit Product Details</h3></div>
  <div class="card-bd">
    <form method="POST">
      <div class="form-row">
        <div class="fg"><label>Product Name *</label><input type="text" name="product_name" value="<?=htmlspecialchars($row['product_name'])?>" required></div>
        <div class="fg"><label>Product Code *</label><input type="text" name="product_code" value="<?=htmlspecialchars($row['product_code'])?>" required></div>
      </div>
      <div class="form-row">
        <div class="fg"><label>Testing Type</label>
          <select name="testing_type">
            <option value="">-- Select --</option>
            <?php
            if($types&&$types->num_rows>0){while($t=$types->fetch_assoc()){$s=($row['testing_type']===$t['type_name'])?'selected':'';echo "<option value='".htmlspecialchars($t['type_name'])."' $s>".htmlspecialchars($t['type_name'])."</option>";}}
            else{foreach(['Electrical Testing','Thermal Testing','Mechanical Testing','Safety Testing']as $d){$s=($row['testing_type']===$d)?'selected':'';echo "<option value='$d' $s>$d</option>";}}
            ?>
          </select>
        </div>
        <div class="fg"><label>Status</label>
          <select name="status"><?php foreach(['Pending','In Progress','Completed','Failed']as $s){echo "<option value='$s' ".($row['status']===$s?'selected':'').">$s</option>";}?></select>
        </div>
      </div>
      <div class="fg" style="margin-bottom:20px;"><label>Remarks</label><textarea name="remarks" rows="4"><?=htmlspecialchars($row['remarks']??'')?></textarea></div>
      <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:15px;border-top:1px solid var(--border2);">
        <a href="view_product.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>
</div>
<?php include 'footer.php'; ?>
