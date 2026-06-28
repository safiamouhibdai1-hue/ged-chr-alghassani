{{-- documents/show.blade.php — Fiche document avec visionneuse --}}
@extends('layouts.app')
@section('title', $document->titre)
@section('page-title', 'Document médical')
@section('page-subtitle', $document->type_label . ' · ' . $document->service)

@push('styles')
<style>
.doc-layout { display: grid; grid-template-columns: 300px 1fr; gap: 1.2rem; align-items: start; }
.meta-row {
  display: flex; justify-content: space-between; align-items: flex-start;
  padding: 9px 1.2rem; border-bottom: 1px solid rgba(221,214,254,0.2);
  font-size: 12.5px;
}
.meta-row:last-child { border-bottom: none; }
.meta-label { color: var(--text-secondary); font-weight: 500; }
.meta-val   { font-weight: 600; color: var(--text-primary); text-align: right; max-width: 55%; }

/* Barre d'actions fixe en haut */
.doc-action-bar {
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap;
  background: var(--bg-card);
  border: 1px solid rgba(221,214,254,0.5);
  border-radius: var(--radius-md);
  padding: 1rem 1.3rem;
  margin-bottom: 1.2rem;
  box-shadow: var(--shadow-sm);
  animation: fadeInUp 0.3s ease both;
}
.doc-action-bar-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
.doc-action-bar-right { display: flex; gap: 7px; flex-wrap: wrap; }

.doc-type-badge {
  width: 46px; height: 46px; border-radius: var(--radius-md);
  background: var(--primary-bg); display: flex; align-items: center;
  justify-content: center; flex-shrink: 0;
}
.doc-type-badge svg { width: 24px; height: 24px; color: var(--primary); }
.doc-title-main  { font-size: 17px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.3px; }
.doc-title-meta  { display: flex; align-items: center; gap: 7px; margin-top: 4px; flex-wrap: wrap; }

/* Visionneuse */
.viewer-wrap {
  background: #2d2d2d;
  border-radius: var(--radius-md);
  overflow: hidden;
  position: relative;
}
.viewer-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: .6rem 1rem;
  background: #1a1a1a;
  border-bottom: 1px solid #444;
}
.viewer-toolbar-title { font-size: 12px; color: rgba(255,255,255,0.6); font-weight: 500; }
.viewer-toolbar-actions { display: flex; gap: 6px; }
.viewer-btn {
  padding: 5px 12px; border-radius: var(--radius);
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
  color: rgba(255,255,255,0.75); font-size: 12px; font-weight: 500;
  cursor: pointer; text-decoration: none; display: inline-flex;
  align-items: center; gap: 5px; transition: var(--t);
}
.viewer-btn:hover { background: rgba(255,255,255,0.15); color: #fff; text-decoration: none; }
.viewer-btn svg { width: 13px; height: 13px; }

/* Historique accès */
.access-row {
  display: flex; align-items: center; gap: 9px;
  padding: 8px 1.1rem;
  border-bottom: 1px solid rgba(221,214,254,0.15);
  font-size: 12px;
}
.access-row:last-child { border-bottom: none; }

@media (max-width: 1000px) {
  .doc-layout { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

@php
  /** @var \App\Models\Document $document */
  $me = Auth::user();
  $avPalette = ['av-purple','av-teal','av-orange','av-blue','av-pink','av-green'];
  $avClass   = fn($str) => $avPalette[abs(crc32((string)($str ?? '?'))) % count($avPalette)];
@endphp

{{-- Barre d'actions --}}
<div class="doc-action-bar">
  <div class="doc-action-bar-left">
    <div class="doc-type-badge">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div style="min-width:0">
      <div class="doc-title-main">{{ $document->titre }}</div>
      <div class="doc-title-meta">
        <span class="badge badge-blue">{{ $document->type_label }}</span>
        <span style="font-size:12px;color:var(--text-secondary)">{{ $document->service }}</span>
        <span style="color:var(--border)">·</span>
        <span style="font-size:12px;color:var(--text-muted)">
          {{ $document->date_import->format('d/m/Y') }}
        </span>
        <span class="badge badge-gray" style="font-size:10.5px">{{ strtoupper($document->extension) }}</span>
      </div>
    </div>
  </div>

  <div class="doc-action-bar-right">
    @if($document->patient)
      <a href="{{ route('patients.show', $document->ipp) }}" class="btn btn-secondary btn-sm">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
        Dossier patient
      </a>
    @endif

    <a href="{{ route('documents.download', $document->id_docum) }}" class="btn btn-secondary btn-sm">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
      Télécharger
    </a>

    <button onclick="imprimerDocument()" class="btn btn-secondary btn-sm">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a1 1 0 001 1h8a1 1 0 001-1v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1zm2 0h6v3H7V4zm-1 9v-1h8v1a1 1 0 01-1 1H7a1 1 0 01-1-1zm8-4a1 1 0 110 2 1 1 0 010-2z" clip-rule="evenodd"/></svg>
      Imprimer
    </button>

    @if($me->isAdministratif())
      <a href="{{ route('documents.edit', $document->id_docum) }}" class="btn btn-secondary btn-sm">Modifier</a>
      <a href="{{ route('documents.confirm-delete', $document->id_docum) }}" class="btn btn-danger btn-sm">
        <svg viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px">
          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        Supprimer
      </a>
    @endif
  </div>
</div>

{{-- Corps 2 colonnes --}}
<div class="doc-layout">

  {{-- Colonne gauche : métadonnées --}}
  <div style="display:flex;flex-direction:column;gap:1rem">

    {{-- Métadonnées --}}
    <div class="section-card" style="animation-delay:0.06s">
      <div class="section-card-head"><span class="section-card-title">Informations</span></div>
      <div style="padding:0">
        <div class="meta-row">
          <span class="meta-label">ID document</span>
          <span class="meta-val">#{{ $document->id_docum }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Type</span>
          <span class="meta-val">{{ $document->type_label }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Service</span>
          <span class="meta-val">{{ $document->service }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Format</span>
          <span class="meta-val">{{ strtoupper($document->extension) }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Date d'import</span>
          <span class="meta-val">{{ $document->date_import->format('d/m/Y') }}</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">Importé par</span>
          <span class="meta-val">{{ $document->utilisateur?->nom_complet ?? '—' }}</span>
        </div>
      </div>
      @if($document->mots_cles)
      <div style="padding:.75rem 1.2rem;border-top:1px solid rgba(221,214,254,0.2)">
        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Mots-clés</div>
        <div style="display:flex;gap:5px;flex-wrap:wrap">
          @foreach(explode(',', $document->mots_cles) as $mot)
            @if(trim($mot))
              <span class="badge badge-outline" style="font-size:11px">{{ trim($mot) }}</span>
            @endif
          @endforeach
        </div>
      </div>
      @endif
    </div>

    {{-- Patient associé --}}
    @if($document->patient)
    <div class="section-card" style="animation-delay:0.1s">
      <div class="section-card-head"><span class="section-card-title">Patient associé</span></div>
      <div style="padding:1rem 1.2rem">
        <a href="{{ route('patients.show', $document->patient->ipp) }}"
           style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit">
          <div class="avatar {{ $avClass($document->patient->initiales) }}"
               style="width:44px;height:44px;font-size:14px;flex-shrink:0">
            {{ $document->patient->initiales }}
          </div>
          <div>
            <div style="font-weight:700;font-size:13.5px;color:var(--text-primary)">
              {{ $document->patient->nom_complet }}
            </div>
            <div style="font-size:11.5px;color:var(--text-secondary);margin-top:2px">
              {{ $document->patient->numero_dossier }}
            </div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px">
              Service : {{ $document->patient->service }}
              &middot; {{ $document->patient->age }} ans
            </div>
            <div style="font-size:11.5px;color:var(--primary);margin-top:4px;font-weight:600">
              Voir le dossier complet →
            </div>
          </div>
        </a>
      </div>
    </div>
    @endif


  </div>

  {{-- Colonne droite : visionneuse --}}
  <div class="section-card" style="overflow:hidden;animation-delay:0.08s">

    {{-- Barre de la visionneuse --}}
    <div class="section-card-head">
      <span class="section-card-title">Aperçu du document</span>
      <div style="display:flex;gap:6px">
        @if($fichierExiste)
          <a href="{{ route('documents.download', $document->id_docum) }}" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            Télécharger
          </a>
          <button onclick="imprimerDocument()" class="btn btn-secondary btn-sm">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a1 1 0 001 1h8a1 1 0 001-1v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1zm2 0h6v3H7V4zm-1 9v-1h8v1a1 1 0 01-1 1H7a1 1 0 01-1-1zm8-4a1 1 0 110 2 1 1 0 010-2z" clip-rule="evenodd"/></svg>
            Imprimer
          </button>
        @endif
      </div>
    </div>

    @if(!$fichierExiste)
      {{-- Fichier absent (données de démo) --}}
      <div style="padding:4rem 2rem;text-align:center;background:var(--bg-light)">
        <div style="width:72px;height:72px;border-radius:var(--radius-lg);background:var(--primary-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 1.2rem">
          <svg viewBox="0 0 20 20" fill="currentColor" style="width:36px;height:36px;color:var(--primary);opacity:.6">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:6px">
          Fichier non disponible
        </div>
        <div style="font-size:13px;color:var(--text-secondary);max-width:340px;margin:0 auto 1.2rem;line-height:1.6">
          Ce document de démonstration n'a pas de fichier physique associé.
          Importez un vrai document PDF ou image pour tester la visionneuse.
        </div>
        <code style="font-size:11px;background:var(--bg-card);border:1px solid var(--border);padding:4px 10px;border-radius:var(--radius);color:var(--text-muted)">
          {{ $document->chemin_fichier }}
        </code>
        <div style="margin-top:1.5rem">
          <a href="{{ route('documents.create') }}?ipp={{ $document->ipp }}" class="btn btn-primary">
            Importer un document réel
          </a>
        </div>
      </div>

    @elseif($document->isPdf())
      {{-- Visionneuse PDF --}}
      <div style="background:#f0f0f0;border-top:1px solid var(--border)">
        <iframe
          id="pdfViewer"
          src="{{ route('documents.preview', $document->id_docum) }}"
          style="width:100%;height:740px;border:none;display:block"
          title="{{ $document->titre }}">
          <div style="padding:2rem;text-align:center">
            Votre navigateur ne supporte pas la lecture PDF intégrée.
            <a href="{{ route('documents.download', $document->id_docum) }}">Télécharger le fichier</a>
          </div>
        </iframe>
      </div>

    @elseif($document->isImage())
      {{-- Visionneuse image --}}
      <div id="imgViewer"
           style="background:#1a1a1a;padding:1.5rem;display:flex;align-items:center;justify-content:center;min-height:500px">
        <img src="{{ route('documents.preview', $document->id_docum) }}"
             alt="{{ $document->titre }}"
             id="docImage"
             style="max-width:100%;max-height:680px;border-radius:var(--radius);object-fit:contain;
                    box-shadow:0 8px 40px rgba(0,0,0,0.4);cursor:zoom-in"
             onclick="toggleZoom(this)" />
      </div>

    @else
      {{-- Format non prévisualisable --}}
      <div style="padding:4rem;text-align:center;background:var(--bg-light)">
        <div class="empty-state-icon" style="margin:0 auto 1rem">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
        </div>
        <div style="font-weight:700;margin-bottom:.5rem">Aperçu non disponible</div>
        <div style="font-size:12.5px;color:var(--text-muted);margin-bottom:1.2rem">
          Format {{ strtoupper($document->extension) }} — téléchargez le fichier pour le consulter.
        </div>
        <a href="{{ route('documents.download', $document->id_docum) }}" class="btn btn-primary">
          Télécharger le fichier
        </a>
      </div>
    @endif

  </div>

</div>

@endsection

@push('scripts')
<script>
function imprimerDocument() {
  @if($fichierExiste && $document->isPdf())
    const iframe = document.getElementById('pdfViewer');
    if (iframe) { iframe.contentWindow.print(); return; }
  @endif
  window.print();
}

function toggleZoom(img) {
  if (img.style.maxWidth === '100%' || !img.style.maxWidth) {
    img.style.maxWidth  = 'none';
    img.style.cursor    = 'zoom-out';
    img.style.maxHeight = 'none';
  } else {
    img.style.maxWidth  = '100%';
    img.style.maxHeight = '680px';
    img.style.cursor    = 'zoom-in';
  }
}
</script>

@if($fichierExiste && $document->isImage())
<style>
@media print {
  .sidebar, .topbar, .doc-action-bar,
  .section-card:first-child, .doc-layout > div:first-child { display: none !important; }
  .doc-layout { grid-template-columns: 1fr !important; }
  #imgViewer { background: white !important; padding: 0 !important; }
  #docImage  { max-width: 100% !important; max-height: none !important; box-shadow: none !important; }
}
</style>
@endif

@if($fichierExiste && $document->isPdf())
<style>
@media print {
  body > * { display: none !important; }
  #pdfViewer { display: block !important; width: 100%; height: 100vh; border: none; }
}
</style>
@endif
@endpush
