## 1. 路由與 Controller 基礎

- [x] 1.1 在 `routes/api.php` 新增 `POST /webhook/{platform}` 路由，指向 `WebhookController@handle`
- [x] 1.2 在 `routes/api.php` 新增 `GET /webhook/facebook` 路由，處理 Facebook challenge 驗證
- [x] 1.3 執行 `php artisan make:controller Webhook/WebhookController --no-interaction` 建立 Controller 骨架
- [x] 1.4 `WebhookController::handle()` 僅負責：讀取 Channel → 檢查 `is_active` → dispatch Job → 回傳 200

## 2. 驗簽 Middleware

- [x] 2.1 執行 `php artisan make:middleware VerifyLineSignature --no-interaction`，實作 HMAC-SHA256 驗簽
- [x] 2.2 執行 `php artisan make:middleware VerifyFacebookSignature --no-interaction`，實作 X-Hub-Signature-256 驗簽
- [x] 2.3 `VerifyInstagramSignature` 可重用 Facebook 相同邏輯（繼承或 trait）
- [x] 2.4 執行 `php artisan make:middleware VerifyGoogleBusinessSignature --no-interaction`，實作 JWT RS256 驗簽（使用 `Cache::remember` 快取公鑰 1 小時）
- [x] 2.5 在路由中套用對應 middleware：`->middleware('verify.line')` 等

## 3. Queue Jobs

- [x] 3.1 執行 `php artisan make:job ProcessLineWebhook --no-interaction`
- [x] 3.2 執行 `php artisan make:job ProcessFacebookWebhook --no-interaction`
- [x] 3.3 執行 `php artisan make:job ProcessInstagramWebhook --no-interaction`
- [x] 3.4 執行 `php artisan make:job ProcessGoogleBusinessWebhook --no-interaction`
- [x] 3.5 每個 Job 的 `handle()` 解析平台 payload，呼叫 `IngestMessageAction`

## 4. IngestMessageAction（共用邏輯）

- [x] 4.1 建立 `app/Actions/Webhook/IngestMessageAction.php`（invokable class）
- [x] 4.2 實作 `__invoke(Channel $channel, string $platformUserId, string $type, ?string $content, Carbon $sentAt): Message`
- [x] 4.3 find-or-create Contact：`Contact::firstOrCreate(['channel_id' => ..., 'platform_user_id' => ...])`
- [x] 4.4 find-or-create Conversation：找最近 open/pending Conversation，否則建立新 Conversation
- [x] 4.5 建立 Message 紀錄（direction=inbound）

## 5. LINE Job 實作

- [x] 5.1 解析 LINE payload `events` 陣列，foreach 每個 event
- [x] 5.2 支援 `message.type`: text → `MessageType::Text`、image → `MessageType::Image`、sticker → `MessageType::Sticker`
- [x] 5.3 非 message type 的 event 靜默跳過

## 6. Facebook Job 實作

- [x] 6.1 解析 Facebook payload `entry[].messaging[]` 陣列
- [x] 6.2 支援 `message.text` → `MessageType::Text`
- [x] 6.3 支援 `message.attachments[].type = image` → `MessageType::Image`
- [x] 6.4 實作 Facebook challenge GET handler（`hub.verify_token` 驗證）

## 7. Instagram Job 實作

- [x] 7.1 解析 Instagram payload（結構與 Facebook 相同）
- [x] 7.2 支援 text 和 image 訊息類型

## 8. Google Business Job 實作

- [x] 8.1 解析 Google Business Review payload
- [x] 8.2 建立 `type=review` 的 Message，`content` 為評論文字

## 9. 測試

- [x] 9.1 `php artisan make:test --phpunit WebhookLineTest`：驗簽成功/失敗、payload 解析、Message 建立、停用 Channel 忽略
- [x] 9.2 `php artisan make:test --phpunit WebhookFacebookTest`：驗簽成功/失敗、challenge GET、payload 解析
- [x] 9.3 `php artisan make:test --phpunit WebhookInstagramTest`：驗簽成功/失敗、payload 解析
- [x] 9.4 `php artisan make:test --phpunit WebhookGoogleBusinessTest`：JWT 驗簽成功/失敗、Review payload 解析
- [x] 9.5 `php artisan make:test --phpunit IngestMessageActionTest`：新職絡人建 Conversation、既有職絡人重用 Conversation、停用 Channel 不處理
- [x] 9.6 執行 `php artisan test --compact` 確認所有測試通過

## 10. 格式化與收尾

- [x] 10.1 執行 `vendor/bin/pint --dirty --format agent` 修正所有 PHP 格式問題
- [x] 10.2 確認 `routes/api.php` webhook 路由排除 CSRF（api middleware group 已自動排除）
