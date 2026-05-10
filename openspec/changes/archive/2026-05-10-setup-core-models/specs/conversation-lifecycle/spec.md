## ADDED Requirements

### Requirement: Conversation 代表與單一 Contact 的完整對話 thread
每個 Conversation SHALL 關聯到一個 Channel、一個 Contact，並屬於某個 Workspace。一個 Contact 可有多個 Conversation（跨時間段）。

#### Scenario: 建立新 Conversation
- **WHEN** 系統收到某 Channel 來自新 Contact 的第一則訊息
- **THEN** 系統 SHALL 建立一筆新的 Conversation，status 預設為 `open`

### Requirement: Conversation 狀態流轉
Conversation 的 status SHALL 為 `open`、`pending`、`resolved` 其中之一，且可在三者間任意流轉。

#### Scenario: 客服標記為 pending
- **WHEN** 客服將 Conversation 標記為 `pending`
- **THEN** 系統 SHALL 更新 status 為 `pending`

#### Scenario: 客服解決對話
- **WHEN** 客服將 Conversation 標記為 `resolved`
- **THEN** 系統 SHALL 更新 status 為 `resolved`

#### Scenario: 重新開啟對話
- **WHEN** 客服將任何狀態的 Conversation 標記為 `open`
- **THEN** 系統 SHALL 更新 status 為 `open`

### Requirement: Conversation 可指派給 Agent
Conversation SHALL 可指派給同一 Workspace 的任一 Agent（或取消指派）。

#### Scenario: 指派給 Agent
- **WHEN** 客服將 Conversation 的 `assigned_to` 設為某個 User id
- **THEN** 系統 SHALL 更新 `assigned_to` 欄位，且該 User 必須屬於同一 Workspace

#### Scenario: 取消指派
- **WHEN** 客服將 `assigned_to` 設為 null
- **THEN** 系統 SHALL 允許，Conversation 變為未指派狀態

### Requirement: last_message_at 紀錄最後訊息時間
每次新增 Message 時，系統 SHALL 自動更新對應 Conversation 的 `last_message_at`，供收件匣排序使用。

#### Scenario: 新訊息更新時間戳
- **WHEN** 一筆新的 Message 被寫入某個 Conversation
- **THEN** 該 Conversation 的 `last_message_at` SHALL 更新為該訊息的 `sent_at`
