{{-- historique/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Journal d\'activités')
@section('page-title', 'Journal d\'activités')
@section('page-subtitle', 'Traçabilité complète de toutes les actions effectuées dans le système')

@section('content')

@php
  /** @var \App\Models\Utilisateur $moi */
  $avPalette  = ['av-purple','av-teal','av-orange','av-blue','av-pink','av-green'];
  $avClass    = fn($str) => $avPalette[abs(crc32((string)($str ?? '?'))) % count($avPalette)];

  $actionBadge = [
    'CONNEXION'    => ['class'=>'badge-green',  'label'=>'Connexion'],
    'DECONNEXION'  => ['class'=>'badge-gray',   'label'=>'Déconnexion'],
    'CONSULTATION' => ['class'=>'badge-blue',   'label'=>'Consultation'],
    'UPLOAD'       => ['class'=>'badge-blue',   'label'=>'Import doc.'],
    'RECHERCHE'    => ['class'=>'badge-outline', 'label'=>'Recherche'],
    'CREATION'     => ['class'=>'badge-yellow', 'label'=>'Création'],
    'MODIFICATION' => ['class'=>'badge-red',    'label'=>'Modification'],
    'SUPPRESSION'  => ['class'=>'badge-red',    'label'=>'Suppression'],
  ];
@endphp

{{-- Cartes statistiques --}}
<div class="stats-grid">

  <div class="stat-card" style="animation-delay:0s">
    <div class="stat-card-badge" style="background:#EDE9FE">
      <svg viewBox="0 0 20 20" fill="currentColor" style="color:var(--primary)">
        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div class="stat-card-val">{{ number_format($stats['total']) }}</div>
    <div class="stat-card-lbl">Total actions</div>
    <div class="stat-card-sub" style="margin-top:6px">Depuis le début</div>
  </div>

  <div class="stat-card" style="animation-delay:0.06s">
    <div class="stat-card-badge" style="background:#F0FDF4">
      <svg viewBox="0 0 20 20" fill="currentColor" style="color:#059669">
        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div class="stat-card-val" style="color:#059669">{{ number_format($stats['aujourd_hui']) }}</div>
    <div class="stat-card-lbl">Aujourd'hui</div>
    <div class="stat-card-sub" style="margin-top:6px">{{ now()->format('d M Y') }}</div>
  </div>

  <div class="stat-card" style="animation-delay:0.12s">
    <div class="stat-card-badge" style="background:#EFF6FF">
      <svg viewBox="0 0 20 20" fill="currentColor" style="color:#2563EB">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div class="stat-card-val" style="color:#2563EB">{{ number_format($stats['cette_semaine']) }}</div>
    <div class="stat-card-lbl">Cette semaine</div>
    <div class="stat-card-sub" style="margin-top:6px">Sem. {{ now()->weekOfYear }}</div>
  </div>

  {{-- Répartition par action --}}
  <div class="stat-card" style="animation-delay:0.18s">
    <div style="font-size:12px;font-weight:700;color:var(--text-primary);margin-bottom:.8rem">Répartition par action</div>
    @forelse($stats['par_action']->take(4) as $action => $count)
      @php $ab = $actionBadge[$action] ?? ['class'=>'badge-gray','label'=>$action]; @endphp
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
        <span class="badge {{ $ab['class'] }}" style="font-size:10px;padding:2px 8px">{{ $ab['label'] }}</span>
        <div style="flex:1;height:4px;background:var(--border);border-radius:2px;overflow:hidden">
          <div style="height:100%;width:{{ $stats['total'] > 0 ? round(($count/$stats['total'])*100) : 0 }}%;background:var(--primary);border-radius:2px"></div>
        </div>
        <span style="font-size:11px;font-weight:700;color:var(--text-primary)">{{ $count }}</span>
      </div>
    @empty
      <div style="font-size:12px;color:var(--text-muted)">Aucune donnée</div>
    @endforelse
  </div>

</div>

{{-- Filtres + Top utilisateurs --}}
<div style="display:grid;grid-template-columns:1fr {{ $moi->isAdministratif() ? '280px' : '' }};gap:1.2rem;margin-bottom:1.2rem;align-items:start">

  {{-- Panneau de filtres --}}
  <div class="section-card" style="animation-delay:0.22s">
    <div class="section-card-head">
      <span class="section-card-title">Filtres</span>
      @if($moi->isAdministratif())
        <a href="{{ route('historique.export', request()->all()) }}" class="btn-pill">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
          Export CSV
        </a>
      @endif
    </div>
    <div style="padding:1.1rem 1.2rem">
      <form method="GET" action="{{ route('historique.index') }}" id="filterForm">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">

          <div style="min-width:175px;flex:1">
            <label class="form-label">Action</label>
            <select name="action" class="form-control" onchange="this.form.submit()">
              <option value="">Toutes les actions</option>
              @foreach($actionsLabels as $key => $label)
                <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          @if($moi->isAdministratif())
          <div style="min-width:190px;flex:1">
            <label class="form-label">Utilisateur</label>
            <select name="utilisateur" class="form-control" onchange="this.form.submit()">
              <option value="">Tous les utilisateurs</option>
              @foreach($utilisateurs as $u)
                <option value="{{ $u->id_utilisateur }}" {{ request('utilisateur') == $u->id_utilisateur ? 'selected' : '' }}>
                  {{ $u->prenom }} {{ $u->nom }}
                </option>
              @endforeach
            </select>
          </div>
          @endif

          <div style="min-width:150px">
            <label class="form-label">Du</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control" onchange="this.form.submit()" />
          </div>

          <div style="min-width:150px">
            <label class="form-label">Adresse IP</label>
            <input type="text" name="ip" value="{{ request('ip') }}" placeholder="Ex: 192.168.1" class="form-control" />
          </div>

          <div style="display:flex;gap:6px;align-items:flex-end;padding-top:1.3rem">
            <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
            @if(request()->hasAny(['action','utilisateur','date_debut','ip']))
              <a href="{{ route('historique.index') }}" class="btn btn-secondary btn-sm">Effacer</a>
            @endif
          </div>

        </div>

        @if(request()->hasAny(['action','utilisateur','date_debut','ip']))
          <div style="margin-top:.8rem;font-size:12px;color:var(--text-secondary)">
            <strong>{{ $activites->total() }}</strong> entrée(s) correspondante(s)
          </div>
        @endif
      </form>
    </div>
  </div>

  {{-- Top utilisateurs (admin) --}}
  @if($moi->isAdministratif() && $stats['top_utilisateurs']->isNotEmpty())
  <div class="section-card" style="animation-delay:0.26s">
    <div class="section-card-head">
      <span class="section-card-title">Utilisateurs les plus actifs</span>
    </div>
    <div style="padding:.8rem 1.2rem">
      @foreach($stats['top_utilisateurs'] as $i => $item)
        @php
          $u = $item->utilisateur;
          $init = $u?->initiales ?? '?';
          $maxTotal = $stats['top_utilisateurs']->first()->total;
          $pct = $maxTotal > 0 ? round(($item->total / $maxTotal) * 100) : 0;
        @endphp
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:.75rem">
          <div style="width:20px;text-align:center;font-size:11px;font-weight:700;color:{{ $i === 0 ? 'var(--warning)' : 'var(--text-muted)' }}">
            {{ $i + 1 }}
          </div>
          <div class="avatar {{ $avClass($init) }}" style="width:30px;height:30px;font-size:10px;flex-shrink:0">{{ $init }}</div>
          <div style="flex:1;min-width:0">
            <div style="display:flex;justify-content:space-between;margin-bottom:3px">
              <span style="font-size:12px;font-weight:600;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $u?->prenom ?? '?' }} {{ $u?->nom ?? '' }}
              </span>
              <span style="font-size:11px;font-weight:700;color:var(--primary);flex-shrink:0;margin-left:6px">{{ $item->total }}</span>
            </div>
            <div style="height:4px;background:var(--border);border-radius:2px;overflow:hidden">
              <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,var(--primary),var(--primary-light));border-radius:2px"></div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  @endif

</div>

{{-- Tableau du journal --}}
<div class="section-card" style="animation-delay:0.3s">
  <div class="section-card-head">
    <span class="section-card-title">Journal d'audit</span>
    <span style="font-size:12px;color:var(--text-muted)">
      @if($activites->total() > 0)
        {{ $activites->firstItem() }}–{{ $activites->lastItem() }} sur {{ $activites->total() }} entrées
      @else
        Aucune entrée
      @endif
    </span>
  </div>

  @if($activites->isEmpty())
    <div class="empty-state" style="padding:3.5rem">
      <div class="empty-state-icon">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
      </div>
      <p>
        @if(request()->hasAny(['action','utilisateur','date_debut','ip']))
          Aucune activité correspondant aux filtres.
          <a href="{{ route('historique.index') }}">Effacer les filtres</a>
        @else
          Le journal se remplit automatiquement à chaque connexion, consultation et import.
        @endif
      </p>
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th style="width:60px">ID</th>
          <th style="width:160px">Action</th>
          @if($moi->isAdministratif())
            <th>Utilisateur</th>
          @endif
          <th>Description</th>
          <th style="width:120px">Document</th>
          <th style="width:140px">Date &amp; Heure</th>
          <th style="width:120px">Adresse IP</th>
        </tr>
      </thead>
      <tbody>
        @foreach($activites as $activite)
        @php
          $ab  = $actionBadge[$activite->action] ?? ['class'=>'badge-gray','label'=>$activite->action];
          $u   = $activite->utilisateur;
          $init = $u?->initiales ?? '?';
        @endphp
        <tr>
          <td style="font-size:11px;color:var(--text-muted);font-weight:600">#{{ $activite->id_logactivite }}</td>

          <td>
            <span class="badge {{ $ab['class'] }}" style="font-size:11px">{{ $ab['label'] }}</span>
          </td>

          @if($moi->isAdministratif())
          <td>
            @if($u)
              <div style="display:flex;align-items:center;gap:8px">
                <div class="avatar {{ $avClass($init) }}" style="width:28px;height:28px;font-size:9px;flex-shrink:0">{{ $init }}</div>
                <div>
                  <div style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $u->prenom }} {{ $u->nom }}</div>
                  <div style="font-size:10px;color:var(--text-muted)">{{ $u->role_label }}</div>
                </div>
              </div>
            @else
              <span style="color:var(--text-muted);font-size:12px">Utilisateur supprimé</span>
            @endif
          </td>
          @endif

          <td style="font-size:12px;color:var(--text-primary);max-width:220px">
            {{ $activite->description ?? '—' }}
          </td>

          <td>
            @if($activite->id_document && $activite->document)
              <a href="{{ route('documents.show', $activite->id_document) }}"
                 style="font-size:11.5px;color:var(--primary);font-weight:600;text-decoration:none;display:flex;align-items:center;gap:4px">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px">
                  <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                </svg>
                #{{ $activite->id_document }}
              </a>
            @elseif($activite->id_document)
              <span style="font-size:11px;color:var(--text-muted)">#{{ $activite->id_document }}</span>
            @else
              <span style="color:var(--text-muted);font-size:11px">—</span>
            @endif
          </td>

          <td>
            <div style="font-size:12px;font-weight:600;color:var(--text-primary)">
              {{ $activite->date_action->format('d/m/Y') }}
            </div>
            <div style="font-size:11px;color:var(--text-muted)">
              {{ $activite->date_action->format('H:i:s') }}
            </div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:1px">
              {{ $activite->date_action->diffForHumans() }}
            </div>
          </td>

          <td>
            @if($activite->adresse_ip)
              <div style="display:flex;align-items:center;gap:5px">
                <span style="width:7px;height:7px;border-radius:50%;background:{{ $activite->adresse_ip === '127.0.0.1' ? 'var(--success)' : 'var(--warning)' }};flex-shrink:0"></span>
                <span style="font-size:11px;font-family:monospace;color:var(--text-primary)">{{ $activite->adresse_ip }}</span>
              </div>
              @if($activite->adresse_ip === '127.0.0.1')
                <div style="font-size:9px;color:var(--success);margin-top:1px;font-weight:500">Localhost</div>
              @endif
            @else
              <span style="color:var(--text-muted);font-size:11px">—</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Pagination --}}
    @if($activites->hasPages())
    <div style="padding:.75rem 1.2rem;border-top:1px solid rgba(221,214,254,0.3);display:flex;align-items:center;justify-content:space-between;font-size:12px;color:var(--text-secondary)">
      <span>Page {{ $activites->currentPage() }} / {{ $activites->lastPage() }} · {{ $activites->total() }} entrées</span>
      <div style="display:flex;gap:4px">
        @if($activites->currentPage() > 2)
          <a href="{{ $activites->url(1) }}" class="btn btn-secondary btn-sm">««</a>
        @endif
        @if(!$activites->onFirstPage())
          <a href="{{ $activites->previousPageUrl() }}" class="btn btn-secondary btn-sm">← Préc.</a>
        @endif
        @foreach($activites->getUrlRange(max(1,$activites->currentPage()-2), min($activites->lastPage(),$activites->currentPage()+2)) as $page => $url)
          @if($page === $activites->currentPage())
            <span class="btn btn-primary btn-sm">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="btn btn-secondary btn-sm">{{ $page }}</a>
          @endif
        @endforeach
        @if($activites->hasMorePages())
          <a href="{{ $activites->nextPageUrl() }}" class="btn btn-secondary btn-sm">Suiv. →</a>
        @endif
        @if($activites->currentPage() < $activites->lastPage() - 1)
          <a href="{{ $activites->url($activites->lastPage()) }}" class="btn btn-secondary btn-sm">»»</a>
        @endif
      </div>
    </div>
    @endif
  @endif
</div>


@endsection

@push('scripts')
<script>
document.querySelectorAll('input[type=date]').forEach(el => {
  el.addEventListener('change', () => el.form.submit());
});
</script>
@endpush
