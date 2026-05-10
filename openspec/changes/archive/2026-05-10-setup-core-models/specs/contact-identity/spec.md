## ADDED Requirements

### Requirement: Contact 代表平台上的外部客人身份
每個 Contact SHALL 代表某個 Channel 上的一個外部客人，以 `(channel_id, platform_user_id)` 唯一識別。MVP 階段 Contact 不跨 Channel 合併。

#### Scenario: 新 Contact 自動建立
- **WHEN** 系統收到來自某 Channel 的新客人訊息，且該 platform_user_id 尚未存在
- **THEN** 系統 SHALL 自動建立一筆新的 Contact 紀錄

#### Scenario: 重複 Contact 防護
- **WHEN** 相同 `(channel_id, platform_user_id)` 的 Contact 已存在
- **THEN** 系統 SHALL 使用現有 Contact，不新增重複紀錄

### Requirement: Contact 跨 Channel 各自獨立
同一個真實客人若透過不同 Channel 傳訊息，SHALL 建立各自獨立的 Contact 紀錄，不自動合併。

#### Scenario: 同一客人不同 Channel
- **WHEN** 客人 A 在「台南 LINE」和「高雄 LINE」各傳一則訊息
- **THEN** 系統 SHALL 建立兩筆 Contact，分別屬於各自的 Channel
