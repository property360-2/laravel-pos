# Client-Side Architecture & UI Specification (StockFlow POS)

This document outlines the user interface structure, responsive layouts, client-side state management (Alpine.js), modal interactions, and component composition.

---

## 1. Global Navigation & Layout

Every authenticated page shares a unified master layout (`resources/views/layouts/app.blade.php`).

```text
┌──────────────────────────────────────────────┐
│ Sidebar (Desktop/Tablet) │ Topbar            │
│                          │                   │
│ 📦 StockFlow             │ Page Title        │
│                          │ Cashier Info      │
│ 📊 Dashboard             ├───────────────────┤
│ 🛒 POS ⭐                │ Content View      │
│ 🏷️ Products              │                   │
│ 🗂️ Categories             │                   │
│ 📦 Inventory             │                   │
│ 🧾 Transactions          │                   │
│ 📈 Reports               │                   │
│ ⚙️ Settings              │                   │
│                          │                   │
│ 🚪 Logout                │                   │
└──────────────────────────────────────────────┘
```

### Responsive System
- **Desktop (≥ 1024px):** Fixed left sidebar + topbar + full-width main view.
- **Tablet (768px - 1023px):** Collapsible sidebar toggle + topbar.
- **Mobile (< 768px):** Top header with burger menu + bottom mobile navigation bar for quick access to POS & Dashboard.

---

## 2. Authentication (Login)

Lightweight, clean centered card interface.

- **Fields:** Email, Password, Remember Me.
- **States:** Default, Loading (Spinner on Login button), Error Banners (Invalid credentials / Validation errors), Success (Redirect to `/dashboard` or `/pos`).

---

## 3. Dashboard

First impressive page highlighting live operational metrics.

### Key Metrics Summary Cards
- **Total Products:** Total count of active catalog items.
- **Low Stock Alerts:** Items requiring immediate restock (badge highlighted red).
- **Inventory Valuation:** Sum of (`stock_quantity * price`) across all products.
- **Today's Sales:** Total ₱ revenue & transaction count for the current day.

### Quick Charts / Lists
- **Best Selling Products:** List of top 5 items sold.
- **Critical Stock Warnings:** Top 5 lowest stock items with quick `[ Restock ]` action trigger.

---

## 4. POS (Point of Sale) ⭐

The primary transactional interface, built reactively using **Alpine.js**.

### Desktop Split-Screen Layout

```text
┌───────────────────────────────────────────┬─────────────────────────────────────┐
│ Products Panel                            │ Cart & Checkout Panel               │
│                                           │                                     │
│ [ 🔍 Search products... ] [ Categories ▾] │ 🛒 Cart Items                       │
│                                           │ ─────────────────────────────────── │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐    │ Coca-Cola 330ml  x2        ₱50.00   │
│ │ Coke     │ │ Bread    │ │ Milk     │    │ [ - ] 2 [ + ] [ 🗑️ ]                 │
│ │ ₱25.00   │ │ ₱40.00   │ │ ₱30.00   │    │                                     │
│ │ Stock: 48│ │ Stock: 12│ │ Stock: 3 │    │ Bread            x1        ₱40.00   │
│ └──────────┘ └──────────┘ └──────────┘    │ [ - ] 1 [ + ] [ 🗑️ ]                 │
│                                           │ ─────────────────────────────────── │
│                                           │ Subtotal:                   ₱90.00  │
│                                           │ Discount (₱ / %):         [ ₱0.00 ] │
│                                           │ Total:                      ₱90.00  │
│                                           │                                     │
│                                           │ [ 💳 Pay & Checkout (₱90.00) ]      │
└───────────────────────────────────────────┴─────────────────────────────────────┘
```

### Mobile Layout (< 768px)
- **Product View:** Full screen product search + grid.
- **Sticky Bottom Cart Trigger:** Floating bar displaying `Cart (3 items) - ₱90.00 -> [ View Cart ]`.
- **Slide-Over Drawer:** Clicking trigger opens full-screen Cart & Checkout drawer.

### Checkout & Payment Modal Flow

When user clicks `[ Pay & Checkout ]`:

```text
┌──────────────────────────────────────────────────────────┐
│ 💳 Checkout & Payment                                    │
│ ──────────────────────────────────────────────────────── │
│ Total Amount Due:                             ₱90.00     │
│                                                          │
│ Payment Method:                                          │
│ [ (•) Cash ]   [ ( ) GCash / E-Wallet ]   [ ( ) Card ]   │
│                                                          │
│ Amount Received:                                         │
│ [ ₱100.00                             ]                  │
│                                                          │
│ Quick Cash Buttons:  [ ₱90 ] [ ₱100 ] [ ₱500 ] [ ₱1000 ]  │
│                                                          │
│ Change Given:                                 ₱10.00     │
│                                                          │
│ [ Cancel ]                      [ 🖨️ Complete & Print ] │
└──────────────────────────────────────────────────────────┘
```

### Thermal Receipt Preview & Printing

Upon successful POST to `/api/transactions`:
1. Modal displays a thermal receipt view (80mm width).
2. Triggers `@media print` standard browser print window.
3. Automatically resets POS cart state.

```text
==================================================
                 STOCKFLOW STORE                  
               123 Business Street                
               TEL: (02) 8123-4567                
==================================================
Receipt #: TXN-20260815-0015
Date: 2026-08-15 22:45
Cashier: John Doe
--------------------------------------------------
Item                  Qty     Price       Subtotal
--------------------------------------------------
Coca-Cola 330ml        2     ₱25.00         ₱50.00
Bread 500g             1     ₱40.00         ₱40.00
--------------------------------------------------
Subtotal:                                   ₱90.00
Discount:                                    ₱0.00
--------------------------------------------------
TOTAL:                                      ₱90.00
Payment Method:                               CASH
Amount Paid:                               ₱100.00
CHANGE:                                     ₱10.00
==================================================
            THANK YOU FOR YOUR PURCHASE!          
```

---

## 5. Products Management

- **Data Table:** Product Name, SKU, Category, Price, Stock Quantity, Low Stock Threshold, Status Badge (In Stock / Low Stock / Out of Stock), Actions.
- **Features:** Client-side & AJAX real-time search, category dropdown filter.
- **Modals:**
  - **Create Product:** Name, SKU (with Auto-Generate button), Category, Price, Stock Quantity, Low Stock Threshold.
  - **Edit Product:** Update attributes.
  - **Delete Confirmation:** Soft-deletes product safely without corrupting historical transactions.

---

## 6. Categories Management

- Simple, fast CRUD table with category name & active product count badge.
- Inline modal for Create / Edit.

---

## 7. Inventory Management

Focuses strictly on **stock accuracy and audit trails**.

- **Tabs / Views:**
  1. **Current Stock Table:** Filter by Low Stock Only. Shows quick action buttons: `[ Stock In ]`, `[ Adjust Stock ]`.
  2. **Stock Movements Audit Log:** Table showing Date, Product, Movement Type (`stock_in`, `sale`, `adjustment`), Quantity (+/-), Reason, and Cashier/User responsible.
- **Stock-In Modal:** Product Select, Quantity (+), Supplier/Delivery Reason.
- **Stock Adjustment Modal:** Product Select, Adjustment Quantity (+/-), Reason (e.g., Damaged, Expired, Inventory Audit).

---

## 8. Transactions History

- **Data Table:** Transaction #, Cashier Name, Items Count, Payment Method, Total Amount, Date & Time.
- **Filters:** Search by Transaction #, Filter by Date Range (Start Date / End Date).
- **Detail Slide-over Modal:** Shows itemized breakdown, discount, amount paid, change, and `[ Re-print Receipt ]` button.

---

## 9. Reports & Analytics

Single tabbed dashboard page featuring printable/exportable analytical summaries:

1. **Sales Summary Card:** Total Revenue, Total Transactions, Average Basket Value.
2. **Best Selling Products Table:** Products ranked by quantity sold and gross revenue generated.
3. **Low Stock Alert Report:** List of items at or below low stock threshold.
4. **Inventory Valuation Table:** Category breakdown of total stored asset values (`stock * price`).
5. **Stock Movement Ledger:** Aggregate inputs vs outputs over chosen date ranges.

---

## 10. Settings

App & store configurations:
- **Store Name & Address:** Displays on printed thermal receipts.
- **Tax Rate & Currency:** Currency symbol (`₱`), Tax inclusion setting.
- **Thermal Receipt Header & Footer Message.**

---

## 11. Core UI States & Components

Every view standardizes five atomic states:
1. **Loading:** Skeleton placeholders or subtle spinner overlays.
2. **Empty:** Illustrated empty state cards with quick creation CTA buttons.
3. **Error:** Toast notification popups & inline validation alerts.
4. **Success:** Green toast popups (`✓ Product saved successfully`).
5. **Confirmation Modal:** Guard rails before soft deletion or stock adjustments.

---

## 12. Client-Side Directory Structure (Blade + Alpine.js)

```text
resources/
├── views/
│   ├── components/            # Atomic Reusable UI Components
│   │   ├── button.blade.php
│   │   ├── input.blade.php
│   │   ├── modal.blade.php
│   │   ├── table.blade.php
│   │   └── toast.blade.php
│   │
│   ├── layouts/
│   │   ├── app.blade.php      # Main Authenticated Layout
│   │   └── guest.blade.php    # Auth Layout
│   │
│   ├── auth/
│   │   └── login.blade.php
│   ├── dashboard/
│   │   └── index.blade.php
│   ├── pos/
│   │   ├── index.blade.php
│   │   └── receipt-modal.blade.php
│   ├── products/
│   │   └── index.blade.php
│   ├── categories/
│   │   └── index.blade.php
│   ├── inventory/
│   │   ├── index.blade.php
│   │   └── movements.blade.php
│   ├── transactions/
│   │   └── index.blade.php
│   ├── reports/
│   │   └── index.blade.php
│   └── settings/
│       └── index.blade.php
│
└── js/
    ├── app.js                 # Main bundle (Imports Alpine.js & Axios)
    └── pos-cart.js            # Alpine.js reactive cart component logic
```
