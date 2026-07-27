<template>
  <AdminLayout>
    <template #header>
      <h1>Manajemen Promo</h1>
      <p>Buat dan kelola kode promo atau diskon platform</p>
    </template>
    <section class="section">
      <div class="card-base">
        <div style="margin-bottom: 20px;">
          <button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Buat Promo Baru</button>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Kode Promo</th>
              <th>Potongan</th>
              <th>Berlaku Hingga</th>
              <th>Kuota Tersisa</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>MERDEKA26</td>
              <td>15%</td>
              <td>31 Agu 2026</td>
              <td>450</td>
              <td><span class="badge success">Aktif</span></td>
              <td>
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-danger" style="margin-left: 4px"><i class="fa-solid fa-trash"></i></button>
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
