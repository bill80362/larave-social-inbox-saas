## Why

客服人員目前無法在後台看到任何對話或訊息。core models 已建立完成，但沒有任何 UI 可以使用它們。`inbox-ui` 是讓產品從資料結構變成可操作收件匣的第一步。

## What Changes

- 新增 Filament `ConversationResource`，支援對話列表瀏覽、篩選、狀態管理
- 新增 `ConversationResource\Pages\ViewConversation`（ViewPage），以自訂 Blade 模板顯示完整對話紀錄與回覆框
- 新增 `ChannelResource`，讓管理者可查看與管理平台連線
- 新增對話列表的快捷 Actions：指派客服、變更狀態（open/pending/resolved）
- 新增 ViewPage 內的內部備註（Note）顯示與建立
- Filament Navigation 加入「收件匣」與「頻道」選單項目

## Capabilities

### New Capabilities

- `conversation-list`: 對話列表頁，含平台/狀態/指派人篩選，支援關鍵字搜尋聯絡人名稱
- `conversation-detail`: 對話詳情頁（ViewPage + Blade），顯示訊息 timeline、聯絡人資訊、狀態/指派控制
- `conversation-actions`: 對話的快捷操作——變更狀態、指派/取消指派給客服
- `internal-notes-ui`: 在 ViewPage 底部顯示並新增內部備註（不對聯絡人可見）
- `channel-management`: 頻道列表頁，顯示各平台連線的名稱、狀態、平台類型

### Modified Capabilities

<!-- 無現有 spec 被修改 -->

## Impact

- **新增檔案**: `app/Filament/Resources/ConversationResource.php`、`app/Filament/Resources/ConversationResource/Pages/`、`app/Filament/Resources/ChannelResource.php`
- **新增 View**: `resources/views/filament/conversations/view.blade.php`（對話 timeline）
- **Models 讀取**: `Conversation`、`Message`、`Note`、`Channel`、`Contact`、`User`（Global Scope 已就位）
- **無 API 新增**，無 migration 變動，無 breaking changes

## Non-goals

- 不實作 Webhook 接收或平台 API 送出訊息（僅 UI 顯示）
- 不實作 real-time 更新（WebSocket / Reverb）
- 不實作 Contact 管理頁面
- 不實作 Workspace 或 User 管理頁面
- 不實作 outbound 回覆送出（僅顯示歷史訊息）
