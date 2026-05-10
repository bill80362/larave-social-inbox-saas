## ADDED Requirements

### Requirement: Instagram webhook 驗簽
Instagram Direct Message 使用與 Facebook 相同的 Graph API 驗簽機制。系統 SHALL 在接收 Instagram webhook 時驗證 `X-Hub-Signature-256` header，使用對應 Channel 的 `credentials.app_secret` 進行 HMAC-SHA256 計算，簽名不符時 SHALL 回傳 403。

#### Scenario: 驗簽成功
- **WHEN** POST `/webhook/instagram` 收到正確的 `X-Hub-Signature-256`
- **THEN** 系統 SHALL 回傳 200 並 dispatch Queue Job

#### Scenario: 驗簽失敗
- **WHEN** POST `/webhook/instagram` 的 `X-Hub-Signature-256` 錯誤或缺少
- **THEN** 系統 SHALL 回傳 403

### Requirement: Instagram DM payload 解析
系統 SHALL 解析 Instagram webhook payload，識別 `messaging` 陣列中的 DM 事件，每則訊息 SHALL 建立對應 Message 紀錄。

#### Scenario: Instagram 文字 DM
- **WHEN** Instagram 傳入包含 `message.text` 的 messaging event
- **THEN** 系統 SHALL 建立 `type=text, direction=inbound` 的 Message

#### Scenario: Instagram 圖片 DM
- **WHEN** Instagram 傳入包含 `message.attachments` 且 type=image 的 messaging event
- **THEN** 系統 SHALL 建立 `type=image, direction=inbound` 的 Message
