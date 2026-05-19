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
Channel SHALL 有 `is_active` 欄位，停用的 Channel SHALL 不接收新訊息——webhook handler 在驗簽通過後 SHALL 檢查 `is_active`，若為 `false` 則回傳 HTTP 200 但不 dispatch Queue Job，不建立任何 Message 或 Conversation。

#### Scenario: 停用 Channel
- **WHEN** `is_active` 設為 false
- **THEN** Channel 紀錄仍存在，不刪除歷史 Conversation

#### Scenario: 停用 Channel 拒絕新 webhook
- **WHEN** `is_active = false` 的 Channel 收到任何平台的 webhook（驗簽已通過）
- **THEN** 系統 SHALL 回傳 HTTP 200 但不 dispatch Job，不建立任何 Message 或 Conversation

### Requirement: Display channel list for the workspace
The system SHALL provide a `ChannelResource` list page showing all channels scoped to the authenticated user's workspace. Columns SHALL include: channel name, platform badge, `platform_account_id`, active status badge, and `created_at`.

#### Scenario: Agent views channel list
- **WHEN** agent navigates to the Channels page
- **THEN** only channels in their workspace are displayed

#### Scenario: Credentials column not exposed
- **WHEN** the channel list or any channel view is displayed
- **THEN** the `credentials` field is never shown in any column or panel

### Requirement: Workspace admin can create a Channel
The system SHALL allow a workspace admin to create a new Channel via the Filament admin panel, providing a name, platform, platform account ID, and platform-specific credentials.

#### Scenario: Admin creates a new LINE channel
- **WHEN** admin fills the Channel create form with valid LINE credentials and submits
- **THEN** a new Channel record SHALL be persisted with `workspace_id` set to the admin's workspace, `platform = line`, and `credentials` encrypted

#### Scenario: Duplicate channel rejected
- **WHEN** admin attempts to create a Channel with the same `(workspace_id, platform, platform_account_id)` as an existing Channel
- **THEN** the form SHALL show a validation error and no new record is created

### Requirement: Workspace admin can edit a Channel
The system SHALL allow a workspace admin to edit an existing Channel's name, `platform_account_id`, credentials, and `is_active` status.

#### Scenario: Admin updates channel access token
- **WHEN** admin opens an existing Channel, changes `channel_access_token`, and saves
- **THEN** the `credentials` column SHALL be updated with the new encrypted JSON containing the revised token

#### Scenario: Admin deactivates a channel via edit form
- **WHEN** admin opens an existing Channel and toggles `is_active` to false
- **THEN** the Channel record SHALL have `is_active = false` and webhook handler SHALL no longer dispatch jobs for it

### Requirement: Workspace admin can delete a Channel
The system SHALL allow a workspace admin to delete a Channel via a confirmation modal. Deleting a Channel SHALL also cascade-delete its associated Conversations and Messages.

#### Scenario: Admin deletes a channel with confirmation
- **WHEN** admin clicks Delete on a Channel and confirms the modal
- **THEN** the Channel record SHALL be removed from the database

