<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/dbcon.php';
$cp = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lab Automation — SRS Electrical</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ══ DESIGN TOKENS ══ */
:root {
  --red:#e8342a; --red2:#c0281f; --red3:#ff6055; --red-a:rgba(232,52,42,.13);
  --sb:#080c12; --sb2:#0f1520; --sb3:#161f2e; --sb4:#1e2a3d;
  --sb-bd:rgba(255,255,255,.055); --sb-dim:rgba(255,255,255,.32); --sb-mid:rgba(255,255,255,.6);
  --text:#0a0d13; --text2:#5a6478; --text3:#94a3b8;
  --bg:#eef1f7; --white:#ffffff;
  --border:#e2e8f0; --border2:#f0f4f9;
  --shadow:0 1px 3px rgba(0,0,0,.04),0 4px 16px rgba(0,0,0,.06);
  --shadow-md:0 4px 24px rgba(0,0,0,.09);
  --radius:11px; --radius-sm:8px;
  --sb-w:262px; --tb-h:62px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Space Grotesk',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}
a{text-decoration:none;}

/* ══ SIDEBAR ══ */
.sidebar{
  position:fixed;top:0;left:0;width:var(--sb-w);height:100vh;
  background:var(--sb);display:flex;flex-direction:column;
  z-index:1000;overflow-y:auto;overflow-x:hidden;
  transition:transform .26s cubic-bezier(.4,0,.2,1);
  scrollbar-width:none;
  border-right:1px solid rgba(255,255,255,.04);
}
.sidebar::-webkit-scrollbar{display:none;}

/* Subtle grid texture in sidebar */
.sidebar::before{
  content:'';position:absolute;inset:0;
  background-image:linear-gradient(rgba(255,255,255,.018) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.018) 1px,transparent 1px);
  background-size:40px 40px;pointer-events:none;
}

/* Brand */
.sb-brand{
  position:relative;padding:20px 16px 18px;
  border-bottom:1px solid var(--sb-bd);
  display:flex;align-items:center;gap:11px;flex-shrink:0;
}
.sb-logo{
  width:36px;height:36px;border-radius:10px;flex-shrink:0;
  background:linear-gradient(135deg,var(--red),var(--red2));
  display:flex;align-items:center;justify-content:center;
  font-size:15px;color:#fff;
  box-shadow:0 0 0 1px rgba(232,52,42,.25),0 6px 18px rgba(232,52,42,.28);
}
.sb-brand-txt h2{font-family:'Outfit',sans-serif;font-size:13px;font-weight:700;color:#fff;line-height:1.2;}
.sb-brand-txt span{font-size:10.5px;color:var(--sb-dim);margin-top:1px;display:block;}

/* User card */
.sb-user{
  position:relative;margin:10px 10px 6px;padding:10px 12px;
  background:rgba(255,255,255,.04);border-radius:9px;
  border:1px solid var(--sb-bd);
  display:flex;align-items:center;gap:10px;
}
.sb-av{
  width:30px;height:30px;border-radius:7px;flex-shrink:0;
  background:linear-gradient(135deg,var(--red),#ff7b5b);
  display:flex;align-items:center;justify-content:center;
  font-family:'Outfit',sans-serif;font-size:12px;font-weight:700;color:#fff;
}
.sb-uname{font-size:12.5px;font-weight:600;color:#fff;line-height:1.3;}
.sb-urole{font-size:10px;color:var(--sb-dim);margin-top:1px;}
.sb-online{
  width:7px;height:7px;border-radius:50%;background:#22c55e;
  box-shadow:0 0 0 2px rgba(34,197,94,.25);
  margin-left:auto;flex-shrink:0;
}

/* Navigation */
.sb-nav{position:relative;padding:8px 0 4px;flex:1;}
.sb-sec{
  font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  color:rgba(255,255,255,.18);padding:12px 16px 4px;display:block;
}
.sb-a{
  position:relative;display:flex;align-items:center;gap:9px;
  padding:9px 16px;margin:1px 7px;border-radius:8px;
  color:rgba(255,255,255,.42);font-size:13px;font-weight:500;
  text-decoration:none;transition:all .15s;
}
.sb-a:hover{background:rgba(255,255,255,.06);color:rgba(255,255,255,.82);}
.sb-a.on{background:var(--red-a);color:#fff;font-weight:600;}
.sb-a.on::before{
  content:'';position:absolute;left:0;top:20%;bottom:20%;
  width:3px;border-radius:0 2px 2px 0;background:var(--red);
}
.sb-a .sbi{
  width:30px;height:30px;border-radius:7px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:12px;transition:all .15s;
}
.sb-a:hover .sbi{background:rgba(255,255,255,.07);}
.sb-a.on .sbi{background:rgba(232,52,42,.18);color:var(--red3);}
.sb-a .slabel{line-height:1;}

/* Footer logout */
.sb-foot{position:relative;padding:10px;border-top:1px solid var(--sb-bd);flex-shrink:0;}
.sb-logout{
  display:flex;align-items:center;gap:9px;
  padding:9px 12px;border-radius:8px;
  background:rgba(232,52,42,.09);color:rgba(232,52,42,.85);
  text-decoration:none;font-size:13px;font-weight:600;
  transition:all .15s;border:1px solid rgba(232,52,42,.15);
}
.sb-logout:hover{background:var(--red);color:#fff;}
.sb-logout i{font-size:12.5px;}

/* ══ TOPBAR ══ */
.topbar{
  position:fixed;top:0;left:var(--sb-w);right:0;
  height:var(--tb-h);background:var(--white);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;padding:0 22px;
  z-index:999;gap:12px;
}
.tb-ham{display:none;background:none;border:none;font-size:18px;color:var(--text2);cursor:pointer;padding:7px;border-radius:8px;flex-shrink:0;}
.tb-ham:hover{background:var(--bg);}
.breadcrumb{display:flex;align-items:center;gap:5px;font-size:13px;flex:1;flex-wrap:wrap;}
.breadcrumb a{color:var(--red);font-weight:500;}
.breadcrumb a:hover{text-decoration:underline;}
.breadcrumb .sep{color:#d1d9e6;font-size:9px;}
.breadcrumb .cur{color:var(--text2);}

/* Topbar right actions */
.tb-right{display:flex;align-items:center;gap:7px;flex-shrink:0;}
.tb-btn{
  width:34px;height:34px;border-radius:8px;
  background:var(--bg);display:flex;align-items:center;justify-content:center;
  color:var(--text2);font-size:13px;
  transition:all .15s;border:none;cursor:pointer;text-decoration:none;
}
.tb-btn:hover{background:#e2e8f0;color:var(--text);}
.tb-srch{
  display:flex;align-items:center;gap:8px;
  padding:6px 12px;background:var(--bg);border-radius:8px;
  border:1.5px solid var(--border);
  font-size:13px;color:var(--text2);font-family:'Space Grotesk',sans-serif;
  cursor:pointer;transition:border-color .15s;
  text-decoration:none;
}
.tb-srch:hover{border-color:var(--red);color:var(--text);}
.tb-srch kbd{font-size:10px;padding:1px 5px;background:#e8edf5;border-radius:4px;border:1px solid #d1d9e6;color:#94a3b8;font-family:inherit;}
.tb-divider{width:1px;height:20px;background:var(--border);flex-shrink:0;}
.tb-user{
  display:flex;align-items:center;gap:8px;
  padding:5px 10px 5px 5px;border-radius:8px;
  background:var(--bg);border:1.5px solid var(--border);
  cursor:pointer;transition:all .15s;
}
.tb-user:hover{background:#e2e8f0;}
.tb-uav{
  width:26px;height:26px;border-radius:7px;
  background:linear-gradient(135deg,var(--red),var(--red3));
  display:flex;align-items:center;justify-content:center;
  font-family:'Outfit',sans-serif;font-size:11px;font-weight:700;color:#fff;
}
.tb-uname{font-size:13px;font-weight:600;color:var(--text);}

/* ══ MAIN CONTENT ══ */
.main-content{margin-left:var(--sb-w);margin-top:var(--tb-h);padding:24px;min-height:calc(100vh - var(--tb-h));}

/* ══ PAGE HEADER ══ */
.pg-hd{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:14px;flex-wrap:wrap;}
.pg-title h2{font-family:'Outfit',sans-serif;font-size:20px;font-weight:800;color:var(--text);margin-bottom:3px;}
.pg-title p{color:var(--text2);font-size:13px;}

/* ══ BUTTONS ══ */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-family:'Space Grotesk',sans-serif;font-size:13.5px;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .15s;white-space:nowrap;}
.btn-primary{background:var(--red);color:#fff;}
.btn-primary:hover{background:var(--red2);transform:translateY(-1px);box-shadow:0 5px 14px rgba(232,52,42,.27);}
.btn-secondary{background:var(--white);color:var(--text);border:1.5px solid var(--border);}
.btn-secondary:hover{background:var(--bg);}
.btn-ghost{background:transparent;color:var(--text2);border:1px solid var(--border);}
.btn-ghost:hover{background:var(--bg);color:var(--text);}
.btn-sm{padding:7px 12px;font-size:12.5px;}

/* ══ CARDS ══ */
.card{background:var(--white);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow);}
.card-hd{padding:14px 18px;border-bottom:1px solid var(--border2);display:flex;align-items:center;justify-content:space-between;}
.card-hd h3{font-size:13.5px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:7px;}
.card-hd h3 i{color:var(--red);}
.card-bd{padding:18px;}

/* ══ STAT CARDS ══ */
.stat-card{background:var(--white);border-radius:var(--radius);padding:17px;display:flex;align-items:center;gap:13px;border:1px solid var(--border);box-shadow:var(--shadow);transition:transform .18s,box-shadow .18s;}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);}
.st-ic{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.ic-blue{background:#eff6ff;color:#2563eb;} .ic-green{background:#f0fdf4;color:#16a34a;}
.ic-red{background:#fff1f1;color:var(--red);} .ic-orange{background:#fff7ed;color:#ea580c;}
.ic-purple{background:#f5f3ff;color:#7c3aed;} .ic-teal{background:#f0fdfa;color:#0d9488;}
.st-val{font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:var(--text);line-height:1;}
.st-lbl{font-size:12px;color:var(--text2);font-weight:500;margin-top:3px;}
.st-chg{font-size:11px;color:#22c55e;font-weight:600;margin-top:2px;}

/* ══ ALERTS ══ */
.alert{display:flex;align-items:flex-start;gap:9px;padding:12px 15px;border-radius:9px;margin-bottom:16px;font-size:13.5px;}
.alert-success{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;}
.alert-danger{background:#fff1f1;color:#b91c1c;border:1px solid #fecaca;}
.alert-warning{background:#fffbeb;color:#92400e;border:1px solid #fde68a;}
.alert-info{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;}

/* ══ BADGES ══ */
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;}
.b-green{background:#dcfce7;color:#166534;} .b-red{background:#fee2e2;color:#991b1b;}
.b-yellow{background:#fef9c3;color:#854d0e;} .b-blue{background:#dbeafe;color:#1e40af;}
.b-gray{background:#f3f4f6;color:#374151;} .b-orange{background:#ffedd5;color:#9a3412;}
.b-purple{background:#f3e8ff;color:#6b21a8;}

/* ══ TABLE ══ */
.tbl-wrap{overflow-x:auto;}
.tbl{width:100%;border-collapse:collapse;font-size:13.5px;}
.tbl thead th{background:#f8fafc;padding:10px 15px;text-align:left;font-size:10.5px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.7px;border-bottom:1.5px solid var(--border);white-space:nowrap;}
.tbl tbody td{padding:12px 15px;border-bottom:1px solid var(--border2);color:var(--text);vertical-align:middle;}
.tbl tbody tr:hover{background:#fafbfe;}
.tbl tbody tr:last-child td{border-bottom:none;}

/* ══ ACTION BUTTONS ══ */
.ab{width:28px;height:28px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:11.5px;color:#fff;text-decoration:none;transition:all .15s;border:none;cursor:pointer;margin:0 1px;}
.ab-edit{background:#3b82f6;} .ab-del{background:var(--red);} .ab-view{background:#10b981;}
.ab:hover{opacity:.82;transform:scale(1.08);}

/* ══ FORMS ══ */
.form-row{display:flex;gap:16px;margin-bottom:14px;}
.fg{flex:1;}
.fg label{display:block;font-size:12px;font-weight:700;color:var(--text);margin-bottom:7px;text-transform:uppercase;letter-spacing:.4px;}
.fg input,.fg select,.fg textarea{
  width:100%;padding:10px 13px;
  border:2px solid #e2e8f0;border-radius:9px;
  font-family:'Space Grotesk',sans-serif;font-size:13.5px;font-weight:500;color:var(--text);
  background:#fafbfc;transition:all .15s;outline:none;
}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--red);background:#fff;box-shadow:0 0 0 3px rgba(232,52,42,.08);}
.fg input::placeholder,.fg textarea::placeholder{color:#c4cad6;font-weight:400;}
.fg textarea{resize:vertical;}

/* ══ FILTER BAR ══ */
.fbar{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.fbar input{flex:1;min-width:200px;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:'Space Grotesk',sans-serif;font-size:13.5px;outline:none;transition:border-color .15s;background:#fafbfc;}
.fbar input:focus{border-color:var(--red);}
.fbar select{padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:'Space Grotesk',sans-serif;font-size:13.5px;color:var(--text);outline:none;background:#fafbfc;}

/* ══ PAGINATION ══ */
.pag{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid var(--border2);flex-wrap:wrap;gap:10px;}
.pag-info{font-size:13px;color:var(--text2);}
.pag-links{display:flex;gap:4px;}
.pg-btn{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid var(--border);color:var(--text2);background:var(--white);transition:all .15s;}
.pg-btn:hover{border-color:var(--red);color:var(--red);}
.pg-btn.on{background:var(--red);border-color:var(--red);color:#fff;}
.pg-btn.off{opacity:.35;pointer-events:none;}

/* ══ RESPONSIVE ══ */
@media(max-width:992px){.sidebar{transform:translateX(-100%);}.sidebar.open{transform:translateX(0);}.topbar{left:0;}.main-content{margin-left:0;}.tb-ham{display:flex;}.tb-srch{display:none;}}
@media(max-width:576px){.form-row{flex-direction:column;}.main-content{padding:14px;}}
</style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="sidebar">
  <div class="sb-brand">
    <div class="sb-logo"><i class="fas fa-flask"></i></div>
    <div class="sb-brand-txt">
      <h2>Lab Automation</h2>
      <span>SRS Electrical Appliances</span>
    </div>
  </div>

  <?php if (!empty($_SESSION['username'])): ?>
  <div class="sb-user">
    <div class="sb-av"><?= strtoupper(substr($_SESSION['full_name'] ?? $_SESSION['username'], 0, 1)) ?></div>
    <div>
      <div class="sb-uname"><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?></div>
      <div class="sb-urole"><?= htmlspecialchars($_SESSION['role'] ?? 'Staff') ?></div>
    </div>
    <div class="sb-online" title="Online"></div>
  </div>
  <?php endif; ?>

  <nav class="sb-nav">
    <span class="sb-sec">Overview</span>
    <a href="dashboard.php" class="sb-a <?= $cp==='dashboard.php'?'on':'' ?>">
      <div class="sbi"><i class="fas fa-gauge-high"></i></div><span class="slabel">Dashboard</span>
    </a>

    <span class="sb-sec">Products</span>
    <a href="view_product.php" class="sb-a <?= $cp==='view_product.php'?'on':'' ?>">
      <div class="sbi"><i class="fas fa-box-open"></i></div><span class="slabel">All Products</span>
    </a>
    <a href="add_product.php" class="sb-a <?= $cp==='add_product.php'?'on':'' ?>">
      <div class="sbi"><i class="fas fa-circle-plus"></i></div><span class="slabel">Add Product</span>
    </a>

    <span class="sb-sec">Testing</span>
    <a href="view_tests.php" class="sb-a <?= $cp==='view_tests.php'?'on':'' ?>">
      <div class="sbi"><i class="fas fa-clipboard-list"></i></div><span class="slabel">Test Records</span>
    </a>
    <a href="add_tests.php" class="sb-a <?= $cp==='add_tests.php'?'on':'' ?>">
      <div class="sbi"><i class="fas fa-vial"></i></div><span class="slabel">Conduct Test</span>
    </a>
    <a href="test_report.php" class="sb-a <?= $cp==='test_report.php'?'on':'' ?>">
      <div class="sbi"><i class="fas fa-file-pdf"></i></div><span class="slabel">Print Report</span>
    </a>

    <span class="sb-sec">Settings</span>
    <a href="add_type.php" class="sb-a <?= $cp==='add_type.php'?'on':'' ?>">
      <div class="sbi"><i class="fas fa-tags"></i></div><span class="slabel">Test Types</span>
    </a>
    <a href="search_results.php" class="sb-a <?= $cp==='search_results.php'?'on':'' ?>">
      <div class="sbi"><i class="fas fa-magnifying-glass"></i></div><span class="slabel">Search</span>
    </a>
  </nav>

  <div class="sb-foot">
    <a href="auth_check.php?action=logout" class="sb-logout">
      <i class="fas fa-right-from-bracket"></i> Sign Out
    </a>
  </div>
</aside>

<!-- ══ TOPBAR ══ -->
<header class="topbar">
  <button class="tb-ham" id="ham" onclick="toggleSB()"><i class="fas fa-bars"></i></button>

  <nav class="breadcrumb">
    <?php
    $labels=['dashboard.php'=>'Dashboard','view_product.php'=>'All Products','add_product.php'=>'Add Product','edit.php'=>'Edit Product','view_tests.php'=>'Test Records','add_tests.php'=>'Conduct Test','edit_tests.php'=>'Update Test','add_type.php'=>'Test Types','test_report.php'=>'Print Report','search_results.php'=>'Search'];
    $parents=['add_product.php'=>['All Products','view_product.php'],'edit.php'=>['All Products','view_product.php'],'add_tests.php'=>['Test Records','view_tests.php'],'edit_tests.php'=>['Test Records','view_tests.php']];
    echo '<a href="dashboard.php">Home</a>';
    if (isset($parents[$cp])) { echo '<span class="sep"><i class="fas fa-chevron-right"></i></span><a href="'.$parents[$cp][1].'">'.$parents[$cp][0].'</a>'; }
    echo '<span class="sep"><i class="fas fa-chevron-right"></i></span><span class="cur">'.($labels[$cp]??$cp).'</span>';
    ?>
  </nav>

  <div class="tb-right">
    <a href="search_results.php" class="tb-srch">
      <i class="fas fa-magnifying-glass"></i> Search <kbd>⌘K</kbd>
    </a>
    <div class="tb-divider"></div>
    <a href="add_tests.php" class="tb-btn" title="New Test"><i class="fas fa-plus"></i></a>
    <a href="test_report.php" class="tb-btn" title="Print Report"><i class="fas fa-print"></i></a>
    <div class="tb-divider"></div>
    <div class="tb-user">
      <div class="tb-uav"><?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?></div>
      <span class="tb-uname"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
  </div>
</header>

<script>
function toggleSB(){document.getElementById('sidebar').classList.toggle('open');}
document.addEventListener('click',function(e){
  const sb=document.getElementById('sidebar'),hm=document.getElementById('ham');
  if(window.innerWidth<=992&&sb&&hm&&!sb.contains(e.target)&&!hm.contains(e.target)) sb.classList.remove('open');
});
</script>
