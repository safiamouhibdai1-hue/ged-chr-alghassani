{{-- patients/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Nouveau Patient')
@section('page-title', 'Nouveau Dossier Patient')
@section('page-subtitle', 'Enregistrement d\'un nouveau patient')

@section('content')

<div style="max-width:600px;margin:0 auto">

  <div class="section-card" style="padding:0">

    <div class="section-card-head">
      <span class="section-card-title">Informations du patient</span>
      <a href="{{ route('patients.index') }}" class="btn btn-secondary btn-sm">← Retour</a>
    </div>

    <div style="padding:1rem 1.2rem">
      <form method="POST" action="{{ route('patients.store') }}">
        @csrf

        {{-- Ligne 1 : IPP + CIN --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem">
          <div>
            <label class="form-label">IPP <span style="color:var(--danger)">*</span></label>
            <input type="number" name="ipp"
              class="form-control {{ $errors->has('ipp') ? 'is-error' : '' }}"
              value="{{ old('ipp') }}" placeholder="Ex: 1011" min="1" required />
            @error('ipp')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div>
            <label class="form-label">CIN <span style="color:var(--danger)">*</span></label>
            <input type="text" name="cin"
              class="form-control {{ $errors->has('cin') ? 'is-error' : '' }}"
              value="{{ old('cin') }}" placeholder="Ex: BE456789" maxlength="20"
              oninput="this.value=this.value.toUpperCase()" required />
            @error('cin')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Ligne 2 : Nom + Prénom --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem">
          <div>
            <label class="form-label">Nom <span style="color:var(--danger)">*</span></label>
            <input type="text" name="nom"
              class="form-control {{ $errors->has('nom') ? 'is-error' : '' }}"
              value="{{ old('nom') }}" placeholder="Ex: BENALI"
              oninput="this.value=this.value.toUpperCase()" required />
            @error('nom')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div>
            <label class="form-label">Prénom <span style="color:var(--danger)">*</span></label>
            <input type="text" name="prenom"
              class="form-control {{ $errors->has('prenom') ? 'is-error' : '' }}"
              value="{{ old('prenom') }}" placeholder="Ex: Karim" required />
            @error('prenom')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Ligne 3 : Date naissance + Service --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem">
          <div>
            <label class="form-label">Date de naissance <span style="color:var(--danger)">*</span></label>
            <input type="date" name="date_naissance" id="date_naissance"
              class="form-control {{ $errors->has('date_naissance') ? 'is-error' : '' }}"
              value="{{ old('date_naissance') }}"
              max="{{ now()->subDay()->format('Y-m-d') }}"
              onchange="calcAge()" required />
            <div id="ageDisplay" class="form-hint" style="display:none"></div>
            @error('date_naissance')<div class="form-error">{{ $message }}</div>@enderror
          </div>
          <div>
            <label class="form-label">Service <span style="color:var(--danger)">*</span></label>
            <select name="service"
              class="form-control {{ $errors->has('service') ? 'is-error' : '' }}" required>
              <option value="">— Sélectionner —</option>
              @foreach ($services as $svc)
                <option value="{{ $svc }}" {{ old('service') === $svc ? 'selected' : '' }}>{{ $svc }}</option>
              @endforeach
            </select>
            @error('service')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Ligne 4 : N° Dossier --}}
        <div style="margin-bottom:1rem">
          <label class="form-label">
            N° Dossier <span style="color:var(--danger)">*</span>
            <span class="form-hint" style="display:inline;margin:0;font-size:11px">(auto-généré)</span>
          </label>
          <input type="text" name="numero_dossier"
            class="form-control {{ $errors->has('numero_dossier') ? 'is-error' : '' }}"
            value="{{ old('numero_dossier', $numeroDossier) }}" required />
          @error('numero_dossier')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:.75rem;padding-top:.25rem;border-top:1px solid rgba(221,214,254,0.3)">
          <button type="submit" class="btn btn-primary">Enregistrer le patient</button>
          <a href="{{ route('patients.index') }}" class="btn btn-secondary">Annuler</a>
        </div>

      </form>
    </div>

  </div>

</div>

@endsection

@push('scripts')
<script>
function calcAge() {
  const dob = document.getElementById('date_naissance').value;
  if (!dob) return;
  const birth = new Date(dob);
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const m = today.getMonth() - birth.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
  const el = document.getElementById('ageDisplay');
  el.textContent = age + ' ans';
  el.style.display = 'block';
}
</script>
@endpush
