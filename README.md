# Social Inbox SaaS MVP 規劃文件

## 產品定位

打造一套：

> 「將 IG / FB / LINE / Google 商家評論 集中於同一個客服收件匣的 SaaS 平台」

產品本質：

- Omnichannel Inbox
- Social Customer Support Hub
- Unified Customer Communication Platform

---

# 一、目標市場

## 主要客群

台灣中小企業：

- 餐廳
- 美業
- 診所
- 工作室
- 電商品牌
- 在地商家

---

## 核心痛點

目前店家常見問題：

- IG 訊息漏回
- FB 留言沒看到
- LINE 回覆混亂
- Google 評論沒處理
- 多位客服互相重複回覆
- 客戶訊息散落各平台

---

# 二、MVP 核心目標

第一版不追求完整 CRM。

而是：

> 「穩定收訊息 + 能直接回覆 + 不漏訊息」

---

# 三、第一版支援平台

## 1. Instagram

支援：

- IG DM
- IG 貼文留言
- Reels 留言

---

## 2. Facebook

支援：

- Messenger
- 粉專貼文留言
- 留言回覆

---

## 3. LINE Official Account

支援：

- LINE 官方帳號訊息
- webhook 接收
- reply message

---

## 4. Google Business

支援：

- Google 評論
- 評論回覆

---

# 四、MVP 功能拆解

# 必做功能（Must Have）

---

## 1. Unified Inbox（核心）

所有平台訊息集中：

- IG
- FB
- LINE
- Google

統一顯示於同一收件匣。

---

## 2. 對話列表（Conversation List）

顯示：

- 平台 icon
- 客戶名稱
- 最後訊息
- 未讀數
- 最後更新時間

---

## 3. 對話內容（Conversation View）

顯示：

- 完整聊天紀錄
- 留言串
- 評論紀錄
- 客服回覆

---

## 4. 回覆功能（最重要）

客服可直接於系統回覆：

- IG DM
- FB Messenger
- FB / IG 留言
- LINE
- Google 評論

---

## 5. 未讀狀態

支援：

- 未讀
- 已讀
- 待處理

---

## 6. 指派客服（Assign）

可將對話：

- 指派給特定客服
- 顯示目前負責人

---

## 7. Internal Note（內部備註）

客服內部留言：

- 客戶狀態
- 特殊需求
- 備註紀錄

---

# 可選功能（Should Have）

---

## 1. Tag 標籤

例如：

- 新客
- 訂單問題
- 客訴
- VIP

---

## 2. 快速回覆模板

例如：

- 感謝詢問
- 目前有現貨
- 請稍候協助確認

---

## 3. 通知系統

新訊息通知：

- Browser Notification
- Email Notification

---

# 五、第一版不要做（Avoid）

避免產品爆炸。

---

## 不建議第一版做：

- 社群排程發文
- AI 貼文生成
- CRM 系統
- 行銷自動化
- 廣告管理
- 複雜權限系統
- 多租戶 Enterprise 功能
- Threads
- TikTok

---

# 六、系統架構（MVP）

# 架構概念

```text
Platform Webhook
    ↓
Platform Adapter
    ↓
Normalize Message
    ↓
Conversation Service
    ↓
Realtime Inbox UI
    ↓
Reply Engine
