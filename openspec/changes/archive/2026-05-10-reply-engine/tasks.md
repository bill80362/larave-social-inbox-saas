## 1. Database Migration

- [x] 1.1 建立 migration：`messages` 資料表新增 `platform_message_id` nullable string 欄位
- [x] 1.2 執行 `php artisan migrate`

## 2. Message Model 更新

- [x] 2.1 在 `app/Models/Message.php` 的 `#[Fillable]` 新增 `platform_message_id`
- [x] 2.2 更新 `MessageFactory`：`platform_message_id` 預設為 null

## 3. Reply Driver 實作

- [x] 3.1 建立 `app/Actions/Reply/Drivers/LineReplyDriver.php`：呼叫 LINE Push API，讀取 `credentials.channel_access_token`，回傳平台訊息 ID
- [x] 3.2 建立 `app/Actions/Reply/Drivers/FacebookReplyDriver.php`：呼叫 Facebook Graph API Send，讀取 `credentials.page_access_token`，回傳 `message_id`
- [x] 3.3 建立 `app/Actions/Reply/Drivers/InstagramReplyDriver.php`：與 Facebook Driver 結構相同，讀取 `credentials.page_access_token`
- [x] 3.4 建立 `app/Actions/Reply/Drivers/GoogleBusinessReplyDriver.php`：呼叫 Google My Business Review Reply API，讀取 `credentials.access_token`

## 4. SendReplyAction 實作

- [x] 4.1 建立 `app/Actions/Reply/SendReplyAction.php`：接受 `Conversation $conversation`、`string $content`、`User $agent`，用 `match` 依平台選擇 Driver，Driver 發送成功後建立 outbound Message 紀錄（含 `platform_message_id`）

## 5. Filament UI 更新

- [x] 5.1 在 `ViewConversation::getHeaderActions()` 新增 `Action::make('reply')` Header Action，包含 required `Textarea` 表單欄位
- [x] 5.2 Action 的 `->action()` closure 呼叫 `SendReplyAction`，成功後 `$this->refreshFormData([])` 或 `redirect()` 刷新頁面，失敗時拋例外讓 Filament 顯示 notification
- [x] 5.3 更新 Blade `resources/views/filament/pages/view-conversation.blade.php`（或對應 view），確保 outbound 訊息有不同的視覺樣式（右對齊或不同背景色）

## 6. 測試

- [x] 6.1 建立 `tests/Feature/Reply/SendReplyActionTest.php`：測試 LINE / FB / IG / Google 各 Driver 被正確路由呼叫，outbound Message 被寫入 DB，含 `platform_message_id`
- [x] 6.2 建立 `tests/Feature/Reply/LineReplyDriverTest.php`：`Http::fake()` mock LINE API，測試成功回覆、缺少 token 拋例外、API 錯誤拋例外
- [x] 6.3 建立 `tests/Feature/Reply/FacebookReplyDriverTest.php`：mock Facebook API，測試成功、缺少 token、API 錯誤
- [x] 6.4 建立 `tests/Feature/Reply/InstagramReplyDriverTest.php`：mock Instagram API，測試成功、缺少 token、API 錯誤
- [x] 6.5 建立 `tests/Feature/Reply/GoogleBusinessReplyDriverTest.php`：mock Google API，測試成功、缺少 token、API 錯誤
- [x] 6.6 建立 `tests/Feature/Reply/ReplyUiTest.php`：Filament livewire test，測試 Reply Action 送出成功寫入 Message，送出空內容顯示驗證錯誤

## 7. 格式化與收尾

- [x] 7.1 執行 `vendor/bin/pint --dirty --format agent` 修正所有 PHP 格式
- [x] 7.2 執行 `php artisan test --compact` 確認全部測試通過
