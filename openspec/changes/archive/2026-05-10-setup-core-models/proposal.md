## Why

目前專案只有 Laravel 骨架，沒有任何業務 Model。在建立收件匣 UI 之前，必須先建立完整的資料基礎層（Migration、Model、Factory、Seeder），讓後續所有功能都能在正確的資料結構上進行開發與測試。

## What Changes

- 新增 `workspaces` 資料表與 `Workspace` Model（租戶單位）
- 新增 `channels` 資料表與 `Channel` Model（平台帳號，同平台可多筆）
- 新增 `contacts` 資料表與 `Contact` Model（外部客人，per channel 身份）
- 新增 `conversations` 資料表與 `Conversation` Model（對話 thread）
- 新增 `messages` 資料表與 `Message` Model（單則訊息，含 type + attachments）
- 新增 `notes` 資料表與 `Note` Model（客服內部備註）
- 擴充 `users` 表，加入 `workspace_id` 與 `role` 欄位（owner / agent）
- 每個 Model 加入對應的 Factory 與 Global Scope（自動 scope 至目前 workspace）
- 新增 `DatabaseSeeder` 填入開發用假資料

## Capabilities

### New Capabilities

- `workspace-tenancy`: Workspace 與 User 的租戶關係，含 Global Scope 自動隔離
- `channel-management`: Channel 連線管理，支援同平台多帳號，唯一鍵保護
- `contact-identity`: Contact 的平台身份（per channel），MVP 不跨 channel 合併
- `conversation-lifecycle`: Conversation 的狀態流轉（open / pending / resolved）與指派
- `message-content`: Message 的結構化內容（type enum + content text + attachments JSON）
- `internal-notes`: 客服內部備註（不對外顯示）

### Modified Capabilities

（無，這是全新建立）

## Impact

- **Models**: `Workspace`、`Channel`、`Contact`、`Conversation`、`Message`、`Note`、`User`（擴充）
- **Migrations**: 6 個新 migration + 1 個 alter users migration
- **Factories**: 每個 Model 對應一個 Factory
- **Seeders**: `DatabaseSeeder` 更新，填入 1 個 workspace、2 個 channels、多筆 conversations / messages
- **Non-goals**: 不包含任何 Filament Resource / UI；不實作 Webhook；不串接任何外部 API；不實作跨 channel Contact 合併
