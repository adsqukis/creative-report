# DEPLOY KE GITHUB + RAILWAY

Domain `creative.qukis.id` di Hostinger **tetap jalan**, ini deployment paralel di Railway.

---

## YANG SUDAH DIPERBAIKI DI PAKET INI

Sebelum baca langkah-langkah, ini yang berubah dari versi Hostinger kamu:

1. **Secrets dikeluarkan dari kode.** `config/database.php`, `config/ai.php`, `config/fonnte.php` sekarang baca dari environment variable, bukan hardcode. Password DB dan API key DeepSeek yang sebelumnya plaintext di file — **sudah dihapus dari kode**, kamu isi ulang nanti di dashboard Railway.
2. **`migrations/002_security_settings.sql` diperbaiki.** Sintaks `ADD INDEX IF NOT EXISTS` itu khusus MariaDB (Hostinger) — MySQL asli yang dipakai Railway tidak mengenalinya, akan error. Sudah diganti ke sintaks standar.
3. **`migrate.php` ditambahkan.** Script CLI yang menjalankan semua file di `migrations/` otomatis, dan aman dijalankan berkali-kali (tidak akan re-run migration yang sudah pernah jalan).
4. **Dockerfile + cron di dalam container.** Railway tidak punya cPanel cron — jadi cron job sekarang jalan di dalam container yang sama lewat `supervisord`, jadwalnya sama seperti sebelumnya.
5. **`.gitignore`** memastikan file upload asli kamu (`storage/uploads/`) dan log tidak ikut ke GitHub — itu data produksi, bukan source code.

---

## LANGKAH 1 — INIT GIT & PUSH KE GITHUB

Buka terminal di folder hasil extract zip ini:

```bash
cd creative-ops
git init
git add .
git commit -m "Initial commit - Creative Ops"
```

Buat repo baru di GitHub (lewat web, **pilih Private** — ini aplikasi internal, bukan open source):
👉 https://github.com/new

Setelah repo dibuat, GitHub akan kasih kamu command seperti ini — jalankan:

```bash
git remote add origin https://github.com/USERNAME/creative-ops.git
git branch -M main
git push -u origin main
```

> **Cek dulu sebelum push:** jalankan `git status` dan pastikan tidak ada file `config/database.php` versi lama dengan password asli ikut ter-stage. File yang sudah saya kirim ke kamu sudah bersih, tapi kalau kamu sempat edit manual sebelum git init, double-check.

---

## LANGKAH 2 — BUAT PROJECT DI RAILWAY

1. Login ke [railway.app](https://railway.app)
2. **New Project** → **Deploy from GitHub repo**
3. Pilih repo `creative-ops` yang baru di-push
4. Railway akan otomatis mendeteksi `Dockerfile` di root dan build dari situ (karena ada `railway.json` yang eksplisit menunjuk ke Dockerfile)
5. Build pertama akan gagal/restart-loop — itu normal, karena belum ada database. Lanjut ke Langkah 3.

---

## LANGKAH 3 — TAMBAH MYSQL

1. Di project Railway yang sama, klik **+ New** → **Database** → **Add MySQL**
2. Railway otomatis generate kredensial dan inject sebagai environment variable ke service lain di project yang sama — **tapi hanya kalau kamu reference-kan**. Klik service aplikasi kamu → tab **Variables** → klik **+ New Variable** → pilih **Add Reference** → pilih variable dari service MySQL satu per satu:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`

> Kalau Railway versi kamu menyediakan tombol "Add all variables from MySQL", itu lebih cepat — pakai itu.

---

## LANGKAH 4 — SET ENVIRONMENT VARIABLES LAINNYA

Di tab **Variables** service aplikasi (bukan service MySQL), tambahkan:

```
APP_URL=https://NAMA-PROJECT-KAMU.up.railway.app
APP_ENV=production
APP_DEBUG=0
AI_PROVIDER=deepseek
DEEPSEEK_API_KEY=sk-xxxxxxxxxxxxxxxx
DEEPSEEK_MODEL=deepseek-chat
```

> Untuk `APP_URL`: deploy dulu sekali, Railway akan kasih kamu domain `*.up.railway.app` di tab **Settings → Networking → Generate Domain**. Salin domain itu, baru isi `APP_URL`, lalu redeploy.

> **DEEPSEEK_API_KEY** isi dengan key yang sama dari Hostinger kamu (`sk-ea8f9...`) — atau bikin key baru di platform.deepseek.com kalau mau pisahkan biaya/limit antara dua environment.

Fonnte token **tidak perlu** diisi di sini — kelola dari halaman `/settings` setelah login, seperti yang sudah berjalan sekarang.

---

## LANGKAH 5 — TAMBAH VOLUME (supaya file upload tidak hilang)

Tanpa ini, semua file yang diupload designer akan **hilang setiap kali kamu redeploy**.

1. Klik service aplikasi kamu di Railway
2. Tab **Settings** → scroll ke **Volumes** → **+ New Volume**
3. Mount path: 
   ```
   /var/www/html/storage/uploads
   ```
4. Save — Railway akan redeploy otomatis

---

## LANGKAH 6 — JALANKAN MIGRATION (bikin semua tabel)

Install [Railway CLI](https://docs.railway.app/guides/cli) kalau belum:

```bash
npm i -g @railway/cli
railway login
railway link
```

Pilih project & service aplikasi kamu saat diminta, lalu jalankan migration:

```bash
railway run php migrate.php
```

Output yang diharapkan:
```
RUN   001_creative_ops.sql ... OK
RUN   002_security_settings.sql ... OK

2 migration berhasil dijalankan.
```

Kalau dijalankan ulang nanti (misal setelah nambah migration baru), yang sudah jalan otomatis di-skip:
```
SKIP  001_creative_ops.sql (sudah pernah dijalankan)
SKIP  002_security_settings.sql (sudah pernah dijalankan)
```

---

## LANGKAH 7 — MIGRASI DATA LAMA DARI HOSTINGER

Kamu pilih opsi ini, jadi datanya perlu dipindah manual (Railway tidak bisa akses DB Hostinger kamu langsung).

### 7.1 — Export dari Hostinger

1. Login ke phpMyAdmin Hostinger
2. Pilih database `creative.qukis.id` kamu
3. Tab **Export** → Format: **SQL** → centang **Structure + Data**
4. Klik **Go** → file `.sql` terdownload ke komputer kamu

### 7.2 — Import ke Railway MySQL

Dapatkan kredensial koneksi eksternal dari Railway:
1. Klik service **MySQL** di Railway
2. Tab **Connect** → salin **Connection URL** atau lihat masing-masing variable (`MYSQLHOST`, `MYSQLPORT`, dst — gunakan yang versi **public/external**, bukan internal, karena kamu connect dari komputer sendiri)

Lalu dari terminal komputer kamu (perlu `mysql` client terinstall):

```bash
mysql -h <MYSQLHOST> -P <MYSQLPORT> -u <MYSQLUSER> -p<MYSQLPASSWORD> <MYSQLDATABASE> < hasil_export_hostinger.sql
```

> **Penting:** Karena tabel sudah dibuat oleh `migrate.php` di Langkah 6, import ini akan kasih error "table already exists" kalau file export Hostinger kamu juga punya `CREATE TABLE`. Dua opsi:
> - **Opsi A (disarankan):** Skip Langkah 6, langsung import full dump Hostinger ke Railway MySQL yang masih kosong. Migration tracking (`co_schema_migrations`) tidak akan ada, tapi itu oke — cukup buat manual: `INSERT INTO co_schema_migrations (filename) VALUES ('001_creative_ops.sql'), ('002_security_settings.sql');` supaya `migrate.php` tidak bingung di masa depan.
> - **Opsi B:** Export hanya **data** (uncheck struktur) dari phpMyAdmin, lalu `INSERT` ke tabel yang sudah dibuat `migrate.php`.

### 7.3 — File upload lama

File-file yang sudah diupload di Hostinger (`storage/uploads/`) **tidak otomatis ikut pindah** — itu file fisik di server Hostinger, bukan bagian dari database. Kalau masih dibutuhkan, upload ulang manual via UI Creative Ops setelah live di Railway, atau pakai `scp`/FTP untuk salin manual ke Volume Railway (lebih teknis, tanya saya kalau perlu).

---

## LANGKAH 8 — UPDATE PASSWORD ADMIN

Migration membuat user admin default dengan password `Admin@2026` — **kalau kamu pilih migrasi data lama**, user-user asli kamu sudah ada lengkap dengan password masing-masing, jadi langkah ini bisa diskip. Kalau Railway DB-nya fresh tanpa data lama, login pakai:

```
Email    : admin@creative-ops.local
Password : Admin@2026
```

Lalu ganti password segera dari halaman Profile.

---

## LANGKAH 9 — VERIFIKASI CRON BERJALAN

Cron jalan di dalam container yang sama (lewat `supervisord` + `cron` daemon), bukan fitur cron Railway terpisah. Untuk cek jalan atau tidak:

1. Railway dashboard → service aplikasi → tab **Logs** (deploy logs / runtime logs)
2. Tunggu sampai jam yang sesuai jadwal (misal `*/30 * * * *` cek tiap 30 menit), atau
3. SSH masuk container untuk trigger manual:
   ```bash
   railway run php cron/sla_check.php
   ```
   Kalau tidak ada error muncul = berhasil.
4. Cek log tersimpan: `railway run cat storage/logs/cron_$(date +%Y-%m-%d).log`

---

## CHECKLIST FINAL

- [ ] Repo GitHub **private**, sudah di-push, tidak ada secret di history
- [ ] MySQL plugin ditambahkan, variable di-reference ke service app
- [ ] Environment variables lengkap (`APP_URL`, `DEEPSEEK_API_KEY`, dst)
- [ ] Volume terpasang di `/var/www/html/storage/uploads`
- [ ] `railway run php migrate.php` sukses (atau data lama sudah diimport manual)
- [ ] Login berhasil ke domain `*.up.railway.app`
- [ ] Test buat 1 request → upload file → cek file tidak hilang setelah redeploy
- [ ] Fonnte token diisi ulang via halaman `/settings` (token lama di config lama tidak otomatis ikut)
- [ ] Domain Hostinger `creative.qukis.id` dicek masih jalan normal (paralel, tidak terganggu)

---

## TROUBLESHOOTING KHUSUS RAILWAY

**"Application failed to respond"**
Apache belum bind ke `$PORT` yang benar. Cek di Logs apakah `entrypoint.sh` jalan duluan sebelum Apache start. Biasanya self-resolve di build berikutnya.

**Upload file hilang setelah redeploy**
Volume belum terpasang, atau mount path salah. Harus persis `/var/www/html/storage/uploads` (lihat Langkah 5).

**Migration error "table already exists"**
Kamu kemungkinan jalankan `migrate.php` dua jalur berbeda (migration runner + import manual). Lihat catatan di Langkah 7.2.

**Cron tidak jalan sama sekali**
Cek Logs — cari baris dari `supervisord` yang menunjukkan program `cron` start. Kalau tidak ada, kemungkinan build gagal di tahap install `cron`/`supervisor` — cek build logs.

**AI Insight kosong terus**
`DEEPSEEK_API_KEY` belum keisi atau salah di Variables. Selama kosong, sistem otomatis fallback ke analisis deterministik (NullProvider) — itu bukan bug, itu yang dirancang dari awal.
