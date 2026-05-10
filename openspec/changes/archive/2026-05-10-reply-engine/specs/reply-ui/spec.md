## ADDED Requirements

### Requirement: ViewConversation 頁面顯示回覆輸入框
系統 SHALL 在 `ViewConversation` 頁面的 Header Actions 區域提供一個「Reply」Action，點擊後開啟 Modal，包含一個 `Textarea` 輸入框，讓客服輸入回覆文字後送出。

#### Scenario: 客服輸入回覆並送出
- **WHEN** 客服點擊「Reply」Action，輸入文字，點擊確認
- **THEN** 系統 SHALL 呼叫 `SendReplyAction` 發送回覆
- **AND** Modal 關閉，頁面刷新顯示新的 outbound Message
- **AND** 顯示成功 notification

#### Scenario: 回覆欄位為空送出
- **WHEN** 客服未輸入任何文字即點擊確認
- **THEN** 系統 SHALL 顯示必填驗證錯誤，不發送回覆

#### Scenario: 平台 API 發送失敗
- **WHEN** Driver 拋出例外
- **THEN** Filament 顯示錯誤 notification，Modal 保持開啟，訊息內容不遺失

### Requirement: 統一的回覆發送入口 SendReplyAction
系統 SHALL 提供 `app/Actions/Reply/SendReplyAction.php`，接受 `Conversation` 和回覆文字，依 `Channel->platform` 選擇對應 Driver，呼叫 Driver 發送，並寫入 `outbound` Message 紀錄。

#### Scenario: 依平台路由至正確 Driver
- **WHEN** `SendReplyAction` 被呼叫，Conversation 屬於 LINE Channel
- **THEN** 系統 SHALL 使用 `LineReplyDriver` 發送
- **AND** 對 Facebook 使用 `FacebookReplyDriver`
- **AND** 對 Instagram 使用 `InstagramReplyDriver`
- **AND** 對 Google Business 使用 `GoogleBusinessReplyDriver`

#### Scenario: 不支援的平台
- **WHEN** `SendReplyAction` 被呼叫，Channel platform 不在已知列表中
- **THEN** 系統 SHALL 拋出例外
