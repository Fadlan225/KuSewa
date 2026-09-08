import { test, expect } from '@playwright/test';

test('Admin can navigate and verify Konfigurasi Aset pages', async ({ page }) => {
  // Login
  await page.goto('http://127.0.0.1:8000/login');
  await page.fill('input[type="email"]', 'Admin@kitasewa.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');

  // Wait for dashboard to load
  await expect(page).toHaveURL(/.*dashboard/);

  // Navigate to Kategori & Tipe Aset
  await page.goto('http://127.0.0.1:8000/admin/konfigurasi-aset/kategori-tipe');
  await expect(page.locator('h1, h2, h3').filter({ hasText: 'Kategori & Tipe Aset' }).first()).toBeVisible();
  await expect(page.locator('text=Daftar Tipe Aset')).toBeVisible();

  // Navigate to Fasilitas Aset
  await page.goto('http://127.0.0.1:8000/admin/konfigurasi-aset/fasilitas');
  await expect(page.locator('h1, h2, h3').filter({ hasText: 'Fasilitas Aset' }).first()).toBeVisible();
  await expect(page.locator('text=Fasilitas Wajib')).toBeVisible();
  await expect(page.locator('text=Kelola Master Fasilitas')).toBeVisible();
});
