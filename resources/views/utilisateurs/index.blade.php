{{-- utilisateurs/index.blade.php — Gestion du personnel (admin) --}}
@extends('layouts.app')
@section('title', 'Gestion du personnel')
@section('page-title', 'Gestion du personnel')
@section('page-subtitle', 'Comptes utilisateurs — CHR Al Ghassani')

@push('styles')
<style>
/* Changement de rôle inline */
.role-select {
  font-size: 11.5px; font-weight: 600;
  padding: 3px 8px 3px 6px;
  border-radius: var(--radius-full);
  border: 1.5px solid var(--primary-border);
  background: var(--primary-bg);
  color: var(--primary);
  cursor: pointer;
  outline: none;
  transition: var(--t);
  -webkit-appearance: none;
  appearance: none;
}
.role-select:focus { border-color: var(--primary); box-shadow: var(--shadow-glow); }
.role-select.role-medecin      { background:#EFF6FF; color:#2563EB; border-color:#BFDBFE; }
.role-select.role-infirmier    { background:#F0FDF4; color:#059669; border-color:#A7F3D0; }
.role-select.role-administratif{ background:#FEF3C7; color:#D97706; border-color:#FDE68A; }

/* Badge permission */
.perm-tag {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 2px 8px; border-radius: 4px;
  font-size: 10.5px; font-weight: 600;
  background: var(--bg-light); color: var(--text-secondary);
  border: 1px solid var(--border);
  margin: 1px;
}
.perm-tag.ok  { background: var(--success-bg); color: var(--success); border-color: var(--success-bd); }
.perm-tag.no  { background: #f9fafb; color: #9CA3AF; border-color: #E5E7EB; text-decoration: line-through; }

/* Ligne hover */
.user-row { transition: background 0.12s; }
.user-row:hover td { background: rgba(245,243,255,0.7); }

/* Modal confirmation suppression */
.delete-confirm-overlay {
  position: fixed; inset: 0;
  background: rgba(30,27,74,0.55);
  z-index: 3000;
  display: none;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
}
.delete-confirm-overlay.open { display: flex; }
.delete-confirm-box {
  background: #fff;
  border-radius: var(--radius-lg);
  padding: 2rem;
  max-width: 420px;
  width: 90%;
  box-shadow: var(--shadow-lg);
  animation: scaleIn 0.2s ease;
}
</style>
@endpush

@section('content')

@php
  /** @var \App\Models\Utilisateur $moi */
  $moi = Auth::user();
  $avPalette = ['av-purple','av-teal','av-orange','av-blue','av-pink','av-green'];
  $avClass   = fn($str) => $avPalette[abs(crc32((string)($str ?? '?'))) % count($avPalette)];

  $permsParRole = [
    'medecin'       => ['Consulter dossiers','Importer documents','Télécharger','Imprimer','Historique'],
    'infirmier'     => ['Créer patient','Modifier patient','Importer documents','Médecin responsable','Mots-clés'],
    'administratif' => ['Gérer utilisateurs','Gérer documents','Gérer patients','Rapports','Historique complet'],
  ];
@endphp

{{-- Statistiques --}}
<div class="stats-grid">
  <div class="stat-card" style="animation-delay:0s">
    <div class="stat-card-badge"><svg viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 12.094A5.973 5.973 0 004 15v1H1v-1a3 3 0 013.75-2.906z"/></svg></div>
    <div class="stat-card-val">{{ $stats['total'] }}</div>
    <div class="stat-card-lbl">Total personnel</div>
  </div>
  <div class="stat-card" style="animation-delay:0.07s">
    <div class="stat-card-badge" style="background:#F0FDF4"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#059669"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg></div>
    <div class="stat-card-val" style="color:#059669">{{ $stats['actifs'] }}</div>
    <div class="stat-card-lbl">Comptes actifs</div>
  </div>
  <div class="stat-card" style="animation-delay:0.14s">
    <div class="stat-card-badge" style="background:#EFF6FF"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#2563EB"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg></div>
    <div class="stat-card-val" style="color:#2563EB">{{ $stats['par_role']['medecin'] ?? 0 }}</div>
    <div class="stat-card-lbl">Médecins</div>
  </div>
  <div class="stat-card" style="animation-delay:0.21s">
    <div class="stat-card-badge" style="background:#FFF7ED"><svg viewBox="0 0 20 20" fill="currentColor" style="color:#EA580C"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg></div>
    <div class="stat-card-val" style="color:#EA580C">{{ $stats['par_role']['infirmier'] ?? 0 }}</div>
    <div class="stat-card-lbl">Infirmiers</div>
  </div>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('utilisateurs.index') }}" class="filter-bar">
  <div class="search-wrap" style="flex:1;min-width:200px">
    <span class="search-ico"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg></span>
    <input type="text" name="search" class="search-input" value="{{ request('search') }}" placeholder="Nom, prénom, e-mail…" />
  </div>
  <select name="role" class="form-control" style="width:auto" onchange="this.form.submit()">
    <option value="">Tous les rôles</option>
    <option value="medecin"       {{ request('role')==='medecin'       ? 'selected':'' }}>Médecin</option>
    <option value="infirmier"     {{ request('role')==='infirmier'     ? 'selected':'' }}>Infirmier(e)</option>
    <option value="administratif" {{ request('role')==='administratif' ? 'selected':'' }}>Administratif</option>
  </select>
  <select name="actif" class="form-control" style="width:auto" onchange="this.form.submit()">
    <option value="">Tous les statuts</option>
    <option value="1" {{ request('actif')==='1' ? 'selected':'' }}>Actifs</option>
    <option value="0" {{ request('actif')==='0' ? 'selected':'' }}>Inactifs</option>
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
  @if(request()->hasAny(['search','role','actif']))
    <a href="{{ route('utilisateurs.index') }}" class="btn btn-secondary btn-sm">Effacer</a>
  @endif
  <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary btn-sm" style="margin-left:auto">
    <svg viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
    Nouveau compte
  </a>
</form>

{{-- Tableau du personnel --}}
<div class="table-wrap">
  <div class="table-top">
    <span class="table-title">Personnel hospitalier</span>
    <span class="table-count">{{ $utilisateurs->total() }} compte(s)</span>
  </div>

  @if($utilisateurs->isEmpty())
    <div class="empty-state" style="padding:3rem">
      <div class="empty-state-icon"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 12.094A5.973 5.973 0 004 15v1H1v-1a3 3 0 013.75-2.906z"/></svg></div>
      <p>Aucun compte trouvé.</p>
    </div>
  @else
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Utilisateur</th>
        <th>Rôle</th>
        <th>Permissions</th>
        <th>Statut</th>
        <th style="text-align:center">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($utilisateurs as $u)
      <tr class="user-row">

        {{-- ID --}}
        <td style="color:var(--text-muted);font-size:12px;font-family:monospace">{{ $u->id_utilisateur }}</td>

        {{-- Utilisateur --}}
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <div class="avatar {{ $avClass($u->initiales) }}" style="width:36px;height:36px;font-size:12px;flex-shrink:0">
              {{ $u->initiales }}
            </div>
            <div>
              <div style="font-weight:700;font-size:13px">
                {{ $u->prenom }} {{ $u->nom }}
                @if($u->id_utilisateur === $moi->id_utilisateur)
                  <span class="badge badge-blue" style="margin-left:4px;font-size:10px">Vous</span>
                @endif
              </div>
              <div style="font-size:11.5px;color:var(--text-muted)">{{ $u->email }}</div>
            </div>
          </div>
        </td>

        {{-- Rôle — modifiable inline --}}
        <td>
          @if($u->id_utilisateur === $moi->id_utilisateur)
            {{-- Propre compte : affichage seulement --}}
            <span class="badge badge-blue">{{ $u->role_label }}</span>
          @else
            <form method="POST" action="{{ route('utilisateurs.change-role', $u->id_utilisateur) }}">
              @csrf @method('PATCH')
              <select name="role"
                      class="role-select role-{{ $u->role }}"
                      onchange="this.form.submit()"
                      title="Changer le rôle — changement immédiat">
                <option value="medecin"       {{ $u->role==='medecin'       ? 'selected':'' }}>Médecin</option>
                <option value="infirmier"     {{ $u->role==='infirmier'     ? 'selected':'' }}>Infirmier(e)</option>
                <option value="administratif" {{ $u->role==='administratif' ? 'selected':'' }}>Administratif</option>
              </select>
            </form>
          @endif
        </td>

        {{-- Permissions selon le rôle --}}
        <td>
          <div style="display:flex;flex-wrap:wrap;gap:2px;max-width:260px">
            @foreach($permsParRole[$u->role] ?? [] as $perm)
              <span class="perm-tag ok">{{ $perm }}</span>
            @endforeach
          </div>
        </td>

        {{-- Statut --}}
        <td>
          @if($u->actif)
            <span class="badge badge-green">
              <svg viewBox="0 0 8 8" fill="currentColor" style="width:6px;height:6px"><circle cx="4" cy="4" r="3"/></svg>
              Actif
            </span>
          @else
            <span class="badge badge-gray">Inactif</span>
          @endif
        </td>

        {{-- Actions --}}
        <td>
          <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap">

            {{-- Modifier --}}
            <a href="{{ route('utilisateurs.edit', $u->id_utilisateur) }}"
               class="action-btn" title="Modifier le compte">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
              </svg>
            </a>

            {{-- Toggle actif/inactif --}}
            @if($u->id_utilisateur !== $moi->id_utilisateur)
              <form method="POST" action="{{ route('utilisateurs.toggle-actif', $u->id_utilisateur) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="action-btn {{ $u->actif ? 'action-btn-red' : 'action-btn-green' }}"
                        title="{{ $u->actif ? 'Désactiver' : 'Activer' }} le compte">
                  @if($u->actif)
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/></svg>
                  @else
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                  @endif
                </button>
              </form>

              {{-- Supprimer --}}
              <button type="button"
                      class="action-btn action-btn-red"
                      title="Supprimer définitivement ce compte"
                      onclick="confirmDelete({{ $u->id_utilisateur }}, '{{ $u->prenom }} {{ $u->nom }}')">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
              </button>
            @else
              <span class="action-btn" style="opacity:.3;cursor:not-allowed" title="Votre propre compte">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
              </span>
            @endif

          </div>
        </td>

      </tr>
      @endforeach
    </tbody>
  </table>

  @if($utilisateurs->hasPages())
    <div class="pagination-wrap">
      <span>{{ $utilisateurs->firstItem() }}–{{ $utilisateurs->lastItem() }} / {{ $utilisateurs->total() }}</span>
      <div style="display:flex;gap:4px">
        @if(!$utilisateurs->onFirstPage())
          <a href="{{ $utilisateurs->previousPageUrl() }}" class="btn btn-secondary btn-sm">← Préc.</a>
        @endif
        @if($utilisateurs->hasMorePages())
          <a href="{{ $utilisateurs->nextPageUrl() }}" class="btn btn-secondary btn-sm">Suiv. →</a>
        @endif
      </div>
    </div>
  @endif
  @endif
</div>

{{-- Modal de confirmation de suppression --}}
<div class="delete-confirm-overlay" id="deleteOverlay">
  <div class="delete-confirm-box">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.2rem">
      <div style="width:44px;height:44px;border-radius:50%;background:var(--danger-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <svg viewBox="0 0 20 20" fill="currentColor" style="width:22px;height:22px;color:var(--danger)">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
      </div>
      <div>
        <div style="font-size:15px;font-weight:800;color:var(--text-primary)">Supprimer le compte ?</div>
        <div style="font-size:12.5px;color:var(--text-secondary);margin-top:2px" id="deleteUserName"></div>
      </div>
    </div>

    <div class="alert alert-danger" style="margin-bottom:1.2rem">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
      Cette action est <strong>irréversible</strong>. Le compte sera supprimé définitivement de la base de données.
    </div>

    <div style="display:flex;gap:8px">
      <form id="deleteForm" method="POST">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">
          <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          Supprimer définitivement
        </button>
      </form>
      <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Annuler</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete(userId, userName) {
  document.getElementById('deleteUserName').textContent = userName;
  document.getElementById('deleteForm').action = '/utilisateurs/' + userId;
  document.getElementById('deleteOverlay').classList.add('open');
}
function closeDeleteModal() {
  document.getElementById('deleteOverlay').classList.remove('open');
}
document.getElementById('deleteOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeDeleteModal();
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeDeleteModal();
});
</script>
@endpush
