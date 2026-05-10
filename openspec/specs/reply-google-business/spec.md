## ADDED Requirements

### Requirement: 透過 Google My Business API 回覆評論
系統 SHALL 透過 Google My Business API，對指定評論（review）送出回覆文字，並將平台回傳的回覆名稱（`name` 欄位）寫入 Message 的 `platform_message_id` 欄位。

#### Scenario: 成功回覆 Google 評論
- **WHEN** 客服對一個 Google Business Channel 的 Conversation 送出回覆
- **THEN** 系統 SHALL 呼叫 `PUT https://mybusiness.googleapis.com/v4/{parent}/reviews/{reviewId}/reply`
- **AND** Contact 的 `platform_user_id` 作為 `reviewId`
- **AND** Channel 的 `platform_account_id` 作為 `parent`（location 路徑）
- **AND** 成功後寫入 direction=`outbound`、type=`text` 的 Message 紀錄

#### Scenario: Google Channel 缺少 access token
- **WHEN** Channel 的 `credentials.access_token` 為空或不存在
- **THEN** 系統 SHALL 拋出例外，不寫入 Message 紀錄

#### Scenario: Google API 回傳錯誤
- **WHEN** Google My Business API 回傳錯誤回應
- **THEN** 系統 SHALL 拋出例外，Filament 顯示錯誤通知，不寫入 Message 紀錄
