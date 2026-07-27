<template>
  <AdminLayout>
    <template #header>
      <h1>Selamat datang, Admin Ibni</h1>
      <p>Senin, 27 Juli 2026</p>
    </template>

    <!-- 2. Alert / Prioritas -->
    <section class="section alerts-section">
      <div class="alert-card danger">
        <div class="alert-icon"><i class="fa-solid fa-house"></i></div>
        <div class="alert-info">
          <h3>12 Properti</h3>
          <p>Menunggu Validasi</p>
        </div>
      </div>
      <div class="alert-card warning">
        <div class="alert-icon"><i class="fa-solid fa-credit-card"></i></div>
        <div class="alert-info">
          <h3>5 Pemilik</h3>
          <p>Belum Bayar Bulanan</p>
        </div>
      </div>
      <div class="alert-card orange">
        <div class="alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="alert-info">
          <h3>3 Laporan</h3>
          <p>Pengguna Baru</p>
        </div>
      </div>
      <div class="alert-card info">
        <div class="alert-icon"><i class="fa-solid fa-user"></i></div>
        <div class="alert-info">
          <h3>2 Akun</h3>
          <p>Menunggu Persetujuan</p>
        </div>
      </div>
    </section>

    <!-- 3. Quick Statistics -->
    <section class="section quick-stats-section">
      <div class="stat-card">
        <p class="stat-title">Total Penyewa</p>
        <h2 class="stat-value">1.582</h2>
        <span class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 12% bulan ini</span>
      </div>
      <div class="stat-card">
        <p class="stat-title">Total Pemilik</p>
        <h2 class="stat-value">248</h2>
        <span class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 5% bulan ini</span>
      </div>
      <div class="stat-card">
        <p class="stat-title">Properti Aktif</p>
        <h2 class="stat-value">430</h2>
        <span class="stat-trend neutral"><i class="fa-solid fa-minus"></i> stabil</span>
      </div>
      <div class="stat-card">
        <p class="stat-title">Baliho Aktif</p>
        <h2 class="stat-value">86</h2>
        <span class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 2% bulan ini</span>
      </div>
      <div class="stat-card">
        <p class="stat-title">Booking Hari Ini</p>
        <h2 class="stat-value">48</h2>
        <span class="stat-trend negative"><i class="fa-solid fa-arrow-down"></i> 3% dari kemarin</span>
      </div>
      <div class="stat-card highlight">
        <p class="stat-title">Pendapatan Platform</p>
        <h2 class="stat-value">Rp18.250.000</h2>
        <span class="stat-trend positive"><i class="fa-solid fa-arrow-up"></i> 18% bulan ini</span>
      </div>
    </section>

    <!-- 4. Grafik -->
    <section class="section charts-section">
      <!-- Grafik Transaksi -->
      <div class="chart-card">
        <h3>Grafik Transaksi</h3>
        <p class="chart-subtitle">Transaksi per Bulan</p>
        <div class="bar-chart">
          <div class="bar-col">
            <div class="bar" style="height: 40%"></div>
            <span>Jan</span>
          </div>
          <div class="bar-col">
            <div class="bar" style="height: 60%"></div>
            <span>Feb</span>
          </div>
          <div class="bar-col">
            <div class="bar" style="height: 75%"></div>
            <span>Mar</span>
          </div>
          <div class="bar-col">
            <div class="bar" style="height: 100%"></div>
            <span>Apr</span>
          </div>
        </div>
      </div>

      <!-- Grafik User Baru -->
      <div class="chart-card">
        <h3>Grafik User Baru</h3>
        <p class="chart-subtitle">Penyewa vs Pemilik</p>
        <div class="progress-chart">
          <div class="progress-item">
            <div class="progress-info">
              <span>Penyewa</span>
              <span>78%</span>
            </div>
            <div class="progress-bar-bg">
              <div class="progress-bar-fill primary" style="width: 78%"></div>
            </div>
          </div>
          <div class="progress-item">
            <div class="progress-info">
              <span>Pemilik</span>
              <span>22%</span>
            </div>
            <div class="progress-bar-bg">
              <div class="progress-bar-fill secondary" style="width: 22%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pie Chart -->
      <div class="chart-card">
        <h3>Status Transaksi</h3>
        <div class="pie-container">
          <div class="pie-chart"></div>
          <div class="pie-legend">
            <div class="legend-item"><span class="dot success"></span> Berhasil (65%)</div>
            <div class="legend-item"><span class="dot warning"></span> Pending (25%)</div>
            <div class="legend-item"><span class="dot danger"></span> Ditolak (10%)</div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. Shortcut Menu -->
    <section class="section quick-menu-section">
      <h3>Quick Access</h3>
      <div class="quick-menu-grid">
        <Link :href="route('admin.validasi-akun')" class="quick-btn"><i class="fa-solid fa-clipboard-check icon"></i> Validasi Akun</Link>
        <Link :href="route('admin.validasi-aset')" class="quick-btn"><i class="fa-solid fa-house-circle-check icon"></i> Validasi Properti</Link>
        <Link :href="route('admin.validasi-aset')" class="quick-btn"><i class="fa-solid fa-sign-hanging icon"></i> Validasi Baliho</Link>
        <Link :href="route('admin.validasi-pembayaran')" class="quick-btn"><i class="fa-solid fa-file-invoice-dollar icon"></i> Validasi Pembayaran</Link>
        <Link :href="route('admin.kelola-penyewa')" class="quick-btn"><i class="fa-solid fa-users-gear icon"></i> Kelola Penyewa</Link>
        <Link :href="route('admin.kelola-pemilik')" class="quick-btn"><i class="fa-solid fa-building-user icon"></i> Kelola Pemilik</Link>
        <Link :href="route('admin.promo')" class="quick-btn"><i class="fa-solid fa-gift icon"></i> Promo</Link>
        <Link :href="route('admin.pembayaran')" class="quick-btn"><i class="fa-solid fa-money-check-dollar icon"></i> Pembayaran</Link>
        <Link :href="route('admin.laporan')" class="quick-btn"><i class="fa-solid fa-flag icon"></i> Laporan</Link>
        <Link :href="route('admin.notifikasi')" class="quick-btn"><i class="fa-solid fa-bullhorn icon"></i> Notifikasi</Link>
      </div>
    </section>

    <div class="grid-2-cols">
      <!-- 7. Daftar Tugas Hari Ini -->
      <section class="section tasks-section">
        <h3>Daftar Tugas Hari Ini</h3>
        <div class="table-responsive">
          <table class="tasks-table">
            <thead>
              <tr>
                <th>Tugas</th>
                <th>Jumlah</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Validasi Properti</td>
                <td><span class="badge danger">12</span></td>
                <td><Link :href="route('admin.validasi-aset')" class="btn btn-sm btn-primary">Lihat</Link></td>
              </tr>
              <tr>
                <td>Validasi Akun</td>
                <td><span class="badge warning">3</span></td>
                <td><Link :href="route('admin.validasi-akun')" class="btn btn-sm btn-primary">Lihat</Link></td>
              </tr>
              <tr>
                <td>Validasi Pembayaran</td>
                <td><span class="badge orange">8</span></td>
                <td><Link :href="route('admin.validasi-pembayaran')" class="btn btn-sm btn-primary">Lihat</Link></td>
              </tr>
              <tr>
                <td>Laporan Pengguna</td>
                <td><span class="badge info">5</span></td>
                <td><Link :href="route('admin.laporan')" class="btn btn-sm btn-primary">Lihat</Link></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 6. Aktivitas Terbaru -->
      <section class="section timeline-section">
        <h3>Aktivitas Terbaru</h3>
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-time">09:30</div>
            <div class="timeline-icon success"><i class="fa-solid fa-check"></i></div>
            <div class="timeline-content">Properti "Villa Alam" disetujui</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-time">09:15</div>
            <div class="timeline-icon success"><i class="fa-solid fa-check"></i></div>
            <div class="timeline-content">Pembayaran INV-092 diverifikasi</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-time">08:58</div>
            <div class="timeline-icon danger"><i class="fa-solid fa-xmark"></i></div>
            <div class="timeline-content">Akun "Budi S." ditolak</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-time">08:22</div>
            <div class="timeline-icon info"><i class="fa-solid fa-bullhorn"></i></div>
            <div class="timeline-content">Promo "Kemerdekaan" dibuat</div>
          </div>
        </div>
      </section>
    </div>

    <div class="grid-2-cols split-layout">
      <!-- 8. Properti Populer -->
      <section class="section popular-section">
        <h3>Top Properti Populer</h3>
        <div class="popular-list">
          <div class="popular-item">
            <div class="rank"><i class="fa-solid fa-medal" style="color: #FFD700"></i></div>
            <div class="popular-info">
              <h4>Villa Alam</h4>
              <p>145 Booking</p>
            </div>
            <div class="trend positive"><i class="fa-solid fa-arrow-up"></i></div>
          </div>
          <div class="popular-item">
            <div class="rank"><i class="fa-solid fa-medal" style="color: #C0C0C0"></i></div>
            <div class="popular-info">
              <h4>Hotel Mawar</h4>
              <p>132 Booking</p>
            </div>
            <div class="trend positive"><i class="fa-solid fa-arrow-up"></i></div>
          </div>
          <div class="popular-item">
            <div class="rank"><i class="fa-solid fa-medal" style="color: #CD7F32"></i></div>
            <div class="popular-info">
              <h4>Kost Elite</h4>
              <p>119 Booking</p>
            </div>
            <div class="trend neutral"><i class="fa-solid fa-minus"></i></div>
          </div>
        </div>
      </section>

      <!-- 9. Monitoring Sistem -->
      <section class="section monitoring-section">
        <h3>Monitoring Sistem</h3>
        <div class="monitor-grid">
          <div class="monitor-card">
            <div class="monitor-icon"><i class="fa-solid fa-server"></i></div>
            <div class="monitor-details">
              <span class="monitor-label">Server</span>
              <span class="monitor-status status-success"><i class="fa-solid fa-circle" style="font-size: 10px;"></i> Online</span>
            </div>
          </div>
          <div class="monitor-card">
            <div class="monitor-icon"><i class="fa-solid fa-database"></i></div>
            <div class="monitor-details">
              <span class="monitor-label">Database</span>
              <span class="monitor-status status-success"><i class="fa-solid fa-circle" style="font-size: 10px;"></i> Connected</span>
            </div>
          </div>
          <div class="monitor-card">
            <div class="monitor-icon"><i class="fa-solid fa-floppy-disk"></i></div>
            <div class="monitor-details">
              <span class="monitor-label">Backup Data</span>
              <span class="monitor-status status-info">Terakhir: 2 jam lalu</span>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

<style scoped>
/* Scoped styles specific to the dashboard widgets */
.alerts-section {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.alert-card {
  display: flex;
  align-items: center;
  padding: 20px;
  border-radius: 12px;
  gap: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
}

.alert-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

.alert-card.danger {
  background-color: var(--danger-light);
  border: 1px solid rgba(220,53,69,0.2);
}
.alert-card.danger .alert-icon { color: var(--danger); }
.alert-card.danger h3 { color: var(--danger); }

.alert-card.warning {
  background-color: var(--warning-light);
  border: 1px solid rgba(255,193,7,0.2);
}
.alert-card.warning .alert-icon { color: #d39e00; }
.alert-card.warning h3 { color: #d39e00; }

.alert-card.orange {
  background-color: var(--orange-light);
  border: 1px solid rgba(253,126,20,0.2);
}
.alert-card.orange .alert-icon { color: var(--orange); }
.alert-card.orange h3 { color: var(--orange); }

.alert-card.info {
  background-color: var(--info-light);
  border: 1px solid rgba(23,162,184,0.2);
}
.alert-card.info .alert-icon { color: var(--info); }
.alert-card.info h3 { color: var(--info); }

.alert-icon {
  font-size: 24px;
  background-color: rgba(255,255,255,0.7);
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.alert-info h3 {
  margin: 0 0 4px;
  font-size: 22px;
  font-weight: 700;
}

.alert-info p {
  margin: 0;
  font-size: 13px;
  color: var(--text-main);
  opacity: 0.8;
  font-weight: 500;
}

.quick-stats-section {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 16px;
}

.stat-card {
  background-color: var(--card-bg);
  padding: 20px 16px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
  border: 1px solid var(--border-color);
  text-align: center;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-card.highlight {
  background-color: var(--secondary);
  color: white;
}
.stat-card.highlight .stat-title,
.stat-card.highlight .stat-trend {
  color: rgba(255,255,255,0.8);
}
.stat-card.highlight .stat-trend.positive {
  color: #4ade80; 
}

.stat-title {
  margin: 0 0 10px;
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
}

.stat-value {
  margin: 0 0 10px;
  font-size: 24px;
  font-weight: 700;
}

.stat-trend {
  font-size: 12px;
  font-weight: 600;
}
.stat-trend.positive { color: var(--success); }
.stat-trend.negative { color: var(--danger); }
.stat-trend.neutral { color: var(--text-muted); }

.charts-section {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 20px;
}

.chart-card {
  background-color: var(--card-bg);
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
  border: 1px solid var(--border-color);
}

.chart-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 20px;
}

.bar-chart {
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  height: 150px;
  padding-top: 20px;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 10px;
}

.bar-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  height: 100%;
  justify-content: flex-end;
}

.bar {
  width: 40px;
  background: linear-gradient(180deg, var(--primary) 0%, #d4a000 100%);
  border-radius: 4px 4px 0 0;
  transition: height 0.5s ease;
}

.bar-col span {
  margin-top: 8px;
  font-size: 12px;
  color: var(--text-muted);
}

.progress-chart {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 30px;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 8px;
}

.progress-bar-bg {
  height: 10px;
  background-color: var(--border-color);
  border-radius: 5px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  border-radius: 5px;
}

.progress-bar-fill.primary { background-color: var(--primary); }
.progress-bar-fill.secondary { background-color: var(--secondary); }

.pie-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
  margin-top: 20px;
}

.pie-chart {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: conic-gradient(
    var(--success) 0% 65%,
    var(--warning) 65% 90%,
    var(--danger) 90% 100%
  );
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  position: relative;
}

.pie-chart::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 60px;
  height: 60px;
  background-color: var(--card-bg);
  border-radius: 50%;
}

.pie-legend {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
}

.legend-item {
  display: flex;
  align-items: center;
  font-size: 13px;
  color: var(--text-main);
}

.dot {
  width: 12px;
  height: 12px;
  border-radius: 3px;
  margin-right: 8px;
}

.dot.success { background-color: var(--success); }
.dot.warning { background-color: var(--warning); }
.dot.danger { background-color: var(--danger); }

.quick-menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
}

.quick-btn {
  background-color: var(--card-bg);
  border: 1px solid var(--border-color);
  padding: 16px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  color: var(--secondary);
  text-decoration: none;
  transition: all 0.2s ease;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}

.quick-btn:hover {
  border-color: var(--primary);
  background-color: rgba(255,192,0,0.05);
  transform: translateY(-2px);
}

.quick-btn .icon {
  font-size: 18px;
}

.tasks-section, .timeline-section, .popular-section, .monitoring-section {
  background-color: var(--card-bg);
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
  border: 1px solid var(--border-color);
}

.table-responsive {
  overflow-x: auto;
}

.tasks-table {
  width: 100%;
  border-collapse: collapse;
}

.tasks-table th {
  text-align: left;
  padding: 12px;
  font-size: 13px;
  color: var(--text-muted);
  border-bottom: 2px solid var(--border-color);
}

.tasks-table td {
  padding: 16px 12px;
  border-bottom: 1px solid var(--border-color);
  font-size: 14px;
  font-weight: 500;
}

.tasks-table tr:last-child td {
  border-bottom: none;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 20px;
  position: relative;
}

.timeline::before {
  content: "";
  position: absolute;
  left: 60px;
  top: 10px;
  bottom: 10px;
  width: 2px;
  background-color: var(--border-color);
  z-index: 1;
}

.timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  position: relative;
  z-index: 2;
}

.timeline-time {
  width: 40px;
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
  padding-top: 4px;
}

.timeline-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: white;
  border: 4px solid var(--card-bg);
}

.timeline-icon.success { background-color: var(--success); }
.timeline-icon.danger { background-color: var(--danger); }
.timeline-icon.info { background-color: var(--info); }

.timeline-content {
  background-color: var(--bg-color);
  padding: 12px 16px;
  border-radius: 8px;
  flex: 1;
  font-size: 14px;
  border: 1px solid var(--border-color);
}

.popular-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.popular-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  background-color: var(--bg-color);
  border-radius: 12px;
  border: 1px solid var(--border-color);
  transition: transform 0.2s ease;
}

.popular-item:hover {
  transform: translateX(5px);
  border-color: var(--primary);
}

.rank {
  font-size: 24px;
  margin-right: 16px;
}

.popular-info {
  flex: 1;
}

.popular-info h4 {
  margin: 0 0 4px;
  font-size: 15px;
  color: var(--secondary);
}

.popular-info p {
  margin: 0;
  font-size: 13px;
  color: var(--text-muted);
}

.trend {
  font-weight: bold;
  font-size: 14px;
}
.trend.positive { color: var(--success); }
.trend.neutral { color: var(--text-muted); }

.monitor-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.monitor-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background-color: var(--bg-color);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.monitor-icon {
  font-size: 20px;
  background-color: white;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  color: var(--secondary);
}

.monitor-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.monitor-label {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
}

.monitor-status {
  font-size: 14px;
  font-weight: 600;
}

.status-success { color: var(--success); }
.status-info { color: var(--info); font-size: 13px; }

@media (max-width: 1400px) {
  .quick-stats-section {
    grid-template-columns: repeat(3, 1fr);
  }
  .charts-section {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) {
  .alerts-section {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
