## MODIFIED Requirements

### Requirement: Messages display direction styling
The system SHALL render a ViewPage for a single conversation showing: contact info (display_name, platform_user_id, channel), conversation metadata (status badge, assigned agent, last_message_at), and a chronological message timeline where inbound and outbound messages are visually distinguished.

#### Scenario: Agent opens a conversation
- **WHEN** agent clicks a conversation row in the list
- **THEN** the ViewPage loads and displays all messages in `sent_at` ascending order

#### Scenario: Inbound message styling
- **WHEN** a message has `direction = inbound`
- **THEN** it SHALL be visually aligned or styled differently from outbound (e.g., left-aligned, grey background)

#### Scenario: Outbound message styling
- **WHEN** a message has `direction = outbound`
- **THEN** it SHALL be visually distinguished (e.g., right-aligned or different colour) to indicate it was sent by an agent

#### Scenario: Conversation belongs to another workspace
- **WHEN** agent attempts to access a conversation URL of another workspace
- **THEN** a 404 or 403 response is returned (Global Scope enforces this)
