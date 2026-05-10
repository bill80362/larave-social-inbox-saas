## ADDED Requirements

### Requirement: Facebook webhook challenge 驗證
系統 SHALL 在 GET `/webhook/facebook` 時驗證 `hub.verify_token`，與對應 Channel `credentials.verify_token` 比對，正確時 SHALL 回傳 `hub.challenge`。

#### Scenario: Challenge 驗證成功
- **WHEN** GET `/webhook/facebook?hub.mode=subscribe&hub.verify_token=<token>&hub.challenge=<challenge>` 到達
- **THEN** 系統 SHALL 回傳純文字 `hub.challenge` 值

#### Scenario: Challenge 驗證失敗
- **WHEN** GET `/webhook/facebook` 的 `hub.verify_token` 不符
- **THEN** 系統 SHALL 回傳 403

### Requirement: Facebook webhook 驗簽
系統 SHALL 在接收 Facebook webhook 時驗證 `X-Hub-Signature-256` header，使用對應 Channel 的 `credentials.app_secret` 進行 HMAC-SHA256 計算，簽名不符時 SHALL 回傳 403。

#### Scenario: 驗簽成功
- **WHEN** POST `/webhook/facebook` 收到正確的 `X-Hub-Signature-256`
- **THEN** 系統 SHALL 回傳 200 並 dispatch Queue Job

#### Scenario: 驗簽失敗
- **WHEN** POST `/webhook/facebook` 的 `X-Hub-Signature-256` 錯誤或缺少
- **THEN** 系統 SHALL 回傳 403，不處理 payload

### Requirement: Facebook Messenger payload 解析
系統 SHALL 解析 Facebook Messenger webhook payload，處理 `messaging` 陣列中的 `message` 事件，每則訊息 SHALL 建立對應 Message 紀錄。

#### Scenario: 文字訊息
- **WHEN** Facebook 傳入包含 `message.text` 的 messaging event
- **THEN** 系統 SHALL 建立 `type=text, direction=inbound` 的 Message

#### Scenario: 附件訊息
- **WHEN** Facebook 傳入包含 `message.attachments` 的 messaging event，type 為 image
- **THEN** 系統 SHALL 建立 `type=image, direction=inbound` 的 Message
