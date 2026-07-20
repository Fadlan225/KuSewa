<template>
    <!-- Skeleton satu kartu aset, ukuran sama dengan kartu aslinya, bentuk kotak lebih persegi -->
    <div class="flex-none w-[150px] sm:w-[180px] md:w-[200px] lg:w-[220px] snap-start flex flex-col animate-pulse rounded-2xl shadow-lg bg-white overflow-hidden">

        <!-- Area Gambar skeleton (aspek rasio lanskap, meniru gambar Marina Bay Sands) -->
        <div class="aspect-[3/2] w-full bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 relative overflow-hidden">
            <!-- Shimmer sweep -->
            <div class="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-white/60 to-transparent"></div>

            <!-- Badge skeleton kiri atas (meniru bentuk badge "PULANG-PERGI", lebih gelap) -->
            <div class="absolute top-3 left-3 h-6 w-28 bg-gray-400 rounded-md"></div>
            <!-- Ikon simpan skeleton kanan atas (meniru bentuk penanda buku dengan tanda +) -->
            <div class="absolute top-3 right-3 w-7 h-8 bg-white/70 rounded"></div>
        </div>

        <!-- Teks skeleton dengan padding yang lebih besar -->
        <div class="flex flex-col gap-2 p-4 pt-4 pb-3">
            <!-- Judul (satu baris penuh, meniru "Jakarta - Singapore") -->
            <div class="h-4.5 rounded bg-gray-200 w-full"></div>

            <!-- Kontainer Detail (meniru Ikon Kuning + Tanggal) -->
            <div class="flex items-center gap-1.5 mt-1">
                <!-- Ikon detail (meniru ikon kuning) -->
                <div class="w-4 h-4 rounded-full bg-gray-300"></div>
                <!-- Tanggal skeleton -->
                <div class="h-3.5 rounded bg-gray-100 w-3/5"></div>
            </div>

            <!-- Harga (Meniru harga oranye, warna skeleton oranye muda) -->
            <div class="h-5 rounded bg-orange-100 w-2/3 mt-1"></div>
        </div>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    100% { transform: translateX(100%); }
}
.animate-shimmer {
    animation: shimmer 1.5s infinite;
}
</style>

<style scoped>
@keyframes shimmer {
    100% { transform: translateX(100%); }
}
.animate-shimmer {
    animation: shimmer 1.5s infinite;
}
</style>
