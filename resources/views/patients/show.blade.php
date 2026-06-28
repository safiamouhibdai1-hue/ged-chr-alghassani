{{-- patients/show.blade.php — Dossier patient complet (vue médecin) --}}
@extends('layouts.app')
@section('title', $patient->nom_complet)
@section('page-title', 'Dossier Patient')
@section('page-subtitle', 'IPP ' . str_pad($patient->ipp, 4, '0', STR_PAD_LEFT) . ' · ' . $patient->numero_dossier)

@push('styles')
<style>
.patient-layout { display: grid; grid-template-columns: 280px 1fr; gap: 1.2rem; align-items: start; }
.info-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 1.1rem; border-bottom: 1px solid rgba(221,214,254,0.2);
  font-size: 12.5px;
}
.info-row:last-child { border-bottom: none; }
.info-label { color: var(--text-secondary); font-weight: 500; }
.info-val   { font-weight: 600; color: var(--text-primary); }

/* Filtres type document */
.type-filters { display: flex; gap: 6px; flex-wrap: wrap; }
.type-filter-btn {
  padding: 4px 12px; border-radius: var(--radius-full); font-size: 11.5px;
  font-weight: 600; border: 1.5px solid var(--border); background: var(--bg-card);
  color: var(--text-secondary); cursor: pointer; transition: var(--t);
}
.type-filter-btn:hover  { border-color: var(--primary); color: var(--primary); }
.type-filter-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; }

/* Ligne de document */
.doc-item {
  display: flex; align-items: center; gap: 12px;
  padding: .85rem 1.1rem;
  border-bottom: 1px solid rgba(221,214,254,0.2);
  transition: var(--t); text-decoration: none; color: inherit;
}
.doc-item:last-child { border-bottom: none; }
.doc-item:hover { background: rgba(245,243,255,0.7); text-decoration: none; }
.doc-item-icon {
  width: 40px; height: 40px; border-radius: var(--radius);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; background: var(--primary-bg);
}
.doc-item-icon svg { width: 20px; height: 20px; color: var(--primary); }

/* Formulaire import rapide */
.upload-zone {
  border: 2px dashed var(--primary-border);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: var(--t);
  background: var(--primary-bg);
  margin-bottom: .75rem;
}
.upload-zone:hover,
.upload-zone.dragover {
  border-color: var(--primary);
  background: var(--mauve-100);
}
.upload-zone-icon { color: var(--primary); opacity: .6; margin-bottom: .5rem; }
.upload-zone-icon svg { width: 32px; height: 32px; }
.upload-zone-text { font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 3px; }
.upload-zone-sub  { font-size: 11.5px; color: var(--text-muted); }
.file-selected    { font-size: 12px; color: var(--primary); font-weight: 600; margin-top: 6px; }

@media (max-width: 960px) {
  .patient-layout { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

@php
  /** @var \App\Models\Patient $patient */
  $me = Auth::user();
  $avPalette = ['av-purple','av-teal','av-orange','av-blue','av-pink','av-green'];
  $avClass   = fn($str) => $avPalette[abs(crc32((string)($str ?? '?'))) % count($avPalette)];
  $docs = $patient->documents->sortByDesc('date_import');
  $typesDocs = [
    'rapport_consultation'    => 'Rapport consultation',
    'compte_rendu_operatoire' => 'CR opératoire',
    'resultat_laboratoire'    => 'Résultat labo',
    'resultat_radiologie'     => 'Résultat radio',
    'ordonnance'              => 'Ordonnance',
    'courrier_medical'        => 'Courrier médical',
    'autre'                   => 'Autre',
  ];
@endphp

{{-- En-tête patient --}}
<div style="background:linear-gradient(135deg,var(--mauve-900) 0%,var(--mauve-700) 100%);
            border-radius:var(--radius-md);padding:1.4rem 1.6rem;margin-bottom:1.2rem;
            display:flex;align-items:center;justify-content:space-between;gap:1rem;
            box-shadow:var(--shadow-md);flex-wrap:wrap">

  <div style="display:flex;align-items:center;gap:14px">
    <div class="avatar {{ $avClass($patient->initiales) }}"
         style="width:60px;height:60px;font-size:20px;border:2px solid rgba(255,255,255,0.25)">
      {{ $patient->initiales }}
    </div>
    <div>
      <div style="font-size:20px;font-weight:800;color:#fff;letter-spacing:-0.4px">
        {{ $patient->nom_complet }}
      </div>
      <div style="display:flex;align-items:center;gap:10px;margin-top:5px;flex-wrap:wrap">
        <span style="font-size:12px;color:rgba(255,255,255,.55)">N° {{ $patient->numero_dossier }}</span>
        <span style="color:rgba(255,255,255,.3)">·</span>
        <span style="padding:2px 10px;border-radius:var(--radius-full);font-size:11.5px;font-weight:600;
                     background:rgba(255,255,255,.15);color:rgba(255,255,255,.9)">
          {{ $patient->service }}
        </span>
        <span style="color:rgba(255,255,255,.3)">·</span>
        <span style="font-size:12px;color:rgba(255,255,255,.55)">
          {{ $patient->age }} ans
          @if($patient->date_naissance)
            (né le {{ $patient->date_naissance->format('d/m/Y') }})
          @endif
        </span>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:8px;flex-shrink:0">
    <a href="{{ route('patients.index') }}"
       style="padding:7px 14px;border-radius:var(--radius);background:rgba(255,255,255,.1);
              border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.85);
              font-size:12.5px;font-weight:600;text-decoration:none;transition:var(--t)"
       onmouseover="this.style.background='rgba(255,255,255,.2)'"
       onmouseout="this.style.background='rgba(255,255,255,.1)'">
      ← Retour
    </a>
    @if($me->isAdministratif() || $me->isInfirmier())
      <a href="{{ route('patients.edit', $patient->ipp) }}"
         style="padding:7px 14px;border-radius:var(--radius);background:rgba(255,255,255,.1);
                border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.85);
                font-size:12.5px;font-weight:600;text-decoration:none">
        Modifier
      </a>
    @endif
    <a href="{{ route('documents.create') }}?ipp={{ $patient->ipp }}"
       style="padding:7px 16px;border-radius:var(--radius);background:rgba(255,255,255,.95);
              color:var(--primary);font-size:12.5px;font-weight:700;text-decoration:none;transition:var(--t)"
       onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,.95)'">
      + Importer un document
    </a>
  </div>

</div>

{{-- Corps --}}
<div class="patient-layout">

  {{-- Colonne gauche : identité + stats --}}
  <div style="display:flex;flex-direction:column;gap:1rem">

    {{-- Identité --}}
    <div class="section-card" style="animation-delay:0.06s">
      <div class="section-card-head"><span class="section-card-title">Identité</span></div>
      <div style="padding:0">
        <div class="info-row">
          <span class="info-label">Nom complet</span>
          <span class="info-val">{{ $patient->nom_complet }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">CIN</span>
          <span class="info-val" style="font-family:monospace">{{ $patient->cin }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">N° dossier</span>
          <span class="info-val" style="font-family:monospace">{{ $patient->numero_dossier }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Date de naissance</span>
          <span class="info-val">
            @if($patient->date_naissance)
              {{ $patient->date_naissance->format('d/m/Y') }}
            @else — @endif
          </span>
        </div>
        <div class="info-row">
          <span class="info-label">Âge</span>
          <span class="info-val">{{ $patient->age }} ans</span>
        </div>
        <div class="info-row">
          <span class="info-label">Service</span>
          <span class="info-val">{{ $patient->service }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Enregistré le</span>
          <span class="info-val">
            @if($patient->date_creation)
              {{ $patient->date_creation->format('d/m/Y') }}
            @else — @endif
          </span>
        </div>
      </div>
    </div>

    {{-- Statistiques documents --}}
    <div class="section-card" style="animation-delay:0.1s">
      <div class="section-card-head">
        <span class="section-card-title">Documents du dossier</span>
        <span style="font-size:22px;font-weight:800;color:var(--primary)">{{ $stats['total_docs'] }}</span>
      </div>
      <div style="padding:.8rem 1.1rem">
        @if($stats['dernier_doc'])
          <div style="font-size:12px;color:var(--text-secondary);margin-bottom:.6rem">
            Dernier import :
            <strong>{{ $stats['dernier_doc']->format('d/m/Y') }}</strong>
          </div>
        @endif
        @forelse($stats['docs_par_type'] as $type => $count)
          @php $pct = $stats['total_docs'] > 0 ? round(($count / $stats['total_docs']) * 100) : 0; @endphp
          <div style="margin-bottom:.6rem">
            <div style="display:flex;justify-content:space-between;font-size:11.5px;margin-bottom:3px">
              <span style="color:var(--text-secondary)">{{ $typesDocs[$type] ?? $type }}</span>
              <span style="font-weight:700;color:var(--primary)">{{ $count }}</span>
            </div>
            <div style="height:4px;background:var(--border-light);border-radius:2px;overflow:hidden">
              <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,var(--primary),var(--primary-light));border-radius:2px"></div>
            </div>
          </div>
        @empty
          <p style="font-size:12.5px;color:var(--text-muted)">Aucun document.</p>
        @endforelse
      </div>
    </div>

  </div>

  {{-- Colonne droite : documents + import rapide --}}
  <div style="display:flex;flex-direction:column;gap:1.2rem">

  <div class="section-card" style="animation-delay:0.08s">

    <div class="section-card-head" style="gap:.75rem;flex-wrap:wrap">
      <span class="section-card-title">
        Documents médicaux
        <span class="table-count" style="margin-left:6px">{{ $stats['total_docs'] }}</span>
      </span>
      {{-- Filtres par type --}}
      <div class="type-filters">
        <button class="type-filter-btn active" data-type="all" onclick="filterDocs('all',this)">Tous</button>
        @foreach($typesDocs as $tKey => $tLabel)
          @if($docs->where('typedocument', $tKey)->count() > 0)
            <button class="type-filter-btn" data-type="{{ $tKey }}" onclick="filterDocs('{{ $tKey }}',this)">
              {{ $tLabel }}
              <span style="opacity:.6">({{ $docs->where('typedocument', $tKey)->count() }})</span>
            </button>
          @endif
        @endforeach
      </div>
    </div>

    @forelse($docs as $doc)
    <a href="{{ route('documents.show', $doc->id_docum) }}"
       class="doc-item" data-type="{{ $doc->typedocument }}">

      {{-- Icône type --}}
      <div class="doc-item-icon">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
        </svg>
      </div>

      {{-- Infos --}}
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--text-primary);
                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          {{ $doc->titre }}
        </div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px;display:flex;gap:8px;align-items:center">
          <span>{{ $doc->date_import->format('d/m/Y') }}</span>
          @if($doc->utilisateur)
            <span style="color:var(--border)">·</span>
            <span>{{ $doc->utilisateur->prenom }} {{ $doc->utilisateur->nom }}</span>
          @endif
        </div>
      </div>

      {{-- Badges --}}
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">
        <span class="badge badge-blue" style="font-size:10.5px">{{ $doc->type_label }}</span>
        <span class="badge badge-gray" style="font-size:10px">{{ strtoupper($doc->extension) }}</span>
      </div>

      {{-- Flèche --}}
      <svg viewBox="0 0 20 20" fill="currentColor"
           style="width:16px;height:16px;color:var(--text-muted);flex-shrink:0;opacity:.4">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
      </svg>

    </a>
    @empty
    <div class="empty-state" style="padding:3.5rem">
      <div class="empty-state-icon">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
      </div>
      <p style="font-weight:600;margin-bottom:4px">Aucun document dans ce dossier</p>
      <p style="font-size:12.5px">Importez le premier document médical de ce patient.</p>
      <a href="{{ route('documents.create') }}?ipp={{ $patient->ipp }}"
         class="btn btn-primary" style="margin-top:.9rem">
        Importer un document
      </a>
    </div>
    @endforelse

  </div>

  <div class="section-card" style="animation-delay:0.18s">
    <div class="section-card-head"><span class="section-card-title">Import rapide</span></div>
    <div style="padding:.7rem 1rem">
      <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" id="quickUploadForm">
        @csrf
        <input type="hidden" name="ipp" value="{{ $patient->ipp }}" />
        <input type="hidden" name="redirect_to_patient" value="1" />

        <div style="margin-bottom:.6rem">
          <label class="form-label" style="margin-bottom:4px">Titre <span style="color:var(--danger)">*</span></label>
          <input type="text" name="titre" class="form-control" style="padding:8px 10px;font-size:13px" placeholder="Ex: Bilan sanguin" required />
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.6rem">
          <div>
            <label class="form-label" style="margin-bottom:4px">Type</label>
            <select name="typedocument" class="form-control" style="padding:7px 10px;font-size:12.5px">
              @foreach($typesDocs as $k => $lbl)
                <option value="{{ $k }}">{{ $lbl }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="form-label" style="margin-bottom:4px">Service</label>
            <select name="service" class="form-control" style="padding:7px 10px;font-size:12.5px">
              @foreach(['Cardiologie','Chirurgie','Pediatrie','Neurologie','Laboratoire','Radiologie','Urgences','Autre'] as $svc)
                <option value="{{ $svc }}" {{ $patient->service === $svc ? 'selected' : '' }}>{{ $svc }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Zone de dépôt compacte --}}
        <div class="upload-zone" id="dropZone" onclick="document.getElementById('fileInput').click()"
             style="padding:.9rem;margin-bottom:.6rem">
          <div class="upload-zone-icon" style="margin-bottom:.3rem">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:22px;height:22px"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
          </div>
          <div class="upload-zone-text" style="font-size:12px">Glissez ou cliquez pour choisir</div>
          <div class="upload-zone-sub" style="font-size:11px">PDF, JPG, PNG · max 10 Mo</div>
          <div class="file-selected" id="fileSelectedName" style="display:none"></div>
          <input type="file" id="fileInput" name="fichier"
                 accept=".pdf,.jpg,.jpeg,.png" style="display:none"
                 onchange="showFile(this)" required />
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:9px;font-size:13px">
          Importer le document
        </button>

      </form>
    </div>
  </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
function filterDocs(type, btn) {
  // Mettre à jour les boutons
  document.querySelectorAll('.type-filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  // Filtrer les lignes
  document.querySelectorAll('.doc-item').forEach(row => {
    row.style.display = (type === 'all' || row.dataset.type === type) ? 'flex' : 'none';
  });
}

function showFile(input) {
  const name = input.files[0]?.name;
  const el = document.getElementById('fileSelectedName');
  if (name) {
    el.textContent = '✓ ' + name;
    el.style.display = 'block';
    document.getElementById('dropZone').style.borderColor = 'var(--primary)';
  }
}

// Drag & drop
const zone = document.getElementById('dropZone');
if (zone) {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
  zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length) {
      document.getElementById('fileInput').files = files;
      showFile(document.getElementById('fileInput'));
    }
  });
}
</script>
@endpush
