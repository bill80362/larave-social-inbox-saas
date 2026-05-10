## Why

目前 Social Inbox SaaS 已完成資料層與後台 UI，但沒有任何資料能真正進入系統——Conversation 和 Message 只能由 Seeder 手動建立。Webhook Ingestion 層是讓系統「活起來」的關鍵：讓各平台（LINE、Facebook、Instagram、Google Business）的訊息能即時流入 inbox，供客服人員處理。

## What Changes

- 新增 `POST /webhook/{platform}` 路由，接受各平台的 webhook 回呼
- 每個平台各自實作驗簽（Signature Verification）中介層，未通過驗簽直接 `abort(403)`
- 驗簽通過後，將原始 payload 丟入 Queue Job 非同步處理（避免 webhook 超時）
- Queue Job 負責：解析 payload → 找到或建立 Contact → 找到或建立 Conversation → 建立 Message
- 支援四個平台：LINE、Facebook Messenger、Instagram Direct Message、Google Business Reviews
- **Non-goals**：本次不實作回覆功能（outbound）、OAuth 平台連接、即時推送（Realtime）

## Capabilities

### New Capabilities

- `webhook-line`: LINE Official Account webhook 驗簽 + payload 解析 + ingestion
- `webhook-facebook`: Facebook Messenger webhook 驗簽（X-Hub-Signature-256）+ ingestion
- `webhook-instagram`: Instagram Direct Message webhook（共用 Facebook Graph API）驗簽 + ingestion
- `webhook-google-business`: Google Business Reviews webhook 驗簽 + ingestion
- `webhook-queue-processing`: 共用的 Queue Job 邏輯——find-or-create Contact/Conversation/Message

### Modified Capabilities

- `channel-management`: Channel 的 `credentials` 欄位（已存在）將被 webhook 驗簽邏輯讀取；`is_active` 停用的 Channel 應拒絕 webhook

## Impact

- **新增路由**：`routes/api.php` 或 `routes/web.php` 新增 `POST /webhook/{platform}`
- **新增 Controller**：`app/Http/Controllers/Webhook/WebhookController.php`
- **新增 Middleware**：各平台驗簽 middleware（`app/Http/Middleware/`）
- **新增 Jobs**：`app/Jobs/ProcessWebhookPayload.php`（通用）或各平台獨立 Job
- **新增 Actions**：`app/Actions/Webhook/` 內各平台 ingestion action
- **依賴**：Queue driver（dev 環境用 `sync` 或 `database`）
- **安全性**：每個平台 webhook 都必須驗簽，credentials 從 Channel model 讀取（已有 encrypted cast）
