{{--
    utilisateurs/edit.blade.php
    Route : GET /utilisateurs/{id}/edit
--}}
@extends('layouts.app')

@section('title', 'Modifier le compte')
@section('page-title', 'Modifier un compte')
@section('page-subtitle', $utilisateur->prenom . ' ' . $utilisateur->nom)

@push('styles')
<style>
.role-card {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  cursor: pointer;
  margin-bottom: .5rem;
  transition: border-color .15s, background .15s;
}
.role-card input[type="radio"] { display: none; }
.role-card:hover, .role-card.selected {
  border-color: var(--accent);
  background: var(--accent-light);
}
.role-card-name { font-size: 13px; font-weight: 600; }
.role-card-desc { font-size: 11.5px; color: var(--text-secondary); }
</style>
@endpush

@section('content')

@php
  $moi    = Auth::user();
  $isSelf = $utilisateur->id_utilisateur === $moi->id_utilisateur;
@endphp

@if($isSelf)
  <div class="notice notice-warning" style="margin-bottom:1rem">
    Vous modifiez votre propre compte. Vous ne pouvez pas vous désactiver vous-même.
  </div>
@endif

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.2rem;align-items:start;max-width:900px">

  {{-- Formulaire --}}
  <form method="POST" action="{{ route('utilisateurs.update', $utilisateur->id_utilisateur) }}">
    @csrf
    @method('PUT')

    {{-- Identité --}}
    <div class="form-section">
      <div class="form-section-title">Identité</div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Prénom <span style="color:var(--danger)">*</span></label>
          <input type="text" name="prenom"
            class="form-control {{ $errors->has('prenom') ? 'is-error' : '' }}"
            value="{{ old('prenom', $utilisateur->prenom) }}" />
          @error('prenom')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label class="form-label">Nom <span style="color:var(--danger)">*</span></label>
          <input type="text" name="nom"
            class="form-control {{ $errors->has('nom') ? 'is-error' : '' }}"
            value="{{ old('nom', $utilisateur->nom) }}" />
          @error('nom')<div class="form-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Adresse e-mail <span style="color:var(--danger)">*</span></label>
        <input type="email" name="email"
          class="form-control {{ $errors->has('email') ? 'is-error' : '' }}"
          value="{{ old('email', $utilisateur->email) }}" />
        @error('email')<div class="form-error">{{ $message }}</div>@enderror
      </div>

      <div class="form-hint">
        ID : #{{ $utilisateur->id_utilisateur }}
      </div>
    </div>

    {{-- Rôle --}}
    <div class="form-section">
      <div class="form-section-title">Rôle hospitalier</div>

      @php
        $roles = [
          'medecin'       => ['Médecin',       'Accès aux dossiers patients et documents médicaux'],
          'infirmier'     => ['Infirmier(e)',   'Import et consultation des bilans et ordonnances'],
          'administratif' => ['Administratif', 'Gestion complète du GED et des utilisateurs'],
        ];
      @endphp

      @foreach($roles as $key => [$name, $desc])
        @php $selected = old('role', $utilisateur->role) === $key; @endphp
        <label class="role-card {{ $selected ? 'selected' : '' }}"
               id="card_{{ $key }}"
               onclick="selectRole('{{ $key }}')">
          <input type="radio" name="role" value="{{ $key }}" {{ $selected ? 'checked' : '' }}>
          <div>
            <div class="role-card-name">{{ $name }}</div>
            <div class="role-card-desc">{{ $desc }}</div>
          </div>
        </label>
      @endforeach

      @error('role')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    {{-- Statut --}}
    <div class="form-section">
      <div class="form-section-title">Statut du compte</div>

      @if($isSelf)
        <div class="form-hint" style="margin-bottom:.75rem">Vous ne pouvez pas modifier le statut de votre propre compte.</div>
      @endif

      <label style="display:flex;align-items:center;gap:10px;cursor:pointer;{{ $isSelf ? 'opacity:.5;pointer-events:none' : '' }}">
        <label class="toggle-switch">
          <input type="checkbox" name="actif" value="1" id="toggleActif"
                 {{ old('actif', $utilisateur->actif ? '1' : '0') == '1' ? 'checked' : '' }}
                 {{ $isSelf ? 'disabled' : '' }}
                 onchange="updateActifLabel()">
          <span class="toggle-slider"></span>
        </label>
        <div>
          <div style="font-weight:600;font-size:13px" id="actifLabel">
            {{ $utilisateur->actif ? 'Compte actif' : 'Compte inactif' }}
          </div>
          <div class="form-hint" style="margin:0" id="actifDesc">
            {{ $utilisateur->actif ? "L'utilisateur peut se connecter" : "L'utilisateur ne peut pas se connecter" }}
          </div>
        </div>
      </label>

      @if($isSelf)
        <input type="hidden" name="actif" value="{{ $utilisateur->actif ? '1' : '0' }}">
      @endif
    </div>

    {{-- Mot de passe --}}
    <div class="form-section">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
        <div class="form-section-title" style="margin:0;border:none;padding:0">Mot de passe</div>
        <button type="button" class="btn btn-secondary btn-sm" id="pwToggleBtn"
                onclick="togglePwSection()">
          Changer le mot de passe
        </button>
      </div>

      <div id="pwSection" style="display:none">
        <div class="form-group">
          <label class="form-label">Nouveau mot de passe</label>
          <div class="pw-wrap">
            <input type="password" name="new_password" id="pw1"
              class="form-control {{ $errors->has('new_password') ? 'is-error' : '' }}"
              placeholder="Minimum 8 caractères" />
            <button type="button" class="pw-eye" onclick="togglePassword('pw1', this)">&#128065;</button>
          </div>
          @error('new_password')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label class="form-label">Confirmer le nouveau mot de passe</label>
          <div class="pw-wrap">
            <input type="password" name="new_password_confirmation" id="pw2"
              class="form-control"
              placeholder="Répétez le nouveau mot de passe" />
            <button type="button" class="pw-eye" onclick="togglePassword('pw2', this)">&#128065;</button>
          </div>
        </div>
      </div>

      <div id="pwPlaceholder" class="form-hint">
        Le mot de passe actuel est conservé si vous ne le modifiez pas.
      </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:.75rem">
      <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
      <a href="{{ route('utilisateurs.index') }}" class="btn btn-secondary">Annuler</a>
    </div>

  </form>

  {{-- Fiche résumé --}}
  <div class="section-card" style="position:sticky;top:1.5rem">
    <div class="section-card-head"><span class="section-card-title">Fiche personnel</span></div>
    <div style="padding:1.2rem;text-align:center">
      <div class="avatar av-purple" style="width:52px;height:52px;font-size:18px;margin:0 auto .75rem">
        {{ $utilisateur->initiales }}
      </div>
      <div style="font-weight:700;font-size:14px">{{ $utilisateur->prenom }} {{ $utilisateur->nom }}</div>
      <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;word-break:break-all">{{ $utilisateur->email }}</div>

      <div style="border-top:1px solid var(--border-light);margin:1rem 0;padding-top:.75rem;text-align:left">
        @foreach([
          ['Identifiant', '#' . $utilisateur->id_utilisateur],
          ['Rôle actuel', $utilisateur->role_label],
          ['Statut', $utilisateur->actif ? 'Actif' : 'Inactif'],
          ['Actions', \App\Models\LogActivite::where('id_utilisateur', $utilisateur->id_utilisateur)->count()],
        ] as [$label, $value])
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:.4rem">
          <span style="color:var(--text-secondary)">{{ $label }}</span>
          <span style="font-weight:600;color:var(--text-primary)">{{ $value }}</span>
        </div>
        @endforeach
      </div>

      <a href="{{ route('historique.index') }}?utilisateur={{ $utilisateur->id_utilisateur }}"
         class="btn btn-secondary btn-sm" style="width:100%;justify-content:center">
        Voir son historique
      </a>
    </div>
  </div>

</div>

@endsection

@push('scripts')
<script>
function selectRole(role) {
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
  const card = document.getElementById('card_' + role);
  if (card) {
    card.classList.add('selected');
    card.querySelector('input[type="radio"]').checked = true;
  }
}

function togglePwSection() {
  const section     = document.getElementById('pwSection');
  const placeholder = document.getElementById('pwPlaceholder');
  const btn         = document.getElementById('pwToggleBtn');
  const visible     = section.style.display !== 'none';
  section.style.display     = visible ? 'none' : '';
  placeholder.style.display = visible ? '' : 'none';
  btn.textContent = visible ? 'Changer le mot de passe' : 'Annuler le changement';
  if (!visible) document.getElementById('pw1').focus();
  else { document.getElementById('pw1').value = ''; document.getElementById('pw2').value = ''; }
}

function updateActifLabel() {
  const actif = document.getElementById('toggleActif').checked;
  document.getElementById('actifLabel').textContent = actif ? 'Compte actif' : 'Compte inactif';
  document.getElementById('actifDesc').textContent  = actif
    ? "L'utilisateur peut se connecter"
    : "L'utilisateur ne peut pas se connecter";
}

@if($errors->has('new_password'))
  togglePwSection();
@endif
</script>
@endpush
