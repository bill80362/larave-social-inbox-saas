## ADDED Requirements

### Requirement: 共用 ingestion 邏輯
系統 SHALL 提供共用的 `IngestMessageAction`，供各平台 Queue Job 呼叫。Action 接收標準化的 payload（platform、platform_user_id、channel、message type/content/sent_at），依序執行：find-or-create Contact → find-or-create Conversation → create Message。

#### Scenario: 新聯絡人第一則訊息
- **WHEN** 某 platform_user_id 在此 Channel 從未出現過
- **THEN** 系統 SHALL 建立新 Contact 紀錄，並建立新 Conversation 與 Message

#### Scenario: 既有聯絡人新訊息
- **WHEN** 某 platform_user_id 在此 Channel 已有 Contact 紀錄
- **THEN** 系統 SHALL 重用既有 Contact，並在最近的 open/pending Conversation 內建立 Message；若無 open/pending Conversation 則建立新 Conversation

#### Scenario: Race condition 防護
- **WHEN** 兩個並發 Job 同時為相同 platform_user_id 建立 Contact
- **THEN** 系統 SHALL 只建立一筆 Contact（透過 unique constraint + firstOrCreate），不拋出未處理例外

### Requirement: Queue Job dispatch
系統 SHALL 在 webhook handler 驗簽通過後，立即 dispatch 對應平台的 Queue Job，HTTP response SHALL 在 dispatch 後立即回傳 200，不等待 Job 完成。

#### Scenario: Job dispatch 成功
- **WHEN** webhook 驗簽通過
- **THEN** 系統 SHALL dispatch Job 並立即回傳 HTTP 200

#### Scenario: Job 執行失敗
- **WHEN** Queue Job 在處理過程中拋出例外
- **THEN** Job SHALL 進入 Queue 的失敗佇列（`failed_jobs`），不影響 HTTP 層

### Requirement: 停用 Channel 靜默忽略
系統 SHALL 在 webhook handler 中檢查對應 Channel 的 `is_active` 狀態，停用的 Channel SHALL 回傳 HTTP 200 但不 dispatch 任何 Job。

#### Scenario: 停用 Channel 收到 webhook
- **WHEN** `is_active = false` 的 Channel 收到驗簽正確的 webhook
- **THEN** 系統 SHALL 回傳 200，不建立任何 Message 或 Conversation
