<template>
  <AdminLayout>
    <template #header>
      <h1>Validasi Akun</h1>
      <p>Kelola dan setujui pendaftaran akun baru</p>
    </template>
    <section class="section">
      <div class="card-base">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Tipe</th>
              <th>Tanggal Daftar</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#USR-001</td>
              <td>Budi Santoso</td>
              <td><span class="badge info">Penyewa</span></td>
              <td>27 Jul 2026</td>
              <td><span class="badge warning">Menunggu</span></td>
              <td>
                <button class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i> Setujui</button>
                <button class="btn btn-sm btn-danger" style="margin-left: 4px"><i class="fa-solid fa-xmark"></i> Tolak</button>
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
