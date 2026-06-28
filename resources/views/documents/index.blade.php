{{-- documents/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Documents')
@section('page-title', 'Documents Médicaux')
@section('page-subtitle', 'Tous les documents importés dans le GED')

@push('styles')
<style>
.stat-card { transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.2s ease !important; }
.stat-card:hover { transform: translateY(-4px) scale(1.01) !important; box-shadow: 0 8px 28px rgba(124,58,237,0.15) !important; }
</style>
@endpush

@section('content')

@php
  /** @var \App\Models\Utilisateur $me */
  $me = Auth::user();
@endphp

{{-- Compteurs --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.25rem">
  <div class="stat-card" style="animation-delay:0s">
    <div class="stat-card-badge" style="background:#EDE9FE">
      <svg viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)">
        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
      </svg>
    </div>
    <div class="stat-card-val">{{ number_format($compteurs['total']) }}</div>
    <div class="stat-card-lbl">Documents au total</div>
  </div>
  <div class="stat-card" style="animation-delay:0.06s">
    <div class="stat-card-badge" style="background:#F0FDF4">
      <svg viewBox="0 0 20 20" fill="currentColor" style="color:#059669">
        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div class="stat-card-val" style="color:#059669">{{ number_format($compteurs['ce_mois']) }}</div>
    <div class="stat-card-lbl">Importés ce mois — {{ now()->isoFormat('MMMM YYYY') }}</div>
  </div>
  <div class="stat-card" style="animation-delay:0.12s">
    <div class="stat-card-badge" style="background:#EFF6FF">
      <svg viewBox="0 0 20 20" fill="currentColor" style="color:#2563EB">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div class="stat-card-val" style="color:#2563EB">{{ number_format($compteurs['cette_semaine']) }}</div>
    <div class="stat-card-lbl">Importés cette semaine</div>
  </div>
</div>

{{-- Barre de filtres --}}
<form method="GET" action="{{ route('documents.index') }}" class="filter-bar">

  <div class="search-wrap" style="flex:1;min-width:200px">
    <span class="search-ico">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
    </span>
    <input type="text" name="search" class="search-input" placeholder="Titre, mots-clés…" value="{{ request('search') }}" />
  </div>

  <select name="type" class="form-control" style="width:auto" onchange="this.form.submit()">
    <option value="">Tous les types</option>
    @foreach ($typesDocs as $key => $label)
      <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
  </select>

  <select name="service" class="form-control" style="width:auto" onchange="this.form.submit()">
    <option value="">Tous les services</option>
    @foreach ($services as $svc)
      <option value="{{ $svc }}" {{ request('service') === $svc ? 'selected' : '' }}>{{ $svc }}</option>
    @endforeach
  </select>

  <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>

  @if(request()->hasAny(['search','type','service']))
    <a href="{{ route('documents.index') }}" class="btn btn-secondary btn-sm">Effacer</a>
  @endif

  <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm" style="margin-left:auto">
    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px">
      <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
    </svg>
    Importer un document
  </a>

</form>

{{-- Tableau --}}
<div class="section-card" style="animation-delay:0.18s">
  <div class="section-card-head">
    <span class="section-card-title">Liste des documents</span>
    @if($documents->total() > 0)
      <span class="badge badge-blue">{{ $documents->firstItem() }}–{{ $documents->lastItem() }} / {{ $documents->total() }}</span>
    @endif
  </div>

  @if($documents->isEmpty())
    <div class="empty-state" style="padding:3rem">
      <div class="empty-state-icon">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
      </div>
      <p>
        @if(request()->hasAny(['search','type','service']))
          Aucun document ne correspond aux filtres.
          <a href="{{ route('documents.index') }}">Effacer les filtres</a>
        @else
          Aucun document importé pour le moment.
        @endif
      </p>
      <a href="{{ route('documents.create') }}" class="btn btn-primary" style="margin-top:.75rem">Importer un document</a>
    </div>
  @else

    <table>
      <thead>
        <tr>
          <th>Document</th>
          <th>Patient</th>
          <th>Type</th>
          <th>Service</th>
          <th>Importé par</th>
          <th>Date</th>
          <th style="text-align:center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($documents as $doc)
        <tr class="doc-table-row" onclick="window.location='{{ route('documents.show', $doc->id_docum) }}'"
            style="cursor:pointer;transition:background 0.14s ease">
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:36px;height:36px;border-radius:var(--radius);background:var(--primary-bg);
                          display:flex;align-items:center;justify-content:center;flex-shrink:0;
                          transition:transform 0.2s,background 0.2s">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:17px;height:17px;color:var(--primary)">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <div style="font-weight:600;font-size:13px;color:var(--text-primary)">{{ $doc->titre }}</div>
              </div>
            </div>
          </td>
          <td>
            @if($doc->patient)
              <div style="font-weight:600;font-size:12.5px">{{ $doc->patient->nom_complet }}</div>
              <div style="font-size:11px;color:var(--text-muted)">IPP {{ $doc->patient->ipp }} · {{ $doc->patient->numero_dossier }}</div>
            @else
              <span style="color:var(--text-muted)">—</span>
            @endif
          </td>
          <td><span class="badge badge-blue">{{ $doc->type_label }}</span></td>
          <td style="font-size:12.5px;color:var(--text-secondary)">{{ $doc->service }}</td>
          <td style="font-size:12.5px;color:var(--text-secondary)">
            {{ $doc->utilisateur?->prenom }} {{ $doc->utilisateur?->nom }}
          </td>
          <td style="white-space:nowrap;font-size:12.5px;color:var(--text-secondary)">
            {{ $doc->date_import->format('d/m/Y') }}
          </td>
          <td onclick="event.stopPropagation()" style="white-space:nowrap">
            <div style="display:flex;gap:6px;align-items:center;justify-content:center">
              <a href="{{ route('documents.show', $doc->id_docum) }}"
                 onclick="event.stopPropagation()"
                 style="font-size:12px;color:var(--primary);font-weight:600;text-decoration:none;padding:3px 8px;border-radius:4px;border:1px solid var(--primary-border);background:var(--primary-bg);transition:background .15s"
                 onmouseover="this.style.background='var(--mauve-200)'" onmouseout="this.style.background='var(--primary-bg)'">
                Voir
              </a>
              <a href="{{ route('documents.download', $doc->id_docum) }}"
                 onclick="event.stopPropagation()"
                 style="font-size:12px;color:#059669;font-weight:600;text-decoration:none;padding:3px 8px;border-radius:4px;border:1px solid #bbf7d0;background:#f0fdf4;transition:background .15s"
                 onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                Télécharger
              </a>
              @if($me->isAdministratif())
                <a href="{{ route('documents.confirm-delete', $doc->id_docum) }}"
                   onclick="event.stopPropagation()"
                   style="font-size:12px;color:#DC2626;font-weight:600;text-decoration:none;padding:3px 8px;border-radius:4px;border:1px solid #fca5a5;background:#fef2f2;transition:background .15s"
                   onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                  Supprimer
                </a>
              @endif
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Pagination --}}
    @if($documents->hasPages())
      <div style="padding:.75rem 1.2rem;border-top:1px solid rgba(221,214,254,0.3);display:flex;align-items:center;justify-content:space-between;font-size:12px;color:var(--text-secondary)">
        <span>{{ $documents->firstItem() }}–{{ $documents->lastItem() }} sur <strong>{{ $documents->total() }}</strong></span>
        <div style="display:flex;gap:4px">
          @if(!$documents->onFirstPage())
            <a href="{{ $documents->previousPageUrl() }}" class="btn btn-secondary btn-sm">← Préc.</a>
          @endif
          @foreach($documents->getUrlRange(max(1,$documents->currentPage()-2), min($documents->lastPage(),$documents->currentPage()+2)) as $page => $url)
            @if($page === $documents->currentPage())
              <span class="btn btn-primary btn-sm">{{ $page }}</span>
            @else
              <a href="{{ $url }}" class="btn btn-secondary btn-sm">{{ $page }}</a>
            @endif
          @endforeach
          @if($documents->hasMorePages())
            <a href="{{ $documents->nextPageUrl() }}" class="btn btn-secondary btn-sm">Suiv. →</a>
          @endif
        </div>
      </div>
    @endif

  @endif
</div>

@endsection
