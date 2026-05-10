## Context

Webhook ingestion 已完成，系統能接收並儲存來自 LINE、Facebook、Instagram、Google Business 的訊息。目前 `Message` 模型有 `direction` 欄位（`inbound`/`outbound`），但尚未有任何 outbound 路徑。

本次實作 **Reply Engine**：一個統一的回覆發送層，讓客服可以從 Filament `ViewConversation` 頁面回覆，系統自動選擇正確的平台 API 送出，並寫入 `outbound` Message 紀錄。

## Goals / Non-Goals

**Goals:**
- 客服可在 Filament ViewConversation 頁面輸入文字並送出回覆
- 系統依 Conversation → Channel → Platform 自動選擇正確的 Driver 發送
- 送出後寫入 `direction=outbound` 的 Message 紀錄
- LINE、Facebook Messenger、Instagram、Google Business Review 各有對應 Driver
- Message 新增 `platform_message_id` 欄位（儲存平台回傳的訊息 ID）

**Non-Goals:**
- 圖片、貼圖、附件等非文字回覆（MVP 只做文字）
- 排程發送
- 自動回覆 / Bot
- Outbound 訊息的已讀確認 / Delivery Receipt
- 重試機制（送出失敗直接拋例外，讓 Filament 顯示錯誤通知）

## Decisions

### 決策 1：Driver Pattern（選用）而非 Strategy DI

**選擇**：`SendReplyAction` 依 `$channel->platform` 值用 `match` 切換 Driver 實例，Driver 為純 PHP class（非 interface binding）。

**理由**：目前只有 4 個平台，沒有動態注冊需求。`match` 讓平台到 Driver 的映射一目了然，比 IoC container binding 更容易追蹤和測試。

**替代方案**：ServiceProvider 中 bind 介面 → 需要額外設定，對 4 個 class 過度設計。

---

### 決策 2：Channel credentials 直接讀 JSON 欄位

**選擇**：每個 Driver 從 `$channel->credentials` JSON 欄位取 access token（如 `$channel->credentials['channel_access_token']`）。

**理由**：所有平台 credential 已在 `channel-management` 時設計好存於 `credentials` JSON 欄位，不需要另建設定。Webhook middleware 也是這樣讀的，保持一致。

---

### 決策 3：Filament Reply 用 Header Action + Modal

**選擇**：在 `ViewConversation` 加一個 `Action::make('reply')` Header Action，包含 `Textarea` 表單，送出呼叫 `SendReplyAction`。

**理由**：
- 與現有的 `changeStatus`、`assign`、`addNote` 三個 Header Actions 風格一致
- 不需要新增 Livewire component 或修改 Blade view
- Filament Action 內建 loading state、錯誤通知、成功 notification

**替代方案**：在 Blade 底部加常駐回覆框 → 需要修改 custom Blade，增加複雜度。

---

### 決策 4：同步發送（不走 Queue）

**選擇**：`SendReplyAction` 同步呼叫平台 API，不 dispatch Job。

**理由**：回覆是客服主動觸發的即時操作，使用者等待 loading 是可接受的；若走 queue，UI 必須另外做「pending → sent」狀態更新，複雜度大幅增加。失敗時直接拋例外，Filament 會顯示錯誤 notification。

---

### 決策 5：`platform_message_id` 新增欄位

**選擇**：`messages` 資料表新增 `platform_message_id` nullable string，儲存平台回傳的訊息 ID。

**理由**：將來如果要做 delivery receipt 或更新/刪除訊息，需要平台端的訊息 ID。現在加欄位成本低。

## Risks / Trade-offs

| 風險 | 緩解 |
|------|------|
| 平台 API Token 過期 | Driver 拋例外，Filament 顯示錯誤通知；Token 輪換由 Channel 管理 UI 負責（未來功能） |
| LINE Reply Token 只能用一次 | LINE Reply API 使用 `replyToken`（有時效性）。**MVP 改用 Push API**（`channel_access_token` + `userId`），不依賴 replyToken |
| Google Business Reply API 限制 | Google My Business API 回覆評論需要 OAuth，credentials 必須有 `access_token`；過期需重新 OAuth（Channel 管理 UI 未來處理） |
| HTTP 呼叫在測試中發出真實請求 | Driver 用 `Http::fake()` 在測試中 mock |

## Migration Plan

1. 建立 migration：`messages` 新增 `platform_message_id` nullable string
2. 建立 Driver classes（4 個）
3. 建立 `SendReplyAction`
4. 修改 `ViewConversation` 加入 Reply Header Action
5. 更新 Message factory / seeder（`platform_message_id` 預設 null）
6. 執行 `php artisan migrate`

Rollback：`php artisan migrate:rollback` 移除 `platform_message_id` 欄位；刪除新增的 Action / Driver files。
