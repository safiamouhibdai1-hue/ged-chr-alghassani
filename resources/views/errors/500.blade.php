<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Erreur serveur — GED Médicale CHR Al Ghassani</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: #F0F2F8; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .error-card { background: #fff; border-radius: 20px; padding: 3rem 3.5rem; text-align: center; max-width: 480px; width: 90%; box-shadow: 0 4px 24px rgba(30,42,74,.10); }
    .error-code { font-size: 6rem; font-weight: 800; background: linear-gradient(135deg, #F59E0B, #E8547A); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; margin-bottom: .5rem; }
    .error-ico  { font-size: 2.5rem; margin-bottom: 1rem; }
    .error-title { font-size: 1.3rem; font-weight: 700; color: #1E2A4A; margin-bottom: .75rem; }
    .error-desc  { font-size: 14px; color: #8892a4; line-height: 1.6; margin-bottom: 2rem; }
    .btn-back { display: inline-flex; align-items: center; gap: 8px; background: #534AB7; color: #fff; text-decoration: none; border-radius: 10px; padding: 11px 24px; font-size: 14px; font-weight: 600; }
    .brand { margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #eef0f6; font-size: 12px; color: #8892a4; }
    .brand strong { color: #1E2A4A; }
  </style>
</head>
<body>
  <div class="error-card">
    <div class="error-ico">⚠️</div>
    <div class="error-code">500</div>
    <div class="error-title">Erreur interne du serveur</div>
    <div class="error-desc">
      Une erreur inattendue s'est produite. L'équipe technique a été notifiée.
      Veuillez réessayer dans quelques instants.
    </div>
    <a href="{{ route('dashboard') }}" class="btn-back">🏠 Retour au dashboard</a>
    <div class="brand">
      🏥 <strong>GED Médicale</strong> — CHR Al Ghassani, Fès
    </div>
  </div>
</body>
</html>
