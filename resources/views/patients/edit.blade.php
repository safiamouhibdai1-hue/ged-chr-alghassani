{{--
    patients/edit.blade.php
    Route : GET /patients/{ipp}/edit
--}}
@extends('layouts.app')

@section('title', 'Modifier — ' . $patient->nom_complet)
@section('page-title', 'Modifier le Dossier Patient')
@section('page-subtitle', $patient->numero_dossier)

@section('content')

<div style="max-width:720px">

  <div style="margin-bottom:1rem">
    <a href="{{ route('patients.show', $patient->ipp) }}" class="btn btn-secondary btn-sm">← Retour</a>
  </div>

  {{-- En-tête patient --}}
  <div style="display:flex;align-items:center;gap:12px;padding:.9rem 1.2rem;background:var(--primary-bg);border:1px solid var(--primary-border);border-radius:var(--radius-md);margin-bottom:1.2rem">
    <div class="avatar av-purple" style="width:40px;height:40px;font-size:13px;flex-shrink:0">{{ $patient->initiales }}</div>
    <div>
      <div style="font-weight:700;font-size:14px;color:var(--text-primary)">{{ $patient->nom_complet }}</div>
      <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">
        N° {{ $patient->numero_dossier }} · CIN {{ $patient->cin }}
        <span style="color:var(--text-muted)"> — CIN et N° dossier non modifiables</span>
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('patients.update', $patient->ipp) }}">
    @csrf
    @method('PUT')

    <div class="form-section">
      <div class="form-section-title">Identité</div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nom <span style="color:var(--danger)">*</span></label>
          <input type="text" name="nom"
            class="form-control {{ $errors->has('nom') ? 'is-error' : '' }}"
            value="{{ old('nom', $patient->nom) }}"
            oninput="this.value=this.value.toUpperCase()"
            required />
          @error('nom')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label class="form-label">Prénom <span style="color:var(--danger)">*</span></label>
          <input type="text" name="prenom"
            class="form-control {{ $errors->has('prenom') ? 'is-error' : '' }}"
            value="{{ old('prenom', $patient->prenom) }}"
            required />
          @error('prenom')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label class="form-label">Date de naissance <span style="color:var(--danger)">*</span></label>
          <input type="date" name="date_naissance"
            class="form-control {{ $errors->has('date_naissance') ? 'is-error' : '' }}"
            value="{{ old('date_naissance', $patient->date_naissance ? $patient->date_naissance->format('Y-m-d') : '') }}"
            max="{{ now()->subDay()->format('Y-m-d') }}"
            required />
          @error('date_naissance')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
          <label class="form-label">Service <span style="color:var(--danger)">*</span></label>
          <select name="service"
            class="form-control {{ $errors->has('service') ? 'is-error' : '' }}"
            required>
            @foreach ($services as $svc)
              <option value="{{ $svc }}" {{ old('service', $patient->service) === $svc ? 'selected' : '' }}>
                {{ $svc }}
              </option>
            @endforeach
          </select>
          @error('service')<div class="form-error">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>

    <div style="display:flex;gap:.75rem">
      <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
      <a href="{{ route('patients.show', $patient->ipp) }}" class="btn btn-secondary">Annuler</a>
    </div>

  </form>

</div>

@endsection
