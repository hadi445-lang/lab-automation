<?php
include_once 'auth_check.php';
if (!empty($_SESSION['user_id'])) { header("Location: dashboard.php"); exit(); }

$msg = ""; $msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username         = trim($_POST['username']         ?? '');
    $full_name        = trim($_POST['full_name']        ?? '');
    $email            = trim($_POST['email']            ?? '');
    $department       = trim($_POST['department']       ?? '');
    $password         = trim($_POST['password']         ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $role             = trim($_POST['role']             ?? 'Staff');
    if (!in_array($role, ['Lab Incharge', 'Staff'])) $role = 'Staff';

    if (empty($username)||empty($full_name)||empty($email)||empty($department)||empty($password)) {
        $msg = "All fields are required!"; $msg_type = "err";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email format!"; $msg_type = "err";
    } elseif (strlen($password) < 6) {
        $msg = "Password must be at least 6 characters."; $msg_type = "err";
    } elseif ($password !== $confirm_password) {
        $msg = "Passwords do not match!"; $msg_type = "err";
    } else {
        $chk = $conn->prepare("SELECT user_id FROM users WHERE username=? OR email=?");
        $chk->bind_param("ss",$username,$email); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) {
            $msg = "Username or Email already registered!"; $msg_type = "err";
        } else {
            $hp = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username,password,full_name,email,role,department) VALUES (?,?,?,?,?,?)");
            if (!$stmt) { $msg = "DB error: ".$conn->error; $msg_type = "err"; }
            else {
                $stmt->bind_param("ssssss",$username,$hp,$full_name,$email,$role,$department);
                if ($stmt->execute()) {
                    $msg = "Account created! Redirecting to login..."; $msg_type = "success";
                    header("refresh:2;url=login.php");
                } else {
                    $msg = "Something went wrong. (".$conn->error.")"; $msg_type = "err";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — Lab Automation System</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{--red:#e8342a;--red2:#c0281f;--red3:#ff6055;--dark:#080c12;--dark2:#0f1520;--bd:rgba(255,255,255,.06);--dim:rgba(255,255,255,.35);--mid:rgba(255,255,255,.62);}
body{font-family:'Space Grotesk',sans-serif;min-height:100vh;background:var(--dark);display:grid;grid-template-columns:420px 1fr;overflow:hidden;}

/* LEFT */
.left{position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:center;padding:56px 48px;background:var(--dark2);}
.grid-bg{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.027) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.027) 1px,transparent 1px);background-size:50px 50px;animation:gridMove 25s linear infinite;}
@keyframes gridMove{to{background-position:50px 50px;}}
.orb{position:absolute;border-radius:50%;pointer-events:none;}
.orb1{width:450px;height:450px;top:-130px;left:-100px;background:radial-gradient(circle,rgba(232,52,42,.17) 0%,transparent 65%);animation:breathe 9s ease-in-out infinite;}
.orb2{width:350px;height:350px;bottom:-100px;right:-70px;background:radial-gradient(circle,rgba(30,80,220,.09) 0%,transparent 65%);animation:breathe 9s ease-in-out infinite 4.5s;}
@keyframes breathe{0%,100%{transform:scale(1);}50%{transform:scale(1.1);}}
.particle{position:absolute;width:2.5px;height:2.5px;border-radius:50%;background:rgba(232,52,42,.5);animation:floatUp linear infinite;pointer-events:none;}
@keyframes floatUp{0%{transform:translateY(100vh);opacity:0;}8%{opacity:1;}92%{opacity:.4;}100%{transform:translateY(-8vh);opacity:0;}}

.linner{position:relative;z-index:2;max-width:340px;}
.llogo-wrap{display:flex;align-items:center;gap:14px;margin-bottom:44px;}
.llogo{width:48px;height:48px;border-radius:13px;flex-shrink:0;background:linear-gradient(135deg,var(--red),var(--red2));display:flex;align-items:center;justify-content:center;font-size:19px;color:#fff;box-shadow:0 0 0 1px rgba(232,52,42,.28),0 10px 26px rgba(232,52,42,.3);animation:logoF 4s ease-in-out infinite;}
@keyframes logoF{0%,100%{transform:translateY(0);}50%{transform:translateY(-7px);}}
.lbt .bn{font-family:'Outfit',sans-serif;font-size:14px;font-weight:700;color:#fff;}
.lbt .bs{font-size:11px;color:var(--dim);margin-top:2px;}

.lhead{font-family:'Outfit',sans-serif;font-size:36px;font-weight:900;line-height:1.08;color:#fff;margin-bottom:14px;}
.lhead em{font-style:normal;background:linear-gradient(90deg,var(--red3),var(--red));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.ltag{font-size:14px;color:var(--mid);line-height:1.75;margin-bottom:38px;}

.feats{display:flex;flex-direction:column;gap:9px;}
.feat{display:flex;align-items:center;gap:12px;padding:11px 15px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.055);border-radius:10px;transition:all .25s;}
.feat:hover{background:rgba(255,255,255,.05);border-color:rgba(232,52,42,.18);transform:translateX(4px);}
.fic{width:32px;height:32px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px;}
.fic-r{background:rgba(232,52,42,.14);color:var(--red3);}
.fic-b{background:rgba(59,130,246,.11);color:#60a5fa;}
.fic-g{background:rgba(34,197,94,.11);color:#4ade80;}
.ftxt .ft{font-size:12.5px;font-weight:600;color:rgba(255,255,255,.82);}
.ftxt .fd{font-size:11px;color:var(--dim);margin-top:1px;}

.lalert{margin-top:32px;padding:12px 16px;background:rgba(232,52,42,.09);border:1px solid rgba(232,52,42,.2);border-radius:10px;font-size:12px;color:rgba(255,180,140,.9);}
.lalert strong{display:block;margin-bottom:3px;font-size:12.5px;color:rgba(255,200,160,.95);}

/* RIGHT */
.right{background:#fff;display:flex;flex-direction:column;justify-content:center;padding:48px 52px;position:relative;overflow-y:auto;}
.right::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--red2),var(--red),var(--red3),var(--red));background-size:200%;animation:shim 2.8s linear infinite;}
@keyframes shim{to{background-position:-200% 0;}}
.right::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 90% 5%,rgba(232,52,42,.04) 0%,transparent 50%);pointer-events:none;}
.rinner{position:relative;z-index:1;max-width:580px;width:100%;margin:0 auto;}

.eyebrow{display:inline-flex;align-items:center;gap:7px;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--red);margin-bottom:12px;}
.eyebrow::before{content:'';display:inline-block;width:16px;height:2px;background:var(--red);border-radius:2px;}
.rtitle{font-family:'Outfit',sans-serif;font-size:27px;font-weight:800;color:#080c12;margin-bottom:5px;}
.rsub{color:#8892a4;font-size:13.5px;margin-bottom:24px;}

.fmsg{display:flex;align-items:center;gap:10px;border-radius:10px;padding:11px 15px;margin-bottom:18px;font-size:13.5px;font-weight:500;}
.fmsg.err{background:#fff5f5;border:1px solid #fca5a5;color:#b91c1c;animation:shake .4s ease;}
.fmsg.success{background:#f0fff4;border:1px solid #9ae6b4;color:#166534;}
@keyframes shake{0%,100%{transform:translateX(0);}25%,75%{transform:translateX(-6px);}50%{transform:translateX(6px);}}

/* Form grid */
.fg2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;}
.fg1{margin-bottom:14px;}
.fld label{display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:7px;letter-spacing:.4px;text-transform:uppercase;}
.iw{position:relative;}
.iico{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#c4cad6;font-size:12.5px;pointer-events:none;transition:color .2s;z-index:1;}
.iw:focus-within .iico{color:var(--red);}
.iw input,.iw select{width:100%;padding:12px 13px 12px 40px;border:2px solid #eaecf0;border-radius:10px;font-family:'Space Grotesk',sans-serif;font-size:13.5px;font-weight:500;color:#080c12;background:#fafbfc;outline:none;transition:all .2s;appearance:none;}
.iw input:focus,.iw select:focus{border-color:var(--red);background:#fff;box-shadow:0 0 0 3px rgba(232,52,42,.09);}
.iw input::placeholder{color:#c4cad6;font-weight:400;}
.iw select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%23888' d='M5 7L0 2h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;background-color:#fafbfc;cursor:pointer;padding-right:32px;}
.pwtog{position:absolute;right:13px;top:50%;transform:translateY(-50%);color:#c4cad6;cursor:pointer;font-size:12.5px;transition:color .2s;padding:4px;}
.pwtog:hover{color:var(--red);}

.btnreg{width:100%;padding:13.5px;background:linear-gradient(135deg,var(--red),var(--red2));color:#fff;border:none;border-radius:11px;font-family:'Space Grotesk',sans-serif;font-size:15px;font-weight:700;cursor:pointer;position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s;letter-spacing:.3px;margin-top:6px;}
.btnreg::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,transparent 40%,rgba(255,255,255,.12) 55%,transparent 65%);transform:translateX(-100%);transition:transform .5s;}
.btnreg:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(232,52,42,.33);}
.btnreg:hover::before{transform:translateX(120%);}
.btnreg i{margin-right:8px;}

.divline{display:flex;align-items:center;gap:12px;margin:22px 0;color:#c4cad6;font-size:12px;font-weight:500;}
.divline::before,.divline::after{content:'';flex:1;height:1px;background:#eaecf0;}

.loginbox{background:#f9fafb;border:1.5px solid #f0f2f5;border-radius:12px;padding:16px 20px;text-align:center;}
.loginbox p{font-size:13.5px;color:#6b7280;margin-bottom:11px;}
.btnlogin{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:11.5px;background:#fff;color:#080c12;border:2px solid #eaecf0;border-radius:10px;font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;text-decoration:none;transition:all .2s;}
.btnlogin:hover{border-color:var(--red);color:var(--red);background:#fff5f4;box-shadow:0 4px 14px rgba(232,52,42,.11);}

@media(max-width:900px){body{grid-template-columns:1fr;}.left{display:none;}.right{padding:40px 24px;}.fg2{grid-template-columns:1fr;}}
</style>
</head>
<body>

<!-- LEFT -->
<div class="left">
  <div class="grid-bg"></div>
  <div class="orb orb1"></div>
  <div class="orb orb2"></div>

  <div class="linner">
    <div class="llogo-wrap">
      <div class="llogo"><i class="fas fa-flask"></i></div>
      <div class="lbt"><div class="bn">Lab Automation System</div><div class="bs">SRS Electrical Appliances</div></div>
    </div>

    <h1 class="lhead">Join the<br><em>Testing</em><br>Platform</h1>
    <p class="ltag">Register your account to start managing product tests, tracking results, and generating reports.</p>

    <div class="feats">
      <div class="feat"><div class="fic fic-r"><i class="fas fa-user-shield"></i></div><div class="ftxt"><div class="ft">Secure Role-Based Access</div><div class="fd">Staff or Lab Incharge permissions</div></div></div>
      <div class="feat"><div class="fic fic-b"><i class="fas fa-sitemap"></i></div><div class="ftxt"><div class="ft">Department Selection</div><div class="fd">QC, R&amp;D, Production, Maintenance</div></div></div>
      <div class="feat"><div class="fic fic-g"><i class="fas fa-cogs"></i></div><div class="ftxt"><div class="ft">Automated Lab Reporting</div><div class="fd">Printable PDF test reports anytime</div></div></div>
    </div>

    <div class="lalert">
      <strong><i class="fas fa-info-circle" style="margin-right:5px;"></i>Role Assignment Info</strong>
      Self-registration allows <strong>Staff</strong> or <strong>Lab Incharge</strong> only. Admin roles are assigned by the System Administrator.
    </div>
  </div>
</div>

<!-- RIGHT -->
<div class="right">
  <div class="rinner">
    <p class="eyebrow">New Account</p>
    <h2 class="rtitle">Create your account</h2>
    <p class="rsub">Fill in your details below to register.</p>

    <?php if ($msg): ?>
    <div class="fmsg <?= $msg_type ?>">
      <i class="fas <?= $msg_type==='err' ? 'fa-circle-exclamation' : 'fa-check-circle' ?>"></i>
      <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
      <!-- Row 1: Full Name + Username -->
      <div class="fg2">
        <div class="fld">
          <label>Full Name</label>
          <div class="iw">
            <i class="fas fa-id-card iico"></i>
            <input type="text" name="full_name" placeholder="e.g. Ahmed Khan"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
          </div>
        </div>
        <div class="fld">
          <label>Username</label>
          <div class="iw">
            <i class="fas fa-user iico"></i>
            <input type="text" name="username" placeholder="e.g. ahmedkhan"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
          </div>
        </div>
      </div>

      <!-- Row 2: Email (full width) -->
      <div class="fg1 fld">
        <label>Email Address</label>
        <div class="iw">
          <i class="fas fa-envelope iico"></i>
          <input type="email" name="email" placeholder="you@example.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
      </div>

      <!-- Row 3: Role + Department -->
      <div class="fg2">
        <div class="fld">
          <label>Role</label>
          <div class="iw">
            <i class="fas fa-user-tag iico"></i>
            <select name="role" required>
              <option value="" disabled selected>Select Role</option>
              <option value="Lab Incharge" <?= (($_POST['role']??'')==='Lab Incharge')?'selected':'' ?>>Lab Incharge</option>
              <option value="Staff"        <?= (($_POST['role']??'')==='Staff')?'selected':'' ?>>Staff</option>
            </select>
          </div>
        </div>
        <div class="fld">
          <label>Department</label>
          <div class="iw">
            <i class="fas fa-building iico"></i>
            <select name="department" required>
              <option value="" disabled selected>Select Dept</option>
              <?php foreach(['Quality Control','R&D','Production','Maintenance'] as $d): ?>
              <option value="<?=$d?>" <?=(($_POST['department']??'')===$d)?'selected':''?>><?=$d?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Row 4: Passwords -->
      <div class="fg2">
        <div class="fld">
          <label>Password</label>
          <div class="iw">
            <i class="fas fa-lock iico"></i>
            <input type="password" id="pw1" name="password" placeholder="Min 6 characters" required>
            <span class="pwtog" onclick="togglePw('pw1','e1')"><i class="fas fa-eye" id="e1"></i></span>
          </div>
        </div>
        <div class="fld">
          <label>Confirm Password</label>
          <div class="iw">
            <i class="fas fa-lock iico"></i>
            <input type="password" id="pw2" name="confirm_password" placeholder="Repeat password" required>
            <span class="pwtog" onclick="togglePw('pw2','e2')"><i class="fas fa-eye" id="e2"></i></span>
          </div>
        </div>
      </div>

      <button type="submit" class="btnreg">
        <i class="fas fa-user-plus"></i>Register Account
      </button>
    </form>

    <div class="divline">or</div>

    <div class="loginbox">
      <p>Already have an account? Sign in here.</p>
      <a href="login.php" class="btnlogin">
        <i class="fas fa-right-to-bracket"></i> Sign In to Dashboard
      </a>
    </div>
  </div>
</div>

<script>
function togglePw(id,ico){
  const i=document.getElementById(id),ic=document.getElementById(ico);
  if(i.type==='password'){i.type='text';ic.className='fas fa-eye-slash';}
  else{i.type='password';ic.className='fas fa-eye';}
}
(function(){
  const l=document.querySelector('.left');
  for(let i=0;i<10;i++){
    const p=document.createElement('div');
    p.className='particle';
    p.style.cssText=`left:${Math.random()*100}%;animation-duration:${Math.random()*14+9}s;animation-delay:${-Math.random()*18}s;`;
    l.appendChild(p);
  }
})();
</script>
</body>
</html>
