## ADDED Requirements

### Requirement: Display channel list for the workspace
The system SHALL provide a read-only `ChannelResource` list page showing all channels scoped to the authenticated user's workspace. Columns SHALL include: channel name, platform badge, `platform_account_id`, active status badge, and `created_at`.

#### Scenario: Agent views channel list
- **WHEN** agent navigates to the Channels page
- **THEN** only channels in their workspace are displayed

#### Scenario: Credentials column not exposed
- **WHEN** the channel list or any channel view is displayed
- **THEN** the `credentials` field is never shown in any column or panel

### Requirement: Channel list is read-only (no Create/Edit/Delete)
The system SHALL disable Create, Edit, and Delete operations on `ChannelResource`. The list page SHALL have no action buttons for modifying records.

#### Scenario: No create button shown
- **WHEN** agent visits the Channels list page
- **THEN** there is no "New Channel" button or equivalent

#### Scenario: No row actions available
- **WHEN** agent views the channel list rows
- **THEN** no edit or delete action buttons appear on any row
