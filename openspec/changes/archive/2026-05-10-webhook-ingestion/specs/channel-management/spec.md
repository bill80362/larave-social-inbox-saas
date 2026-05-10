## MODIFIED Requirements

### Requirement: Channel 可停用
Channel SHALL 有 `is_active` 欄位，停用的 Channel SHALL 不接收新訊息——webhook handler 在驗簽通過後 SHALL 檢查 `is_active`，若為 `false` 則回傳 HTTP 200 但不 dispatch Queue Job，不建立任何 Message 或 Conversation。

#### Scenario: 停用 Channel
- **WHEN** `is_active` 設為 false
- **THEN** Channel 紀錄仍存在，不刪除歷史 Conversation

#### Scenario: 停用 Channel 拒絕新 webhook
- **WHEN** `is_active = false` 的 Channel 收到任何平台的 webhook（驗簽已通過）
- **THEN** 系統 SHALL 回傳 HTTP 200 但不 dispatch Job，不建立任何 Message 或 Conversation
