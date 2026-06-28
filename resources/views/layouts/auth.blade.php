<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Connexion') — GED Médicale CHR Al Ghassani</title>

  {{-- Police Inter (Google Fonts) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

  {{-- Feuille de style principale --}}
  <link rel="stylesheet" href="{{ asset('assets/css/hospital.css') }}" />

  @stack('styles')
</head>
<body>

  {{-- Le contenu de la page de connexion est injecté ici --}}
  @yield('content')

  <script src="{{ asset('assets/js/hospital.js') }}"></script>
  @stack('scripts')
</body>
</html>
