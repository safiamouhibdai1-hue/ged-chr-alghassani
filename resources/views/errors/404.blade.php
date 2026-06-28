<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Page introuvable — GED Médicale CHR Al Ghassani</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      background: #F0F2F8;
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
    }
    .error-card {
      background: #fff;
      border-radius: 20px;
      padding: 3rem 3.5rem;
      text-align: center;
      max-width: 480px; width: 90%;
      box-shadow: 0 4px 24px rgba(30,42,74,.10);
    }
    .error-code {
      font-size: 6rem; font-weight: 800;
      background: linear-gradient(135deg, #2AB09A, #534AB7);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1; margin-bottom: .5rem;
    }
    .error-ico  { font-size: 2.5rem; margin-bottom: 1rem; }
    .error-title { font-size: 1.3rem; font-weight: 700; color: #1E2A4A; margin-bottom: .75rem; }
    .error-desc  { font-size: 14px; color: #8892a4; line-height: 1.6; margin-bottom: 2rem; }
    .btn-back {
      display: inline-flex; align-items: center; gap: 8px;
      background: #534AB7; color: #fff;
      text-decoration: none; border-radius: 10px;
      padding: 11px 24px; font-size: 14px; font-weight: 600;
      transition: background .2s;
    }
    .btn-back:hover { background: #3d35a0; }
    .btn-secondary {
      display: inline-flex; align-items: center; gap: 8px;
      color: #534AB7; background: #EEF2FF;
      text-decoration: none; border-radius: 10px;
      padding: 11px 24px; font-size: 14px; font-weight: 600;
      margin-left: .75rem; transition: background .2s;
    }
    .btn-secondary:hover { background: #dde3fc; }
    .brand { margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #eef0f6; font-size: 12px; color: #8892a4; }
    .brand strong { color: #1E2A4A; }
  </style>
</head>
<body>
  <div class="error-card">
    <div class="error-ico">🔍</div>
    <div class="error-code">404</div>
    <div class="error-title">Page introuvable</div>
    <div class="error-desc">
      La page que vous cherchez n'existe pas ou a été déplacée.
      Vérifiez l'adresse ou retournez au tableau de bord.
    </div>
    <div>
      <a href="{{ route('dashboard') }}" class="btn-back">🏠 Tableau de bord</a>
      <a href="{{ url()->previous() }}" class="btn-secondary">← Retour</a>
    </div>
    <div class="brand">
      🏥 <strong>GED Médicale</strong> — CHR Al Ghassani, Fès
    </div>
  </div>
</body>
</html>
