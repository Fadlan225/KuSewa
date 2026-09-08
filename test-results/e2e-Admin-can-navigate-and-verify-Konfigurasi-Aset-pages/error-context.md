# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: e2e.test.js >> Admin can navigate and verify Konfigurasi Aset pages
- Location: e2e.test.js:3:1

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: page.fill: Test timeout of 30000ms exceeded.
Call log:
  - waiting for locator('input[type="password"]')

```

# Page snapshot

```yaml
- generic [ref=e6]:
  - link "KitaSewa Logo kitasewa.id" [ref=e7] [cursor=pointer]:
    - /url: http://127.0.0.1:8000/
    - img "KitaSewa Logo" [ref=e8]
    - generic [ref=e9]: kitasewa.id
  - generic [ref=e10]:
    - generic [ref=e11]:
      - heading "Temukan Beragam Aset Properti Dalam Satu Platform." [level=1] [ref=e12]
      - paragraph [ref=e13]: Temukan lahan, gedung, gudang, baliho, hunian, dan berbagai aset lainnya dengan mudah.
    - generic [ref=e14]:
      - link "Ke Halaman Utama KitaSewa" [ref=e15] [cursor=pointer]:
        - /url: http://127.0.0.1:8000/
      - generic [ref=e21]:
        - heading "Selamat Datang di KitaSewa" [level=3] [ref=e22]
        - paragraph [ref=e23]: Masukkan email Anda untuk masuk atau mendaftar.
        - generic [ref=e24]:
          - generic [ref=e25]:
            - generic [ref=e26]: Email
            - textbox "Email" [active] [ref=e27]:
              - /placeholder: example@gmail.com
              - text: Admin@kitasewa.com
          - button "Lanjutkan" [ref=e28]
          - generic [ref=e29]: atau
          - link [ref=e33] [cursor=pointer]:
            - /url: http://127.0.0.1:8000/auth/google/redirect
            - img "Google" [ref=e34]
            - text: Lanjutkan dengan Google
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | test('Admin can navigate and verify Konfigurasi Aset pages', async ({ page }) => {
  4  |   // Login
  5  |   await page.goto('http://127.0.0.1:8000/login');
  6  |   await page.fill('input[type="email"]', 'Admin@kitasewa.com');
> 7  |   await page.fill('input[type="password"]', 'password');
     |              ^ Error: page.fill: Test timeout of 30000ms exceeded.
  8  |   await page.click('button[type="submit"]');
  9  | 
  10 |   // Wait for dashboard to load
  11 |   await expect(page).toHaveURL(/.*dashboard/);
  12 | 
  13 |   // Navigate to Kategori & Tipe Aset
  14 |   await page.goto('http://127.0.0.1:8000/admin/konfigurasi-aset/kategori-tipe');
  15 |   await expect(page.locator('h1, h2, h3').filter({ hasText: 'Kategori & Tipe Aset' }).first()).toBeVisible();
  16 |   await expect(page.locator('text=Daftar Tipe Aset')).toBeVisible();
  17 | 
  18 |   // Navigate to Fasilitas Aset
  19 |   await page.goto('http://127.0.0.1:8000/admin/konfigurasi-aset/fasilitas');
  20 |   await expect(page.locator('h1, h2, h3').filter({ hasText: 'Fasilitas Aset' }).first()).toBeVisible();
  21 |   await expect(page.locator('text=Fasilitas Wajib')).toBeVisible();
  22 |   await expect(page.locator('text=Kelola Master Fasilitas')).toBeVisible();
  23 | });
  24 | 
```