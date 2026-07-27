<template>
  <AdminLayout>
    <template #header>
      <h1>Kelola Penyewa</h1>
      <p>Manajemen data akun penyewa</p>
    </template>
    <section class="section">
      <div class="card-base">
        <div style="margin-bottom: 20px;">
          <button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Penyewa</button>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Nama Penyewa</th>
              <th>Email</th>
              <th>Total Booking</th>
              <th>Status Akun</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Siti Aminah</td>
              <td>siti@example.com</td>
              <td>4</td>
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
