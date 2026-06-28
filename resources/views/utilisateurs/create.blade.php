{{--
    utilisateurs/create.blade.php
    Route : GET /utilisateurs/create
--}}
@extends('layouts.app')

@section('title', 'Nouveau compte')
@section('page-title', 'Créer un compte')
@section('page-subtitle', 'Ajouter un membre du personnel')

@push('styles')
<style>
.role-card {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  cursor: pointer;
  margin-bottom: .5rem;
  transition: border-color .15s, background .15s;
}
.role-card input[type="radio"] { display: none; }
.role-card:hover { border-color: var(--accent); background: var(--accent-light); }
.role-card.selected {
  border-color: var(--accent);
  background: var(--accent-light);
  font-weight: 600;
}
.role-card-name { font-size: 13px; font-weight: 600; }
.role-card-desc { font-size: 11.5px; color: var(--text-secondary); }
.section-title {
  font-weight: 700; font-size: 13px; color: var(--text-primary);
  margin: 1.1rem 0 .6rem; padding-bottom: .4rem;
  border-bottom: 1px solid var(--border-light);
}
.section-title:first-child { margin-top: 0; }
</style>
@endpush

@section('content')

<div style="max-width:600px;margin:0 auto">

  <div class="section-card" style="padding:0">

    <div class="section-card-head">
      <span class="section-card-title">Créer un compte</span>
      <a href="{{ route('utilisateurs.index') }}" class="btn btn-secondary btn-sm">← Retour</a>
    </div>

    <div style="padding:1rem 1.2rem">
      <form method="POST" action="{{ route('utilisateurs.store') }}" id="createForm">
        @csrf

        {{-- Aperçu compact --}}
        <div style="display:flex;align-items:center;gap:12px;padding:.7rem 1rem;
                    background:var(--primary-bg);border:1px solid var(--primary-border);
                    border-radius:var(--radius);margin-bottom:1rem">
          <div class="avatar av-purple" id="recapAvatar" style="width:42px;height:42px;font-size:14px;flex-shrink:0">
            <span id="recapInitiales">??</span>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:13.5px" id="recapNom">—</div>
            <div style="font-size:11.5px;color:var(--text-secondary)" id="recapEmail">—</div>
          </div>
          <div style="text-align:right;font-size:12px">
            <div style="font-weight:600" id="recapRole">Médecin</div>
            <div style="font-weight:600;color:var(--success)" id="recapStatut">Actif</div>
          </div>
        </div>

        {{-- Identité --}}
        <div class="section-title">Identité du personnel</div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem">
          <div>
            <label class="form-label">Prénom <span style="color:var(--danger)">*</span></label>
            <input type="text" name="prenom" id="inp_prenom"
              class="form-control {{ $errors->has('prenom') ? 'is-error' : '' }}"
              value="{{ old('prenom') }}"
              placeholder="Ex : Fatima"
              autocomplete="off"
              oninput="updateRecap()" />
            @error('prenom')<div class="form-error">{{ $message }}</div>@enderror
          </div>

          <div>
            <label class="form-label">Nom <span style="color:var(--danger)">*</span></label>
            <input type="text" name="nom" id="inp_nom"
              class="form-control {{ $errors->has('nom') ? 'is-error' : '' }}"
              value="{{ old('nom') }}"
              placeholder="Ex : Ben Ali"
              autocomplete="off"
              oninput="updateRecap()" />
            @error('nom')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        <div style="margin-bottom:.75rem">
          <label class="form-label">Adresse e-mail <span style="color:var(--danger)">*</span></label>
          <input type="email" name="email" id="inp_email"
            class="form-control {{ $errors->has('email') ? 'is-error' : '' }}"
            value="{{ old('email') }}"
            placeholder="prenom.nom@chr.ma"
            autocomplete="off"
            oninput="updateRecap()" />
          @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Rôle --}}
        <div class="section-title">Rôle hospitalier</div>

        @php
          $roles = [
            'medecin'       => ['Médecin',       'Accès aux dossiers patients et documents médicaux'],
            'infirmier'     => ['Infirmier(e)',   'Import et consultation des bilans et ordonnances'],
            'administratif' => ['Administratif', 'Gestion complète du GED et des utilisateurs'],
          ];
          $selectedRole = old('role', 'medecin');
        @endphp

        <div style="margin-bottom:.75rem">
          @foreach($roles as $key => [$name, $desc])
            <label class="role-card {{ $selectedRole === $key ? 'selected' : '' }}"
                   id="card_{{ $key }}"
                   onclick="selectRole('{{ $key }}')">
              <input type="radio" name="role" value="{{ $key }}" {{ $selectedRole === $key ? 'checked' : '' }}>
              <div>
                <div class="role-card-name">{{ $name }}</div>
                <div class="role-card-desc">{{ $desc }}</div>
              </div>
            </label>
          @endforeach
          @error('role')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Mot de passe --}}
        <div class="section-title">Mot de passe</div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.25rem">
          <div>
            <label class="form-label">Mot de passe <span style="color:var(--danger)">*</span></label>
            <div class="pw-wrap">
              <input type="password" name="password" id="pw1"
                class="form-control {{ $errors->has('password') ? 'is-error' : '' }}"
                placeholder="Min. 8 caractères"
                autocomplete="new-password" />
              <button type="button" class="pw-eye" onclick="togglePassword('pw1', this)">&#128065;</button>
            </div>
            @error('password')<div class="form-error">{{ $message }}</div>@enderror
          </div>

          <div>
            <label class="form-label">Confirmer <span style="color:var(--danger)">*</span></label>
            <div class="pw-wrap">
              <input type="password" name="password_confirmation" id="pw2"
                class="form-control"
                placeholder="Répétez"
                autocomplete="new-password" />
              <button type="button" class="pw-eye" onclick="togglePassword('pw2', this)">&#128065;</button>
            </div>
          </div>
        </div>
        <div class="form-hint" style="margin-bottom:.75rem">Minimum 8 caractères.</div>

        {{-- Statut --}}
        <div class="section-title">Statut du compte</div>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:1rem">
          <label class="toggle-switch">
            <input type="checkbox" name="actif" value="1" id="toggleActif"
                   {{ old('actif', '1') ? 'checked' : '' }}
                   onchange="updateRecap()">
            <span class="toggle-slider"></span>
          </label>
          <div>
            <div style="font-weight:600;font-size:13px" id="actifLabel">Compte actif</div>
            <div class="form-hint" style="margin:0" id="actifDesc">L'utilisateur peut se connecter immédiatement</div>
          </div>
        </label>

        {{-- Actions --}}
        <div style="display:flex;gap:.75rem;padding-top:.25rem;border-top:1px solid rgba(221,214,254,0.3)">
          <button type="submit" class="btn btn-primary">Créer le compte</button>
          <a href="{{ route('utilisateurs.index') }}" class="btn btn-secondary">Annuler</a>
        </div>

      </form>
    </div>

  </div>

</div>

@endsection

@push('scripts')
<script>
const roleLabels = { medecin:'Médecin', infirmier:'Infirmier(e)', administratif:'Administratif' };
let currentRole = '{{ old('role', 'medecin') }}';

function selectRole(role) {
  currentRole = role;
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
  const card = document.getElementById('card_' + role);
  if (card) {
    card.classList.add('selected');
    card.querySelector('input[type="radio"]').checked = true;
  }
  updateRecap();
}

function updateRecap() {
  const prenom = document.getElementById('inp_prenom').value.trim();
  const nom    = document.getElementById('inp_nom').value.trim();
  const email  = document.getElementById('inp_email').value.trim();
  const actif  = document.getElementById('toggleActif').checked;

  const initiales = ((prenom[0] ?? '') + (nom[0] ?? '')).toUpperCase() || '??';
  document.getElementById('recapInitiales').textContent = initiales;
  document.getElementById('recapNom').textContent = [prenom, nom.toUpperCase()].filter(Boolean).join(' ') || '—';
  document.getElementById('recapEmail').textContent = email || '—';
  document.getElementById('recapRole').textContent = roleLabels[currentRole] || currentRole;
  document.getElementById('recapStatut').textContent = actif ? 'Actif' : 'Inactif';
  document.getElementById('recapStatut').style.color = actif ? 'var(--success)' : 'var(--text-muted)';

  document.getElementById('actifLabel').textContent = actif ? 'Compte actif' : 'Compte inactif';
  document.getElementById('actifDesc').textContent  = actif
    ? "L'utilisateur peut se connecter immédiatement"
    : "L'utilisateur ne peut pas se connecter";
}

updateRecap();
</script>
@endpush
