## ADDED Requirements

### Requirement: Workspace 可連接多個 Channel
一個 Workspace SHALL 可以連接多個社群平台帳號（Channel）。同一平台（如 LINE）SHALL 可連接多個帳號，以 `(workspace_id, platform, platform_account_id)` 的組合唯一識別。

#### Scenario: 新增 Channel
- **WHEN** 管理員在同一 workspace 新增兩個 platform 相同的 Channel，但 platform_account_id 不同
- **THEN** 系統 SHALL 允許並各自建立獨立的 Channel 紀錄

#### Scenario: 重複 Channel 防護
- **WHEN** 管理員嘗試新增與現有 Channel 相同 (workspace_id, platform, platform_account_id) 的紀錄
- **THEN** 系統 SHALL 拒絕並回傳唯一鍵衝突錯誤

### Requirement: Channel credentials 加密儲存
Channel 的 `credentials` 欄位（存放 API token 等敏感資訊）SHALL 在資料庫層使用 Laravel 的 `encrypted` cast 自動加解密，不得明文儲存。

#### Scenario: 讀取 credentials
- **WHEN** 程式碼讀取 `$channel->credentials`
- **THEN** 系統 SHALL 自動解密並回傳原始 JSON 結構

### Requirement: Channel 可停用
Channel SHALL 有 `is_active` 欄位，停用的 Channel 不應接收新訊息（由後續 Webhook 實作處理）。

#### Scenario: 停用 Channel
- **WHEN** `is_active` 設為 false
- **THEN** Channel 紀錄仍存在，不刪除歷史 Conversation
