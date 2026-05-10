## Context

`setup-core-models` change 已完成：7 個 migration、5 個 Enum、6 個 Model（含 Global Scope workspace tenancy）、Factories、Seeder 全部就位。目前沒有任何 Filament Resource，客服人員無法透過後台看到或操作任何對話。

此 design 說明如何在 Filament v5 上建構「對話收件匣」UI，包含兩個 Resource（Conversation、Channel）及其頁面、Actions、Blade View。

## Goals / Non-Goals

**Goals:**
- 建立可用的對話列表（篩選、搜尋、排序）
- 建立對話詳情頁（訊息 timeline、聯絡人資訊、狀態/指派控制、內部備註）
- 建立頻道管理列表（唯讀展示）
- 所有 UI 均受 Global Scope 保護，只顯示當前 workspace 資料

**Non-Goals:**
- 不實作 outbound 訊息送出
- 不實作 real-time 推播（WebSocket）
- 不實作 Contact / Workspace / User 的 CRUD 頁面
- 不新增任何 migration

## Decisions

### 決策 1：ConversationResource 詳情頁使用 ViewPage + 自訂 Blade

**選擇**：`ViewPage` 搭配 `resources/views/filament/conversations/view.blade.php` 自訂模板。

**理由**：訊息 timeline 是高度客製化的 UI（氣泡式排版、方向 inbound/outbound、不同 type 的渲染），Filament Infolist 的 `TextEntry` / `ImageEntry` 無法靈活處理。自訂 Blade 可完全掌控排版，同時保留 Filament 的 Header Actions（狀態切換、指派）。

**被否決的方案**：純 Infolist — 無法做氣泡排版；Livewire Component — 增加複雜度，ViewPage 本身已是 Livewire。

### 決策 2：狀態變更與指派透過 Header Actions 實作

**選擇**：在 `ViewPage` 的 `getHeaderActions()` 中定義 `Action`（`UpdateStatusAction`、`AssignAction`），使用 modal form。

**理由**：Header Actions 是 Filament 標準模式，適合「對這筆記錄做操作」的場景，不需要離開頁面。

### 決策 3：內部備註使用 ViewPage 內嵌 `CreateNoteAction`

**選擇**：在 ViewPage 底部顯示 note 列表（Blade 渲染），並提供一個「新增備註」`Action`（modal textarea）。

**理由**：備註是附屬於對話的補充資訊，不需要獨立 Resource；ViewPage 已是 Livewire，modal action 不需額外 JS。

### 決策 4：ChannelResource 設為唯讀列表（無 Create/Edit/Delete）

**選擇**：`ChannelResource` 只提供 `ListChannels` 頁，禁用所有 CRUD 操作。

**理由**：MVP 階段平台連線由開發者或 seed 建立，UI 操作將在後續 `channel-integration` change 中實作。過早開放 CRUD 會暴露 `credentials` 加密欄位的操作風險。

### 決策 5：Conversation 列表預設排序為 last_message_at DESC

**選擇**：`->defaultSort('last_message_at', 'desc')`，null 值排在後面。

**理由**：模擬真實收件匣行為，最新有動態的對話排最前。

### 決策 6：Filament Panel 使用預設 `app` panel

**選擇**：使用現有的 Filament panel（`app`），將兩個 Resource 直接註冊。

**理由**：目前只有一個 panel，無需多 panel 設定。

## Filament Components 使用清單

| Component | 用途 |
|-----------|------|
| `Resource` | `ConversationResource`、`ChannelResource` |
| `ListRecords` Page | 對話列表、頻道列表 |
| `ViewRecord` Page | 對話詳情（自訂 Blade $view） |
| `Table\Columns\TextColumn` | 列表欄位 |
| `Table\Columns\BadgeColumn` | 狀態 badge |
| `Table\Filters\SelectFilter` | 平台、狀態、指派人篩選 |
| `Actions\Action` | 狀態變更、指派、新增備註 |
| `Forms\Components\Select` | Action modal 內的下拉選單 |
| `Forms\Components\Textarea` | 備註輸入框 |
| `Support\Icons\Heroicon` | 導航與 action 圖示 |

## Risks / Trade-offs

- **Global Scope + Filament 查詢**：Filament Table 內部的 query 會通過 Global Scope，只要 `actingAs()` 有設定 `workspace_id` 就安全。測試時必須確保 `actingAs()` 正確。→ 已有 WorkspaceTenancyTest 覆蓋。
- **ViewPage 自訂 Blade 的 Livewire 刷新**：Actions 執行後需 `$this->refreshFormData([])` 或重新導向才能更新 Blade 內容。→ 狀態/指派 Actions 執行後呼叫 `$this->redirect()` 或 `$this->fillForm()` 刷新。
- **credentials 欄位不顯示**：ChannelResource 的表格不得顯示 `credentials` 欄位（加密敏感資料）。→ 在 `table()` 中明確只列出安全欄位。

## Migration Plan

無 migration 變動。部署步驟：
1. `php artisan migrate`（本次無新 migration，可跳過）
2. `npm run build`（確保 Filament assets 更新）
3. `php artisan filament:optimize`（清除 Filament 快取）

## Open Questions

- 訊息 timeline 中的圖片/檔案 attachment 如何顯示？MVP 只顯示 URL 連結，還是 `<img>` 預覽？→ 建議 MVP 用 URL 連結，後續再加預覽。
