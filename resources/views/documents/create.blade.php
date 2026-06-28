{{--
    documents/create.blade.php
    Route : GET /documents/create
--}}
@extends('layouts.app')

@section('title', 'Importer un document')
@section('page-title', 'Importer un document')
@section('page-subtitle', 'Associer un fichier médical à un dossier patient')

@section('content')

@php /** @var \App\Models\Utilisateur $me */ $me = Auth::user(); @endphp

<div style="max-width:600px;margin:0 auto">

  <div class="section-card" style="padding:0">

    <div class="section-card-head">
      <span class="section-card-title">Importer un document</span>
      <a href="{{ route('documents.index') }}" class="btn btn-secondary btn-sm">← Retour</a>
    </div>

    <div style="padding:1rem 1.2rem">
      <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" id="uploadForm">
        @csrf

        {{-- Sélection du patient --}}
        <div style="margin-bottom:.75rem">
          <label class="form-label">Patient <span style="color:var(--danger)">*</span></label>

          <input type="text" id="patientSearch"
            class="form-control"
            placeholder="Rechercher par nom, CIN, numéro dossier…"
            oninput="filterPatients(this.value)"
            style="margin-bottom:.5rem;display:{{ $patientPreselectionne ? 'none' : 'block' }}"
          />

          <input type="hidden" name="ipp" id="selectedIpp"
                 value="{{ old('ipp', $patientPreselectionne?->ipp) }}" required />

          {{-- Patient sélectionné --}}
          <div id="selectedPatientBanner"
               style="display:{{ $patientPreselectionne ? 'flex' : 'none' }};
                      align-items:center;gap:10px;
                      padding:8px 12px;border-radius:var(--radius);
                      background:var(--primary-bg);border:1px solid var(--primary-border);
                      margin-bottom:.5rem">
            <div class="avatar av-purple" id="bannerInitiales" style="width:30px;height:30px;font-size:11px">
              {{ $patientPreselectionne?->initiales ?? '' }}
            </div>
            <div style="flex:1">
              <div style="font-size:13px;font-weight:700" id="bannerNom">
                {{ $patientPreselectionne?->nom_complet ?? '' }}
              </div>
              <div style="font-size:11.5px;color:var(--text-secondary)" id="bannerDossier">
                {{ $patientPreselectionne ? $patientPreselectionne->numero_dossier . ' · ' . $patientPreselectionne->service : '' }}
              </div>
            </div>
            <button type="button" onclick="clearPatient()"
              style="background:none;border:none;cursor:pointer;font-size:16px;color:var(--text-muted)"
              title="Changer">✕</button>
          </div>

          {{-- Liste des patients --}}
          <div id="patientList"
               style="max-height:160px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--radius);display:{{ $patientPreselectionne ? 'none' : 'block' }}">
            @foreach($patients as $p)
            <div class="patient-option"
                 data-ipp="{{ $p->ipp }}"
                 data-nom="{{ strtolower($p->nom . ' ' . $p->prenom) }}"
                 data-cin="{{ strtolower($p->cin ?? '') }}"
                 data-dossier="{{ strtolower($p->numero_dossier) }}"
                 data-initiales="{{ substr($p->prenom,0,1) . substr($p->nom,0,1) }}"
                 data-complet="{{ strtoupper($p->nom) . ' ' . $p->prenom }}"
                 data-detail="{{ $p->numero_dossier }} · {{ $p->service }}"
                 onclick="selectPatient(this)"
                 style="display:flex;align-items:center;gap:10px;padding:7px 12px;cursor:pointer;border-bottom:1px solid var(--border-light)"
                 onmouseover="this.style.background='var(--bg-light)'"
                 onmouseout="this.style.background='transparent'">
              <div class="avatar av-purple" style="width:26px;height:26px;font-size:10px">
                {{ substr($p->prenom,0,1) }}{{ substr($p->nom,0,1) }}
              </div>
              <div>
                <div style="font-size:12.5px;font-weight:600">{{ strtoupper($p->nom) }} {{ $p->prenom }}</div>
                <div style="font-size:11px;color:var(--text-secondary)">{{ $p->numero_dossier }} · {{ $p->service }}</div>
              </div>
            </div>
            @endforeach
          </div>

          @error('ipp')
            <div class="form-error" style="margin-top:5px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Zone de dépôt de fichier --}}
        <div style="margin-bottom:.75rem">
          <label class="form-label">Fichier <span style="color:var(--danger)">*</span></label>

          <div id="dropZone"
            style="border:2px dashed var(--primary-border);border-radius:var(--radius-md);padding:.7rem 1rem;text-align:center;cursor:pointer;background:var(--primary-bg);transition:all .2s"
            onclick="document.getElementById('fileInput').click()"
            ondragover="onDragOver(event)"
            ondragleave="onDragLeave(event)"
            ondrop="onDrop(event)">

            <div id="dropIdle" style="display:flex;align-items:center;gap:10px;justify-content:center">
              <div style="width:32px;height:32px;border-radius:var(--radius);background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:var(--shadow-sm)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:17px;height:17px;color:var(--primary)">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
              </div>
              <div style="text-align:left">
                <div style="font-size:12.5px;font-weight:700;color:var(--text-primary)">Glissez votre fichier ici</div>
                <div style="font-size:11px;color:var(--text-secondary)">ou cliquez pour parcourir · PDF, JPG, PNG — max 10 Mo</div>
              </div>
            </div>

            <div id="dropSelected" style="display:none">
              <div id="filePreviewWrap" style="margin-bottom:6px"></div>
              <div style="font-size:13px;font-weight:700;color:var(--accent)" id="fileName"></div>
              <div style="font-size:12px;color:var(--text-secondary);margin-top:3px" id="fileSize"></div>
              <button type="button" onclick="clearFile(event)"
                style="margin-top:6px;padding:4px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--bg-card);color:var(--text-secondary);font-size:11px;cursor:pointer">
                Changer de fichier
              </button>
            </div>

          </div>

          <input type="file" id="fileInput" name="fichier"
                 accept=".pdf,.jpg,.jpeg,.png"
                 style="display:none"
                 onchange="onFileSelected(this)" />

          @error('fichier')
            <div class="form-error" style="margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        {{-- Titre --}}
        <div style="margin-bottom:.75rem">
          <label class="form-label">Titre <span style="color:var(--danger)">*</span></label>
          <input type="text" name="titre"
            class="form-control {{ $errors->has('titre') ? 'is-error' : '' }}"
            value="{{ old('titre') }}"
            placeholder="Ex: Bilan sanguin complet — Juin 2026"
            required />
          @error('titre')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Type + Service --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem">
          <div>
            <label class="form-label">Type <span style="color:var(--danger)">*</span></label>
            <select name="typedocument"
              class="form-control {{ $errors->has('typedocument') ? 'is-error' : '' }}"
              required>
              <option value="">— Choisir —</option>
              @foreach($typesDocs as $key => $label)
                <option value="{{ $key }}" {{ old('typedocument') === $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('typedocument')<div class="form-error">{{ $message }}</div>@enderror
          </div>

          <div>
            <label class="form-label">Service <span style="color:var(--danger)">*</span></label>
            <select name="service"
              class="form-control {{ $errors->has('service') ? 'is-error' : '' }}"
              required>
              <option value="">— Choisir —</option>
              @foreach($services as $svc)
                <option value="{{ $svc }}" {{ old('service') === $svc ? 'selected' : '' }}>{{ $svc }}</option>
              @endforeach
            </select>
            @error('service')<div class="form-error">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Mots-clés --}}
        <div style="margin-bottom:1rem">
          <label class="form-label">
            Mots-clés
            <span style="font-size:10.5px;color:var(--text-muted);font-weight:400;margin-left:4px">(séparés par des virgules)</span>
          </label>
          <input type="text" name="mots_cles"
            class="form-control"
            value="{{ old('mots_cles') }}"
            placeholder="Ex: bilan, anémie, urgence, routine…" />
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:.75rem;padding-top:.25rem;border-top:1px solid rgba(221,214,254,0.3)">
          <button type="submit" class="btn btn-primary" id="submitBtn">Importer le document</button>
          <a href="{{ route('documents.index') }}" class="btn btn-secondary">Annuler</a>
        </div>

      </form>
    </div>

  </div>

</div>

@endsection

@push('scripts')
<script>
function filterPatients(term) {
  const t = term.toLowerCase().trim();
  document.querySelectorAll('.patient-option').forEach(el => {
    const match = !t || el.dataset.nom.includes(t) || el.dataset.cin.includes(t) || el.dataset.dossier.includes(t);
    el.style.display = match ? 'flex' : 'none';
  });
}

function selectPatient(el) {
  document.getElementById('selectedIpp').value = el.dataset.ipp;
  const banner = document.getElementById('selectedPatientBanner');
  document.getElementById('bannerInitiales').textContent = el.dataset.initiales.toUpperCase();
  document.getElementById('bannerNom').textContent       = el.dataset.complet;
  document.getElementById('bannerDossier').textContent   = el.dataset.detail;
  banner.style.display = 'flex';
  document.getElementById('patientList').style.display   = 'none';
  document.getElementById('patientSearch').style.display = 'none';
}

function clearPatient() {
  document.getElementById('selectedIpp').value = '';
  document.getElementById('selectedPatientBanner').style.display = 'none';
  document.getElementById('patientList').style.display   = 'block';
  document.getElementById('patientSearch').style.display = 'block';
  document.getElementById('patientSearch').value = '';
  filterPatients('');
}

function onDragOver(e) {
  e.preventDefault();
  const z = document.getElementById('dropZone');
  z.style.borderColor = 'var(--accent)';
  z.style.background  = 'var(--accent-light)';
}

function onDragLeave(e) {
  const z = document.getElementById('dropZone');
  z.style.borderColor = 'var(--border)';
  z.style.background  = 'var(--bg-light)';
}

function onDrop(e) {
  e.preventDefault();
  onDragLeave(e);
  if (e.dataTransfer.files.length > 0) {
    const dt = new DataTransfer();
    dt.items.add(e.dataTransfer.files[0]);
    document.getElementById('fileInput').files = dt.files;
    showFile(e.dataTransfer.files[0]);
  }
}

function onFileSelected(input) {
  if (input.files.length > 0) showFile(input.files[0]);
}

function showFile(file) {
  const maxBytes = 10 * 1024 * 1024;
  const allowed  = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
  if (!allowed.includes(file.type)) { alert('Format non accepté. Utilisez PDF, JPG ou PNG.'); return; }
  if (file.size > maxBytes)         { alert('Fichier trop volumineux. Maximum 10 Mo.'); return; }

  const previewWrap = document.getElementById('filePreviewWrap');
  if (file.type.startsWith('image/')) {
    const r = new FileReader();
    r.onload = e => { previewWrap.innerHTML = '<img src="'+e.target.result+'" style="max-height:60px;border-radius:4px">'; };
    r.readAsDataURL(file);
  } else {
    previewWrap.innerHTML = '<div style="font-size:32px">📄</div>';
  }

  document.getElementById('fileName').textContent = file.name;
  document.getElementById('fileSize').textContent = (file.size < 1048576 ? (file.size/1024).toFixed(1)+' Ko' : (file.size/1048576).toFixed(2)+' Mo');
  document.getElementById('dropIdle').style.display    = 'none';
  document.getElementById('dropSelected').style.display = 'block';

  const z = document.getElementById('dropZone');
  z.style.borderStyle = 'solid';
  z.style.borderColor = 'var(--accent)';
  z.style.background  = 'var(--accent-light)';
}

function clearFile(e) {
  e.stopPropagation();
  document.getElementById('fileInput').value = '';
  document.getElementById('filePreviewWrap').innerHTML = '';
  document.getElementById('dropIdle').style.display    = 'block';
  document.getElementById('dropSelected').style.display = 'none';
  const z = document.getElementById('dropZone');
  z.style.borderStyle = 'dashed';
  z.style.borderColor = 'var(--border)';
  z.style.background  = 'var(--bg-light)';
}

document.getElementById('uploadForm').addEventListener('submit', function(e) {
  if (!document.getElementById('selectedIpp').value) { e.preventDefault(); alert('Veuillez sélectionner un patient.'); return; }
  if (!document.getElementById('fileInput').files.length) { e.preventDefault(); alert('Veuillez sélectionner un fichier.'); return; }
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.textContent = 'Import en cours…';
  btn.style.opacity = '.75';
});
</script>
@endpush
