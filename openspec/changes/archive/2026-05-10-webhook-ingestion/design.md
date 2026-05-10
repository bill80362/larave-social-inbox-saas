## Context

目前系統有完整的資料層（Workspace / Channel / Contact / Conversation / Message）與 Filament 後台 UI，但沒有任何入口可以讓外部平台訊息進入系統。Seeder 是目前唯一能建立 Message 的方式。

各平台 Webhook 的特性：
- **LINE**: HMAC-SHA256 簽名，header `X-Line-Signature`，channel secret 在 `credentials.channel_secret`
- **Facebook / Instagram**: HMAC-SHA256，header `X-Hub-Signature-256`，共用 Graph API，`app_secret` 在 `credentials.app_secret`；需要 GET challenge 驗證端點
- **Google Business**: Google 用 JWT（RS256）簽名，需向 Google 公鑰端點驗證

## Goals / Non-Goals

**Goals:**
- 實作四個平台的 webhook 驗簽
- 驗簽通過後 dispatch Queue Job，不在 HTTP request 內做業務邏輯
- Queue Job 負責 find-or-create Contact、Conversation、Message
- `is_active = false` 的 Channel 拒絕 webhook（回 200 但不處理）
- 每個平台測試覆蓋：驗簽成功/失敗、payload 解析、Message 建立

**Non-Goals:**
- Outbound 回覆（呼叫平台 API）
- Platform OAuth 授權連接
- Realtime 即時推送
- Webhook 重試機制（留給 Queue driver 處理）

## Decisions

### 決策 1：單一路由 vs 各平台獨立路由

**選擇：單一 Controller，各平台獨立 Middleware 驗簽**

```
POST /webhook/{platform}   → WebhookController@handle
  ↓ 根據 {platform} 選擇驗簽 Middleware
  ↓ 驗簽通過 → dispatch ProcessWebhookPayload::class
```

理由：路由統一好管理；驗簽邏輯依平台不同，用 middleware 隔離最清晰；Controller 保持薄層只做 dispatch。

**捨棄方案**：各平台獨立 Controller — 重複 dispatch 程式碼，路由散亂。

### 決策 2：單一 Job vs 各平台獨立 Job

**選擇：各平台獨立 Job，共用 IngestMessage Action**

```
ProcessLineWebhook
ProcessFacebookWebhook      → 各自解析 payload
ProcessInstagramWebhook          ↓
ProcessGoogleBusinessWebhook    共用 IngestMessageAction::handle()
                                  ↓
                              find-or-create Contact
                              find-or-create Conversation
                              create Message
```

理由：各平台 payload 結構差異大，獨立 Job 的 `handle()` 專注解析；IngestMessage Action 封裝共用邏輯，避免重複。

**捨棄方案**：單一 Job + switch case — 隨平台增加變成巨型 Job，難以測試。

### 決策 3：Webhook 路由放 api.php vs web.php

**選擇：`routes/api.php`**

理由：webhook 是 API 呼叫，不需要 session/CSRF；Laravel 的 API 路由已排除 `VerifyCsrfToken` middleware。

### 決策 4：credentials 讀取方式

Channel model 已有 `credentials` 欄位（`encrypted` cast）。驗簽 middleware 從 `Channel::where('platform', $platform)->where('platform_account_id', $accountId)->first()` 讀取 secret。

若找不到對應 Channel → `abort(404)`。

### 決策 5：Facebook challenge 驗證

Facebook 在綁定 webhook 時會發 `GET /webhook/facebook?hub.verify_token=...`，需要獨立處理：Controller `handle()` 先判斷 `$request->isMethod('GET')` → 驗證 `hub.verify_token`。

## Risks / Trade-offs

- **[Risk] LINE 發多事件陣列**：一個 webhook request 可包含多個 events → Job 內需 `foreach` 處理，Message 會建立多筆。Mitigation：Job 內 loop events。
- **[Risk] Google Business JWT 驗證需網路請求**：需從 Google 公鑰端點（`https://www.googleapis.com/oauth2/v3/certs`）取得公鑰。Mitigation：快取公鑰 1 小時（`Cache::remember`）。
- **[Risk] find-or-create race condition**：高並發時可能重複建立 Conversation。Mitigation：資料庫 unique constraint 已存在（`channel_id + contact_id`），Job 用 `firstOrCreate` + 捕捉 unique violation。
- **[Risk] Queue driver 在開發環境為 sync**：會讓 webhook handler 變同步，但不影響功能，只影響開發體驗。

## Open Questions

- Google Business Review webhook 的觸發條件是否包含「評論回覆」？（留待 spec 確認）
- Instagram DM 與 Facebook Messenger 的 App Secret 是否共用同一個 Channel record？（初步假設：各自獨立 Channel）
