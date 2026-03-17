<?php
include_once 'auth_check.php';
if (!empty($_SESSION['user_id'])) { header("Location: dashboard.php"); exit(); }

$error = "";
if (isset($_POST['login'])) {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if (!$u || !$p) {
        $error = "Please enter both username and password.";
    } elseif (login_user($u, $p, $conn)) {
        header("Location: dashboard.php"); exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Lab Automation System</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --red:#e8342a;--red2:#c0281f;--red3:#ff6055;
  --dark:#080c12;--dark2:#0f1520;--dark3:#161f2e;
  --bd:rgba(255,255,255,.06);
  --dim:rgba(255,255,255,.35);
  --mid:rgba(255,255,255,.62);
}
body{font-family:'Space Grotesk',sans-serif;min-height:100vh;background:var(--dark);display:grid;grid-template-columns:1fr 490px;overflow:hidden;}

/* LEFT */
.left{position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:center;padding:64px 68px;background:var(--dark2);}
.grid-bg{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.027) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.027) 1px,transparent 1px);background-size:50px 50px;animation:gridMove 25s linear infinite;}
@keyframes gridMove{to{background-position:50px 50px;}}
.orb{position:absolute;border-radius:50%;pointer-events:none;}
.orb1{width:550px;height:550px;top:-160px;left:-120px;background:radial-gradient(circle,rgba(232,52,42,.17) 0%,transparent 65%);animation:breathe 9s ease-in-out infinite;}
.orb2{width:420px;height:420px;bottom:-130px;right:-90px;background:radial-gradient(circle,rgba(30,80,220,.09) 0%,transparent 65%);animation:breathe 9s ease-in-out infinite 4.5s;}
@keyframes breathe{0%,100%{transform:scale(1);}50%{transform:scale(1.1);}}
.particle{position:absolute;width:2.5px;height:2.5px;border-radius:50%;background:rgba(232,52,42,.55);animation:floatUp linear infinite;pointer-events:none;}
@keyframes floatUp{0%{transform:translateY(100vh);opacity:0;}8%{opacity:1;}92%{opacity:.4;}100%{transform:translateY(-8vh);opacity:0;}}

.linner{position:relative;z-index:2;max-width:470px;}
.lbrand{display:flex;align-items:center;gap:15px;margin-bottom:50px;}
.llogo{width:50px;height:50px;border-radius:13px;flex-shrink:0;background:linear-gradient(135deg,var(--red),var(--red2));display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;box-shadow:0 0 0 1px rgba(232,52,42,.28),0 10px 28px rgba(232,52,42,.32);animation:logoFloat 4.5s ease-in-out infinite;}
@keyframes logoFloat{0%,100%{transform:translateY(0);}50%{transform:translateY(-8px);}}
.lbrand-txt .bname{font-family:'Outfit',sans-serif;font-size:14.5px;font-weight:700;color:#fff;}
.lbrand-txt .bsub{font-size:11.5px;color:var(--dim);margin-top:2px;}

.lhead{font-family:'Outfit',sans-serif;font-size:46px;font-weight:900;line-height:1.06;color:#fff;margin-bottom:16px;}
.lhead em{font-style:normal;background:linear-gradient(90deg,var(--red3),var(--red));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.ltag{font-size:14.5px;color:var(--mid);line-height:1.78;margin-bottom:46px;font-weight:400;}

.feats{display:flex;flex-direction:column;gap:9px;}
.feat{display:flex;align-items:center;gap:13px;padding:12px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.055);border-radius:11px;transition:all .25s;cursor:default;}
.feat:hover{background:rgba(255,255,255,.05);border-color:rgba(232,52,42,.18);transform:translateX(5px);}
.fic{width:33px;height:33px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12.5px;}
.fic-r{background:rgba(232,52,42,.14);color:var(--red3);}
.fic-b{background:rgba(59,130,246,.11);color:#60a5fa;}
.fic-g{background:rgba(34,197,94,.11);color:#4ade80;}
.fic-p{background:rgba(139,92,246,.11);color:#a78bfa;}
.ftxt .ft{font-size:13px;font-weight:600;color:rgba(255,255,255,.83);}
.ftxt .fd{font-size:11.5px;color:var(--dim);margin-top:1.5px;}

.lstats{display:flex;gap:30px;margin-top:46px;padding-top:26px;border-top:1px solid var(--bd);}
.lsv{font-family:'Outfit',sans-serif;font-size:27px;font-weight:800;color:#fff;}
.lsl{font-size:10.5px;color:var(--dim);text-transform:uppercase;letter-spacing:1.2px;margin-top:2px;}

/* RIGHT */
.right{background:#fff;display:flex;flex-direction:column;justify-content:center;padding:56px 50px;position:relative;overflow:hidden;}
.right::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--red2),var(--red),var(--red3),var(--red));background-size:200%;animation:shim 2.8s linear infinite;}
@keyframes shim{to{background-position:-200% 0;}}
.right::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 85% 5%,rgba(232,52,42,.045) 0%,transparent 50%);pointer-events:none;}
.rinner{position:relative;z-index:1;}

.eyebrow{display:inline-flex;align-items:center;gap:7px;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--red);margin-bottom:14px;}
.eyebrow::before{content:'';display:inline-block;width:16px;height:2px;background:var(--red);border-radius:2px;}
.rtitle{font-family:'Outfit',sans-serif;font-size:29px;font-weight:800;color:#080c12;margin-bottom:6px;}
.rsub{color:#8892a4;font-size:14px;margin-bottom:34px;font-weight:400;}

.ferr{display:flex;align-items:center;gap:10px;background:#fff5f5;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:22px;font-size:13.5px;color:#b91c1c;font-weight:500;animation:shake .4s ease;}
@keyframes shake{0%,100%{transform:translateX(0);}25%,75%{transform:translateX(-7px);}50%{transform:translateX(7px);}}

.fld{margin-bottom:18px;}
.fld label{display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:8px;letter-spacing:.4px;text-transform:uppercase;}
.iw{position:relative;}
.iico{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#c4cad6;font-size:13px;pointer-events:none;transition:color .2s;}
.iw:focus-within .iico{color:var(--red);}
.iw input{width:100%;padding:13px 44px;border:2px solid #eaecf0;border-radius:11px;font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:500;color:#080c12;background:#fafbfc;outline:none;transition:all .2s;}
.iw input:focus{border-color:var(--red);background:#fff;box-shadow:0 0 0 4px rgba(232,52,42,.09);}
.iw input::placeholder{color:#c4cad6;font-weight:400;}
.pwtog{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#c4cad6;cursor:pointer;font-size:13px;transition:color .2s;padding:4px;}
.pwtog:hover{color:var(--red);}

.remrow{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;}
.chk{display:flex;align-items:center;gap:8px;cursor:pointer;}
.chk input{width:15px;height:15px;accent-color:var(--red);cursor:pointer;}
.chk span{font-size:13px;color:#6b7280;font-weight:500;}
.fgt{font-size:13px;color:var(--red);text-decoration:none;font-weight:600;}
.fgt:hover{text-decoration:underline;}

.btnlogin{width:100%;padding:14px;background:linear-gradient(135deg,var(--red),var(--red2));color:#fff;border:none;border-radius:11px;font-family:'Space Grotesk',sans-serif;font-size:15px;font-weight:700;cursor:pointer;position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s;letter-spacing:.3px;}
.btnlogin::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,transparent 40%,rgba(255,255,255,.13) 55%,transparent 65%);transform:translateX(-100%);transition:transform .55s;}
.btnlogin:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(232,52,42,.34);}
.btnlogin:hover::before{transform:translateX(120%);}
.btnlogin:active{transform:translateY(0);}
.btnlogin i{margin-right:8px;}

.divline{display:flex;align-items:center;gap:12px;margin:24px 0;color:#c4cad6;font-size:12px;font-weight:500;}
.divline::before,.divline::after{content:'';flex:1;height:1px;background:#eaecf0;}

.regbox{background:#f9fafb;border:1.5px solid #f0f2f5;border-radius:12px;padding:18px 20px;text-align:center;}
.regbox p{font-size:13.5px;color:#6b7280;margin-bottom:12px;}
.btnreg{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px;background:#fff;color:#080c12;border:2px solid #eaecf0;border-radius:10px;font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s;}
.btnreg:hover{border-color:var(--red);color:var(--red);background:#fff5f4;box-shadow:0 4px 14px rgba(232,52,42,.11);}

@media(max-width:880px){body{grid-template-columns:1fr;}.left{display:none;}.right{padding:48px 28px;}}
</style>
</head>
<body>

<!-- LEFT -->
<div class="left">
  <div class="grid-bg"></div>
  <div class="orb orb1"></div>
  <div class="orb orb2"></div>

  <div class="linner">
    <div class="lbrand">
      <div class="llogo"><i class="fas fa-flask"></i></div>
      <div class="lbrand-txt">
        <div class="bname">Lab Automation System</div>
        <div class="bsub">SRS Electrical Appliances</div>
      </div>
    </div>

    <h1 class="lhead">Precision<br>Testing <em>Control</em><br>Platform</h1>
    <p class="ltag">Complete lifecycle management for electrical product testing — from registration to certified reporting.</p>

    <div class="feats">
      <div class="feat"><div class="fic fic-r"><i class="fas fa-box-open"></i></div><div class="ftxt"><div class="ft">Product Registration &amp; Tracking</div><div class="fd">Full inventory management with real-time status</div></div></div>
      <div class="feat"><div class="fic fic-b"><i class="fas fa-fingerprint"></i></div><div class="ftxt"><div class="ft">Auto 12-digit Unique Test IDs</div><div class="fd">Traceable, collision-free identifiers per test</div></div></div>
      <div class="feat"><div class="fic fic-g"><i class="fas fa-chart-bar"></i></div><div class="ftxt"><div class="ft">Real-time Pass / Fail Analytics</div><div class="fd">Live dashboard with trend charts &amp; leaderboards</div></div></div>
      <div class="feat"><div class="fic fic-p"><i class="fas fa-file-pdf"></i></div><div class="ftxt"><div class="ft">Printable &amp; Exportable Reports</div><div class="fd">Filter and print PDF reports for any period</div></div></div>
    </div>

    <div class="lstats">
      <div><div class="lsv">10+</div><div class="lsl">Products</div></div>
      <div><div class="lsv">5</div><div class="lsl">Test Types</div></div>
      <div><div class="lsv">100%</div><div class="lsl">Secure</div></div>
    </div>
  </div>
</div>

<!-- RIGHT -->
<div class="right">
  <div class="rinner">
    <p class="eyebrow">Secure Portal</p>
    <h2 class="rtitle">Welcome back </h2>
    <p class="rsub">Sign in to access your admin dashboard.</p>

    <?php if ($error): ?>
    <div class="ferr"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="fld">
        <label for="uname">Username</label>
        <div class="iw">
          <i class="fas fa-user iico"></i>
          <input type="text" id="uname" name="username" placeholder="Enter your username"
                 value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                 required autocomplete="username" autofocus>
        </div>
      </div>

      <div class="fld">
        <label for="pword">Password</label>
        <div class="iw">
          <i class="fas fa-lock iico"></i>
          <input type="password" id="pword" name="password"
                 placeholder="Enter your password" required autocomplete="current-password">
          <span class="pwtog" onclick="togglePw()"><i class="fas fa-eye" id="pwIco"></i></span>
        </div>
      </div>

      <div class="remrow">
        <label class="chk">
          <input type="checkbox" name="remember">
          <span>Remember me</span>
        </label>
        <a href="#" class="fgt">Forgot password?</a>
      </div>

      <button type="submit" name="login" class="btnlogin">
        <i class="fas fa-right-to-bracket"></i>Sign In to Dashboard
      </button>
    </form>

    <div class="divline">or</div>

    <div class="regbox">
      <p>Don't have an account? Register to get started.</p>
      <a href="signup.php" class="btnreg">
        <i class="fas fa-user-plus"></i> Create New Account
      </a>
    </div>
  </div>
</div>

<script>
function togglePw(){
  const i=document.getElementById('pword'),ic=document.getElementById('pwIco');
  if(i.type==='password'){i.type='text';ic.className='fas fa-eye-slash';}
  else{i.type='password';ic.className='fas fa-eye';}
}
// Add floating particles dynamically
(function(){
  const l=document.querySelector('.left');
  for(let i=0;i<12;i++){
    const p=document.createElement('div');
    p.className='particle';
    p.style.cssText=`left:${Math.random()*100}%;width:${Math.random()*2.5+1.5}px;height:${Math.random()*2.5+1.5}px;animation-duration:${Math.random()*14+9}s;animation-delay:${-Math.random()*20}s;`;
    l.appendChild(p);
  }
})();
</script>
</body>
</html>
