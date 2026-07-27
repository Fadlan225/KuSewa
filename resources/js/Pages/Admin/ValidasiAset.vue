<template>
  <AdminLayout>
    <template #header>
      <h1>Validasi Aset</h1>
      <p>Persetujuan Properti dan Baliho baru</p>
    </template>
    <section class="section">
      <div class="card-base">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID Aset</th>
              <th>Nama Aset</th>
              <th>Tipe</th>
              <th>Pemilik</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#AST-102</td>
              <td>Villa Alam Raya</td>
              <td><span class="badge success">Properti</span></td>
              <td>PT. Alam Indah</td>
              <td><span class="badge warning">Menunggu</span></td>
              <td>
                <button class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i> Setujui</button>
                <button class="btn btn-sm btn-danger" style="margin-left: 4px"><i class="fa-solid fa-xmark"></i> Tolak</button>
              </td>
            </tr>
            <tr>
              <td>#AST-103</td>
              <td>Baliho Jl. Sudirman</td>
              <td><span class="badge info">Baliho</span></td>
              <td>CV. Media Iklan</td>
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
