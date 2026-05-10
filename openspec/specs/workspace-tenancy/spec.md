## ADDED Requirements

### Requirement: Workspace 作為租戶單位
每個企業客戶 SHALL 擁有一個獨立的 Workspace，所有核心資料（channels, contacts, conversations, messages）都 SHALL 隸屬於某個 workspace，且不得跨 workspace 存取。

#### Scenario: 建立 Workspace
- **WHEN** 系統初始化一個新企業帳號
- **THEN** 系統 SHALL 建立一筆 workspace 紀錄，包含 name 與 slug

#### Scenario: 租戶隔離
- **WHEN** 已登入的 Agent 查詢任何資源（Channel, Contact, Conversation, Message）
- **THEN** 系統 SHALL 只回傳屬於該 Agent 所在 workspace 的資料

### Requirement: Agent 屬於單一 Workspace
每位 Agent（User）SHALL 屬於一個且只有一個 Workspace，透過 `users.workspace_id` 欄位綁定。Agent 的 role SHALL 為 `owner` 或 `agent`。

#### Scenario: Agent 登入後自動 scope
- **WHEN** Agent 登入後發出任何資源查詢
- **THEN** 所有 Model 的 Global Scope SHALL 自動套用 `workspace_id = auth()->user()->workspace_id`

#### Scenario: 未登入時不套用 scope
- **WHEN** 沒有已認證的 User（如 CLI seed 或未登入請求）
- **THEN** Global Scope SHALL 不套用，避免報錯
