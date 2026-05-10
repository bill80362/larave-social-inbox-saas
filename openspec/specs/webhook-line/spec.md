## ADDED Requirements

### Requirement: LINE webhook 驗簽
系統 SHALL 在接收 LINE webhook 時驗證 `X-Line-Signature` header，使用對應 Channel 的 `credentials.channel_secret` 進行 HMAC-SHA256 計算，簽名不符時 SHALL 回傳 403。

#### Scenario: 驗簽成功
- **WHEN** POST `/webhook/line` 收到正確的 `X-Line-Signature`
- **THEN** 系統 SHALL 回傳 200 並 dispatch Queue Job

#### Scenario: 驗簽失敗
- **WHEN** POST `/webhook/line` 收到錯誤或缺少的 `X-Line-Signature`
- **THEN** 系統 SHALL 回傳 403，不處理 payload

#### Scenario: Channel 停用時拒絕
- **WHEN** 對應 Channel 的 `is_active = false` 時收到 webhook
- **THEN** 系統 SHALL 回傳 200 但不 dispatch Job（靜默忽略）

### Requirement: LINE webhook payload 解析
系統 SHALL 解析 LINE webhook payload 中的 `events` 陣列，每個 event SHALL 各自建立對應的 Message 紀錄。目前支援 `message` type event；其他 event type SHALL 靜默忽略。

#### Scenario: 文字訊息事件
- **WHEN** LINE 傳入 type=message, message.type=text 的 event
- **THEN** 系統 SHALL 建立 `type=text, direction=inbound` 的 Message，`content` 為訊息文字

#### Scenario: 圖片訊息事件
- **WHEN** LINE 傳入 type=message, message.type=image 的 event
- **THEN** 系統 SHALL 建立 `type=image, direction=inbound` 的 Message

#### Scenario: 貼圖事件
- **WHEN** LINE 傳入 type=message, message.type=sticker 的 event
- **THEN** 系統 SHALL 建立 `type=sticker, direction=inbound` 的 Message

#### Scenario: 非 message 事件
- **WHEN** LINE 傳入 type=follow 或其他非 message type 的 event
- **THEN** 系統 SHALL 靜默忽略該 event，不建立任何紀錄
