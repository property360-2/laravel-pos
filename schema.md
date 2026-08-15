# Database Schema Specification (StockFlow POS)

This document defines the relational database schema, data types, indexes, and relationships for the StockFlow Laravel POS application.

---

## Tables Overview

1. `users`
2. `categories`
3. `products`
4. `transactions`
5. `transaction_items`
6. `stock_movements`

---

### 1. `users`

Handles authentication and staff roles.

```text
users
--------------------------------------------------
id                   bigint unsigned PK AUTO_INCREMENT
name                 string(255)
email                string(255) UNIQUE
role                 enum('admin', 'cashier') DEFAULT 'cashier'
email_verified_at    timestamp NULLABLE
password             string(255)
remember_token       string(100) NULLABLE
created_at           timestamp
updated_at           timestamp
```

---

### 2. `categories`

Product categorization.

```text
categories
--------------------------------------------------
id                   bigint unsigned PK AUTO_INCREMENT
name                 string(255) UNIQUE
created_at           timestamp
updated_at           timestamp
deleted_at           timestamp NULLABLE (Soft Deletes)
```

**Relationship:**
- Category `hasMany` Product

---

### 3. `products`

Main inventory table.

```text
products
--------------------------------------------------
id                   bigint unsigned PK AUTO_INCREMENT
category_id          bigint unsigned FK -> categories.id (onDelete RESTRICT)
name                 string(255)
sku                  string(100) UNIQUE
price                decimal(12,2)
stock_quantity       integer DEFAULT 0
low_stock_threshold integer DEFAULT 5
created_at           timestamp
updated_at           timestamp
deleted_at           timestamp NULLABLE (Soft Deletes)
```

**Indexes:**
- `UNIQUE(sku)`
- `INDEX(category_id)`
- `INDEX(stock_quantity)`

**Relationships:**
- Product `belongsTo` Category
- Product `hasMany` TransactionItem
- Product `hasMany` StockMovement

---

### 4. `transactions`

Sales history header (one record per completed checkout).

```text
transactions
--------------------------------------------------
id                   bigint unsigned PK AUTO_INCREMENT
transaction_number   string(100) UNIQUE
user_id              bigint unsigned FK -> users.id (Cashier)
subtotal             decimal(12,2)
discount             decimal(12,2) DEFAULT 0.00
total                decimal(12,2)
payment_method       enum('cash', 'gcash', 'card') DEFAULT 'cash'
amount_paid          decimal(12,2)
change_amount        decimal(12,2) DEFAULT 0.00
created_at           timestamp
updated_at           timestamp
```

**Indexes:**
- `UNIQUE(transaction_number)`
- `INDEX(user_id)`
- `INDEX(created_at)`

**Relationships:**
- Transaction `belongsTo` User (Cashier)
- Transaction `hasMany` TransactionItem

---

### 5. `transaction_items`

Line items recorded for each transaction.

```text
transaction_items
--------------------------------------------------
id                   bigint unsigned PK AUTO_INCREMENT
transaction_id       bigint unsigned FK -> transactions.id (onDelete CASCADE)
product_id           bigint unsigned FK -> products.id (onDelete SET NULL) NULLABLE
product_name         string(255) (Historical snapshot)
quantity             integer
unit_price           decimal(12,2) (Historical snapshot)
subtotal             decimal(12,2)
created_at           timestamp
updated_at           timestamp
```

**Relationships:**
- TransactionItem `belongsTo` Transaction
- TransactionItem `belongsTo` Product

---

### 6. `stock_movements`

Audit log for stock adjustments, deliveries, and sales deductions.

```text
stock_movements
--------------------------------------------------
id                   bigint unsigned PK AUTO_INCREMENT
product_id           bigint unsigned FK -> products.id (onDelete CASCADE)
user_id              bigint unsigned FK -> users.id (onDelete SET NULL) NULLABLE
type                 enum('stock_in', 'sale', 'adjustment')
quantity             integer (Positive for in/adjustment+, Negative for sale/adjustment-)
reason               string(255) NULLABLE
created_at           timestamp
updated_at           timestamp
```

**Relationships:**
- StockMovement `belongsTo` Product
- StockMovement `belongsTo` User

---

## Entity Relationship Diagram

```text
       users
       ├── 1:* ──> transactions ── 1:* ──> transaction_items
       └── 1:* ──> stock_movements               │
                                                 │ *:1
categories ── 1:* ──> products ──────────────────┘
                         │
                         └── 1:* ──> stock_movements
```
