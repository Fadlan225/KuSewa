@extends('layouts.app')

@section('title', __('Home - Cari Sewa Properti Terbaik'))

@section('hero')
@include('components.hero')
@endsection

@section('content')
@php
$keyword = trim((string) request('keyword'));
$matchesKeyword = static fn (string $text): bool => $keyword === ''
|| str_contains(\Illuminate\Support\Str::lower($text), \Illuminate\Support\Str::lower($keyword));
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 mt-8">

  <section>
    <div class="text-center max-w-2xl mx-auto mb-16">
      <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">{{ __('Pilihan Terpopuler') }}</h2>
      <div class="h-1 w-20 bg-[#FFC107] mx-auto mt-4 rounded-full"></div>
      <p class="text-gray-500 mt-4 text-base md:text-lg">{{ __('Temukan hunian terfavorit dengan pelayanan terbaik,
        keamanan terjamin, dan harga yang bersahabat.') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

      @if ($matchesKeyword('apartemen central park residence jakarta barat dki jakarta'))
      <div
        class="bg-white rounded-3xl overflow-hidden shadow-md hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 group">
        <div class="relative overflow-hidden h-64">
          <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80"
            alt="Apartemen" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
          <span
            class="absolute top-4 left-4 bg-yellow-400 text-white font-bold text-xs uppercase px-3 py-1.5 rounded-full tracking-wide shadow-sm">{{
            __('Apartemen') }}</span>
        </div>
        <div class="p-6">
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-xl font-bold text-gray-900 truncate">{{ __('Central Park Residence') }}</h3>
            <div class="flex items-center text-yellow-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                <path
                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="text-sm font-bold text-gray-800 ml-1">4.8</span>
            </div>
          </div>
          <p class="text-gray-500 text-sm flex items-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ __('Jakarta Barat, DKI Jakarta') }}
          </p>
          <div class="flex items-center gap-4 text-gray-500 text-xs font-semibold mb-6">
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('🛏️ 2 Kamar') }}</span>
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('🚿 1 Toilet') }}</span>
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('📐 45 m²') }}</span>
          </div>
          <div class="flex justify-between items-center pt-4 border-t border-gray-100">
            <div class="text-lg font-black text-gray-900">Rp 4.500.000<span class="text-xs font-normal text-gray-500">{{
                __('/bln') }}</span></div>
            <a href="#" class="text-sm font-bold text-yellow-500 hover:text-yellow-600 transition">{!! __('Lihat Detail
              &rarr;') !!}</a>
          </div>
        </div>
      </div>
      @endif

      @if ($matchesKeyword('rumah seminyak green oasis badung bali'))
      <div
        class="bg-white rounded-3xl overflow-hidden shadow-md hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 group">
        <div class="relative overflow-hidden h-64">
          <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80"
            alt="Rumah" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
          <span
            class="absolute top-4 left-4 bg-yellow-400 text-white font-bold text-xs uppercase px-3 py-1.5 rounded-full tracking-wide shadow-sm">{{
            __('Rumah') }}</span>
        </div>
        <div class="p-6">
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-xl font-bold text-gray-900 truncate">{{ __('Seminyak Green Oasis') }}</h3>
            <div class="flex items-center text-yellow-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                <path
                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="text-sm font-bold text-gray-800 ml-1">4.9</span>
            </div>
          </div>
          <p class="text-gray-500 text-sm flex items-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ __('Badung, Bali') }}
          </p>
          <div class="flex items-center gap-4 text-gray-500 text-xs font-semibold mb-6">
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('🛏️ 3 Kamar') }}</span>
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('🚿 2 Toilet') }}</span>
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('📐 120 m²') }}</span>
          </div>
          <div class="flex justify-between items-center pt-4 border-t border-gray-100">
            <div class="text-lg font-black text-gray-900">Rp 12.000.000<span
                class="text-xs font-normal text-gray-500">{{ __('/bln') }}</span></div>
            <a href="#" class="text-sm font-bold text-yellow-500 hover:text-yellow-600 transition">{!! __('Lihat Detail
              &rarr;') !!}</a>
          </div>
        </div>
      </div>
      @endif

      @if ($matchesKeyword('vila dago hilltop mansion dago bandung'))
      <div
        class="bg-white rounded-3xl overflow-hidden shadow-md hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 group">
        <div class="relative overflow-hidden h-64">
          <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80"
            alt="Villa" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
          <span
            class="absolute top-4 left-4 bg-yellow-400 text-white font-bold text-xs uppercase px-3 py-1.5 rounded-full tracking-wide shadow-sm">{{
            __('Vila') }}</span>
        </div>
        <div class="p-6">
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-xl font-bold text-gray-900 truncate">{{ __('Dago Hilltop Mansion') }}</h3>
            <div class="flex items-center text-yellow-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                <path
                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="text-sm font-bold text-gray-800 ml-1">4.7</span>
            </div>
          </div>
          <p class="text-gray-500 text-sm flex items-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ __('Dago, Bandung') }}
          </p>
          <div class="flex items-center gap-4 text-gray-500 text-xs font-semibold mb-6">
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('🛏️ 4 Kamar') }}</span>
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('🚿 3 Toilet') }}</span>
            <span class="bg-gray-50 px-3 py-1.5 rounded-lg flex items-center gap-1">{{ __('📐 250 m²') }}</span>
          </div>
          <div class="flex justify-between items-center pt-4 border-t border-gray-100">
            <div class="text-lg font-black text-gray-900">Rp 18.000.000<span
                class="text-xs font-normal text-gray-500">{{ __('/bln') }}</span></div>
            <a href="#" class="text-sm font-bold text-yellow-500 hover:text-yellow-600 transition">{!! __('Lihat Detail
              &rarr;') !!}</a>
          </div>
        </div>
      </div>
      @endif

    </div>

    <div class="mt-10 text-center">
      <a href="{{ route('properties.index') }}"
        class="inline-flex items-center gap-2 rounded-xl border border-yellow-400 px-6 py-3 text-sm font-bold text-yellow-600 transition hover:bg-yellow-400 hover:text-white">
        Lihat Selengkapnya
        <span aria-hidden="true">→</span>
      </a>
    </div>

    @if ($keyword && ! $matchesKeyword('apartemen central park residence jakarta barat dki jakarta rumah seminyak green
    oasis badung bali vila dago hilltop mansion dago bandung'))
    <p class="mt-8 text-center text-gray-500">Tidak ada properti yang sesuai dengan pencarian “{{ $keyword }}”.</p>
    @endif
  </section>

  <section class="py-24">
    <div class="bg-gray-50 rounded-[40px] px-8 py-16 md:p-20 border border-gray-100">
      <div class="text-center max-w-2xl mx-auto mb-16">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">{{ __('Kenapa Memilih KuSewa?') }}
        </h2>
        <div class="h-1 w-20 bg-[#FFC107] mx-auto mt-4 rounded-full"></div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-12">

        <div class="text-center group">
          <div
            class="w-16 h-16 bg-yellow-100 text-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:rotate-12 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('Keamanan & Legalitas Terjamin') }}</h3>
          <p class="text-gray-500 text-sm leading-relaxed">{{ __('Semua listing properti telah terverifikasi secara
            resmi untuk menghindarkan Anda dari penipuan.') }}</p>
        </div>

        <div class="text-center group">
          <div
            class="w-16 h-16 bg-yellow-100 text-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:rotate-12 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('Transaksi Transparan') }}</h3>
          <p class="text-gray-500 text-sm leading-relaxed">{{ __('Tanpa biaya tambahan atau tersembunyi. Semua detail
            kontrak sewa disepakati secara terbuka.') }}</p>
        </div>

        <div class="text-center group">
          <div
            class="w-16 h-16 bg-yellow-100 text-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:rotate-12 transition-transform duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('Layanan Dukungan 24/7') }}</h3>
          <p class="text-gray-500 text-sm leading-relaxed">{{ __('Tim customer support kami selalu siap membantu
            permasalahan sewa Anda kapanpun diperlukan.') }}</p>
        </div>

      </div>
    </div>
  </section>

  <section class="pb-12">
    <div
      class="relative rounded-[40px] overflow-hidden bg-gradient-to-r from-gray-900 to-slate-800 px-8 py-16 md:p-20 text-white shadow-2xl">
      <div
        class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-purple-500 via-pink-500 to-indigo-900">
      </div>
      <div class="relative z-10 max-w-2xl">
        <h2 class="text-3xl md:text-5xl font-extrabold mb-6 leading-tight">{{ __('Miliki Properti untuk Disewakan?') }}
        </h2>
        <p class="text-gray-300 text-lg mb-8 leading-relaxed">{{ __('Daftarkan properti Anda sekarang di KuSewa dan
          jangkau ribuan calon penyewa setiap harinya dengan mudah dan cepat.') }}</p>

        <div class="flex flex-wrap gap-4">
          @auth
          <a href="{{ route('properties.create') }}"
            class="inline-flex items-center justify-center bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 transition-all duration-300 text-white font-bold px-8 py-4 rounded-xl shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transform hover:-translate-y-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Mulai Pasang Iklan') }}
          </a>
          @else
          <a href="{{ route('login') }}"
            class="inline-flex items-center justify-center bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 transition-all duration-300 text-white font-bold px-8 py-4 rounded-xl shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transform hover:-translate-y-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('Mulai Pasang Iklan') }}
          </a>

          <a href="{{ route('register') }}"
            class="inline-flex items-center justify-center bg-white/10 hover:bg-white/20 border border-white/20 transition-all duration-300 text-white font-bold px-8 py-4 rounded-xl">
            {{ __('Daftar Akun Baru') }}
          </a>
          @endauth
        </div>
      </div>
    </div>
  </section>

</div>
@endsection