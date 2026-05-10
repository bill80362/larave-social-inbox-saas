## 1. ConversationResource 骨架

- [x] 1.1 執行 `php artisan make:filament-resource Conversation --view --no-interaction` 建立 Resource 骨架
- [x] 1.2 設定 `ConversationResource::$navigationLabel`、`$navigationIcon`（Heroicon）、`$navigationGroup`
- [x] 1.3 在 `ConversationResource::table()` 加入 columns：contact display_name（關聯）、channel name（關聯）、platform badge、status badge、assigned agent name（關聯）、last_message_at（human readable）
- [x] 1.4 加入 `->defaultSort('last_message_at', 'desc')`
- [x] 1.5 加入 SelectFilter：status（ConversationStatus enum）
- [x] 1.6 加入 SelectFilter：platform（透過 channel 關聯，Platform enum options）
- [x] 1.7 加入 SelectFilter：assigned_to（workspace 內的 User 列表）
- [x] 1.8 加入 table search by contact `display_name`（`->searchQuery()` 或 `->modifyQueryUsing()` join contacts）

## 2. ConversationResource ViewPage

- [x] 2.1 確認 `ViewConversation` Page 存在（`make:filament-resource` 已產生），設定 `protected string $view = 'filament.conversations.view'`
- [x] 2.2 建立 `resources/views/filament/conversations/view.blade.php`（繼承 Filament ViewRecord layout）
- [x] 2.3 在 Blade view 加入：聯絡人資訊區（display_name、platform_user_id、channel name/platform）
- [x] 2.4 在 Blade view 加入：訊息 timeline（依 sent_at ASC 排列，inbound 靠左/outbound 靠右）
- [x] 2.5 Timeline 依 message type 渲染：text 顯示 content；image/audio/video/file 顯示 attachments URL 連結；review 顯示 content 加 Review label；sticker 顯示 URL 連結
- [x] 2.6 在 Blade view 底部加入：內部備註列表（notes，含 body、author name、created_at）
- [x] 2.7 加入「No internal notes yet」空狀態（當 notes 為空時）

## 3. Conversation Header Actions

- [x] 3.1 在 `ViewConversation::getHeaderActions()` 加入「Change Status」`Action`（modal Select，options 為 ConversationStatus）
- [x] 3.2 Change Status action 儲存後更新 `$record->status` 並 `$this->redirect($this->getResource()::getUrl('view', ['record' => $record]))`
- [x] 3.3 加入「Assign」`Action`（modal Select，options 為 workspace 內的 User 列表，含「Unassigned」null 選項）
- [x] 3.4 Assign action 儲存後更新 `$record->assigned_to`
- [x] 3.5 加入「Add Note」`Action`（modal Textarea，required，儲存後建立 Note record）

## 4. ChannelResource

- [x] 4.1 執行 `php artisan make:filament-resource Channel --no-interaction` 建立骨架
- [x] 4.2 在 `ChannelResource::table()` 加入 columns：name、platform badge、platform_account_id、is_active badge、created_at
- [x] 4.3 確認 `credentials` 欄位不出現在任何 column 或 infolist
- [x] 4.4 在 Resource 上禁用 Create、Edit、Delete：移除 `CreateChannel`、`EditChannel` pages，移除 DeleteAction
- [x] 4.5 設定 `$navigationLabel`、`$navigationIcon`

## 5. Contact model 補充

- [x] 5.1 確認 `Contact` model 有 `display_name` 欄位（讀取 migration 確認），若無則評估用 `platform_user_id` 作為 fallback 顯示

## 6. 測試

- [x] 6.1 執行 `php artisan make:test --phpunit ConversationListTest` 並撰寫：驗證列表只顯示當前 workspace 的 conversations
- [x] 6.2 撰寫 ConversationListTest：驗證 status filter 正確篩選
- [x] 6.3 撰寫 ConversationListTest：驗證 assigned_to filter 正確篩選
- [x] 6.4 執行 `php artisan make:test --phpunit ConversationViewTest` 並撰寫：驗證 ViewPage 可載入對應 conversation
- [x] 6.5 撰寫 ConversationViewTest：驗證 Change Status action 正確更新 status
- [x] 6.6 撰寫 ConversationViewTest：驗證 Assign action 正確更新 assigned_to
- [x] 6.7 撰寫 ConversationViewTest：驗證 Add Note action 建立 Note 並關聯正確 user
- [x] 6.8 撰寫 ConversationViewTest：驗證 Add Note action 空 body 被拒絕
- [x] 6.9 執行 `php artisan make:test --phpunit ChannelListTest` 並撰寫：驗證頻道列表只顯示當前 workspace 頻道、credentials 不顯示
- [x] 6.10 執行 `php artisan test --compact` 確認所有測試通過

## 7. 格式化與收尾

- [x] 7.1 執行 `vendor/bin/pint --dirty --format agent` 修正所有 PHP 格式問題
- [x] 7.2 執行 `npm run build`（確認 Filament assets 正確載入）
- [x] 7.3 執行 `php artisan filament:optimize --clear`（清除 Filament 快取）
