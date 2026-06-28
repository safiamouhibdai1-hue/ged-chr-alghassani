@extends('layouts.app')

@section('title', 'Rapports & Statistiques')

@section('page-title', 'Rapports & Statistiques')
@section('page-subtitle', 'Analyse du GED Médicale CHR Al Ghassani')

@push('styles')
<style>
.periode-bar { display:flex;align-items:center;gap:.5rem;margin-bottom:1.4rem;flex-wrap:wrap; }
.periode-label { font-size:13px;font-weight:600;color:var(--text-primary);margin-right:.2rem; }
.periode-btn {
  padding:5px 14px;border-radius:var(--radius-full);font-size:12px;font-weight:600;
  border:1.5px solid var(--border);background:#fff;color:var(--text-muted);
  cursor:pointer;text-decoration:none;transition:var(--t);
}
.periode-btn:hover { border-color:var(--primary);color:var(--primary); }
.periode-btn.active { background:var(--primary);border-color:var(--primary);color:#fff; }

.summary-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.4rem; }
.sum-card {
  background:var(--bg-card);border-radius:var(--radius-md);padding:1.2rem 1.3rem;
  box-shadow:var(--shadow-sm);border:1px solid rgba(221,214,254,0.45);
  position:relative;overflow:hidden;animation:fadeInUp .4s ease both;
}
.sum-card::before { content:'';position:absolute;top:0;left:0;width:4px;height:100%;border-radius:var(--radius-md) 0 0 var(--radius-md); }
.sum-card.teal::before   { background:var(--success); }
.sum-card.purple::before { background:var(--primary); }
.sum-card.pink::before   { background:var(--danger); }
.sum-card.amber::before  { background:var(--warning); }
.sum-label { font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px; }
.sum-value { font-size:2rem;font-weight:800;color:var(--text-primary);line-height:1; }
.sum-sub   { font-size:11px;color:var(--text-muted);margin-top:5px; }
.sum-trend { display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:600;margin-top:4px;padding:2px 8px;border-radius:var(--radius-full); }
.trend-up   { background:var(--success-bg);color:var(--success); }
.trend-down { background:var(--danger-bg);color:var(--danger); }
.trend-neu  { background:var(--bg-light);color:var(--text-muted); }

.charts-row-2 { display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.2rem; }
.charts-row-1 { margin-bottom:1.2rem; }
.chart-card {
  background:var(--bg-card);border-radius:var(--radius-md);padding:1.3rem;
  box-shadow:var(--shadow-sm);border:1px solid rgba(221,214,254,0.45);
  animation:fadeInUp .4s ease both;
}
.chart-card-title {
  font-size:14px;font-weight:700;color:var(--text-primary);
  margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;
}
.chart-badge { font-size:10px;font-weight:600;color:var(--text-muted);background:var(--primary-bg);padding:3px 9px;border-radius:var(--radius-full); }
.chart-wrap { position:relative;height:240px; }
.chart-wrap.tall { height:280px; }

.tables-row { display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.2rem; }
.rap-table-card {
  background:var(--bg-card);border-radius:var(--radius-md);
  box-shadow:var(--shadow-sm);border:1px solid rgba(221,214,254,0.45);
  overflow:hidden;animation:fadeInUp .4s ease both;
}
.rap-table-head {
  padding:.9rem 1.2rem;font-size:14px;font-weight:700;color:var(--text-primary);
  border-bottom:1px solid rgba(221,214,254,0.3);display:flex;align-items:center;gap:8px;
}

.rank { display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;font-size:11px;font-weight:700; }
.rank-1 { background:#FFF3CD;color:#856404; }
.rank-2 { background:#E9ECEF;color:#6C757D; }
.rank-3 { background:#FFE5D0;color:#854B00; }
.rank-n { background:var(--primary-bg);color:var(--primary); }

.mini-bar-wrap { display:flex;align-items:center;gap:8px; }
.mini-bar { flex:1;height:5px;background:var(--border);border-radius:3px;overflow:hidden; }
.mini-bar-fill { height:100%;border-radius:3px; }

.u-avatar { width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0; }

.badge-type { display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;padding:3px 8px;border-radius:var(--radius-full); }

@media (max-width:1100px) {
  .summary-grid { grid-template-columns:repeat(2,1fr); }
  .charts-row-2 { grid-template-columns:1fr; }
  .tables-row   { grid-template-columns:1fr; }
}
@media (max-width:700px) {
  .summary-grid { grid-template-columns:1fr 1fr; }
}
</style>
@endpush

@section('content')

@php
  /** @var \App\Models\Utilisateur $moi */

  // Couleurs par type
  $typeCouleurs = [
    'rapport_consultation'    => ['bg' => '#E8F8F5', 'tx' => '#0e7a65', 'ico' => '📋'],
    'compte_rendu_operatoire' => ['bg' => '#EEF2FF', 'tx' => '#4338ca', 'ico' => '🔬'],
    'resultat_laboratoire'    => ['bg' => '#FEF3C7', 'tx' => '#92400e', 'ico' => '🧪'],
    'resultat_radiologie'     => ['bg' => '#DBEAFE', 'tx' => '#1d4ed8', 'ico' => '🩻'],
    'ordonnance'              => ['bg' => '#D1FAE5', 'tx' => '#065f46', 'ico' => '💊'],
    'courrier_medical'        => ['bg' => '#FDE8EE', 'tx' => '#9b2548', 'ico' => '📧'],
    'autre'                   => ['bg' => '#F0F2F8', 'tx' => '#4a5568', 'ico' => '📄'],
  ];

  // Couleur avatar
  $avatarColor = fn(string $role): string => match($role) {
    'medecin'       => '#2AB09A',
    'infirmier'     => '#534AB7',
    'administratif' => '#E8547A',
    default         => '#8892a4',
  };
@endphp

{{-- SÉLECTEUR DE PÉRIODE + BOUTON EXPORT --}}
<div class="periode-bar">
  <span class="periode-label">Période :</span>
  @foreach(['3' => '3 mois', '6' => '6 mois', '12' => '12 mois'] as $val => $lbl)
    <a href="{{ route('rapports.index', ['periode' => $val]) }}"
       class="periode-btn {{ $periode == $val ? 'active' : '' }}">{{ $lbl }}</a>
  @endforeach
  <a href="{{ route('rapports.export') }}" class="btn-pill" style="margin-left:auto">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    Exporter CSV
  </a>
</div>

{{-- CARTES SYNTHÈSE --}}
<div class="summary-grid">

  <div class="sum-card teal">
    <div class="sum-label">Patients enregistrés</div>
    <div class="sum-value">{{ number_format($cartes['patients_total']) }}</div>
    <div class="sum-sub">Total dans le système</div>
  </div>

  <div class="sum-card purple">
    <div class="sum-label">Documents actifs</div>
    <div class="sum-value">{{ number_format($cartes['documents_total']) }}</div>
    <div class="sum-sub">Tous types confondus</div>
  </div>

  <div class="sum-card pink">
    <div class="sum-label">Uploads ce mois</div>
    <div class="sum-value">{{ $cartes['uploads_ce_mois'] }}</div>
    @if($cartes['variation_uploads'] !== null)
      <div class="sum-trend {{ $cartes['variation_uploads'] >= 0 ? 'trend-up' : 'trend-down' }}">
        {{ $cartes['variation_uploads'] >= 0 ? '▲' : '▼' }}
        {{ abs($cartes['variation_uploads']) }}% vs mois préc.
      </div>
    @else
      <div class="sum-sub">Aucun document le mois précédent</div>
    @endif
  </div>

  <div class="sum-card amber">
    <div class="sum-label">Utilisateurs actifs</div>
    <div class="sum-value">{{ $cartes['utilisateurs_actifs'] }}</div>
    <div class="sum-sub">{{ $cartes['connexions_semaine'] }} connexions cette semaine</div>
  </div>

</div>

{{-- LIGNE 1 — Types de documents + Services --}}
<div class="charts-row-2">

  <div class="chart-card">
    <div class="chart-card-title">
      Documents par type
      <span class="chart-badge">{{ $cartes['documents_total'] }} total</span>
    </div>
    <div class="chart-wrap"><canvas id="chartTypes"></canvas></div>
  </div>

  <div class="chart-card">
    <div class="chart-card-title">
      Documents par service
      <span class="chart-badge">{{ count($chartServices['labels']) }} services</span>
    </div>
    <div class="chart-wrap"><canvas id="chartServices"></canvas></div>
  </div>

</div>

{{-- LIGNE 2 — Courbe activité mensuelle (pleine largeur) --}}
<div class="charts-row-1">
  <div class="chart-card">
    <div class="chart-card-title">
      Uploads par mois — {{ $periode }} derniers mois
      <span class="chart-badge">{{ array_sum($chartMois['data']) }} uploads sur la période</span>
    </div>
    <div class="chart-wrap tall"><canvas id="chartMois"></canvas></div>
  </div>
</div>

{{-- LIGNE 3 — Actions journal + Uploads récents (7j) --}}
<div class="charts-row-2" style="margin-bottom:1.25rem">

  <div class="chart-card">
    <div class="chart-card-title">
      Journal d'audit — répartition
      <span class="chart-badge">{{ array_sum($chartActions['data']) }} actions</span>
    </div>
    <div class="chart-wrap"><canvas id="chartActions"></canvas></div>
  </div>

  <div class="rap-table-card">
    <div class="rap-table-head">Uploads des 7 derniers jours</div>
    @if($uploadsRecents->isEmpty())
      <div style="padding:2rem;text-align:center;color:#8892a4;font-size:13px">
        Aucun document importé cette semaine.
      </div>
    @else
      <table>
        <thead>
          <tr>
            <th>Document</th>
            <th>Patient</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @foreach($uploadsRecents as $doc)
            <tr>
              <td>
                @php $tc = $typeCouleurs[$doc->typedocument] ?? $typeCouleurs['autre']; @endphp
                <span class="badge-type" style="background:{{ $tc['bg'] }};color:{{ $tc['tx'] }}">
                  {{ $tc['ico'] }} {{ $doc->titre }}
                </span>
              </td>
              <td style="color:#8892a4;font-size:11px">
                @if($doc->patient)
                  <a href="{{ route('patients.show', $doc->patient->ipp) }}"
                     style="color:#534AB7;text-decoration:none;font-weight:600">
                    {{ $doc->patient->nom }} {{ $doc->patient->prenom }}
                  </a>
                @else
                  —
                @endif
              </td>
              <td style="color:#8892a4;font-size:11px;white-space:nowrap">
                {{ $doc->date_import->format('d/m/Y') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

</div>

{{-- LIGNE 4 — Top patients + Classement utilisateurs --}}
<div class="tables-row">

  <div class="rap-table-card">
    <div class="rap-table-head">Top patients — nombre de documents</div>
    @if($topPatients->isEmpty())
      <div style="padding:2rem;text-align:center;color:#8892a4;font-size:13px">
        Aucun patient avec des documents.
      </div>
    @else
      @php $maxDocs = $topPatients->first()->documents_count; @endphp
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Patient</th>
            <th>Service</th>
            <th style="text-align:right">Docs</th>
          </tr>
        </thead>
        <tbody>
          @foreach($topPatients as $i => $p)
            <tr>
              <td>
                <span class="rank rank-n">{{ $i + 1 }}</span>
              </td>
              <td>
                <a href="{{ route('patients.show', $p->ipp) }}"
                   style="color:#1E2A4A;text-decoration:none;font-weight:600">
                  {{ strtoupper($p->nom) }} {{ $p->prenom }}
                </a>
                <div style="font-size:10px;color:#8892a4">IPP : {{ $p->ipp }}</div>
              </td>
              <td>
                <span style="font-size:11px;color:#534AB7;font-weight:600">
                  {{ ucfirst($p->service ?? '—') }}
                </span>
              </td>
              <td style="text-align:right">
                <div class="mini-bar-wrap" style="justify-content:flex-end">
                  <div class="mini-bar" style="max-width:80px">
                    <div class="mini-bar-fill"
                         style="width:{{ $maxDocs > 0 ? round($p->documents_count / $maxDocs * 100) : 0 }}%;background:#534AB7"></div>
                  </div>
                  <strong style="font-size:13px;min-width:24px;text-align:right">
                    {{ $p->documents_count }}
                  </strong>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <div class="rap-table-card">
    <div class="rap-table-head">Activité du personnel</div>
    @if($classementUtilisateurs->isEmpty())
      <div style="padding:2rem;text-align:center;color:#8892a4;font-size:13px">
        Aucune activité enregistrée.
      </div>
    @else
      @php $maxAct = $classementUtilisateurs->first()->log_activites_count; @endphp
      <table>
        <thead>
          <tr>
            <th>Utilisateur</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($classementUtilisateurs as $u)
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <div class="u-avatar" style="background:{{ $avatarColor($u->role) }}">
                    {{ $u->initiales }}
                  </div>
                  <div>
                    <div style="font-weight:600;font-size:12px">
                      {{ $u->prenom }} {{ $u->nom }}
                    </div>
                    <div style="font-size:10px;color:#8892a4">{{ $u->email }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span style="font-size:11px;color:#8892a4">{{ $u->role_label }}</span>
              </td>
              <td>
                @if($u->actif)
                  <span style="font-size:10px;font-weight:600;color:#0e7a65">● Actif</span>
                @else
                  <span style="font-size:10px;font-weight:600;color:#8892a4">○ Inactif</span>
                @endif
              </td>
              <td style="text-align:right">
                <div class="mini-bar-wrap" style="justify-content:flex-end">
                  <div class="mini-bar" style="max-width:80px">
                    <div class="mini-bar-fill"
                         style="width:{{ $maxAct > 0 ? round($u->log_activites_count / $maxAct * 100) : 0 }}%;background:#2AB09A"></div>
                  </div>
                  <strong style="font-size:13px;min-width:28px;text-align:right">
                    {{ $u->log_activites_count }}
                  </strong>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

</div>


@endsection

{{-- GRAPHIQUES — Chart.js --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Valeurs PHP → JS
const chartTypesData    = @json($chartTypes);
const chartServicesData = @json($chartServices);
const chartMoisData     = @json($chartMois);
const chartActionsData  = @json($chartActions);

// Options communes
const fontFamily = "'Inter', -apple-system, sans-serif";
Chart.defaults.font.family = fontFamily;
Chart.defaults.color       = '#8892a4';

// DONUT — Documents par type
new Chart(document.getElementById('chartTypes'), {
  type: 'doughnut',
  data: {
    labels:   chartTypesData.labels,
    datasets: [{
      data:            chartTypesData.data,
      backgroundColor: chartTypesData.couleurs,
      borderWidth:     2,
      borderColor:     '#fff',
      hoverOffset:     8,
    }],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '62%',
    plugins: {
      legend: {
        position: 'right',
        labels: {
          boxWidth: 12, boxHeight: 12,
          padding:  14,
          font: { size: 11 },
        },
      },
      tooltip: {
        callbacks: {
          label: (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
            const pct   = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
            return ` ${ctx.label} : ${ctx.parsed} (${pct}%)`;
          },
        },
      },
    },
  },
});

// BARRES VERTICALES — Documents par service
new Chart(document.getElementById('chartServices'), {
  type: 'bar',
  data: {
    labels:   chartServicesData.labels,
    datasets: [{
      label:           'Documents',
      data:            chartServicesData.data,
      backgroundColor: chartServicesData.couleurs,
      borderRadius:    6,
      borderSkipped:   false,
    }],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => ` ${ctx.parsed.y} document${ctx.parsed.y > 1 ? 's' : ''}`,
        },
      },
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { font: { size: 11 } },
      },
      y: {
        grid: { color: '#f0f2f8' },
        ticks: {
          stepSize: 1,
          font: { size: 11 },
          callback: (v) => Number.isInteger(v) ? v : '',
        },
        beginAtZero: true,
      },
    },
  },
});

// COURBE — Uploads par mois
new Chart(document.getElementById('chartMois'), {
  type: 'line',
  data: {
    labels:   chartMoisData.labels,
    datasets: [{
      label:           'Uploads',
      data:            chartMoisData.data,
      borderColor:     '#534AB7',
      backgroundColor: 'rgba(83,74,183,0.10)',
      borderWidth:     2.5,
      pointRadius:     5,
      pointBackgroundColor: '#534AB7',
      pointBorderColor:     '#fff',
      pointBorderWidth:     2,
      fill:            true,
      tension:         0.4,
    }],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => ` ${ctx.parsed.y} upload${ctx.parsed.y > 1 ? 's' : ''}`,
        },
      },
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { font: { size: 11 } },
      },
      y: {
        grid: { color: '#f0f2f8' },
        ticks: {
          stepSize: 1,
          font: { size: 11 },
          callback: (v) => Number.isInteger(v) ? v : '',
        },
        beginAtZero: true,
      },
    },
  },
});

// BARRES HORIZONTALES — Journal d'audit
new Chart(document.getElementById('chartActions'), {
  type: 'bar',
  data: {
    labels:   chartActionsData.labels,
    datasets: [{
      label:           'Occurrences',
      data:            chartActionsData.data,
      backgroundColor: chartActionsData.couleurs,
      borderRadius:    5,
      borderSkipped:   false,
    }],
  },
  options: {
    indexAxis: 'y',   // barres horizontales
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => ` ${ctx.parsed.x} action${ctx.parsed.x > 1 ? 's' : ''}`,
        },
      },
    },
    scales: {
      x: {
        grid: { color: '#f0f2f8' },
        ticks: {
          stepSize: 1,
          font: { size: 11 },
          callback: (v) => Number.isInteger(v) ? v : '',
        },
        beginAtZero: true,
      },
      y: {
        grid: { display: false },
        ticks: { font: { size: 11, weight: '600' } },
      },
    },
  },
});
</script>
@endpush
