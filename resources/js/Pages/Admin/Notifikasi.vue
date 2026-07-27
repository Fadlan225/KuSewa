<template>
  <AdminLayout>
    <template #header>
      <h1>Notifikasi Sistem</h1>
      <p>Pemberitahuan aktivitas sistem secara realtime</p>
    </template>
    <section class="section">
      <div class="card-base">
        <ul style="list-style: none; padding: 0; margin: 0;">
          <li style="padding: 16px; border-bottom: 1px solid #eee; display: flex; gap: 16px; align-items: flex-start;">
            <div style="background-color: var(--info-light); color: var(--info); width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
              <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
              <h4 style="margin: 0 0 4px;">Pendaftaran Akun Baru</h4>
              <p style="margin: 0; font-size: 13px; color: var(--text-muted);">Budi Santoso telah mendaftar sebagai Penyewa.</p>
              <span style="font-size: 11px; color: var(--text-muted); margin-top: 8px; display: block;">15 Menit yang lalu</span>
            </div>
          </li>
          <li style="padding: 16px; display: flex; gap: 16px; align-items: flex-start;">
            <div style="background-color: var(--danger-light); color: var(--danger); width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
              <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
              <h4 style="margin: 0 0 4px;">Laporan Diterima</h4>
              <p style="margin: 0; font-size: 13px; color: var(--text-muted);">Laporan baru terkait properti "Villa Alam Raya".</p>
              <span style="font-size: 11px; color: var(--text-muted); margin-top: 8px; display: block;">1 Jam yang lalu</span>
            </div>
          </li>
        </ul>
      </div>
    </section>
  </AdminLayout>
</template>
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>
