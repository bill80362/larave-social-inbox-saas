## Why

系統目前只能**接收**訊息（webhook ingestion 已完成），但客服無法從 Filament 介面回覆任何平台。「回覆功能」是 README 特別標記的最重要 Must-Have，也是讓產品從展示品變成實際工具的臨界點。

## What Changes

- **新增 Reply Engine**：統一的回覆發送流程，依平台派發至對應的 Driver
- **新增 4 個平台 Driver**：LINE / Facebook Messenger / Instagram / Google Business Review，各自封裝平台 API 呼叫
- **新增 Filament 回覆表單**：在 `ViewConversation` 頁面底部加入回覆輸入框與送出 Action
- **新增 outbound Message 記錄**：送出成功後，寫入 `MessageDirection::Outbound` 的 Message 紀錄
- **新增 `SendReplyAction`**：`app/Actions/Reply/SendReplyAction.php`，統一入口

## Capabilities

### New Capabilities
- `reply-line`: 透過 LINE Reply API 傳送文字訊息，並寫入 outbound Message 記錄
- `reply-facebook`: 透過 Facebook Messenger Send API 傳送文字訊息，並寫入 outbound Message 記錄
- `reply-instagram`: 透過 Instagram Messaging API 傳送文字訊息，並寫入 outbound Message 記錄
- `reply-google-business`: 透過 Google My Business API 回覆評論，並寫入 outbound Message 記錄
- `reply-ui`: Filament `ViewConversation` 頁面的回覆表單，送出後呼叫 SendReplyAction

### Modified Capabilities
- `conversation-detail`: 對話詳情頁新增回覆表單區塊，並顯示 outbound 訊息（不同樣式）
- `message-content`: Message 新增 `platform_message_id`（回傳的平台訊息 ID）欄位，用於追蹤送出狀態

## Impact

- **Models**: `Message`（新增 `platform_message_id` 欄位）
- **新增 Actions**: `app/Actions/Reply/SendReplyAction.php`、各平台 Driver（`app/Actions/Reply/Drivers/`）
- **Filament**: `app/Filament/Resources/ConversationResource/Pages/ViewConversation.php` — 新增 Reply Action
- **設定**: `config/services.php` 各平台的 access token / credential 已存在於 Channel `credentials` JSON 欄位，無需新增全域設定
- **外部 API 依賴**: LINE Messaging API、Facebook Graph API、Instagram Graph API、Google My Business API
- **Non-goals**: 不做圖片/貼圖/附件回覆（MVP 只做文字）；不做排程發送；不做自動回覆
