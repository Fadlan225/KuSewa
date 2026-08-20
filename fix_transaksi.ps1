$content = Get-Content "resources\js\Pages\Home\Activity\Transaksi.vue" -Raw

# 1. Hide mobile top navbar
$content = $content.Replace(
    '<div class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">',
    '<div v-if="!isComponent" class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">'
)

# 2. Add Component Header
$mobileTopFilter = '      <!-- Mobile Tabs Navigation -->'
$componentHeader = @"
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-[#1D1D1F]">
        <!-- DESKTOP COMPONENT HEADER -->
        <div v-if="isComponent" class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-[#1D1D1F]">Riwayat Transaksi & Booking</h2>
        </div>
      </div>

      <!-- Mobile Tabs Navigation -->
"@
$content = $content.Replace($mobileTopFilter, $componentHeader)

Set-Content "resources\js\Pages\Home\Activity\Transaksi.vue" $content
