## REMOVED Requirements

### Requirement: Channel list is read-only (no Create/Edit/Delete)
**Reason**: Replaced by full CRUD management to allow workspace admins to self-serve channel configuration without developer access.
**Migration**: Existing read-only behaviour removed. Create, Edit, and Delete operations are now available to authenticated workspace admins.

## ADDED Requirements

### Requirement: Workspace admin can create a Channel
The system SHALL allow a workspace admin to create a new Channel via the Filament admin panel, providing a name, platform, platform account ID, and platform-specific credentials.

#### Scenario: Admin creates a new LINE channel
- **WHEN** admin fills the Channel create form with valid LINE credentials and submits
- **THEN** a new Channel record SHALL be persisted with `workspace_id` set to the admin's workspace, `platform = line`, and `credentials` encrypted

#### Scenario: Duplicate channel rejected
- **WHEN** admin attempts to create a Channel with the same `(workspace_id, platform, platform_account_id)` as an existing Channel
- **THEN** the form SHALL show a validation error and no new record is created

### Requirement: Workspace admin can edit a Channel
The system SHALL allow a workspace admin to edit an existing Channel's name, `platform_account_id`, credentials, and `is_active` status.

#### Scenario: Admin updates channel access token
- **WHEN** admin opens an existing Channel, changes `channel_access_token`, and saves
- **THEN** the `credentials` column SHALL be updated with the new encrypted JSON containing the revised token

#### Scenario: Admin deactivates a channel via edit form
- **WHEN** admin opens an existing Channel and toggles `is_active` to false
- **THEN** the Channel record SHALL have `is_active = false` and webhook handler SHALL no longer dispatch jobs for it

### Requirement: Workspace admin can delete a Channel
The system SHALL allow a workspace admin to delete a Channel via a confirmation modal. Deleting a Channel SHALL also cascade-delete its associated Conversations and Messages.

#### Scenario: Admin deletes a channel with confirmation
- **WHEN** admin clicks Delete on a Channel and confirms the modal
- **THEN** the Channel record SHALL be removed from the database
