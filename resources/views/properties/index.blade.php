@extends('layouts.app')

@section('title', 'Daftar Properti - KuSewa')

@section('content')
@php
// Data dummy yang disesuaikan persis dengan screenshot KuSewa
$properties = [
[
'name' => 'Villa Pondok Jabuk',
'location' => 'Batuaa, Samarinda',
'price' => 'Rp. 2.100.000,00',
'old_price' => 'Total 2.210.000,00',
'rating' => '8/10 Memuaskan',
'review_count' => '12rb ulasan',
'stars' => 4,
'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=900&q=85',
'thumbs' => [
'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=400&q=85',
'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=400&q=85',
],
],
[
'name' => 'Villa Pondok Jabuk',
'location' => 'Batuaa, Samarinda',
'price' => 'Rp. 2.100.000,00',
'old_price' => 'Total 2.210.000,00',
'rating' => '8/10 Memuaskan',
'review_count' => '12rb ulasan',
'stars' => 4,
'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=900&q=85',
'thumbs' => [
'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=400&q=85',
'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=400&q=85',
],
],
[
'name' => 'Homestay Garden',
'location' => 'Samarinda Seberang',
'price' => 'Rp. 1.150.000,00',
'old_price' => 'Total 1.250.000,00',
'rating' => '7/10 Memuaskan',
'review_count' => '12rb ulasan',
'stars' => 3,
'image' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=900&q=85',
'thumbs' => [
'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=400&q=85',
'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=400&q=85',
],
],
];

$ratingFilters = [
['count' => '(52)', 'stars' => 3],
['count' => '(31)', 'stars' => 4],
['count' => '(22)', 'stars' => 2],
['count' => '(12)', 'stars' => 5],
['count' => '(5)', 'stars' => 1],
];

$popularFilters = ['Promo liburan', 'Dekat Bandara', 'Kolam Renang', 'Dekat pasar pagi', 'Pemandian air hangat'];
$areaFilters = ['Samarinda Kota', 'Sebulu', 'Kepulauan Derawan', 'Kutai Kartanegara', 'Balikpapan Baru'];
@endphp

<main class="min-h-screen bg-white pb-20 pt-6 font-sans antialiased text-slate-800">
  <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">

    <form action="{{ route('properties.index') }}" method="GET"
      class="mb-8 flex flex-wrap items-center gap-2 rounded-full border border-slate-300 bg-white p-1.5 shadow-xs md:flex-nowrap">
      <div class="w-full px-4 border-r border-slate-200 py-1 md:w-1/4">
        <select name="type"
          class="w-full text-xs font-medium text-slate-700 bg-transparent outline-none cursor-pointer">
          <option>Jenis Properti</option>
          <option>Villa</option>
          <option>Homestay</option>
        </select>
      </div>
      <div class="w-full px-4 border-r border-slate-200 py-1 md:w-1/4">
        <select name="location"
          class="w-full text-xs font-medium text-slate-700 bg-transparent outline-none cursor-pointer">
          <option>Lokasi</option>
          <option>Samarinda</option>
        </select>
      </div>
      <div class="w-full px-4 border-r border-slate-200 py-1 md:w-1/4">
        <select name="date"
          class="w-full text-xs font-medium text-slate-700 bg-transparent outline-none cursor-pointer">
          <option>Jadwal</option>
        </select>
      </div>
      <div class="w-full px-4 py-1 md:w-1/4">
        <select name="price"
          class="w-full text-xs font-medium text-slate-700 bg-transparent outline-none cursor-pointer">
          <option>Harga</option>
        </select>
      </div>
      <button type="submit"
        class="flex h-10 w-16 shrink-0 items-center justify-center rounded-full bg-[#fec107] text-white transition hover:bg-amber-500">
        <svg class="h-4 w-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
        </svg>
      </button>
    </form>

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h1 class="text-base font-bold text-slate-900">Samarinda</h1>
        <p class="text-xs font-medium text-slate-500 mt-0.5">230 properti ditemukan</p>
      </div>
      <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
        <span>Urutkan berdasarkan</span>
        <select
          class="h-8 rounded-full border border-slate-400 bg-white px-4 text-xs font-medium text-slate-800 outline-none cursor-pointer">
          <option>Harga</option>
        </select>
        <select
          class="h-8 rounded-full border border-slate-400 bg-white px-4 text-xs font-medium text-slate-800 outline-none cursor-pointer">
          <option>Rating</option>
        </select>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[240px_1fr]">

      <aside class="space-y-6">

        <div
          class="relative h-24 w-full overflow-hidden rounded-xl border border-slate-300 shadow-md group cursor-pointer">
          <img
            src="https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/117.1537,0.5012,11,0/400x200?access_token=pk.eyJ1IjoiZXhhbXBsZSIsImEiOiJjbGV4YW1wbGUifQ.example"
            alt="Peta" class="h-full w-full object-cover" onerror="this.style.display='none'">
          <div class="absolute inset-0 bg-slate-900/10 flex items-center justify-center">
            <span
              class="inline-flex items-center rounded-md bg-[#fec107] px-3 py-1 text-[10px] font-bold text-white shadow-xs uppercase tracking-wide">
              Eksplor peta
            </span>
          </div>
        </div>

        <div class="rounded-xl border border-slate-300 bg-white p-4 shadow-md">
          <h3 class="text-xs font-bold text-slate-900">Rentang harga</h3>
          <p class="text-[10px] text-slate-500 mb-3">Per malam</p>
          <div class="flex items-center gap-2 mb-4">
            <input type="text" value="IDR 0"
              class="h-7 w-full rounded-md border border-slate-300 bg-slate-50 px-2 text-[10px] font-medium text-slate-700 text-center outline-none">
            <span class="text-slate-400 text-xs">-</span>
            <input type="text" value="IDR 16.000.000"
              class="h-7 w-full rounded-md border border-slate-300 bg-slate-50 px-2 text-[10px] font-medium text-slate-700 text-center outline-none">
          </div>
          <div class="relative h-1 w-full bg-slate-200 rounded-full">
            <div class="absolute left-0 right-0 h-full bg-[#fec107] rounded-full"></div>
            <div class="absolute left-2 -top-1 h-3 w-3 rounded-full border-2 border-[#fec107] bg-white"></div>
            <div class="absolute right-4 -top-1 h-3 w-3 rounded-full border-2 border-[#fec107] bg-white"></div>
          </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-md">
          <div class="flex items-center justify-between bg-[#fec107] px-4 py-2.5 text-xs font-bold text-slate-900">
            <span>Rating</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <div class="space-y-2.5 p-4">
            @foreach ($ratingFilters as $f)
            <label class="flex cursor-pointer items-center gap-2.5 text-xs font-medium text-slate-700">
              <input type="checkbox" class="h-3.5 w-3.5 rounded-sm border-slate-400 focus:ring-0">
              <span class="text-slate-600">{{ $f['count'] }}</span>
              <span class="text-amber-500 text-xs tracking-wider">
                @for($i = 0; $i < $f['stars']; $i++)★@endfor </span>
            </label>
            @endforeach
          </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-md">
          <div class="flex items-center justify-between bg-[#fec107] px-4 py-2.5 text-xs font-bold text-slate-900">
            <span>Filter populer</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <div class="space-y-2.5 p-4">
            @foreach ($popularFilters as $item)
            <label class="flex cursor-pointer items-center gap-2.5 text-xs font-medium text-slate-700">
              <input type="checkbox" class="h-3.5 w-3.5 rounded-sm border-slate-400 focus:ring-0">
              <span class="text-slate-600">{{ $item }}</span>
            </label>
            @endforeach
            <div class="pt-1 text-center">
              <a href="#" class="text-[11px] font-bold text-slate-800 underline">Lihat selengkapnya</a>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-md">
          <div class="flex items-center justify-between bg-[#fec107] px-4 py-2.5 text-xs font-bold text-slate-900">
            <span>Area Populer</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <div class="space-y-2.5 p-4">
            @foreach ($areaFilters as $item)
            <label class="flex cursor-pointer items-center gap-2.5 text-xs font-medium text-slate-700">
              <input type="checkbox" class="h-3.5 w-3.5 rounded-sm border-slate-400 focus:ring-0">
              <span class="text-slate-600">{{ $item }}</span>
            </label>
            @endforeach
            <div class="pt-1 text-center">
              <a href="#" class="text-[11px] font-bold text-slate-800 underline">Lihat selengkapnya</a>
            </div>
          </div>
        </div>
      </aside>

      <section class="min-w-0 space-y-6">
        <div class="space-y-6">
          @foreach ($properties as $property)
          <article
            class="grid overflow-hidden rounded-md border border-slate-200 bg-white shadow-[0_12px_30px_rgba(0,0,0,0.15)] md:grid-cols-[200px_1fr_180px]">

            <div class="grid grid-rows-[1.2fr_1fr] gap-0.5 bg-white p-0.5">
              <div class="h-32 w-full md:h-auto overflow-hidden">
                <img src="{{ $property['image'] }}" alt="{{ $property['name'] }}" class="h-full w-full object-cover">
              </div>
              <div class="grid grid-cols-2 gap-0.5">
                <div class="overflow-hidden h-16 md:h-auto">
                  <img src="{{ $property['thumbs'][0] }}" alt="" class="h-full w-full object-cover">
                </div>
                <div class="relative overflow-hidden h-16 md:h-auto">
                  <img src="{{ $property['thumbs'][1] }}" alt="" class="h-full w-full object-cover">
                  <div
                    class="absolute inset-0 bg-slate-900/50 flex flex-col items-center justify-center text-[9px] font-bold text-white text-center leading-tight">
                    <span class="underline">Lihat Foto</span>
                    <span>Lainnya</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-4 flex flex-col justify-between border-b md:border-b-0 md:border-r border-slate-200">
              <div>
                <h2 class="text-sm font-bold text-slate-900 mb-1">{{ $property['name'] }}</h2>

                <div class="flex items-center gap-2 mb-2">
                  <span
                    class="inline-flex items-center gap-1 rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500 border border-slate-200">
                    🏠 {{ (strpos($property['name'], 'Villa') !== false) ? 'Villa' : 'Homestay' }}
                  </span>
                  <div class="flex text-amber-400 text-xs tracking-tighter">
                    @for($i = 0; $i < $property['stars']; $i++)★@endfor </div>
                  </div>

                  <p class="mb-3 flex items-center gap-1 text-[10px] font-medium text-slate-600">
                    <svg class="h-3.5 w-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                      <path
                        d="M12 2.25a7.25 7.25 0 00-7.25 7.25c0 5.15 6.35 11.8 6.62 12.08a.9.9 0 001.26 0c.27-.28 6.62-6.93 6.62-12.08A7.25 7.25 0 0012 2.25zm0 10a2.75 2.75 0 110-5.5 2.75 2.75 0 010 5.5z" />
                    </svg>
                    {{ $property['location'] }}
                  </p>

                  <div class="flex flex-wrap gap-1 mb-3">
                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-medium text-slate-500">Fasilitas
                      Bisnis</span>
                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-medium text-slate-500">Kolam
                      Renang</span>
                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-medium text-slate-500">Aksesibel
                      bagi penyandang disabilitas</span>
                  </div>
                </div>

                <div class="text-[10px] font-medium text-slate-600">
                  <span class="text-amber-500 font-bold">★ {{ $property['rating'] }}</span>
                  <span class="text-slate-400">({{ $property['review_count'] }})</span>
                </div>
              </div>

              <div class="p-4 flex flex-col justify-between bg-white text-right">
                <div class="mt-2">
                  <p class="text-sm font-bold text-[#fec107]">{{ $property['price'] }}</p>
                  <p class="text-[9px] font-medium text-slate-400 leading-tight mt-0.5">{{ $property['old_price'] }}</p>
                  <p class="text-[9px] text-slate-400">Termasuk pajak & biaya</p>
                  <p class="text-[9px] font-bold text-emerald-600 mt-1">Kamar masih tersedia</p>
                </div>

                <a href="#"
                  class="mt-4 inline-flex h-8 items-center justify-center rounded-sm bg-[#fec107] px-4 text-center text-[10px] font-bold text-white transition hover:bg-amber-500 shadow-xs">
                  Lihat detail
                </a>
              </div>

          </article>
          @endforeach
        </div>
      </section>

    </div>
  </div>
</main>
@endsection