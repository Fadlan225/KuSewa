<template>
  <AdminLayout>
    <template #header>
      <h1>Laporan Pengguna</h1>
      <p>Laporan komplain dan masalah terkait transaksi atau akun</p>
    </template>
    <section class="section">
      <div class="card-base">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID Laporan</th>
              <th>Pelapor</th>
              <th>Kategori Masalah</th>
              <th>Waktu</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#RPT-045</td>
              <td>Budi S.</td>
              <td>Fasilitas Tidak Sesuai</td>
              <td>27 Jul 2026 10:15</td>
              <td><span class="badge warning">Menunggu Tindakan</span></td>
              <td>
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-folder-open"></i> Buka Tiket</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </AdminLayout>
</template>
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>
