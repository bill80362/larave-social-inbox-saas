## ADDED Requirements

### Requirement: Platform-specific credential form fields
The Channel form SHALL render different credential input fields based on the selected platform value. Credential fields SHALL be hidden for platforms they do not belong to.

#### Scenario: LINE platform selected
- **WHEN** admin selects Platform = LINE in the Channel form
- **THEN** the form SHALL show `channel_secret`, `channel_access_token`, and `destination` fields, and hide fields for other platforms

#### Scenario: Facebook platform selected
- **WHEN** admin selects Platform = Facebook in the Channel form
- **THEN** the form SHALL show `verify_token` and `page_access_token` fields, and hide LINE and Google fields

#### Scenario: Instagram platform selected
- **WHEN** admin selects Platform = Instagram in the Channel form
- **THEN** the form SHALL show `verify_token` and `page_access_token` fields, and hide LINE and Google fields

#### Scenario: Google Business platform selected
- **WHEN** admin selects Platform = Google Business in the Channel form
- **THEN** the form SHALL show `access_token` and `account_id` fields, and hide LINE and Facebook/Instagram fields

### Requirement: Credentials stored as encrypted JSON
The Channel form SHALL encode all credential fields into a single JSON string and store it via the `credentials` column with Laravel's `encrypted` cast. On edit, the stored JSON SHALL be decoded back into individual form fields.

#### Scenario: Create channel with credentials
- **WHEN** admin fills the credential fields and saves a new Channel
- **THEN** the `credentials` column SHALL contain an encrypted JSON string with only the keys relevant to the selected platform

#### Scenario: Edit channel restores credential fields
- **WHEN** admin opens an existing Channel for editing
- **THEN** each credential form field SHALL be pre-populated from the decoded JSON stored in `credentials`

### Requirement: Credential fields are masked by default
Token and secret fields in the Channel form SHALL be rendered as password inputs that are hidden by default, with a toggle to reveal the value.

#### Scenario: Token field hidden by default
- **WHEN** admin views the Channel create or edit form
- **THEN** `channel_access_token`, `page_access_token`, and `access_token` fields SHALL display masked characters (•••) until the reveal icon is clicked

### Requirement: Credentials never exposed in list or detail views
The `credentials` JSON SHALL never appear as a table column, infolist entry, or any read-only view component.

#### Scenario: Channel list shows no credential data
- **WHEN** admin views the Channel list page
- **THEN** no column shows raw or decoded credential values
