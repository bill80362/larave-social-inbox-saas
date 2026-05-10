## ADDED Requirements

### Requirement: 透過 Facebook Messenger Send API 傳送文字回覆
系統 SHALL 透過 Facebook Graph API Messenger Send 端點，將文字訊息傳送給指定的 Facebook PSID（page-scoped user ID），並將平台回傳的 `message_id` 寫入 Message 的 `platform_message_id` 欄位。

#### Scenario: 成功傳送文字回覆
- **WHEN** 客服對一個 Facebook Channel 的 Conversation 送出回覆
- **THEN** 系統 SHALL 呼叫 `POST https://graph.facebook.com/v19.0/me/messages`
- **AND** Contact 的 `platform_user_id` 作為 `recipient.id`
- **AND** 成功後寫入 direction=`outbound`、type=`text` 的 Message 紀錄

#### Scenario: Facebook Channel 缺少 page access token
- **WHEN** Channel 的 `credentials.page_access_token` 為空或不存在
- **THEN** 系統 SHALL 拋出例外，不寫入 Message 紀錄

#### Scenario: Facebook API 回傳錯誤
- **WHEN** Graph API 回傳錯誤回應
- **THEN** 系統 SHALL 拋出例外，Filament 顯示錯誤通知，不寫入 Message 紀錄
