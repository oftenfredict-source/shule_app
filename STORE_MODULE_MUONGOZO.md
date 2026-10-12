# ShuleXpert - Store Module: Muhtasari wa Logic & Kilicho Miss

> Hati hii imetengenezwa baada ya kusoma kwa makini: `StoreController.php`, models, migrations, `manage_store.blade.php`, na routes.
> **Lengo:** Uweze kuona kile kilichopo, kile kilichokosekana, na kutoa logic ili tumalize moduli.

---

## 1. Muhtasari - Nini Nimeona (Logic Iliyopo)

Moduli ya **Inventory / Store Management** iko kwenye tab moja kubwa (`manage_store`) na inafanya kazi kama mfumo wa **Admin-managed store** (si self-service request bado).

### Mtiririko mkuu wa data

```
[1] REGISTER ITEM          [2] ADD STOCK              [3] ISSUE (REQUISITION)       [4] RETURN
     │                          │                            │                          │
     ▼                          ▼                            ▼                          ▼
store_items              store_item_stock_entries      store_requisitions         status=returned
store_item_units         store_stock_units             quantity_taken             remaining_items +=
store_item_package_specs   label (unique)              take_level/key             good / defective
  (nested chains)          initial_items               take_display
                           remaining_items
                           package_specID
```

### Kile kilichojengwa vizuri (complete / working)

| Sehemu | Kile kinachofanya |
|--------|-------------------|
| **Item registration** | Category, measurement type, units nyingi, package chains (Set→Carton→Box→Item), chains nyingi kwa item moja |
| **Package chain** | Nested linear chain, realtime preview, block parent reuse kwenye sub-level |
| **Stock intake - Unit** | Quantity + label kwa kila unit |
| **Stock intake - Package** | Multiple package receipts kwa wakati mmoja (Set 50 + Piece 60), total items realtime |
| **Stock intake - Labels** | Kila package/unit ina label ya kipekee (mfano Set-01) |
| **Requisition - Recipient** | Teacher, Staff, **Student** (na class) |
| **Requisition - Issue** | Chagua label → Request method (Main / Sub-package / Item inside) |
| **Requisition - Sub-package** | Checkboxes, quantity kwa kila level, multiple issues kwa submit moja |
| **Requisition - Partial** | `remaining_items` inapungua; stock haiondoki kabisa mpaka imeisha |
| **Requisition - Return** | Good (restock) / Bad (defective), tarehe, maelezo, overdue badge |
| **Can return / Can't return** | Filter, expected return date, return modal |
| **Report** | **All Inventory Report** tu - filters, summary, table, historical date |
| **UI** | DataTables, AJAX, SweetAlert2, loading bar, View More widgets |

### Jedwali la database (muhimu)

| Table | Jukumu |
|-------|--------|
| `store_items` | Bidhaa + `has_packages` |
| `store_item_package_specs` | Chain: `parent_specID`, `has_sub_package`, `quantity`, `content_quantity` |
| `store_item_stock_entries` | Batch ya kupokea stock |
| `store_stock_units` | Label + `initial_items` / `remaining_items` + `package_specID` |
| `store_requisitions` | Issue record + `take_*` fields + return fields |

---

## 2. Logic ya Kina - Sehemu Muhimu

### A. Package chain (kusajili item)

- Chain ni **linear** tu: kila level ina **mtoto mmoja** (`children[0]`).
- Main package **haina quantity field** - inahesabiwa kutoka sub-chain.
- Sub-package ya mwisho (leaf) ina `content_quantity` = idadi ya items ndani.
- Kila level kwenye chain inaweza **stocking point** (mfano unaweza pokea Carton badala ya Set).

### B. Stock intake

- **Package:** `batches[]` - kila batch = package type + quantity + labels[].
- Kila label → `store_stock_units` moja.
- `initial_items` = `items_per_package` kwa package; `1` kwa unit measure.
- `package_specID` inaunganisha label na chain level iliyochaguliwa.

### C. Requisition / Issue

- `buildTakeStructureForStockUnit()` inatengeneza:
  - **By Main Package** - package kuu + quantity
  - **By Sub-Package** - levels zote (checkbox + qty kila moja)
  - **By Item Inside** - unit measure ya ndani (max = content kwenye box/package ya mwisho)
- `itemsTaken = quantity × items_each` → inapunguza `remaining_items`.
- `stock_status`: `in_stock` (bado kuna kitu) au `requisitioned` (imeisha).

### D. Return

- **Good:** `remaining_items += quantity_taken` (si zaidi ya `initial_items`).
- **Bad:** stock hairudishwi; `stock_status = defective` ikiwa imeisha.
- **Hakuna partial return** - daima `quantity_returned = quantity_taken`.

### E. Report (All Inventory)

- Inahesabu labels zilizo `in_stock` kwa tarehe.
- **Historical:** `wasUnitInStockOnDate()` - inaangalia issued/returned timeline.

---

## 3. Kilicho MISS - Utaalamu & Mapungufu

### 🔴 Kipaumbele cha juu (inaweza kuvunja / kudanganya data)

| # | Tatizo | Maelezo |
|---|--------|---------|
| **1** | **`syncAvailableStockFromEntry` haijui `take_structure`** | Baada ya ku-add stock bila reload, Issue modal inaweza kosa package options (`remaining_items`, `take_structure`, nk. hazipo kwenye `availableStockList`). |
| **2** | **Historical report si sahihi kwa partial withdrawal** | `wasUnitInStockOnDate()` inaona: issued = nje kabisa, returned = ndani kabisa. Haizingatii `remaining_items` (mfano set bado na carton 5 baada ya kutoa piece 2). |
| **3** | **Hakuna partial return** | Mtu akichukua chalk 20 lazima arudishe 20; hawezi kurudisha 10. |
| **4** | **Label inaweza kurudiwa** | Ukaguzi wa duplicate ni kwa `stock_status = in_stock` tu. Label ya Set-01 iliyomalizika inaweza kutumika tena. |
| **5** | **`deleteItem` bila guard** | Unaweza kufuta item yenye stock active au requisitions - specs zinafutwa, data inabaki orphan. |
| **6** | **`updateItem` package resync bila guard** | Kubadilisha chain inafuta specs zote; stock units zenye `package_specID` za zamani zinakuwa inconsistent. |

### 🟠 Kipaumbele cha kati (features zilizoombwa / logic haijakamilika)

| # | Tatizo | Maelezo |
|---|--------|---------|
| **7** | **Requisition Report** | Ipo kwenye UI kama "coming soon" - hakuna backend. |
| **8** | **Specific Item Report** | Ipo kwenye UI kama "coming soon" - hakuna backend. |
| **9** | **Items Requests page** | Route `items_requests` ipo nav; view ni placeholder tu. Hakuna workflow: request → approve → issue. |
| **10** | **Report inahesabu labels, si items** | Set yenye chalk 1000 iliyobaki 200 bado inaonekana "1 in stock" (label 1), si "200 chalk". |
| **11** | **Root package mara mbili kwenye issue UI** | "Main Package" (`full_*`) na "Sub-Package" (`root_*`) zinaweza kuonyesha Set mara mbili na keys tofauti. |
| **12** | **Hakuna export** | PDF / Excel kwa reports. |
| **13** | **`deleteItemStock` guard dhaifu** | Haizuilii kufuta batch yenye open issued requisitions kwenye partial-depleted unit. |
| **14** | **Concurrent issue** | Hakuna row lock; issues mbili kwa pamoja zinaweza over-draw `remaining_items`. |

### 🟡 Kipaumbele cha chini (cleanup / polish)

| # | Tatizo | Maelezo |
|---|--------|---------|
| **15** | **Package categories** | Table ipo; UI/controller hazitumii. |
| **16** | **`StoreStockUnit::requisition()` ni `hasOne`** | Partial issues zinaweza kuwa requisitions nyingi kwa unit moja - relationship si sahihi. |
| **17** | **`stock_status` si enum** | `in_stock`, `requisitioned`, `defective` - string tu. |
| **18** | **Summary report duplicate** | `total_in_stock` na `available_in_stock` zinafanana. |
| **19** | **Validation gaps** | `returned_at` vs `issued_at`; backdating expected return. |
| **20** | **Chain linear tu** | Hakuna branching (carton moja ina box A na box B tofauti). |

---

## 4. Muongozo - Logic Unayoweza Kutoa (Tujaze Mapungufu)

Jaza sehemu hizi ili tuendelee na implementation. Unaweza kujibu kwa Kiswahili au Kiingereza.

### A. Reports (kile kilichobaki)

#### A1. Requisition Report
- [ ] Inaonyesha nini? (issued this month / date range / by person / by item?)
- [ ] Filters: tarehe, item type (can return / can't), recipient type, status (issued / returned / overdue)?
- [ ] Columns za table?
- [ ] Summary cards (total issued, returned, overdue, defective)?
- [ ] Export PDF/Excel?

#### A2. Specific Item Report
- [ ] Chagua item moja → inaonyesha nini?
- [ ] Stock history (received / issued / returned / remaining)?
- [ ] Per-label breakdown?
- [ ] Historical date?

#### A3. All Inventory Report - marekebisho
- [ ] Historical: tuhesabu `remaining_items` kwa tarehe (si issued/returned binary)?
- [ ] Onyeshe **item quantity** pamoja na label count?
- [ ] `total_in_stock` vs `available_in_stock` - tofauti yao ni nini?

---

### B. Items Requests (workflow mpya?)

- [ ] Nani anaweza **request**? (Teacher / Staff / Student / Admin tu?)
- [ ] Request inaenda wapi? (Admin approve → issue automatically?)
- [ ] Status flow: `pending` → `approved` → `rejected` → `issued`?
- [ ] Je inahusiana na requisition iliyopo au ni module tofauti?
- [ ] Notification / email?

---

### C. Return logic

- [ ] **Partial return** inaruhusiwa? (mfano alichukua chalk 20, anarudisha 10)
- [ ] Ikiwa partial return: `quantity_returned` < `quantity_taken` - stock inarudi kiasi gani?
- [ ] Requisition inabaki `issued` (partial) au status mpya?

---

### D. Stock & labels

- [ ] Label inaweza **kutumika tena** baada ya unit kumalizika? (Ndiyo / Hapana)
- [ ] Kufuta item yenye stock - ruhusu / zuia / soft-delete tu?
- [ ] Kubadilisha package chain item yenye stock - ruhusu / zuia / block fields fulani?

---

### E. Issue / sub-package (clarify business rules)

- [ ] Mtu anaweza kuchukua **Set 2 + Piece 5 + Box 3** kwa submit moja - ni sahihi? (sasa inaruhusiwa)
- [ ] Je `itemsTaken` zinajumlishwa na kupunguza `remaining_items` mara moja - au kila issue ni transaction tofauti?
- [ ] Kikomo: hawezi kuchukua zaidi ya `remaining_items` - **ndio** (sasa hivi)?

---

### F. Report & display preferences

- [ ] Stock table: onyeshe **labels** au **remaining items** kama namba kuu?
- [ ] Defective items - tab/report tofauti?
- [ ] Low stock alert (threshold)?

---

## 5. Mpango wa Kukamilisha (Pendekezo la Mpangilio)

| Hatua | Kazi | Tegemezi logic kutoka kwako |
|-------|------|----------------------------|
| **1** | Fix `syncAvailableStockFromEntry` + `formatStockEntryUi` | Hakuna - technical fix |
| **2** | Fix historical report (`remaining_items` timeline) | A3 |
| **3** | Requisition Report | A1 |
| **4** | Specific Item Report | A2 |
| **5** | Partial return (ikiwa unataka) | C |
| **6** | Delete/update guards | D |
| **7** | Items Requests module | B |
| **8** | Export reports | A1/A2 |

---

## 6. Faili Muhimu (reference)

| Faili | Jukumu |
|-------|--------|
| `app/Http/Controllers/StoreController.php` | Logic yote |
| `resources/views/Admin/manage_store.blade.php` | UI + JS (~5800 lines) |
| `resources/views/Admin/items_requests.blade.php` | Placeholder |
| `routes/web.php` (lines ~2000-2025) | Store routes |
| `database/migrations/2026_07_02_*` - `2026_07_04_*` | Schema |
| `app/Models/Store*.php` | Eloquent models |

---

## 7. Hitimisho

**Kile kilichopo:** Mfumo thabiti wa catalogue → labelled stock → partial package issue → return, na UI nzuri.

**Kile kilichokosekana zaidi:** Reports 2/3, Items Requests workflow, historical accuracy, partial return, data integrity guards, na sync ya frontend baada ya stock intake.

**Hatua yako:** Jaza **Sehemu 4** hapo juu (angalau A, B, C) - nitaweza kutekeleza logic iliyobaki kwa mpangilio wa Sehemu 5.

---

*Imetengenezwa: Jul 5, 2026 - kutokana na code review ya `StoreController`, migrations, na `manage_store.blade.php`.*
