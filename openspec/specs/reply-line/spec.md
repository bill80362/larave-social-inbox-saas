## ADDED Requirements

### Requirement: 透過 LINE Push API 傳送文字回覆
系統 SHALL 透過 LINE Messaging API Push 端點，將文字訊息傳送給指定的 LINE userId，並將平台回傳的訊息 ID 寫入 Message 的 `platform_message_id` 欄位。

#### Scenario: 成功傳送文字回覆
- **WHEN** 客服對一個 LINE Channel 的 Conversation 送出回覆
- **THEN** 系統 SHALL 呼叫 LINE Push API（`POST https://api.line.me/v2/bot/message/push`）
- **AND** Contact 的 `platform_user_id` 作為 `to` 欄位
- **AND** 成功後寫入 direction=`outbound`、type=`text` 的 Message 紀錄

#### Scenario: LINE Channel 缺少 access token
- **WHEN** Channel 的 `credentials.channel_access_token` 為空或不存在
- **THEN** 系統 SHALL 拋出例外，不寫入 Message 紀錄

#### Scenario: LINE API 回傳錯誤
- **WHEN** LINE Push API 回傳 4xx 或 5xx 錯誤
- **THEN** 系統 SHALL 拋出例外，Filament 顯示錯誤通知，不寫入 Message 紀錄
