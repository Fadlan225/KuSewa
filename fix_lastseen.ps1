$content = Get-Content "resources\js\Pages\Home\LastSeen.vue" -Raw

# 1. Hide mobile top navbar
$content = $content.Replace(
    '<div class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">',
    '<div v-if="!isComponent" class="sticky top-0 z-50 bg-white border-b border-slate-100 flex items-center justify-between px-4 h-14 shadow-sm">'
)

# 2. Add Component Header (Title ONLY since no Sort in LastSeen)
$mobileTopFilter = '      <!-- Mobile Top: Search or Filter Summary -->'
$componentHeader = @"
      <!-- DESKTOP COMPONENT HEADER -->
      <div v-if="isComponent" class="flex justify-between items-center mb-4 mt-2">
          <h2 class="text-xl font-bold text-[#1D1D1F]">Terakhir Dilihat</h2>
      </div>

      <!-- Mobile Top: Search or Filter Summary -->
"@
$content = $content.Replace($mobileTopFilter, $componentHeader)

# 3. Modify Filter Summary block to show if isComponent
$content = $content.Replace(
    '<div class="flex justify-between items-center mb-5 lg:hidden">',
    '<div class="flex justify-between items-center mb-5" :class="isComponent ? '''' : ''lg:hidden''">'
)

# 4. Hide Sidebar if isComponent
$content = $content.Replace(
    '<aside class="hidden lg:block lg:col-span-3">',
    '<aside v-if="!isComponent" class="hidden lg:block lg:col-span-3">'
)

# 5. Span full if isComponent
$content = $content.Replace(
    '<section class="col-span-12 lg:col-span-9">',
    '<section class="col-span-12" :class="!isComponent ? ''lg:col-span-9'' : ''''">'
)

Set-Content "resources\js\Pages\Home\LastSeen.vue" $content
