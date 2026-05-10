## MODIFIED Requirements

### Requirement: Message 紀錄方向與發送者
每則 Message SHALL 記錄 `direction`（`inbound` 客人傳入 / `outbound` 客服回覆）與發送者（`sender_type` + `sender_id`），以及 `platform_message_id`（平台端訊息 ID，outbound 訊息送出成功後由平台回傳）。

#### Scenario: 客人傳入訊息
- **WHEN** 平台傳來客人的訊息
- **THEN** 系統 SHALL 建立 direction=`inbound`、sender_type=`contact` 的 Message，`platform_message_id` 為 null

#### Scenario: 客服回覆訊息
- **WHEN** 客服透過系統回覆
- **THEN** 系統 SHALL 建立 direction=`outbound`、sender_type=`agent` 的 Message，`platform_message_id` 為平台回傳的訊息 ID
