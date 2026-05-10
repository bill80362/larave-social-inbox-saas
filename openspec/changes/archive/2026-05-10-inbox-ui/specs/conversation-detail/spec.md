## ADDED Requirements

### Requirement: Display conversation detail page with message timeline
The system SHALL render a ViewPage for a single conversation showing: contact info (display_name, platform_user_id, channel), conversation metadata (status badge, assigned agent, last_message_at), and a chronological message timeline.

#### Scenario: Agent opens a conversation
- **WHEN** agent clicks a conversation row in the list
- **THEN** the ViewPage loads and displays all messages in `sent_at` ascending order

#### Scenario: Messages display direction styling
- **WHEN** a message has `direction = inbound`
- **THEN** it is visually distinguished from outbound messages (e.g., aligned left vs right)

#### Scenario: Conversation belongs to another workspace
- **WHEN** agent attempts to access a conversation URL of another workspace
- **THEN** a 404 or 403 response is returned (Global Scope enforces this)

### Requirement: Message timeline renders content by type
The system SHALL render message content based on `type`: `text` displays plain text; `image` displays a linked thumbnail or URL; `audio`/`video`/`file` display a download link; `sticker` displays a URL link; `review` displays the text content.

#### Scenario: Text message
- **WHEN** a message has `type = text`
- **THEN** the `content` field is rendered as plain text in the timeline

#### Scenario: Image message
- **WHEN** a message has `type = image` with a URL in `attachments`
- **THEN** the attachment URL is rendered as a link (MVP: no inline preview required)

#### Scenario: Review message
- **WHEN** a message has `type = review`
- **THEN** the `content` field is rendered with a "Review" label

### Requirement: ViewPage shows contact info panel
The system SHALL display a sidebar or info section with the contact's `display_name`, `platform_user_id`, and the channel name and platform.

#### Scenario: Contact info visible
- **WHEN** agent is on the ViewPage
- **THEN** contact display_name and channel information are always visible without scrolling
