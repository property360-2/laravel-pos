# REST API Specification (StockFlow POS)

This document defines the HTTP API routes, request query parameters, payload bodies, and status codes for the StockFlow POS application.

---

## Authentication (`/api/*` or Web Routes)

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/login` | Authenticate user & start session | No |
| `POST` | `/api/logout` | Terminate session | Yes |
| `GET` | `/api/user` | Fetch current logged-in user profile | Yes |

---

## Products (`/api/products`)

| Method | Endpoint | Description | Query Params / Body |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/products` | List products | `?search=&category_id=&low_stock=true&per_page=15` |
| `POST` | `/api/products` | Create product | Body: `{ category_id, name, sku, price, stock_quantity, low_stock_threshold }` |
| `GET` | `/api/products/{id}` | Single product details | N/A |
| `PUT` | `/api/products/{id}` | Update product | Body: `{ category_id, name, sku, price, low_stock_threshold }` |
| `DELETE`| `/api/products/{id}` | Soft delete product | N/A |

---

## Categories (`/api/categories`)

| Method | Endpoint | Description | Query Params / Body |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/categories` | List categories | `?search=&with_count=true` |
| `POST` | `/api/categories` | Create category | Body: `{ name }` |
| `GET` | `/api/categories/{id}`| Single category | N/A |
| `PUT` | `/api/categories/{id}`| Update category | Body: `{ name }` |
| `DELETE`| `/api/categories/{id}`| Soft delete category | N/A |

---

## Transactions & POS (`/api/transactions`)

| Method | Endpoint | Description | Query Params / Body |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/transactions` | List transaction history | `?search=&start_date=&end_date=&per_page=15` |
| `POST` | `/api/transactions` | Process POS Sale & Deduct Stock | Body: `{ items: [{ product_id, quantity }], discount, payment_method, amount_paid }` |
| `GET` | `/api/transactions/{id}`| Transaction detail & receipt payload | N/A |

**`POST /api/transactions` Validation & Execution Rules:**
- Server verifies each item's current stock inside a Database Transaction (`DB::transaction()`).
- Returns `422 Unprocessable Entity` if any item stock < requested quantity.
- Deducts product stock atomically.
- Records a `stock_movement` entry (`type: 'sale'`).
- Returns `201 Created` with full transaction object, change amount, and item details.

---

## Inventory Management (`/api/inventory`)

| Method | Endpoint | Description | Query Params / Body |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/inventory` | List product inventory statuses | `?search=&low_stock_only=true` |
| `GET` | `/api/inventory/low-stock` | Products below low stock threshold | N/A |
| `POST` | `/api/inventory/stock-in` | Restock items | Body: `{ product_id, quantity, reason }` |
| `POST` | `/api/inventory/adjust` | Manual stock adjustment | Body: `{ product_id, quantity, reason }` |
| `GET` | `/api/inventory/movements` | Audit log of stock movements | `?product_id=&type=&start_date=&end_date=` |

---

## Reports & Analytics (`/api/reports`)

| Method | Endpoint | Description | Query Params |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/dashboard` | KPI metrics summary | `?period=today|week|month` |
| `GET` | `/api/reports/sales-summary` | Aggregate sales metrics | `?start_date=&end_date=` |
| `GET` | `/api/reports/best-selling` | Top products by quantity sold | `?limit=10&start_date=&end_date=` |
| `GET` | `/api/reports/low-stock` | Alert report for low stock items | N/A |
| `GET` | `/api/reports/inventory-value` | Valuation (stock * price) | N/A |
| `GET` | `/api/reports/stock-movement` | Movement totals (in vs sold vs adj) | `?start_date=&end_date=` |
