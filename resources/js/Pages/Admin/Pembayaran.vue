<template>
  <AdminLayout>
    <template #header>
      <h1>Riwayat Transaksi & Pembayaran</h1>
      <p>Seluruh riwayat pembayaran transaksi yang masuk</p>
    </template>
    <section class="section">
      <div class="card-base">
        <div style="margin-bottom: 20px; display: flex; gap: 10px;">
          <input type="text" placeholder="Cari No. Transaksi..." style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;" />
          <button class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
          <button class="btn btn-success"><i class="fa-solid fa-file-excel"></i> Export</button>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>ID Transaksi</th>
              <th>Penyewa</th>
              <th>Aset</th>
              <th>Total</th>
              <th>Tanggal</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#TRX-9001</td>
              <td>Siti Aminah</td>
              <td>Villa Alam Raya</td>
              <td>Rp 4.500.000</td>
              <td>26 Jul 2026</td>
              <td><span class="badge success">Berhasil</span></td>
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
