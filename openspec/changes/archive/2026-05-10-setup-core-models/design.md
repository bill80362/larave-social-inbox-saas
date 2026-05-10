## Context

目前專案僅有 Laravel 骨架與 Filament v5 安裝，沒有任何業務 Model。本設計定義所有核心資料表的結構、Model 關聯、租戶隔離機制，以及開發用假資料的生成策略。所有後續功能（收件匣 UI、Webhook、回覆引擎）都依賴這層基礎。

## Goals / Non-Goals

**Goals:**
- 建立 6 個新資料表（workspaces, channels, contacts, conversations, messages, notes）
- 擴充 users 表（加入 workspace_id, role）
- 每個 Model 實作 Global Scope 確保租戶隔離
- 每個 Model 提供 Factory，Seeder 填入可用的開發假資料

**Non-Goals:**
- 不實作任何 Filament Resource / UI
- 不串接任何外部平台 API
- 不實作 Webhook 接收
- 不實作跨 channel Contact 合併

## Decisions

### 租戶隔離：Global Scope + users.workspace_id

**決策**：User 直接帶 `workspace_id` 欄位，所有 Model 用 Global Scope 自動 scope 至 `auth()->user()->workspace_id`。

**理由**：MVP 階段一個客服帳號只屬於一個企業，不需要 Filament 的 many-to-many tenancy 系統。Global Scope 實作簡單，未來如需升級到 Filament tenancy 只需移除 Global Scope 並加上 pivot 表，資料表結構不需改動。

**捨棄方案**：Filament `->tenant(Workspace::class)` — 需要 `workspace_user` pivot、URL 含 tenant slug、User 實作 `HasTenants` interface，MVP 過度複雜。

---

### Channel：同平台可多帳號，platform_account_id 唯一鍵

**決策**：Channel 以 `(workspace_id, platform, platform_account_id)` 為 unique key，允許同一個 workspace 連接多個同平台帳號（例如兩個 LINE 官方帳號）。

**理由**：台灣中小企業常見連鎖店場景（台南店、高雄店各一個 LINE 帳號），若限制每個 platform 只能一筆會造成產品限制。

**欄位定義**：
```
channels
  id                    BIGINT UNSIGNED PK
  workspace_id          FK → workspaces
  platform              ENUM('instagram','facebook','line','google_business')
  name                  VARCHAR(255)       # 顯示名稱，例如「台南店 LINE」
  platform_account_id   VARCHAR(255)       # 平台唯一識別
  credentials           TEXT (encrypted)   # JSON，存 access token 等
  is_active             BOOLEAN DEFAULT true
  created_at, updated_at
  UNIQUE(workspace_id, platform, platform_account_id)
```

---

### Contact：per channel 身份，MVP 不跨 channel 合併

**決策**：Contact 帶 `channel_id`，每個 channel 的客人是獨立身份。

**理由**：跨平台身份合併需要比對電話、Email 或人工標記，複雜度高，MVP 不做。同一個真實客人在不同 channel 傳訊息會建立不同 Contact 紀錄，收件匣按 channel 瀏覽即可區分。

**欄位定義**：
```
contacts
  id                  BIGINT UNSIGNED PK
  workspace_id        FK → workspaces
  channel_id          FK → channels
  platform_user_id    VARCHAR(255)   # 平台的 user/sender ID
  name                VARCHAR(255) nullable
  avatar_url          VARCHAR(2048) nullable
  created_at, updated_at
  UNIQUE(channel_id, platform_user_id)
```

---

### Message：type enum + content text + attachments JSON

**決策**：Message 用 `type` enum 標記訊息種類，`content` 存文字內容，`attachments` 存 JSON 陣列存放圖片/檔案 URL。

**理由**：MVP 顯示文字為主，但日後需要顯示圖片、貼圖時不需要改 schema，只需在 UI 層判斷 type。純文字一個欄位就夠，不需要 polymorphic 設計。

**欄位定義**：
```
messages
  id              BIGINT UNSIGNED PK
  conversation_id FK → conversations
  direction       ENUM('inbound','outbound')
  type            ENUM('text','image','sticker','audio','video','file','review')
  content         TEXT nullable
  attachments     JSON nullable   # [{url, mime_type, filename}]
  sender_type     ENUM('contact','agent')
  sender_id       BIGINT UNSIGNED
  sent_at         TIMESTAMP
  created_at, updated_at
```

---

### Conversation 狀態流轉

```
open ──────────────▶ pending ──────────────▶ resolved
 ▲                      │                       │
 └──────────────────────┘                       │
 ▲                                              │
 └──────────────────────────────────────────────┘
         (任何狀態都可回到 open)
```

**欄位定義**：
```
conversations
  id                BIGINT UNSIGNED PK
  workspace_id      FK → workspaces
  channel_id        FK → channels
  contact_id        FK → contacts
  status            ENUM('open','pending','resolved') DEFAULT 'open'
  assigned_to       FK → users, nullable
  last_message_at   TIMESTAMP nullable
  created_at, updated_at
```

---

### PHP 8.3 Attribute 取代陣列屬性

**決策**：所有新 Model 用 `#[Fillable]`、`#[Hidden]` attribute，不用 `$fillable`、`$hidden` 陣列，與現有 `User` model 保持一致。

## Risks / Trade-offs

- **credentials 加密**：`credentials` 欄位存 API token，需在 cast 層用 `encrypted` cast 保護。若忘記設定，token 明文存入 DB。→ 在 Channel model 的 casts() 明確設定 `'credentials' => 'encrypted'`。
- **Global Scope 在測試中的干擾**：測試中若未登入，Global Scope 的 `auth()->hasUser()` 為 false，查詢不會 scope，可能造成跨 workspace 資料洩漏。→ 所有測試都必須 `actingAs()` 並使用對應 workspace 的 user。
- **sender_id 的 polymorphic 問題**：`messages.sender_id` 不是標準 polymorphic，需搭配 `sender_type` 手動 join。→ MVP 階段只需要顯示 sender name，可用 `sender_type` + `sender_id` 分開 eager load。

## Migration Plan

1. 執行 `php artisan migrate` — 所有新表一次建立
2. 執行 `php artisan db:seed` — 填入開發假資料
3. 無需 rollback 策略（開發階段，可直接 `migrate:fresh`）

## Open Questions

- `credentials` JSON 的結構因平台而異（LINE 有 channel secret / access token，Google 有 OAuth token）。目前先用無結構 JSON，待 Webhook 實作時再定義各平台的 schema。
