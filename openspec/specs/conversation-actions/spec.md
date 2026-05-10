## ADDED Requirements

### Requirement: Change conversation status via header action
The system SHALL provide a "Change Status" header action on the ViewPage that opens a modal with a `Select` field for choosing `open`, `pending`, or `resolved`. Upon save, the conversation `status` SHALL be updated and the page SHALL reflect the new status.

#### Scenario: Agent changes status to resolved
- **WHEN** agent clicks "Change Status" and selects "Resolved" and confirms
- **THEN** `conversations.status` is updated to `resolved`
- **AND** the status badge on the page updates to reflect the change

#### Scenario: Agent cancels status change
- **WHEN** agent opens the modal and clicks cancel
- **THEN** no changes are made

### Requirement: Assign conversation to an agent via header action
The system SHALL provide an "Assign" header action on the ViewPage that opens a modal with a `Select` field listing all users in the workspace. Selecting an agent and saving SHALL update `conversations.assigned_to`. Selecting "Unassigned" (null) SHALL clear the assignment.

#### Scenario: Agent assigns conversation to themselves
- **WHEN** agent selects their name from the assign dropdown and confirms
- **THEN** `conversations.assigned_to` is set to the agent's user ID

#### Scenario: Agent unassigns a conversation
- **WHEN** agent selects "Unassigned" from the dropdown and confirms
- **THEN** `conversations.assigned_to` is set to null

#### Scenario: Only workspace agents appear in the dropdown
- **WHEN** agent opens the assign modal
- **THEN** only users belonging to the same workspace are listed
