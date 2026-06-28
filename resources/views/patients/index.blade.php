{{-- patients/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Patients')
@section('page-title', 'Dossiers Patients')
@section('page-subtitle', $totalPatients . ' patient(s) enregistré(s)')

@push('styles')
<style>
/* Ligne cliquable */
.patient-row {
  cursor: pointer;
  transition: background 0.14s ease;
}
.patient-row:hover td { background: rgba(245,243,255,0.85) !important; }

/* Badge IPP */
.ipp-badge {
  display: inline-flex; align-items: center;
  font-size: 10.5px; font-weight: 700;
  padding: 2px 8px; border-radius: 4px;
  background: var(--primary-bg); color: var(--primary);
  border: 1px solid var(--primary-border);
  font-family: monospace; letter-spacing: 0.05em;
}

/* Flèche */
.row-arrow { color:var(--text-muted); opacity:.3; transition: opacity .14s, transform .14s; }
.patient-row:hover .row-arrow { opacity:.8; transform:translateX(3px); }

/* Ligne masquée par le filtre live */
.patient-row.hidden-filter { display:none; }

/* Message aucun résultat */
#noResultMsg {
  display:none; padding:2.5rem; text-align:center;
  font-size:13.5px; color:var(--text-muted);
  font-weight: 500;
}

/* Zone de recherche — highlight de la lettre tapée */
.search-input:focus { box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }
</style>
@endpush

@section('content')

{{-- Barre de filtres --}}
<form method="GET" action="{{ route('patients.index') }}" class="filter-bar">

  <div class="search-wrap" style="flex:1;min-width:200px">
    <span class="search-ico">
      <svg viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
      </svg>
    </span>
    <input type="text"
           id="liveSearch"
           name="search"
           class="search-input"
           placeholder="Nom, CIN, numéro de dossier, IPP…"
           value="{{ request('search') }}"
           oninput="liveFilter(this.value)"
           autocomplete="off" />
  </div>

  <select name="service" class="form-control" style="width:auto" onchange="this.form.submit()">
    <option value="">Tous les services</option>
    @foreach ($services as $svc)
      <option value="{{ $svc }}" {{ request('service') === $svc ? 'selected' : '' }}>{{ $svc }}</option>
    @endforeach
  </select>

  <button type="submit" class="btn btn-primary btn-sm">
    <svg viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px">
      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
    </svg>
    Rechercher
  </button>

  @if(request('search') || request('service'))
    <a href="{{ route('patients.index') }}" class="btn btn-secondary btn-sm">Effacer</a>
  @endif

  @if(Auth::user()->isAdministratif() || Auth::user()->isInfirmier())
    <a href="{{ route('patients.create') }}" class="btn btn-primary btn-sm" style="margin-left:auto">
      <svg viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px">
        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
      </svg>
      Nouveau patient
    </a>
  @endif

</form>

@if(request('search') || request('service'))
  <p style="font-size:12px;color:var(--text-secondary);margin-bottom:.75rem">
    <strong>{{ $patients->total() }}</strong> résultat(s)
    @if(request('search')) pour « <strong>{{ request('search') }}</strong> »@endif
    @if(request('service')) — service <strong>{{ request('service') }}</strong>@endif
  </p>
@endif

{{-- Tableau patients --}}
@if($patients->isEmpty())
  <div class="table-wrap">
    <div class="empty-state" style="padding:3.5rem">
      <div class="empty-state-icon">
        <svg viewBox="0 0 20 20" fill="currentColor">
          <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
        </svg>
      </div>
      <p style="font-weight:600;margin-bottom:4px">Aucun patient trouvé</p>
      <p style="font-size:12.5px">Modifiez vos critères de recherche.</p>
      @if(Auth::user()->isAdministratif())
        <a href="{{ route('patients.create') }}" class="btn btn-primary" style="margin-top:.75rem">Ajouter un patient</a>
      @endif
    </div>
  </div>
@else

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>IPP</th>
          <th>Patient</th>
          <th>N° Dossier</th>
          <th>CIN</th>
          <th>Service</th>
          <th>Âge</th>
          <th>Docs</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($patients as $patient)
        <tr class="patient-row"
            onclick="window.location='{{ route('patients.show', $patient->ipp) }}'"
            title="Cliquer pour ouvrir le dossier"
            data-nom="{{ strtolower($patient->nom ?? '') }}"
            data-prenom="{{ strtolower($patient->prenom ?? '') }}"
            data-cin="{{ strtolower($patient->cin ?? '') }}"
            data-dossier="{{ strtolower($patient->numero_dossier ?? '') }}"
            data-ipp="{{ str_pad($patient->ipp, 4, '0', STR_PAD_LEFT) }}">
          <td>
            <span class="ipp-badge">{{ str_pad($patient->ipp, 4, '0', STR_PAD_LEFT) }}</span>
          </td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar av-purple" style="width:34px;height:34px;font-size:11px;flex-shrink:0">
                {{ $patient->initiales }}
              </div>
              <div>
                <div style="font-weight:700;font-size:13px">{{ $patient->nom_complet }}</div>
              </div>
            </div>
          </td>
          <td style="font-family:monospace;font-size:12px;color:var(--text-secondary)">{{ $patient->numero_dossier }}</td>
          <td style="font-size:12.5px;color:var(--text-secondary)">{{ $patient->cin }}</td>
          <td><span class="badge badge-blue">{{ $patient->service }}</span></td>
          <td style="font-size:12.5px;color:var(--text-secondary)">{{ $patient->age }} ans</td>
          <td style="text-align:center">
            <span style="font-weight:700;font-size:15px;color:var(--primary)">
              {{ $patient->documents_count ?? 0 }}
            </span>
          </td>
          <td>
            <svg class="row-arrow" viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Message aucun résultat (filtre live) --}}
    <div id="noResultMsg">
      <div style="display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:2rem">
        <svg viewBox="0 0 20 20" fill="currentColor" style="width:36px;height:36px;color:var(--text-muted);opacity:.4">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
        </svg>
        <span id="noResultText">Aucun patient trouvé</span>
        <span style="font-size:11.5px;color:var(--text-muted)">Appuyez sur Entrée pour une recherche complète</span>
      </div>
    </div>

    {{-- Compteur résultats live --}}
    <div id="liveCount" style="display:none;padding:.5rem 1.1rem;font-size:12px;color:var(--text-muted);border-top:1px solid rgba(221,214,254,0.2);background:var(--bg-app)"></div>

  </div>

  {{-- Pagination --}}
  @if($patients->hasPages())
    <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;color:var(--text-secondary)">
      <span>{{ $patients->firstItem() }}–{{ $patients->lastItem() }} sur <strong>{{ $patients->total() }}</strong></span>
      <div style="display:flex;gap:4px">
        @if($patients->onFirstPage())
          <span class="btn btn-secondary btn-sm" style="opacity:.4">← Préc.</span>
        @else
          <a href="{{ $patients->previousPageUrl() }}" class="btn btn-secondary btn-sm">← Préc.</a>
        @endif
        @foreach($patients->getUrlRange(max(1,$patients->currentPage()-2), min($patients->lastPage(),$patients->currentPage()+2)) as $page => $url)
          @if($page === $patients->currentPage())
            <span class="btn btn-primary btn-sm">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="btn btn-secondary btn-sm">{{ $page }}</a>
          @endif
        @endforeach
        @if($patients->hasMorePages())
          <a href="{{ $patients->nextPageUrl() }}" class="btn btn-secondary btn-sm">Suiv. →</a>
        @else
          <span class="btn btn-secondary btn-sm" style="opacity:.4">Suiv. →</span>
        @endif
      </div>
    </div>
  @endif

@endif

@endsection

@push('scripts')
<script>
/* Filtre patient en temps réel (client-side uniquement) */
function liveFilter(q) {
  q = q.trim().toLowerCase();

  const rows    = document.querySelectorAll('.patient-row');
  const noMsg   = document.getElementById('noResultMsg');
  const counter = document.getElementById('liveCount');
  let   visible = 0;

  rows.forEach(function(row) {
    const nom     = (row.dataset.nom    || '');
    const prenom  = (row.dataset.prenom || '');
    const cin     = (row.dataset.cin    || '');
    const dossier = (row.dataset.dossier|| '');
    const ipp     = (row.dataset.ipp    || '');

    /* Commence PAR la lettre (nom ou prénom) OU inclusion dans CIN/dossier/IPP */
    var match = !q
      || nom.startsWith(q)
      || prenom.startsWith(q)
      || (nom + ' ' + prenom).startsWith(q)
      || cin.includes(q)
      || dossier.includes(q)
      || ipp.startsWith(q);

    if (match) {
      row.classList.remove('hidden-filter');
      visible++;
    } else {
      row.classList.add('hidden-filter');
    }
  });

  /* Message aucun résultat */
  if (q && visible === 0) {
    noMsg.style.display = 'block';
    document.getElementById('noResultText').textContent =
      'Aucun patient trouvé pour « ' + q + ' »';
    counter.style.display = 'none';
  } else {
    noMsg.style.display = 'none';
    if (q && rows.length > 0) {
      counter.style.display = 'block';
      counter.textContent   = visible + ' patient(s) sur ' + rows.length;
    } else {
      counter.style.display = 'none';
    }
  }
}

/* Bouton "Rechercher" → soumet le formulaire pour recherche serveur */
document.addEventListener('DOMContentLoaded', function() {
  var form = document.querySelector('.filter-bar');
  var inp  = document.getElementById('liveSearch');

  /* Appliquer le filtre si valeur déjà présente (retour de recherche) */
  if (inp && inp.value.trim()) liveFilter(inp.value);

  /* Entrée → recherche serveur */
  if (inp) {
    inp.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        form.submit();
      }
    });
  }
});
</script>
@endpush
