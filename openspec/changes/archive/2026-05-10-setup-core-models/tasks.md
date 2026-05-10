## 1. Migrations

- [x] 1.1 建立 `create_workspaces_table` migration（id, name, slug, timestamps）
- [x] 1.2 建立 `add_workspace_id_role_to_users_table` migration（workspace_id FK, role enum）
- [x] 1.3 建立 `create_channels_table` migration（含 platform enum, platform_account_id, credentials, is_active, unique key）
- [x] 1.4 建立 `create_contacts_table` migration（含 channel_id FK, platform_user_id, unique key）
- [x] 1.5 建立 `create_conversations_table` migration（含 status enum, assigned_to FK nullable, last_message_at）
- [x] 1.6 建立 `create_messages_table` migration（含 direction enum, type enum, content text, attachments JSON, sender_type, sender_id, sent_at）
- [x] 1.7 建立 `create_notes_table` migration（conversation_id FK, user_id FK, body text）
- [x] 1.8 執行 `php artisan migrate` 確認所有 migration 無誤

## 2. Enums

- [x] 2.1 建立 `App\Enums\Platform` enum（Instagram, Facebook, Line, GoogleBusiness）
- [x] 2.2 建立 `App\Enums\ConversationStatus` enum（Open, Pending, Resolved）
- [x] 2.3 建立 `App\Enums\MessageType` enum（Text, Image, Sticker, Audio, Video, File, Review）
- [x] 2.4 建立 `App\Enums\MessageDirection` enum（Inbound, Outbound）
- [x] 2.5 建立 `App\Enums\UserRole` enum（Owner, Agent）

## 3. Models

- [x] 3.1 建立 `Workspace` model（HasMany channels, users）
- [x] 3.2 更新 `User` model（BelongsTo workspace, role cast, UserRole enum）
- [x] 3.3 建立 `Channel` model（BelongsTo workspace, HasMany contacts/conversations, credentials encrypted cast, Platform enum cast, Global Scope）
- [x] 3.4 建立 `Contact` model（BelongsTo workspace/channel, HasMany conversations, Global Scope）
- [x] 3.5 建立 `Conversation` model（BelongsTo workspace/channel/contact, BelongsTo assignedTo, HasMany messages/notes, ConversationStatus enum cast, Global Scope）
- [x] 3.6 建立 `Message` model（BelongsTo conversation, MessageType/MessageDirection enum cast, attachments JSON cast）
- [x] 3.7 建立 `Note` model（BelongsTo conversation/user）
- [x] 3.8 在 `Conversation` model 加入 Observer 或 `creating`/`updating` boot，於新增 Message 後自動更新 `last_message_at`

## 4. Factories

- [x] 4.1 建立 `WorkspaceFactory`
- [x] 4.2 建立 `ChannelFactory`（用 Platform enum，fake credentials）
- [x] 4.3 建立 `ContactFactory`（關聯 channel）
- [x] 4.4 建立 `ConversationFactory`（預設 status=open）
- [x] 4.5 建立 `MessageFactory`（預設 type=text, direction=inbound）
- [x] 4.6 建立 `NoteFactory`

## 5. Seeder

- [x] 5.1 更新 `DatabaseSeeder`：建立 1 個 workspace、1 個 owner user、1 個 agent user
- [x] 5.2 seed 2 個 channels（LINE + Google Business 各一）
- [x] 5.3 seed 5 個 contacts（分佈在不同 channel）
- [x] 5.4 seed 10 個 conversations（open/pending/resolved 各有）
- [x] 5.5 seed 每個 conversation 3–5 則 messages（含 inbound/outbound）
- [x] 5.6 seed 每個 conversation 1 則 note
- [x] 5.7 執行 `php artisan db:seed` 確認假資料正確填入

## 6. Tests

- [x] 6.1 建立 `WorkspaceTenancyTest`：驗證 Global Scope 只回傳同 workspace 資料
- [x] 6.2 建立 `ChannelManagementTest`：驗證重複 (workspace_id, platform, platform_account_id) 無法建立
- [x] 6.3 建立 `ContactIdentityTest`：驗證重複 (channel_id, platform_user_id) 無法建立
- [x] 6.4 建立 `ConversationLifecycleTest`：驗證狀態流轉（open→pending→resolved→open）
- [x] 6.5 建立 `ConversationLifecycleTest`：驗證指派與取消指派
- [x] 6.6 建立 `ConversationLifecycleTest`：驗證新增 Message 後 last_message_at 自動更新
- [x] 6.7 建立 `MessageContentTest`：驗證各 type 的 Message 可正確建立
- [x] 6.8 建立 `InternalNotesTest`：驗證 Note 關聯 conversation 與 user
- [x] 6.9 執行 `php artisan test --compact` 確認所有測試通過

## 7. 格式化

- [x] 7.1 執行 `vendor/bin/pint --dirty --format agent` 修正所有 PHP 格式問題
