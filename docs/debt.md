# Fitur Debt & Receivable (Hutang Piutang)

## Ringkasan

Fitur **Debt & Receivable** memungkinkan pengguna untuk mencatat, melacak, dan mengelola hutang (money owed to others) serta piutang (money owed by others) secara terintegrasi dengan sistem transaksi dan saldo akun yang ada.

---

## Domain Model

### `debts` Table

| Kolom           | Tipe                                  | Keterangan                                |
|-----------------|---------------------------------------|-------------------------------------------|
| `id`            | bigint PK                             | Primary key                               |
| `user_id`       | FK → users                            | Pemilik record                            |
| `title`         | string                                | Nama/deskripsi hutang                     |
| `type`          | enum: `debt`, `receivable`            | Tipe: hutang saya / piutang (orang berhutang ke saya) |
| `amount`        | decimal(20,2)                         | Total nominal                             |
| `paid_amount`   | decimal(20,2) default 0               | Total yang sudah dibayar                  |
| `contact_name`  | string nullable                       | Nama kontak (peminjam/pemberi hutang)     |
| `contact_phone` | string nullable                       | Nomor telepon kontak                      |
| `due_date`      | date nullable                         | Tanggal jatuh tempo                       |
| `notes`         | text nullable                         | Catatan tambahan                          |
| `status`        | enum: `unpaid`, `partial`, `paid`     | Status pembayaran (dihitung otomatis)     |

### `debt_payments` Table

| Kolom            | Tipe            | Keterangan                                                |
|------------------|-----------------|-----------------------------------------------------------|
| `id`             | bigint PK       | Primary key                                               |
| `user_id`        | FK → users      | Pemilik record                                            |
| `debt_id`        | FK → debts      | Debt yang dibayar (cascade delete)                        |
| `transaction_id` | FK → transactions nullable | Transaksi expense yang dibuat otomatis (jika akun dipilih) |
| `amount`         | decimal(20,2)   | Nominal pembayaran                                        |
| `payment_date`   | date            | Tanggal pembayaran                                        |
| `notes`          | text nullable   | Catatan pembayaran                                        |

---

## Status Lifecycle

```
        [CREATE]
            │
            ▼
         unpaid
            │
  [Record partial payment]
            │
            ▼
         partial  ◄──── [Delete payment]
            │
  [Record full payment]
            │
            ▼
           paid
```

Status dihitung **otomatis** oleh `DebtService` setiap kali payment dicatat atau dihapus:

| Kondisi                             | Status    |
|-------------------------------------|-----------|
| `paid_amount == 0`                  | `unpaid`  |
| `0 < paid_amount < amount`          | `partial` |
| `paid_amount >= amount`             | `paid`    |

---

## Alur Bisnis

### Mencatat Hutang/Piutang Baru

1. User mengklik **+ Debt** di halaman `/debt`
2. Mengisi form: title, type, amount, contact info (opsional), due date (opsional)
3. `DebtController::store()` → `DebtService::create()`
4. Debt dibuat dengan `paid_amount = 0` dan `status = 'unpaid'`

### Mencatat Pembayaran

1. User mengklik **Record Payment** pada row actions
2. Mengisi: nominal, tanggal, akun (opsional), catatan
3. `DebtPaymentController::store()` → `DebtService::createPayment()`
4. **Jika akun dipilih**: `TransactionService::create()` membuat transaksi `expense` otomatis dan memotong saldo akun
5. `DebtPayment` dibuat dengan `transaction_id` (jika ada)
6. `debt.paid_amount` diincrement
7. Status debt dihitung ulang

### Menghapus Pembayaran

1. User mengklik ikon hapus di detail dialog
2. `DebtPaymentController::destroy()` → `DebtService::deletePayment()`
3. Jika payment memiliki linked transaction → `TransactionService::delete()` merollback saldo akun
4. `debt.paid_amount` dikurangi
5. Status debt dihitung ulang

### Menghapus Debt

- Semua payment dihapus terlebih dahulu (beserta linked transactions)
- Kemudian debt dihapus

---

## Arsitektur

```
routes/web.php
    └── DebtController          (index, store, update, destroy, multipleDestroy)
    └── DebtPaymentController   (store, destroy)
            │
            ▼
        DebtService
            ├── getFilteredDebts()   — paginated + filter
            ├── create()             — buat debt baru
            ├── update()             — update debt + recalculate status
            ├── delete()             — hapus debt + rollback payments
            ├── deleteMany()         — bulk delete
            ├── createPayment()      — catat payment + opsional buat transaction
            ├── deletePayment()      — hapus payment + rollback transaction
            └── recalculateStatus()  — update status berdasarkan paid_amount
                    │
                    ▼ (optional)
            TransactionService::create() / delete()
                    │
                    ▼
            Account balance updated
```

---

## Routes

| Method | URI                             | Name                    | Deskripsi                    |
|--------|---------------------------------|-------------------------|------------------------------|
| GET    | `/debt`                         | `debt.index`            | List semua hutang/piutang    |
| POST   | `/debt`                         | `debt.store`            | Buat baru                    |
| PUT    | `/debt/{debt}`                  | `debt.update`           | Update                       |
| DELETE | `/debt/{debt}`                  | `debt.destroy`          | Hapus (dengan payments)      |
| DELETE | `/debt/multiple`                | `debt.multiple-destroy` | Bulk hapus                   |
| POST   | `/debt/{debt}/payments`         | `debt.payment.store`    | Catat pembayaran             |
| DELETE | `/debt-payment/{debtPayment}`   | `debt.payment.destroy`  | Hapus pembayaran             |

---

## Frontend

```
resources/js/pages/debt/
├── Index.vue                    # Halaman utama — DataTable hutang/piutang
└── partials/
    ├── column.ts                # Definisi kolom TanStack Table
    ├── FormDialog.vue           # Dialog create/edit debt
    ├── PaymentDialog.vue        # Dialog record payment
    ├── DetailDialog.vue         # Dialog detail + riwayat pembayaran
    └── RowActions.vue           # Dropdown actions per baris
```

---

## Computed Attributes (Model)

| Attribute             | Deskripsi                                      |
|-----------------------|------------------------------------------------|
| `remaining_amount`    | `amount - paid_amount` (tidak pernah negatif) |
| `progress`            | Persentase pembayaran (0–100)                  |
| `formatted_due_date`  | Due date dalam format `d M Y` atau `-`         |

---

## Authorization (Policy)

`DebtPolicy` diotomatis-discover oleh Laravel. Semua operasi dibatasi pada user yang memiliki record tersebut (`user_id === auth()->id()`).

---

## Catatan Migrasi

File: `database/migrations/2026_04_25_000000_add_partial_status_to_debts_table.php`

Karena PostgreSQL mengimplementasikan `->enum()` sebagai varchar dengan CHECK constraint, migration ini:
1. Drop constraint lama `debts_status_check`
2. Buat ulang constraint baru yang menyertakan nilai `partial`

---

## Pengembangan Selanjutnya

- [ ] Notifikasi/reminder jika due date sudah dekat atau terlewat
- [ ] Export laporan hutang/piutang ke PDF atau Excel
- [ ] Filter tampilan by type (debt vs receivable) dengan tab
- [ ] Dashboard widget: ringkasan total hutang dan piutang outstanding
- [ ] Recurring debt support (cicilan tetap)
