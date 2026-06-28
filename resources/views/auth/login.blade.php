{{-- auth/login.blade.php — Page de connexion premium avec animations --}}
@extends('layouts.auth')
@section('title', 'Connexion')

@section('content')

<div class="login-page">

  {{-- PANNEAU GAUCHE — Illustration --}}
  <div class="login-left">
    <img src="{{ asset('assets/img/image_connexion.webp') }}"
         alt="Médecin utilisant la GED Médicale"
         class="login-illustration">
  </div>

  {{-- PANNEAU DROIT — Formulaire --}}
  <div class="login-right">
    <div class="login-box">

      {{-- En-tête animé --}}
      <div class="login-header">

        {{-- Cœur qui se dessine --}}
        <div class="wh-wrap">
          <svg class="wh-svg" viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="hGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#7C3AED"/>
                <stop offset="100%" stop-color="#0D9488"/>
              </linearGradient>
            </defs>
            {{-- Remplissage (apparaît après le contour) --}}
            <path class="wh-fill"
              d="M60,28 C60,24 54,14 42,14 C26,14 20,32 30,44 C36,52 52,64 60,72 C68,64 84,52 90,44 C100,32 94,14 78,14 C66,14 60,24 60,28 Z"
              fill="url(#hGrad)"/>
            {{-- Contour du cœur --}}
            <path class="wh-stroke"
              d="M60,28 C60,24 54,14 42,14 C26,14 20,32 30,44 C36,52 52,64 60,72 C68,64 84,52 90,44 C100,32 94,14 78,14 C66,14 60,24 60,28 Z"
              stroke="url(#hGrad)" stroke-width="2.8" stroke-linejoin="round"/>
            {{-- Ligne ECG à l'intérieur --}}
            <polyline class="wh-ecg"
              points="24,43 32,43 35,35 39,51 43,23 47,47 51,39 60,39 69,39 78,39 94,39"
              stroke="url(#hGrad)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        {{-- "Bienvenue" lettre par lettre --}}
        <h2 class="wletter-wrap">
          <span class="wl" style="--wi:0">B</span><span
              class="wl" style="--wi:1">i</span><span
              class="wl" style="--wi:2">e</span><span
              class="wl" style="--wi:3">n</span><span
              class="wl" style="--wi:4">v</span><span
              class="wl" style="--wi:5">e</span><span
              class="wl" style="--wi:6">n</span><span
              class="wl" style="--wi:7">u</span><span
              class="wl" style="--wi:8">e</span>
        </h2>

        {{-- Sous-titre qui glisse --}}
        <p class="wsub">Connectez-vous à votre espace médical sécurisé</p>

      </div>

      {{-- Alertes --}}
      @if (session('error'))
        <div class="alert alert-danger">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          {{ session('error') }}
        </div>
      @endif

      @if (session('success'))
        <div class="alert alert-success">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
          {{ session('success') }}
        </div>
      @endif

      {{-- Formulaire --}}
      <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
        @csrf

        <div class="form-group anim-field" style="--fd: 0.3s">
          <label class="form-label" for="email">Adresse e-mail</label>
          <div class="input-icon-wrap">
            <span class="input-icon-left">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
              </svg>
            </span>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control has-icon-left {{ $errors->has('email') ? 'is-invalid' : '' }}"
              value="{{ old('email') }}"
              placeholder="votre.email@chr.ma"
              autocomplete="email"
              required
            />
          </div>
          @error('email')
            <div class="form-error">
              <svg viewBox="0 0 20 20" fill="currentColor" style="width:12px;height:12px"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
              {{ $message }}
            </div>
          @enderror
        </div>

        <div class="form-group anim-field" style="--fd: 0.42s">
          <label class="form-label" for="password">Mot de passe</label>
          <div class="input-icon-wrap pw-wrap">
            <span class="input-icon-left">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
              </svg>
            </span>
            <input
              type="password"
              id="password"
              name="password"
              class="form-control has-icon-left {{ $errors->has('password') ? 'is-invalid' : '' }}"
              placeholder="••••••••"
              autocomplete="current-password"
              required
            />
            <button type="button" class="pw-eye" id="pwEyeBtn" onclick="togglePw()" title="Afficher le mot de passe">
              <svg id="eyeOpen" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
              </svg>
              <svg id="eyeClosed" viewBox="0 0 20 20" fill="currentColor" style="display:none">
                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
              </svg>
            </button>
          </div>
          @error('password')
            <div class="form-error">{{ $message }}</div>
          @enderror
        </div>

        <div class="anim-field" style="--fd: 0.54s">
          <button type="submit" class="btn-login" id="submitBtn">
            <span class="btn-login-text">Se connecter</span>
            <svg class="btn-login-arrow" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
            <span class="btn-login-spinner" style="display:none">
              <svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3"/>
                <path d="M12 2a10 10 0 0110 10" stroke="white" stroke-width="3" stroke-linecap="round"/>
              </svg>
            </span>
          </button>
        </div>

      </form>

      <div class="login-footer anim-field" style="--fd: 0.62s">
        GED Médicale — CHR Al Ghassani, Fès<br>
        Accès réservé au personnel autorisé · &copy;{{ date('Y') }}
      </div>

    </div>
  </div>

</div>

@endsection

@push('scripts')
<script>
function togglePw() {
  const inp    = document.getElementById('password');
  const open   = document.getElementById('eyeOpen');
  const closed = document.getElementById('eyeClosed');
  if (inp.type === 'password') {
    inp.type = 'text';
    open.style.display   = 'none';
    closed.style.display = '';
  } else {
    inp.type = 'password';
    open.style.display   = '';
    closed.style.display = 'none';
  }
}

document.getElementById('loginForm').addEventListener('submit', function() {
  const btn    = document.getElementById('submitBtn');
  const txtEl  = btn.querySelector('.btn-login-text');
  const arrEl  = btn.querySelector('.btn-login-arrow');
  const spinEl = btn.querySelector('.btn-login-spinner');
  btn.disabled           = true;
  txtEl.textContent      = 'Connexion…';
  arrEl.style.display    = 'none';
  spinEl.style.display   = 'inline-flex';
  btn.style.opacity      = '0.85';
});
</script>
@endpush
