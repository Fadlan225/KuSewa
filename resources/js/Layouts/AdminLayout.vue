<template>
  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <h2>KuSewa</h2>
        <span>Admin Panel</span>
      </div>

      <div class="sidebar-menu">
        <div class="menu-category">Menu Utama</div>
        <Link :href="route('admin.dashboard')" class="menu-item" :class="{ active: route().current('admin.dashboard') }">
          <i class="fa-solid fa-chart-line icon"></i> Dashboard
        </Link>

        <div class="menu-category">User</div>
        <Link :href="route('admin.kelola-penyewa')" class="menu-item" :class="{ active: route().current('admin.kelola-penyewa') }"><i class="fa-solid fa-users icon"></i> Penyewa</Link>
        <Link :href="route('admin.kelola-pemilik')" class="menu-item" :class="{ active: route().current('admin.kelola-pemilik') }"><i class="fa-solid fa-building icon"></i> Pemilik</Link>
        <a href="#" class="menu-item"><i class="fa-solid fa-shield-halved icon"></i> Administrator</a>

        <div class="menu-category">Validasi</div>
        <Link :href="route('admin.validasi-akun')" class="menu-item" :class="{ active: route().current('admin.validasi-akun') }"><i class="fa-solid fa-check-circle icon"></i> Akun</Link>
        <Link :href="route('admin.validasi-aset')" class="menu-item" :class="{ active: route().current('admin.validasi-aset') }"><i class="fa-solid fa-house-circle-check icon"></i> Aset (Properti & Baliho)</Link>
        <Link :href="route('admin.validasi-pembayaran')" class="menu-item" :class="{ active: route().current('admin.validasi-pembayaran') }"><i class="fa-solid fa-credit-card icon"></i> Pembayaran</Link>

        <div class="menu-category">Keuangan</div>
        <Link :href="route('admin.pembayaran')" class="menu-item" :class="{ active: route().current('admin.pembayaran') }"><i class="fa-solid fa-money-bill-wave icon"></i> Transaksi</Link>
        <Link :href="route('admin.promo')" class="menu-item" :class="{ active: route().current('admin.promo') }"><i class="fa-solid fa-gift icon"></i> Promo</Link>

        <div class="menu-category">Laporan & Info</div>
        <Link :href="route('admin.laporan')" class="menu-item" :class="{ active: route().current('admin.laporan') }"><i class="fa-solid fa-chart-bar icon"></i> Laporan</Link>
        <Link :href="route('admin.notifikasi')" class="menu-item" :class="{ active: route().current('admin.notifikasi') }"><i class="fa-solid fa-bell icon"></i> Notifikasi</Link>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-wrapper">
      <!-- Header Dashboard -->
      <header class="header">
        <div class="header-welcome">
          <slot name="header">
            <h1>Selamat datang, Admin</h1>
            <p>Admin Panel</p>
          </slot>
        </div>
        <div class="header-actions">
          <div class="search-box">
            <input type="text" placeholder="Cari data..." />
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
          </div>
          <button class="notification-btn">
            <i class="fa-regular fa-bell"></i> <span class="badge">3</span>
          </button>
          <div class="profile-menu">
            <img src="https://ui-avatars.com/api/?name=Admin&background=FFC000&color=0A2540" alt="Profile" class="avatar" />
            <div class="profile-info">
              <span class="name">Admin Ibni</span>
              <span class="role">Super Admin</span>
            </div>
          </div>
        </div>
      </header>

      <div class="dashboard-content">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style>
/* Base Styles from Dashboard moved here to be global for Admin pages */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

.admin-container {
  --bg-color: #F8F9FA;
  --bg-sidebar: #0A2540;
  --primary: #FFC000;
  --primary-hover: #e5ac00;
  --secondary: #0A2540;
  --text-main: #000000;
  --text-muted: #6C757D;
  --text-light: #FFFFFF;
  --border-color: #E9ECEF;
  --card-bg: #FFFFFF;
  
  --success: #28A745;
  --success-light: #d4edda;
  --warning: #FFC107;
  --warning-light: #fff3cd;
  --danger: #DC3545;
  --danger-light: #f8d7da;
  --info: #17A2B8;
  --info-light: #d1ecf1;
  --orange: #FD7E14;
  --orange-light: #ffebd8;

  font-family: 'Inter', sans-serif;
  display: flex;
  height: 100vh;
  background-color: var(--bg-color);
  color: var(--text-main);
  overflow: hidden;
}

/* Sidebar Styles */
.sidebar {
  width: 260px;
  background-color: var(--bg-sidebar);
  color: var(--text-light);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  overflow-y: auto;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar::-webkit-scrollbar {
  width: 6px;
}
.sidebar::-webkit-scrollbar-thumb {
  background-color: rgba(255,255,255,0.2);
  border-radius: 4px;
}

.sidebar-brand {
  padding: 24px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  text-align: center;
}

.sidebar-brand h2 {
  margin: 0;
  color: var(--primary);
  font-size: 24px;
  font-weight: 700;
  letter-spacing: 1px;
}

.sidebar-brand span {
  font-size: 12px;
  color: rgba(255,255,255,0.6);
  text-transform: uppercase;
  letter-spacing: 2px;
}

.sidebar-menu {
  padding: 16px 0;
}

.menu-category {
  padding: 16px 24px 8px;
  font-size: 11px;
  color: rgba(255,255,255,0.4);
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 12px 24px;
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  font-size: 14px;
  transition: all 0.2s ease;
}

.menu-item .icon {
  margin-right: 12px;
  font-size: 16px;
  width: 20px;
  text-align: center;
  opacity: 0.8;
}

.menu-item:hover, .menu-item.active {
  background-color: rgba(255,255,255,0.05);
  color: var(--text-light);
  border-left: 3px solid var(--primary);
}

.menu-item.active .icon {
  opacity: 1;
  color: var(--primary);
}

/* Main Content Wrapper */
.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Header */
.header {
  height: 80px;
  background-color: var(--card-bg);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  z-index: 10;
}

.header-welcome h1 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: var(--secondary);
}

.header-welcome p {
  margin: 4px 0 0;
  font-size: 13px;
  color: var(--text-muted);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 24px;
}

.search-box {
  position: relative;
}

.search-box input {
  padding: 10px 16px 10px 40px;
  border-radius: 20px;
  border: 1px solid var(--border-color);
  background-color: var(--bg-color);
  font-size: 14px;
  width: 250px;
  transition: all 0.3s ease;
  outline: none;
}

.search-box input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(255,192,0,0.2);
}

.search-box .search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  font-size: 14px;
}

.notification-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  position: relative;
  transition: transform 0.2s ease;
  color: var(--text-main);
}

.notification-btn:hover {
  transform: scale(1.1);
}

.notification-btn .badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background-color: var(--danger);
  color: white;
  font-size: 10px;
  font-weight: bold;
  padding: 2px 5px;
  border-radius: 10px;
  border: 2px solid var(--card-bg);
}

.profile-menu {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

.profile-menu .avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--border-color);
}

.profile-info {
  display: flex;
  flex-direction: column;
}

.profile-info .name {
  font-weight: 600;
  font-size: 14px;
}

.profile-info .role {
  font-size: 12px;
  color: var(--text-muted);
}

/* Dashboard Content Area */
.dashboard-content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.section {
  background-color: transparent;
}

.section h3 {
  margin: 0 0 16px;
  font-size: 16px;
  font-weight: 600;
  color: var(--secondary);
}

/* Common Table Base */
.card-base {
  background-color: var(--card-bg);
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03);
  border: 1px solid var(--border-color);
}
.data-table {
  width: 100%;
  border-collapse: collapse;
}
.data-table th {
  text-align: left;
  padding: 12px;
  font-size: 13px;
  color: var(--text-muted);
  border-bottom: 2px solid var(--border-color);
}
.data-table td {
  padding: 16px 12px;
  border-bottom: 1px solid var(--border-color);
  font-size: 14px;
}
.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}
.badge.success { background-color: var(--success-light); color: var(--success); }
.badge.warning { background-color: var(--warning-light); color: #d39e00; }
.badge.danger { background-color: var(--danger-light); color: var(--danger); }
.badge.info { background-color: var(--info-light); color: var(--info); }
.badge.orange { background-color: var(--orange-light); color: var(--orange); }

.btn {
  padding: 6px 16px;
  border-radius: 6px;
  border: none;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s ease;
  text-decoration: none;
  display: inline-block;
}
.btn:hover { opacity: 0.9; }
.btn-primary { background-color: var(--primary); color: var(--text-main); }
.btn-success { background-color: var(--success); color: white; }
.btn-danger { background-color: var(--danger); color: white; }
.btn-sm { padding: 4px 12px; font-size: 12px; }

/* Grid Helpers */
.grid-2-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.grid-2-cols.split-layout { grid-template-columns: 2fr 1fr; }
@media (max-width: 1024px) {
  .grid-2-cols, .grid-2-cols.split-layout { grid-template-columns: 1fr; }
}
</style>
