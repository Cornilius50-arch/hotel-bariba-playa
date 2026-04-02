<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Connexion — Admin Bariba Playa</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --navy: #1a0b06; --gold: #c82018; --gold-l: #e87018;
      --dark: #1a0b06; --muted: #7a6860; --border: #f0e0d0;
      --white: #ffffff; --cream: #fdf8f2;
    }
    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh; display: flex;
      background: linear-gradient(135deg, var(--navy) 0%, #4a2010 100%);
    }
    .login-left {
      flex: 1; display: flex; flex-direction: column; justify-content: center;
      padding: 60px; position: relative; overflow: hidden;
    }
    .login-left::before {
      content: '';
      position: absolute; inset: 0;
      background: url('../uploads/gallery/facade-hotel.png') center/cover no-repeat;
      opacity: .22;
    }
    .login-brand { position: relative; z-index: 2; }
    .login-brand .hotel-name { font-family: 'Playfair Display', serif; font-size: 2.4rem; font-weight: 700; color: var(--white); line-height: 1.2; }
    .login-brand .hotel-sub { font-size: .72rem; letter-spacing: .24em; text-transform: uppercase; color: var(--gold-l); margin-top: 4px; display: block; }
    .login-brand .admin-badge { display: inline-block; margin-top: 20px; background: rgba(200,32,24,.25); border: 1px solid rgba(200,32,24,.4); color: var(--gold-l); font-size: .7rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; padding: 6px 16px; border-radius: 50px; }
    .login-quote { position: relative; z-index: 2; margin-top: auto; font-family: 'Playfair Display', serif; font-size: 1.1rem; font-style: italic; color: rgba(255,255,255,.6); max-width: 380px; line-height: 1.7; }
    .login-right {
      width: 480px; background: var(--white);
      display: flex; align-items: center; justify-content: center;
      padding: 60px 48px;
    }
    .login-box { width: 100%; }
    .login-box h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
    .login-box p { font-size: .9rem; color: var(--muted); margin-bottom: 36px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--navy); margin-bottom: 8px; }
    .input-wrap { position: relative; }
    .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: .9rem; }
    .input-wrap input { width: 100%; padding: 13px 14px 13px 40px; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Inter', sans-serif; font-size: .92rem; color: var(--navy); outline: none; transition: border-color .2s, box-shadow .2s; }
    .input-wrap input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(200,32,24,.12); }
    .pass-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--muted); cursor: pointer; font-size: .9rem; transition: color .2s; }
    .pass-toggle:hover { color: var(--navy); }
    .login-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--gold), var(--gold-l)); border: none; border-radius: 10px; font-family: 'Inter', sans-serif; font-size: .95rem; font-weight: 700; color: var(--dark); cursor: pointer; transition: .2s; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 8px; }
    .login-btn:hover { filter: brightness(1.06); transform: translateY(-1px); }
    .login-btn:disabled { opacity: .7; cursor: wait; }
    .error-msg { background: #fdf0ef; border: 1px solid rgba(192,57,43,.25); border-radius: 8px; padding: 12px 16px; font-size: .85rem; color: #c0392b; margin-bottom: 20px; display: none; align-items: center; gap: 8px; }
    .error-msg.show { display: flex; }
    .back-link { display: flex; align-items: center; gap: 6px; font-size: .82rem; color: var(--muted); margin-top: 28px; transition: color .2s; }
    .back-link:hover { color: var(--gold); }
    @media (max-width: 768px) {
      .login-left { display: none; }
      .login-right { width: 100%; padding: 40px 24px; }
    }
  </style>
</head>
<body>

<div class="login-left">
  <div class="login-brand">
    <div class="hotel-name">Bariba Playa</div>
    <span class="hotel-sub">Hôtel &amp; Lagune — Cotonou, Bénin</span>
    <span class="admin-badge"><i class="fa-solid fa-lock"></i> &nbsp;Espace Administration</span>
  </div>
  <div class="login-quote">
    "Gérez votre établissement avec élégance. Réservations, galerie et contenus — tout en un seul endroit."
  </div>
</div>

<div class="login-right">
  <div class="login-box">
    <h2>Connexion</h2>
    <p>Accédez au panneau d'administration de l'hôtel.</p>

    <div class="error-msg" id="errorMsg">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span id="errorText">Identifiants incorrects.</span>
    </div>

    <form id="loginForm">
      <div class="form-group">
        <label>Identifiant</label>
        <div class="input-wrap">
          <i class="fa-solid fa-user"></i>
          <input type="text" name="username" placeholder="Votre identifiant" required autocomplete="username" />
        </div>
      </div>
      <div class="form-group">
        <label>Mot de passe</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password" id="passInput" placeholder="••••••••" required autocomplete="current-password" />
          <button type="button" class="pass-toggle" onclick="togglePass()"><i class="fa-regular fa-eye" id="eyeIcon"></i></button>
        </div>
      </div>
      <button type="submit" class="login-btn" id="loginBtn">
        <i class="fa-solid fa-right-to-bracket"></i> Se connecter
      </button>
    </form>

    <a href="../index.html" class="back-link">
      <i class="fa-solid fa-arrow-left"></i> Retour au site
    </a>
  </div>
</div>

<script>
  function togglePass() {
    const inp = document.getElementById('passInput');
    const ico = document.getElementById('eyeIcon');
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'fa-regular fa-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'fa-regular fa-eye'; }
  }

  document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    const err = document.getElementById('errorMsg');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Connexion...';
    err.classList.remove('show');

    const data = new FormData(this);
    try {
      const res  = await fetch('../api/auth.php', { method: 'POST', body: data });
      const json = await res.json();
      if (json.success) {
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Connecté !';
        window.location.href = json.redirect;
      } else {
        document.getElementById('errorText').textContent = json.error || 'Identifiants incorrects.';
        err.classList.add('show');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Se connecter';
      }
    } catch {
      document.getElementById('errorText').textContent = 'Erreur réseau. Réessayez.';
      err.classList.add('show');
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Se connecter';
    }
  });
</script>
</body>
</html>
