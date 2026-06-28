<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'Dashboard') — GED Médicale CHR Al Ghassani</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/css/hospital.css') }}" />

  @stack('styles')

  <style>
  /* Topbar dropdowns */
  .topbar-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 320px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 40px rgba(124,58,237,0.18), 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid rgba(221,214,254,0.6);
    z-index: 1000;
    display: none;
    overflow: hidden;
    animation: dropIn 0.2s cubic-bezier(0.34,1.56,0.64,1);
  }
  .topbar-dropdown.open { display: block; }

  @keyframes dropIn {
    from { opacity:0; transform: translateY(-8px) scale(0.97); }
    to   { opacity:1; transform: translateY(0)    scale(1);    }
  }

  .dropdown-header {
    padding: .85rem 1rem;
    border-bottom: 1px solid rgba(221,214,254,0.4);
    font-size: 12px;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .06em;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .notif-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: .75rem 1rem;
    border-bottom: 1px solid rgba(221,214,254,0.2);
    text-decoration: none;
    color: inherit;
    transition: background 0.14s;
    cursor: pointer;
  }
  .notif-item:last-child { border-bottom: none; }
  .notif-item:hover { background: rgba(245,243,255,0.8); text-decoration: none; }
  .notif-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--primary); flex-shrink: 0; margin-top: 5px;
  }
  .notif-text  { font-size: 12.5px; font-weight: 500; color: var(--text-primary); line-height: 1.4; }
  .notif-time  { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
  .notif-empty { padding: 2rem 1rem; text-align: center; font-size: 12.5px; color: var(--text-muted); }

  /* Profil dropdown (plus large) */
  .profile-dropdown { width: 260px; }
  .profile-info {
    padding: 1.1rem 1rem;
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, var(--mauve-900) 0%, var(--mauve-700) 100%);
  }
  .profile-info-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    border: 2px solid rgba(255,255,255,.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 800; color: #fff; flex-shrink: 0;
  }
  .profile-info-name { font-size: 13.5px; font-weight: 700; color: #fff; }
  .profile-info-role { font-size: 11.5px; color: rgba(255,255,255,.55); margin-top: 2px; }
  .profile-info-email{ font-size: 11px;   color: rgba(255,255,255,.40); margin-top: 1px; }

  .profile-menu-item {
    display: flex; align-items: center; gap: 10px;
    padding: .7rem 1rem;
    font-size: 13px; font-weight: 500;
    color: var(--text-primary);
    text-decoration: none;
    border-bottom: 1px solid rgba(221,214,254,0.2);
    transition: background 0.14s;
    cursor: pointer;
    background: none; border-left: none; border-right: none; border-top: none;
    width: 100%; text-align: left;
  }
  .profile-menu-item:last-child { border-bottom: none; }
  .profile-menu-item:hover { background: rgba(245,243,255,0.8); text-decoration: none; }
  .profile-menu-item svg { width: 16px; height: 16px; color: var(--text-muted); flex-shrink: 0; }
  .profile-menu-item.danger { color: var(--danger); }
  .profile-menu-item.danger svg { color: var(--danger); }
  .profile-menu-item.danger:hover { background: var(--danger-bg); }

  /* Search overlay */
  .search-overlay {
    position: fixed; inset: 0;
    background: rgba(30,27,74,0.5);
    z-index: 2000;
    display: none;
    align-items: flex-start;
    justify-content: center;
    padding-top: 12vh;
    backdrop-filter: blur(4px);
  }
  .search-overlay.open { display: flex; }

  .search-modal {
    width: 100%; max-width: 560px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 24px 80px rgba(124,58,237,0.25);
    overflow: hidden;
    animation: dropIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
  }

  .search-modal-input-wrap {
    display: flex; align-items: center; gap: 12px;
    padding: 1rem 1.2rem;
    border-bottom: 1px solid rgba(221,214,254,0.4);
  }
  .search-modal-input-wrap svg { width: 20px; height: 20px; color: var(--primary); flex-shrink: 0; }
  .search-modal-input {
    flex: 1; border: none; outline: none;
    font-size: 16px; font-weight: 500;
    color: var(--text-primary);
    background: transparent;
    font-family: var(--font);
  }
  .search-modal-input::placeholder { color: var(--text-muted); }

  .search-shortcuts {
    display: flex; gap: 8px; padding: .6rem 1.2rem;
    background: var(--bg-app);
    border-top: 1px solid rgba(221,214,254,0.3);
  }
  .search-shortcut-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: var(--radius-full);
    background: #fff; border: 1.5px solid var(--border-mauve);
    font-size: 12px; font-weight: 600; color: var(--primary);
    cursor: pointer; text-decoration: none; transition: var(--t);
  }
  .search-shortcut-btn:hover {
    background: var(--primary); color: #fff;
    border-color: var(--primary); text-decoration: none;
  }
  .search-shortcut-btn svg { width: 13px; height: 13px; }

  .search-result-item {
    display: flex; align-items: center; gap: 10px;
    padding: .7rem 1.2rem;
    border-bottom: 1px solid rgba(221,214,254,0.15);
    cursor: pointer; text-decoration: none; color: inherit;
    transition: background .12s;
  }
  .search-result-item:hover { background: rgba(245,243,255,0.8); text-decoration: none; }
  .search-result-icon {
    width: 34px; height: 34px; border-radius: var(--radius);
    background: var(--primary-bg); display: flex;
    align-items: center; justify-content: center; flex-shrink: 0;
    color: var(--primary);
  }
  .search-result-icon svg { width: 16px; height: 16px; }
  .search-result-title { font-size: 13px; font-weight: 600; color: var(--text-primary); }
  .search-result-sub   { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; }
  </style>
</head>
<body>

@php
  /** @var \App\Models\Utilisateur $me */
  $me = Auth::user();

  $avPalette = ['av-purple','av-teal','av-orange','av-blue','av-pink','av-green'];
  $avClass   = fn($str) => $avPalette[abs(crc32((string)($str ?? '?'))) % count($avPalette)];

  // Notifications : 5 dernières activités de l'utilisateur
  $notifItems = \App\Models\LogActivite::with('document')
      ->where('id_utilisateur', $me->id_utilisateur)
      ->orderByDesc('date_action')
      ->limit(5)
      ->get();

  $notifCount = \App\Models\LogActivite::where('id_utilisateur', $me->id_utilisateur)
      ->whereDate('date_action', today())
      ->count();
@endphp

{{-- SEARCH OVERLAY --}}
<div class="search-overlay" id="searchOverlay" onclick="closeSearch(event)">
  <div class="search-modal" onclick="event.stopPropagation()">

    <div class="search-modal-input-wrap">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
      </svg>
      <input type="text" class="search-modal-input" id="globalSearchInput"
             placeholder="Rechercher un patient, un document…"
             oninput="onGlobalSearch(this.value)"
             onkeydown="onSearchKey(event)" />
      <kbd style="font-size:11px;padding:2px 6px;border-radius:4px;background:var(--bg-app);
                  border:1px solid var(--border);color:var(--text-muted);font-family:monospace">Esc</kbd>
    </div>

    <div id="searchResults"></div>

    <div class="search-shortcuts">
      <a href="{{ route('patients.index') }}" class="search-shortcut-btn" onclick="closeSearchNow()">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
        Tous les patients
      </a>
      <a href="{{ route('documents.index') }}" class="search-shortcut-btn" onclick="closeSearchNow()">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
        Tous les documents
      </a>
    </div>

  </div>
</div>

<div class="app-layout">

  {{-- SIDEBAR --}}
  <aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
      <div class="sidebar-brand-logo">
        <div class="sidebar-logo-icon">
          <img src="{{ asset('assets/img/logo.png') }}" alt="Logo CHR Al Ghassani"
               style="width:44px;height:44px;object-fit:contain;">
        </div>
        <div>
          <div class="sidebar-brand-name">CHR Al Ghassani</div>
          <div class="sidebar-brand-sub">GED Médicale</div>
        </div>
      </div>
    </div>

    <a href="{{ route('documents.create') }}" class="sidebar-new-btn">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
      </svg>
      Nouveau document
    </a>

    {{-- Nouveau patient (infirmier + administratif) --}}
    @if($me->isInfirmier() || $me->isAdministratif())
    <a href="{{ route('patients.create') }}"
       class="sidebar-new-btn"
       style="margin-top:4px;background:rgba(255,255,255,.08);border:1px dashed rgba(255,255,255,.18)">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
      </svg>
      Nouveau patient
    </a>
    @endif

    <nav class="sidebar-nav">
      <div class="sidebar-section">Principal</div>

      <a href="{{ route('dashboard') }}"
         class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor">
          <path d="M2 4a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H4a2 2 0 01-2-2V4zm9 0a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2V4zM2 13a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H4a2 2 0 01-2-2v-3zm9 0a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2v-3z"/>
        </svg>
        Tableau de bord
      </a>

      <a href="{{ route('patients.index') }}"
         class="sidebar-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor">
          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
        </svg>
        Patients
      </a>

      <a href="{{ route('documents.index') }}"
         class="sidebar-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor">
          <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
        </svg>
        Documents
      </a>

      <a href="{{ route('historique.index') }}"
         class="sidebar-link {{ request()->routeIs('historique.*') ? 'active' : '' }}">
        <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
        </svg>
        Historique
      </a>

      @if ($me->isAdministratif())
        <div class="sidebar-section">Administration</div>

        <a href="{{ route('utilisateurs.index') }}"
           class="sidebar-link {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}">
          <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 12.094A5.973 5.973 0 004 15v1H1v-1a3 3 0 013.75-2.906z"/>
          </svg>
          Utilisateurs
        </a>

        <a href="{{ route('rapports.index') }}"
           class="sidebar-link {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
          <svg class="nav-icon" viewBox="0 0 20 20" fill="currentColor">
            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
          </svg>
          Rapports
        </a>
      @endif
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-profile">
        <div class="sidebar-avatar {{ $avClass($me->initiales) }}">{{ $me->initiales }}</div>
        <div style="min-width:0;flex:1">
          <div class="sidebar-user-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            {{ $me->prenom }} {{ $me->nom }}
          </div>
          <div class="sidebar-user-role">{{ $me->role_label }}</div>
        </div>
      </div>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sidebar-logout">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
          </svg>
          Se déconnecter
        </button>
      </form>
    </div>

  </aside>

  {{-- CONTENU PRINCIPAL --}}
  <div class="main-content">

    {{-- TOPBAR --}}
    <header class="topbar">
      <div class="topbar-left">
        <h1>@yield('page-title', 'Tableau de bord')</h1>
        {{-- Date en français --}}
        <p>{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
      </div>

      <div class="topbar-right">

        {{-- Bouton Recherche --}}
        <button class="topbar-icon-btn" id="searchBtn"
                title="Rechercher (Ctrl+K)"
                onclick="openSearch()">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
          </svg>
        </button>

        {{-- Cloche Notifications --}}
        <div style="position:relative">
          <button class="topbar-icon-btn" id="notifBtn"
                  title="Mes activités d'aujourd'hui"
                  onclick="toggleDropdown('notifDropdown', 'notifBtn')">
            <svg viewBox="0 0 20 20" fill="currentColor">
              <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-2.83-2h5.66A3 3 0 0110 18z"/>
            </svg>
            @if($notifCount > 0)
              <span style="position:absolute;top:5px;right:5px;width:8px;height:8px;
                           background:#EF4444;border-radius:50%;border:2px solid #fff"></span>
            @endif
          </button>

          {{-- Dropdown notifications --}}
          <div class="topbar-dropdown" id="notifDropdown">
            <div class="dropdown-header">
              Mes activités aujourd'hui
              <span style="background:var(--primary-bg);color:var(--primary);padding:1px 8px;
                           border-radius:20px;font-size:11px;font-weight:700">
                {{ $notifCount }}
              </span>
            </div>
            @forelse($notifItems as $notif)
              <div class="notif-item"
                   onclick="@if($notif->id_docum) window.location='{{ route('documents.show', $notif->id_docum) }}' @endif">
                <div class="notif-dot" style="background:
                  @if(str_contains(strtoupper($notif->description ?? ''), 'UPLOAD')) var(--success)
                  @elseif(str_contains(strtoupper($notif->description ?? ''), 'CONNEXION')) var(--primary)
                  @elseif(str_contains(strtoupper($notif->description ?? ''), 'CONSULTATION')) #2563EB
                  @else var(--text-muted) @endif
                "></div>
                <div>
                  <div class="notif-text">{{ Str::limit($notif->description ?? '—', 55) }}</div>
                  <div class="notif-time">{{ $notif->date_action->format('d/m/Y') }}</div>
                </div>
              </div>
            @empty
              <div class="notif-empty">Aucune activité récente</div>
            @endforelse
            <a href="{{ route('historique.index') }}"
               style="display:block;text-align:center;padding:.7rem;font-size:12px;font-weight:600;
                      color:var(--primary);border-top:1px solid rgba(221,214,254,0.3);text-decoration:none"
               onclick="closeAllDropdowns()">
              Voir tout l'historique →
            </a>
          </div>
        </div>

        {{-- Profil (clic = dropdown) --}}
        <div style="position:relative">
          <div class="topbar-user" id="profileBtn"
               title="Cliquer pour voir le profil"
               onclick="toggleDropdown('profileDropdown', 'profileBtn')"
               style="cursor:pointer">
            <div class="topbar-avatar {{ $avClass($me->initiales) }}">{{ $me->initiales }}</div>
            <span class="topbar-name">{{ $me->prenom }} {{ $me->nom }}</span>
            <svg viewBox="0 0 20 20" fill="currentColor"
                 style="width:12px;height:12px;color:var(--text-muted);margin-left:2px;transition:transform .2s"
                 id="profileChevron">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </div>

          {{-- Dropdown profil --}}
          <div class="topbar-dropdown profile-dropdown" id="profileDropdown">
            <div class="profile-info">
              <div class="profile-info-avatar">{{ $me->initiales }}</div>
              <div>
                <div class="profile-info-name">{{ $me->prenom }} {{ $me->nom }}</div>
                <div class="profile-info-role">{{ $me->role_label }}</div>
                <div class="profile-info-email">{{ $me->email }}</div>
              </div>
            </div>

            <a href="{{ route('historique.index') }}" class="profile-menu-item" onclick="closeAllDropdowns()">
              <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
              Mes activités
            </a>

            <a href="{{ route('documents.index') }}" class="profile-menu-item" onclick="closeAllDropdowns()">
              <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
              Mes documents
            </a>

            <div style="border-top:1px solid rgba(221,214,254,0.4)"></div>

            <form method="POST" action="{{ route('logout') }}" style="margin:0">
              @csrf
              <button type="submit" class="profile-menu-item danger">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                </svg>
                Se déconnecter
              </button>
            </form>
          </div>
        </div>

      </div>
    </header>

    {{-- Messages flash --}}
    @if (session('success'))
      <div style="margin:1rem 1.5rem 0" data-flash>
        <div class="alert alert-success">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
          {{ session('success') }}
        </div>
      </div>
    @endif
    @if (session('error'))
      <div style="margin:1rem 1.5rem 0" data-flash>
        <div class="alert alert-danger">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          {{ session('error') }}
        </div>
      </div>
    @endif

    <main class="page-content">
      @yield('content')
    </main>

  </div>
</div>

<script src="{{ asset('assets/js/hospital.js') }}"></script>

@php
  // Préparer les données de recherche globale (évite le parsing Blade des arrays imbriqués)
  $searchPatients = \App\Models\Patient::select(['ipp','nom','prenom','numero_dossier','service'])
    ->limit(100)->get()
    ->map(function($p) {
      return [
        'ipp'     => $p->ipp,
        'ipp_f'   => str_pad((string)$p->ipp, 4, '0', STR_PAD_LEFT),
        'nom'     => strtoupper($p->nom ?? ''),
        'prenom'  => $p->prenom ?? '',
        'dossier' => $p->numero_dossier ?? '',
        'service' => $p->service ?? '',
        'url'     => route('patients.show', $p->ipp),
      ];
    })->values()->toArray();

  $searchDocuments = \App\Models\Document::select(['id_docum','titre','typedocument','service','date_import'])
    ->orderByDesc('date_import')->limit(80)->get()
    ->map(function($d) {
      return [
        'id'      => $d->id_docum,
        'titre'   => $d->titre ?? '',
        'type'    => $d->type_label,
        'service' => $d->service ?? '',
        'url'     => route('documents.show', $d->id_docum),
      ];
    })->values()->toArray();
@endphp

<script>
/* Topbar : dropdowns, recherche, profil */

const searchData = {
  patients : {!! json_encode($searchPatients) !!},
  documents: {!! json_encode($searchDocuments) !!},
};

/* Recherche globale */
function openSearch() {
  document.getElementById('searchOverlay').classList.add('open');
  document.getElementById('globalSearchInput').value = '';
  document.getElementById('searchResults').innerHTML = '';
  setTimeout(() => document.getElementById('globalSearchInput').focus(), 80);
}
function closeSearch(e) { e.target === document.getElementById('searchOverlay') && closeSearchNow(); }
function closeSearchNow() { document.getElementById('searchOverlay').classList.remove('open'); }

function onSearchKey(e) {
  if (e.key === 'Escape') closeSearchNow();
  if (e.key === 'Enter') {
    const first = document.querySelector('.search-result-item');
    if (first && first.href) window.location.href = first.href;
  }
}

function onGlobalSearch(q) {
  const box = document.getElementById('searchResults');
  q = q.trim().toLowerCase();
  if (!q) { box.innerHTML = ''; return; }

  let html = '';
  let count = 0;

  // Patients
  searchData.patients.filter(p =>
    p.nom.toLowerCase().includes(q) ||
    p.prenom.toLowerCase().includes(q) ||
    (p.dossier && p.dossier.toLowerCase().includes(q)) ||
    p.ipp_f.includes(q)
  ).slice(0, 4).forEach(p => {
    html += `<a href="${p.url}" class="search-result-item" onclick="closeSearchNow()">
      <div class="search-result-icon">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
      </div>
      <div>
        <div class="search-result-title">${p.nom} ${p.prenom}</div>
        <div class="search-result-sub">Patient · IPP ${p.ipp_f} · ${p.service}</div>
      </div>
    </a>`;
    count++;
  });

  // Documents
  searchData.documents.filter(d =>
    d.titre.toLowerCase().includes(q) ||
    d.type.toLowerCase().includes(q) ||
    d.service.toLowerCase().includes(q)
  ).slice(0, 4).forEach(d => {
    html += `<a href="${d.url}" class="search-result-item" onclick="closeSearchNow()">
      <div class="search-result-icon" style="background:#EFF6FF;color:#2563EB">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
      </div>
      <div>
        <div class="search-result-title">${d.titre}</div>
        <div class="search-result-sub">Document · ${d.type} · ${d.service}</div>
      </div>
    </a>`;
    count++;
  });

  box.innerHTML = count > 0 ? html
    : '<div style="padding:1.5rem;text-align:center;font-size:13px;color:var(--text-muted)">Aucun résultat pour « ' + q + ' »</div>';
}

/* Dropdowns (notifications + profil) */
function toggleDropdown(id, btnId) {
  const dd  = document.getElementById(id);
  const was = dd.classList.contains('open');
  closeAllDropdowns();
  if (!was) {
    dd.classList.add('open');
    if (id === 'profileDropdown') {
      document.getElementById('profileChevron').style.transform = 'rotate(180deg)';
    }
  }
}

function closeAllDropdowns() {
  document.querySelectorAll('.topbar-dropdown').forEach(d => d.classList.remove('open'));
  const ch = document.getElementById('profileChevron');
  if (ch) ch.style.transform = '';
}

// Fermer en cliquant à l'extérieur
document.addEventListener('click', e => {
  if (!e.target.closest('#notifBtn, #notifDropdown, #profileBtn, #profileDropdown')) {
    closeAllDropdowns();
  }
});


/* Raccourci clavier Ctrl+K */
document.addEventListener('keydown', e => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openSearch(); }
  if (e.key === 'Escape') { closeSearchNow(); closeAllDropdowns(); }
});
</script>

@stack('scripts')

</body>
</html>
