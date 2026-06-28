{{-- dashboard/index.blade.php — Interface médecin --}}
@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', now()->locale('fr')->isoFormat('dddd D MMMM YYYY'))

@push('styles')
<style>
/* Stat card cliquable (double-clic) */
.sc-clickable {
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1),
              box-shadow 0.25s ease !important;
}

/* Animation d'entrée décalée */
.sc-clickable:nth-child(1) { animation: cardEntrance 0.5s 0.05s ease both; }
.sc-clickable:nth-child(2) { animation: cardEntrance 0.5s 0.12s ease both; }
.sc-clickable:nth-child(3) { animation: cardEntrance 0.5s 0.19s ease both; }
.sc-clickable:nth-child(4) { animation: cardEntrance 0.5s 0.26s ease both; }

@keyframes cardEntrance {
  from { opacity:0; transform: translateY(20px) scale(0.96); }
  to   { opacity:1; transform: translateY(0)    scale(1);    }
}

/* Hover : lift + glow + scale */
.sc-clickable:hover {
  transform: translateY(-6px) scale(1.02) !important;
  box-shadow: 0 16px 40px rgba(124,58,237,0.18), 0 4px 12px rgba(0,0,0,0.06) !important;
  z-index: 2;
}

/* Clic : press effect */
.sc-clickable:active {
  transform: translateY(-2px) scale(0.99) !important;
  transition-duration: 0.08s !important;
}

/* Ligne colorée du bas */
.sc-bar {
  position: absolute; bottom: 0; left: 0; right: 0;
  height: 3px;
  background: var(--sc-color, var(--primary));
  opacity: 0;
  transition: opacity 0.2s ease;
  border-radius: 0 0 var(--radius-md) var(--radius-md);
}
.sc-clickable:hover .sc-bar { opacity: 1; }

/* Reflet brillant au hover */
.sc-clickable::before {
  content: '';
  position: absolute; top: 0; left: -100%; width: 60%;
  height: 100%;
  background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.4) 50%, transparent 60%);
  transition: left 0.5s ease;
  pointer-events: none;
  z-index: 1;
}
.sc-clickable:hover::before { left: 150%; }

/* Hint "double-clic" */
.sc-dblclick-hint {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-muted);
  opacity: 0;
  transition: opacity 0.2s ease;
  white-space: nowrap;
}
.sc-clickable:hover .sc-dblclick-hint { opacity: 1; }
</style>
@endpush

@section('content')

@php
  /** @var \App\Models\Utilisateur $me */
  $me = Auth::user();
  $avPalette = ['av-purple','av-teal','av-orange','av-blue','av-pink','av-green'];
  $avClass   = fn($str) => $avPalette[abs(crc32((string)($str ?? '?'))) % count($avPalette)];
@endphp

{{-- Bandeau de bienvenue --}}
<div class="welcome-banner">
  <div class="welcome-banner-left">
    <div class="welcome-avatar {{ $avClass($me->initiales) }}">{{ $me->initiales }}</div>
    <div>
      <div class="welcome-title">Bonjour, {{ $me->isMedecin() ? 'Dr.' : '' }} {{ $me->prenom }} {{ $me->nom }}</div>
      <div class="welcome-sub">{{ $me->role_label }} · CHR Al Ghassani, Fès</div>
    </div>
  </div>
  <div class="welcome-banner-right">
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
      Importer un document
    </a>
    <a href="{{ route('patients.index') }}" class="btn btn-secondary">
      <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
      Dossiers patients
    </a>
  </div>
</div>

{{-- Statistiques (double-clic pour naviguer) --}}
<div class="stats-grid">

  <div class="stat-card sc-clickable" style="animation-delay:0s"
       ondblclick="window.location='{{ route('patients.index') }}'"
       title="Double-cliquer pour voir la liste des patients">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem">
      <div class="stat-card-badge" style="margin-bottom:0">
        <svg viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)">
          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
        </svg>
      </div>
      <span class="sc-dblclick-hint">↗ double-clic</span>
    </div>
    <div class="stat-card-val">{{ $stats['total_patients'] }}</div>
    <div class="stat-card-lbl">Patients enregistrés</div>
    <div class="sc-bar" style="--sc-color:var(--primary)"></div>
  </div>

  <div class="stat-card sc-clickable" style="animation-delay:0.08s"
       ondblclick="window.location='{{ route('documents.index') }}'"
       title="Double-cliquer pour parcourir les documents">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem">
      <div class="stat-card-badge" style="background:#EFF6FF;margin-bottom:0">
        <svg viewBox="0 0 20 20" fill="currentColor" style="color:#2563EB">
          <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
        </svg>
      </div>
      <span class="sc-dblclick-hint" style="color:#2563EB">↗ double-clic</span>
    </div>
    <div class="stat-card-val" style="color:#2563EB">{{ $stats['total_documents'] }}</div>
    <div class="stat-card-lbl">Documents dans le GED</div>
    <div class="sc-bar" style="--sc-color:#2563EB"></div>
  </div>

  <div class="stat-card sc-clickable" style="animation-delay:0.16s"
       ondblclick="window.location='{{ route('documents.create') }}'"
       title="Double-cliquer pour importer un document">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem">
      <div class="stat-card-badge" style="background:#F0FDF4;margin-bottom:0">
        <svg viewBox="0 0 20 20" fill="currentColor" style="color:#059669">
          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
        </svg>
      </div>
      <span class="sc-dblclick-hint" style="color:#059669">↗ double-clic</span>
    </div>
    <div class="stat-card-val" style="color:#059669">{{ $stats['uploads_ce_mois'] }}</div>
    <div class="stat-card-lbl">Imports ce mois — {{ now()->locale('fr')->isoFormat('MMMM YYYY') }}</div>
    <div class="sc-bar" style="--sc-color:#059669"></div>
  </div>

  <div class="stat-card sc-clickable" style="animation-delay:0.24s"
       ondblclick="window.location='{{ route('historique.index') }}'"
       title="Double-cliquer pour voir l'historique">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem">
      <div class="stat-card-badge" style="background:#FFF7ED;margin-bottom:0">
        <svg viewBox="0 0 20 20" fill="currentColor" style="color:#EA580C">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
        </svg>
      </div>
      <span class="sc-dblclick-hint" style="color:#EA580C">↗ double-clic</span>
    </div>
    <div class="stat-card-val" style="color:#EA580C">{{ $stats['mes_actions_aujourd_hui'] }}</div>
    <div class="stat-card-lbl">Mes actions aujourd'hui</div>
    <div class="sc-bar" style="--sc-color:#EA580C"></div>
  </div>

</div>

{{-- Actions rapides (adaptées selon le rôle) --}}
<div class="section-card" style="margin-bottom:1.2rem;animation-delay:0.25s">
  <div class="section-card-head">
    <span class="section-card-title">Actions rapides</span>
    <span class="badge badge-blue" style="font-size:10px">{{ $me->role_label }}</span>
  </div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border-mauve)">

    @if($me->isInfirmier())
      {{-- Actions spécifiques infirmier --}}
      <a href="{{ route('patients.create') }}" class="quick-action">
        <div class="quick-action-icon" style="background:#EDE9FE;color:#7C3AED">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
          </svg>
        </div>
        <span class="quick-action-label">Créer une fiche patient</span>
        <span class="quick-action-sub">Enregistrer un nouveau patient</span>
      </a>

      <a href="{{ route('documents.create') }}" class="quick-action">
        <div class="quick-action-icon" style="background:#F0FDF4;color:#059669">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
        </div>
        <span class="quick-action-label">Importer un document</span>
        <span class="quick-action-sub">PDF ou image numérisée</span>
      </a>

      <a href="{{ route('patients.index') }}" class="quick-action">
        <div class="quick-action-icon" style="background:#EFF6FF;color:#2563EB">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
          </svg>
        </div>
        <span class="quick-action-label">Consulter un dossier</span>
        <span class="quick-action-sub">Rechercher un patient</span>
      </a>

      <a href="{{ route('historique.index') }}" class="quick-action">
        <div class="quick-action-icon" style="background:#FFF7ED;color:#EA580C">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
          </svg>
        </div>
        <span class="quick-action-label">Historique documents</span>
        <span class="quick-action-sub">Journal d'activités</span>
      </a>

    @else
      {{-- Actions médecin / administratif --}}
      <a href="{{ route('documents.create') }}" class="quick-action">
        <div class="quick-action-icon" style="background:#EDE9FE;color:#7C3AED">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </div>
        <span class="quick-action-label">Importer un document</span>
        <span class="quick-action-sub">Upload PDF ou image</span>
      </a>

      <a href="{{ route('patients.index') }}" class="quick-action">
        <div class="quick-action-icon" style="background:#EFF6FF;color:#2563EB">
          <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
        </div>
        <span class="quick-action-label">Consulter un dossier</span>
        <span class="quick-action-sub">Rechercher un patient</span>
      </a>

      <a href="{{ route('documents.index') }}" class="quick-action">
        <div class="quick-action-icon" style="background:#F0FDF4;color:#059669">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
        </div>
        <span class="quick-action-label">Rechercher un document</span>
        <span class="quick-action-sub">Filtres multicritères</span>
      </a>

      <a href="{{ route('historique.index') }}" class="quick-action">
        <div class="quick-action-icon" style="background:#FFF7ED;color:#EA580C">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
        </div>
        <span class="quick-action-label">Mes consultations</span>
        <span class="quick-action-sub">Journal d'activités</span>
      </a>
    @endif

  </div>
</div>

{{-- Documents récents --}}
<div class="section-card" style="animation-delay:0.3s;margin-bottom:1.2rem">
  <div class="section-card-head">
    <span class="section-card-title">Documents récents</span>
    <a href="{{ route('documents.index') }}" class="btn-pill">
      Voir tout
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    </a>
  </div>

  @forelse ($documentsRecents as $doc)
  <a href="{{ route('documents.show', $doc->id_docum) }}" class="doc-list-row doc-list-row--compact">
    <div class="doc-list-icon doc-list-icon--sm">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
    </div>
    <div class="doc-list-body">
      <div class="doc-list-title" style="font-size:12.5px">{{ $doc->titre }}</div>
      <div class="doc-list-sub" style="font-size:11px">
        @if($doc->patient){{ $doc->patient->nom_complet }} &middot; @endif{{ $doc->date_import->format('d/m/Y') }}
      </div>
    </div>
    <span class="badge badge-blue" style="font-size:10px;flex-shrink:0">{{ $doc->type_label }}</span>
  </a>
  @empty
  <div style="padding:1.2rem 1.1rem;text-align:center;color:var(--text-muted);font-size:13px">
    Aucun document. <a href="{{ route('documents.create') }}">Importer</a>
  </div>
  @endforelse
</div>

{{-- Répartition par type de document — Graphiques Chart.js --}}
@if($docParType->isNotEmpty())
@php
  $typeLabels = [
    'rapport_consultation'    => 'Rapport consultation',
    'compte_rendu_operatoire' => 'CR opératoire',
    'resultat_laboratoire'    => 'Résultat labo',
    'resultat_radiologie'     => 'Résultat radio',
    'ordonnance'              => 'Ordonnance',
    'courrier_medical'        => 'Courrier médical',
    'autre'                   => 'Autre',
  ];
  $typeColors = [
    'rapport_consultation'    => '#7C3AED',
    'compte_rendu_operatoire' => '#2563EB',
    'resultat_laboratoire'    => '#D97706',
    'resultat_radiologie'     => '#0891B2',
    'ordonnance'              => '#059669',
    'courrier_medical'        => '#DC2626',
    'autre'                   => '#6B7280',
  ];
  $total = $docParType->sum();
  $chartLabels = [];
  $chartData   = [];
  $chartColors = [];
  foreach ($docParType as $type => $count) {
    $chartLabels[] = $typeLabels[$type] ?? $type;
    $chartData[]   = $count;
    $chartColors[] = $typeColors[$type] ?? '#6B7280';
  }
@endphp

<div class="section-card" style="animation-delay:0.4s">
  <div class="section-card-head">
    <span class="section-card-title">Répartition des documents par type</span>
    <a href="{{ route('documents.index') }}" class="btn-pill">Parcourir</a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;padding:1.2rem 1.4rem;align-items:center">

    {{-- Doughnut chart --}}
    <div style="display:flex;flex-direction:column;align-items:center;gap:10px">
      <div style="position:relative;width:220px;height:220px">
        <canvas id="chartDonut"></canvas>
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none">
          <div style="font-size:28px;font-weight:900;color:var(--text-primary)">{{ $total }}</div>
          <div style="font-size:11px;color:var(--text-muted);font-weight:600">documents</div>
        </div>
      </div>
    </div>

    {{-- Bar chart --}}
    <div style="height:220px">
      <canvas id="chartBar"></canvas>
    </div>

  </div>

  {{-- Légende cliquable --}}
  <div style="display:flex;flex-wrap:wrap;gap:8px;padding:.8rem 1.4rem 1.2rem;border-top:1px solid rgba(221,214,254,0.3)">
    @foreach($docParType as $type => $count)
    <a href="{{ route('documents.index', ['type' => $type]) }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;
              background:rgba({{ implode(',', sscanf($typeColors[$type] ?? '#6B7280', '#%02x%02x%02x')) }},0.1);
              border:1.5px solid {{ $typeColors[$type] ?? '#6B7280' }};
              text-decoration:none;transition:opacity .15s"
       onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
      <span style="width:9px;height:9px;border-radius:50%;background:{{ $typeColors[$type] ?? '#6B7280' }};flex-shrink:0"></span>
      <span style="font-size:11.5px;font-weight:600;color:var(--text-primary)">{{ $typeLabels[$type] ?? $type }}</span>
      <span style="font-size:11.5px;font-weight:800;color:{{ $typeColors[$type] ?? '#6B7280' }}">{{ $count }}</span>
    </a>
    @endforeach
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
  const labels = @json($chartLabels);
  const data   = @json($chartData);
  const colors = @json($chartColors);

  // Doughnut
  new Chart(document.getElementById('chartDonut'), {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: colors,
        borderColor: '#fff',
        borderWidth: 3,
        hoverOffset: 8,
      }]
    },
    options: {
      cutout: '68%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label} : ${ctx.parsed} doc (${Math.round(ctx.parsed/data.reduce((a,b)=>a+b,0)*100)}%)`
          }
        }
      },
      animation: { animateRotate: true, duration: 800 }
    }
  });

  // Bar horizontal
  new Chart(document.getElementById('chartBar'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Documents',
        data,
        backgroundColor: colors.map(c => c + 'CC'),
        borderColor: colors,
        borderWidth: 2,
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      indexAxis: 'y',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.parsed.x} document(s)`
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(221,214,254,0.3)' },
          ticks: { font: { size: 11 }, color: '#888' }
        },
        y: {
          grid: { display: false },
          ticks: { font: { size: 11 }, color: '#555' }
        }
      },
      animation: { duration: 800 }
    }
  });
})();
</script>
@endpush
@endif

@endsection

@push('styles')
<style>
/* Bandeau de bienvenue */
.welcome-banner {
  background: linear-gradient(135deg, var(--mauve-900) 0%, var(--mauve-700) 100%);
  border-radius: var(--radius-md);
  padding: 1.4rem 1.6rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--shadow-md);
  flex-wrap: wrap;
}
.welcome-banner-left { display: flex; align-items: center; gap: 14px; }
.welcome-banner-right { display: flex; gap: 8px; }
.welcome-avatar {
  width: 52px; height: 52px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; font-weight: 800; color: #fff;
  border: 2px solid rgba(255,255,255,0.25);
  flex-shrink: 0;
}
.welcome-title { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: -0.3px; }
.welcome-sub   { font-size: 12.5px; color: rgba(255,255,255,0.55); margin-top: 2px; }

/* Actions rapides */
.quick-action {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.2rem 1rem;
  background: var(--bg-card);
  text-decoration: none;
  transition: var(--t);
  gap: 8px;
  text-align: center;
}
.quick-action:hover { background: var(--primary-bg); text-decoration: none; }
.quick-action:hover .quick-action-label { color: var(--primary); }
.quick-action-icon {
  width: 46px; height: 46px;
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  transition: var(--t-spring);
}
.quick-action:hover .quick-action-icon { transform: translateY(-3px); }
.quick-action-icon svg { width: 22px; height: 22px; }
.quick-action-label { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.quick-action-sub   { font-size: 11px; color: var(--text-muted); }

/* Liste de documents */
.doc-list-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: .5rem 1rem;
  border-bottom: 1px solid rgba(221,214,254,0.2);
  text-decoration: none;
  transition: var(--t);
}
.doc-list-row:last-child { border-bottom: none; }
.doc-list-row:hover { background: rgba(245,243,255,0.7); text-decoration: none; }
.doc-list-icon {
  width: 30px; height: 30px;
  border-radius: var(--radius);
  background: var(--primary-bg);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  color: var(--primary);
}
.doc-list-icon svg { width: 15px; height: 15px; }
.doc-list-body { flex: 1; min-width: 0; }
.doc-list-title {
  font-size: 12.5px; font-weight: 600; color: var(--text-primary);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.doc-list-sub { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

/* Btn pill */
.btn-pill {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 4px 12px;
  border-radius: var(--radius-full);
  background: var(--primary-bg);
  color: var(--primary);
  font-size: 12px; font-weight: 600;
  text-decoration: none;
  border: 1px solid var(--primary-border);
  transition: var(--t);
}
.btn-pill:hover { background: var(--mauve-200); text-decoration: none; }
.btn-pill svg { width: 13px; height: 13px; }

/* Dash row */
.dash-row {
  display: flex; align-items: center;
  padding: .75rem 1rem;
  border-bottom: 1px solid rgba(221,214,254,0.2);
  gap: 12px;
}
.dash-row:last-child { border-bottom: none; }
.dash-row-body { flex: 1; min-width: 0; }
.dash-row-title { font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dash-row-sub   { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
</style>
@endpush
