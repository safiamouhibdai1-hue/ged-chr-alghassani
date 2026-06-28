{{-- documents/confirm-delete.blade.php --}}
@extends('layouts.app')
@section('title', 'Confirmer la suppression')
@section('page-title', 'Suppression de document')

@section('content')
<div style="max-width:560px;margin:0 auto">

  {{-- Alerte danger --}}
  <div style="background:#FEF2F2;border:2px solid #FCA5A5;border-radius:12px;padding:1.4rem 1.6rem;margin-bottom:1.5rem;display:flex;gap:14px;align-items:flex-start">
    <div style="width:42px;height:42px;border-radius:50%;background:#FEE2E2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg viewBox="0 0 20 20" fill="currentColor" style="width:22px;height:22px;color:#DC2626">
        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div>
      <div style="font-size:15px;font-weight:800;color:#DC2626;margin-bottom:4px">Action irréversible</div>
      <div style="font-size:13px;color:#7F1D1D;line-height:1.6">
        La suppression est <strong>définitive</strong>. Le fichier sera effacé du serveur
        et ne pourra pas être récupéré.
      </div>
    </div>
  </div>

  {{-- Infos du document --}}
  <div class="section-card" style="margin-bottom:1.5rem">
    <div class="section-card-head">
      <span class="section-card-title">Document à supprimer</span>
    </div>
    <div style="padding:0">
      <div style="display:flex;justify-content:space-between;padding:10px 1.2rem;border-bottom:1px solid rgba(221,214,254,0.2);font-size:13px">
        <span style="color:var(--text-secondary);font-weight:500">Titre</span>
        <span style="font-weight:700;color:var(--text-primary)">{{ $document->titre }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 1.2rem;border-bottom:1px solid rgba(221,214,254,0.2);font-size:13px">
        <span style="color:var(--text-secondary);font-weight:500">Type</span>
        <span class="badge badge-blue">{{ $document->type_label }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 1.2rem;border-bottom:1px solid rgba(221,214,254,0.2);font-size:13px">
        <span style="color:var(--text-secondary);font-weight:500">Service</span>
        <span style="font-weight:600">{{ $document->service }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 1.2rem;border-bottom:1px solid rgba(221,214,254,0.2);font-size:13px">
        <span style="color:var(--text-secondary);font-weight:500">Patient</span>
        <span style="font-weight:600">{{ $document->patient?->nom_complet ?? '—' }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 1.2rem;font-size:13px">
        <span style="color:var(--text-secondary);font-weight:500">Date d'import</span>
        <span style="font-weight:600">{{ $document->date_import->format('d/m/Y') }}</span>
      </div>
    </div>
  </div>

  {{-- Formulaire de confirmation --}}
  <div class="section-card">
    <div class="section-card-head">
      <span class="section-card-title">Validation requise</span>
    </div>
    <div style="padding:1.2rem 1.4rem">

      <p style="font-size:13px;color:var(--text-secondary);margin-bottom:1rem;line-height:1.6">
        Pour confirmer la suppression définitive, saisissez le mot
        <strong style="color:#DC2626;font-family:monospace;font-size:14px">SUPPRIMER</strong>
        dans le champ ci-dessous.
      </p>

      <form method="POST" action="{{ route('documents.destroy', $document->id_docum) }}">
        @csrf
        @method('DELETE')

        <div class="form-group">
          <input type="text"
                 name="confirmation"
                 id="confirmInput"
                 class="form-control {{ $errors->has('confirmation') ? 'is-error' : '' }}"
                 placeholder="Tapez SUPPRIMER pour confirmer"
                 autocomplete="off"
                 oninput="checkConfirm(this.value)"
                 style="font-family:monospace;font-size:14px;letter-spacing:1px" />

          @error('confirmation')
            <div class="form-error" style="margin-top:6px">{{ $message }}</div>
          @enderror
        </div>

        <div style="display:flex;gap:8px;margin-top:1.2rem">
          <button type="submit"
                  id="btnConfirm"
                  class="btn btn-danger"
                  style="flex:1;opacity:.4;pointer-events:none"
                  disabled>
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Supprimer définitivement
          </button>
          <a href="{{ route('documents.show', $document->id_docum) }}"
             class="btn btn-secondary" style="flex:1;justify-content:center">
            Annuler
          </a>
        </div>

      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
function checkConfirm(val) {
  const btn = document.getElementById('btnConfirm');
  if (val === 'SUPPRIMER') {
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.pointerEvents = 'auto';
  } else {
    btn.disabled = true;
    btn.style.opacity = '.4';
    btn.style.pointerEvents = 'none';
  }
}
</script>
@endpush
