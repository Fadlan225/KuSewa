<template>
  <AdminLayout>
    <template #header>
      <h1>Validasi Pembayaran</h1>
      <p>Verifikasi bukti pembayaran transaksi pengguna</p>
    </template>
    <section class="section">
      <div class="card-base">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID Transaksi</th>
              <th>Metode</th>
              <th>Total</th>
              <th>Waktu Bayar</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#TRX-9002</td>
              <td>Transfer Bank BCA</td>
              <td>Rp 2.500.000</td>
              <td>27 Jul 2026 09:12</td>
              <td><span class="badge warning">Menunggu</span></td>
              <td>
                <button class="btn btn-sm btn-primary"><i class="fa-solid fa-eye"></i> Cek Bukti</button>
                <button class="btn btn-sm btn-success" style="margin-left: 4px"><i class="fa-solid fa-check"></i></button>
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
