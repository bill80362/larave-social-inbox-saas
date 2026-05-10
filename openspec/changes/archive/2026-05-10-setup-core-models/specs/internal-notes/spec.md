## ADDED Requirements

### Requirement: Agent 可在 Conversation 留下內部備註
每個 Conversation SHALL 可附加一或多則 Note，Note 只對同 Workspace 的 Agent 可見，不對外部客人顯示。

#### Scenario: 新增備註
- **WHEN** Agent 在某個 Conversation 新增一則備註
- **THEN** 系統 SHALL 建立一筆 Note 紀錄，關聯到該 Conversation 與該 Agent

#### Scenario: 備註不對客人顯示
- **WHEN** 系統渲染 Conversation 的訊息串
- **THEN** Note SHALL 只在客服介面顯示，不出現在任何對外回傳的訊息中

### Requirement: Note 保存建立者與時間
每則 Note SHALL 記錄建立的 Agent（`user_id`）與建立時間（`created_at`），且建立後不可修改。

#### Scenario: 備註記錄建立者
- **WHEN** Agent A 建立一則備註
- **THEN** 系統 SHALL 將 `user_id` 設為 Agent A 的 id，且 `created_at` 自動填入
