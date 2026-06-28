{{-- documents/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Modifier le document')
@section('page-title', 'Modifier un document')
@section('page-subtitle', 'Mise à jour des métadonnées')

@section('content')

@php
  /** @var \App\Models\Document $document */
  $typeCouleurs = [
    'rapport_consultation'    => ['bg'=>'#EDE9FE','tx'=>'var(--primary)'],
    'compte_rendu_operatoire' => ['bg'=>'#EFF6FF','tx'=>'#2563EB'],
    'resultat_laboratoire'    => ['bg'=>'#FEF3C7','tx'=>'#92400E'],
    'resultat_radiologie'     => ['bg'=>'#DBEAFE','tx'=>'#1D4ED8'],
    'ordonnance'              => ['bg'=>'#D1FAE5','tx'=>'#065F46'],
    'courrier_medical'        => ['bg'=>'#FDE8EE','tx'=>'#9B2548'],
    'autre'                   => ['bg'=>'#F3F4F6','tx'=>'#6B7280'],
  ];
  $tc = $typeCouleurs[$document->typedocument] ?? $typeCouleurs['autre'];
@endphp

<div style="display:grid;grid-template-columns:1fr 290px;gap:1.2rem;align-items:start;max-width:1000px">

  {{-- Formulaire --}}
  <div>
    <form method="POST" action="{{ route('documents.update', $document->id_document) }}">
      @csrf
      @method('PUT')

      {{-- Métadonnées textuelles --}}
      <div class="form-section">
        <div class="form-section-title">Métadonnées du document</div>

        <div class="form-group">
          <label class="form-label">Titre <span style="color:var(--danger)">*</span></label>
          <input type="text" name="titre"
                 class="form-control {{ $errors->has('titre') ? 'is-error' : '' }}"
                 value="{{ old('titre', $document->titre) }}"
                 placeholder="Titre descriptif du document"
                 maxlength="200" />
          @error('titre')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Type de document <span style="color:var(--danger)">*</span></label>
            <select name="typedocument"
                    class="form-control {{ $errors->has('typedocument') ? 'is-error' : '' }}">
              @foreach($typesDocs as $val => $lbl)
                <option value="{{ $val }}" {{ old('typedocument', $document->typedocument) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
              @endforeach
            </select>
            @error('typedocument')<div class="form-error">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label class="form-label">Service <span style="color:var(--danger)">*</span></label>
            <select name="service"
                    class="form-control {{ $errors->has('service') ? 'is-error' : '' }}">
              @foreach($services as $s)
                <option value="{{ $s }}" {{ old('service', $document->service) === $s ? 'selected' : '' }}>{{ $s }}</option>
              @endforeach
            </select>
            @error('service')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Mots-clés</label>
          <input type="text" name="mots_cles"
                 class="form-control"
                 value="{{ old('mots_cles', $document->mots_cles) }}"
                 placeholder="Ex : cardio, ECG, bilan, urgent…"
                 maxlength="500" />
          <div class="form-hint">Séparés par des virgules — facilitent la recherche</div>
          @error('mots_cles')<div class="form-error">{{ $message }}</div>@enderror
        </div>
      </div>

      {{-- Infos non modifiables --}}
      <div class="form-section">
        <div class="form-section-title">Informations non modifiables</div>
        <div class="form-row">
          <div>
            <label class="form-label">Patient associé</label>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);padding:4px 0">
              @if($document->patient)
                {{ $document->patient->nom }} {{ $document->patient->prenom }}
                <span style="font-size:11px;color:var(--text-muted)">(IPP: {{ $document->ipp }})</span>
              @else —
              @endif
            </div>
          </div>
          <div>
            <label class="form-label">Importé par</label>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);padding:4px 0">
              {{ $document->utilisateur?->prenom }} {{ $document->utilisateur?->nom }}
            </div>
          </div>
          <div>
            <label class="form-label">Date d'import</label>
            <div style="font-size:13px;color:var(--text-primary);padding:4px 0">{{ $document->date_import->format('d/m/Y à H:i') }}</div>
          </div>
          <div>
            <label class="form-label">Fichier</label>
            <div style="font-size:12px;color:var(--text-muted);padding:4px 0;word-break:break-all">{{ basename($document->chemin_fichier) }}</div>
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div style="display:flex;gap:.75rem">
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        <a href="{{ route('documents.show', $document->id_document) }}" class="btn btn-secondary">Annuler</a>
      </div>

    </form>
  </div>

  {{-- Sidebar aperçu --}}
  <div class="section-card" style="position:sticky;top:1.5rem;animation-delay:0.1s">
    <div class="section-card-head">
      <span class="section-card-title">Aperçu du document</span>
    </div>
    <div style="padding:1.2rem;text-align:center">
      <div style="width:56px;height:56px;border-radius:var(--radius-md);background:{{ $tc['bg'] }};display:flex;align-items:center;justify-content:center;margin:0 auto .9rem">
        <svg viewBox="0 0 24 24" fill="none" stroke="{{ $tc['tx'] }}" stroke-width="1.5" style="width:28px;height:28px">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <div style="font-weight:700;font-size:13.5px;color:var(--text-primary);word-break:break-word;margin-bottom:.8rem">{{ $document->titre }}</div>

      <div style="border-top:1px solid var(--border-light);padding-top:.8rem;text-align:left">
        @foreach([
          ['ID',          '#' . $document->id_document],
          ['Type',         $document->type_label],
          ['Service',      $document->service],
          ['Extension',    strtoupper($document->extension)],
          ['Importé',      $document->date_import->format('d/m/Y')],
        ] as [$lbl, $val])
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:.4rem">
          <span style="color:var(--text-secondary)">{{ $lbl }}</span>
          <span style="font-weight:600;color:var(--text-primary)">{{ $val }}</span>
        </div>
        @endforeach
      </div>

      <div style="margin-top:1rem;display:flex;flex-direction:column;gap:6px">
        <a href="{{ route('documents.show', $document->id_document) }}" class="btn btn-secondary btn-sm" style="justify-content:center">Voir l'aperçu</a>
        <a href="{{ route('documents.download', $document->id_document) }}" class="btn btn-secondary btn-sm" style="justify-content:center">Télécharger</a>
      </div>
    </div>
  </div>

</div>

@endsection
