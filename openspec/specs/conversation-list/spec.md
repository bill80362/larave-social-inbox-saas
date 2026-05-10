## ADDED Requirements

### Requirement: Display paginated conversation list
The system SHALL display a paginated table of conversations scoped to the authenticated user's workspace, ordered by `last_message_at` descending (null values last).

#### Scenario: Agent views inbox
- **WHEN** an authenticated agent navigates to the Conversations list page
- **THEN** only conversations belonging to the agent's workspace are shown
- **AND** conversations are ordered by most recent message activity first

#### Scenario: No conversations exist
- **WHEN** the workspace has no conversations
- **THEN** an empty state message is shown

### Requirement: Conversation list shows key metadata columns
The system SHALL display the following columns: contact name (via `contact.display_name`), channel name (via `channel.name`), platform badge, conversation status badge, assigned agent name (nullable), and `last_message_at` human-readable timestamp.

#### Scenario: All columns render
- **WHEN** a conversation has a contact, channel, assigned agent, and messages
- **THEN** all metadata columns display correct values

#### Scenario: Unassigned conversation
- **WHEN** a conversation has `assigned_to = null`
- **THEN** the assigned agent column shows an empty/dash value

### Requirement: Filter conversations by status, platform, and assigned agent
The system SHALL provide SelectFilter controls for: `status` (Open/Pending/Resolved), `platform` (Instagram/Facebook/LINE/Google Business via channel relationship), and `assigned_to` (list of agents in the workspace).

#### Scenario: Filter by status
- **WHEN** agent selects "Resolved" in the status filter
- **THEN** only resolved conversations are shown

#### Scenario: Filter by platform
- **WHEN** agent selects "LINE" in the platform filter
- **THEN** only conversations from LINE channels are shown

#### Scenario: Filter by assigned agent
- **WHEN** agent selects themselves in the assigned filter
- **THEN** only conversations assigned to that agent are shown

### Requirement: Search conversations by contact name
The system SHALL provide a table search that filters conversations by the associated contact's `display_name`.

#### Scenario: Search matches contact name
- **WHEN** agent types a name in the search box
- **THEN** only conversations whose contact display_name contains the search string are shown

#### Scenario: No match
- **WHEN** agent searches for a non-existent name
- **THEN** empty state is shown
