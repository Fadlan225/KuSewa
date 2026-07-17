@extends('layouts.app')

@section('title', 'Login - KuSewa')

@section('content')
<main class="relative min-h-screen w-full bg-cover bg-center bg-no-repeat font-sans antialiased text-slate-800"
  style="background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1920&q=85');">

  <!-- Dark overlay untuk menyamakan tone background -->
  <div class="absolute inset-0 bg-slate-900/30"></div>

  <div class="relative mx-auto flex min-h-screen w-full max-w-7xl items-center justify-between px-6 py-12 md:px-12">

    <!-- TAGLINE KIRI -->
    <div class="max-w-xl text-white hidden md:block select-none">
      <h1 class="text-5xl font-extrabold tracking-tight leading-[1.15] drop-shadow-md">
        Langkah Mudah<br>Menuju <span class="text-[#fec107]">Tempat</span><br>Impian.
      </h1>
      <p class="mt-6 max-w-md text-sm font-medium leading-relaxed text-slate-100 drop-shadow-xs">
        Menghubungkan Anda dengan properti sewaan terbaik dan terverifikasi di seluruh Indonesia. Proses transparan,
        transaksi aman.
      </p>
    </div>

    <!-- FORM CARD LOGIN KANAN -->
    <div class="mx-auto w-full max-w-[420px] rounded-2xl bg-white p-8 shadow-2xl md:mx-0">
      <!-- Logo Brand -->
      <div class="flex flex-col items-center justify-center">
        <div class="flex items-center gap-1.5">
          <svg class="h-6 w-6 text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span class="text-lg font-black text-slate-800 tracking-tight">kusewa<span
              class="text-[#fec107]">.id</span></span>
        </div>
        <h2 class="mt-4 text-xl font-bold text-[#fec107] tracking-wide">Login</h2>
        <div class="mt-4 h-[1px] w-full bg-slate-100"></div>
      </div>

      <!-- Form Inputs -->
      <form action="{{ route('login') }}" method="POST" class="mt-6 space-y-5">
        @csrf
        <!-- Email Field -->
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-800">Email</label>
          <div
            class="relative border-b border-slate-300 focus-within:border-[#fec107] transition pb-1.5 flex items-center">
            <span class="absolute left-0 text-amber-500/70 text-sm">✉</span>
            <input type="email" name="email" placeholder="johndoe@example.com" required
              class="w-full bg-transparent pl-6 text-xs text-slate-700 outline-none placeholder:text-slate-300">
          </div>
        </div>

        <!-- Password Field -->
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-800">Password</label>
          <div
            class="relative border-b border-slate-300 focus-within:border-[#fec107] transition pb-1.5 flex items-center">
            <span class="absolute left-0 text-amber-500/70 text-sm">🔒</span>
            <input type="password" name="password" placeholder="********" required
              class="w-full bg-transparent pl-6 text-xs text-slate-700 outline-none placeholder:text-slate-300">
          </div>
        </div>

        <!-- Forgot Password -->
        <div class="text-right">
          <a href="#" class="text-[10px] font-bold text-[#fec107] hover:underline">forgot password?</a>
        </div>

        <!-- Submit Button -->
        <button type="submit"
          class="w-full h-10 rounded-full bg-[#fec107] text-xs font-bold text-white transition hover:bg-amber-500 shadow-sm">
          Login
        </button>
      </form>

      <div class="my-5 h-[1px] w-full bg-slate-100"></div>

      <!-- Google OAuth Button -->
      <a href="#"
        class="flex h-10 w-full items-center justify-center gap-2 rounded-full border border-slate-700 bg-white transition hover:bg-slate-50">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-4 w-4">
        <span class="text-xs font-bold text-slate-700">Sign in with Google</span>
      </a>

      <!-- Redirect Link -->
      <p class="mt-6 text-center text-[11px] font-medium text-slate-600">
        Belum punya akun? <a href="{{ route('register') }}"
          class="font-bold text-[#fec107] hover:underline">register</a>
      </p>
    </div>

  </div>
</main>
@endsection