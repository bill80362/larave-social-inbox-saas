## ADDED Requirements

### Requirement: Display internal notes on conversation ViewPage
The system SHALL display all notes associated with a conversation in a dedicated section at the bottom of the ViewPage, showing the note body, author name, and created timestamp. Notes SHALL be ordered by `created_at` ascending.

#### Scenario: Notes section visible on ViewPage
- **WHEN** agent is viewing a conversation that has notes
- **THEN** each note shows the body text, author's name, and human-readable timestamp

#### Scenario: No notes exist
- **WHEN** a conversation has no notes
- **THEN** an empty state message is shown in the notes section (e.g., "No internal notes yet")

### Requirement: Add an internal note via action
The system SHALL provide an "Add Note" action (button) in the notes section or as a header action on the ViewPage. Clicking it opens a modal with a required `Textarea` field. On save, a new `Note` record SHALL be created linked to the conversation and the authenticated user.

#### Scenario: Agent adds a note
- **WHEN** agent clicks "Add Note", enters text, and confirms
- **THEN** a new Note record is created with `conversation_id`, `user_id = auth()->id()`, and the entered body
- **AND** the note appears in the notes list on the page

#### Scenario: Empty note rejected
- **WHEN** agent submits the Add Note modal with an empty body
- **THEN** a validation error is shown and no Note record is created
