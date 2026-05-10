## ADDED Requirements

### Requirement: Google Business webhook 驗簽
系統 SHALL 在接收 Google Business Reviews webhook 時驗證請求的 JWT token（Bearer），使用 Google 公鑰端點（`https://www.googleapis.com/oauth2/v3/certs`）驗證 RS256 簽名，驗證失敗時 SHALL 回傳 403。公鑰 SHALL 快取 1 小時以減少外部請求。

#### Scenario: JWT 驗簽成功
- **WHEN** POST `/webhook/google-business` 收到有效的 Google JWT Bearer token
- **THEN** 系統 SHALL 回傳 200 並 dispatch Queue Job

#### Scenario: JWT 驗簽失敗
- **WHEN** POST `/webhook/google-business` 的 Bearer token 無效或過期
- **THEN** 系統 SHALL 回傳 403，不處理 payload

### Requirement: Google Business Review payload 解析
系統 SHALL 解析 Google Business Reviews webhook payload，處理新評論事件，建立 Message 紀錄，type SHALL 為 `review`，`content` 為評論文字。

#### Scenario: 新評論
- **WHEN** Google Business 傳入新評論通知 payload
- **THEN** 系統 SHALL 建立 `type=review, direction=inbound` 的 Message，`content` 包含評論文字
