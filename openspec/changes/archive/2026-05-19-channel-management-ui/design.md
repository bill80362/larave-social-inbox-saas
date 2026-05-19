## Context

`ChannelResource` currently has an empty `form()` schema and no record/toolbar actions — it is completely read-only. Channels can only be added via seeders. The Reply Engine (phase 4) requires valid platform credentials in the `credentials` column to send messages; without a UI for credential input, the system cannot be used in production without developer access to the database.

The `credentials` column is already encrypted via Laravel's `'encrypted'` cast. The cast stores and returns a **JSON string** (not an array) — all credential reads must go through `json_decode($channel->credentials, true)`.

## Goals / Non-Goals

**Goals:**
- Allow workspace admins to create, edit, and delete Channels via the Filament admin panel.
- Platform-specific credential form fields that adapt dynamically to the selected platform.
- Credentials are never displayed in list columns, table rows, or view panels.
- `is_active` toggle accessible in the edit form.

**Non-Goals:**
- OAuth flow / token refresh automation — credentials are entered manually for MVP.
- Platform connection testing / validation on save (nice-to-have, deferred).
- Role-based access control beyond the existing workspace scope.
- API endpoints for channel management.

## Decisions

### Decision 1: Conditional form fields via `->live()` + `->visible()`

Use `Select::make('platform')->live()` to trigger Livewire re-render on change. Each credential `TextInput` has `->visible(fn (Get $get) => in_array($get('platform'), [...]))`.

**Alternative considered**: Separate Create pages per platform. Rejected — too many pages, violates DRY, harder to maintain.

**Credential fields per platform:**
| Platform | Fields |
|---|---|
| LINE | `channel_secret`, `channel_access_token`, `destination` (LINE userId for push) |
| Facebook | `verify_token`, `page_access_token` |
| Instagram | `verify_token`, `page_access_token` |
| Google Business | `access_token`, `account_id` |

### Decision 2: Store credentials as JSON-encoded string

The `'encrypted'` cast encrypts a string. We must `json_encode($data)` before saving and `json_decode($data, true)` when reading. The form must decode the stored JSON into individual fields on load, and re-encode on save.

Use Filament's `->afterStateHydrated()` to decode credentials into flat form fields, and `->dehydrateStateUsing()` / `->mutateFormDataBeforeSave()` to re-encode back to JSON before persisting.

**Alternative**: Separate `credential_*` columns in the DB. Rejected — would expose sensitive fields in DB schema; encryption cast keeps them in one secure blob.

### Decision 3: Delete with confirmation modal

Use Filament's built-in `DeleteAction` on the table rows and the edit page. No soft-delete needed — if a channel is decommissioned, admins should `is_active = false` first, then delete when ready.

### Decision 4: Filament Pages — Create + Edit

Register both `CreateChannel` and `EditChannel` pages (they already exist as stubs from artisan scaffolding). Implement `form()` on the Resource (shared by both pages).

## Risks / Trade-offs

- **Credential exposure during edit**: TextInputs will show credentials as text. Mitigate with `->password()` + `->revealable()` on sensitive token fields so values are hidden by default.
- **Existing encrypted format**: The `'encrypted'` cast is already in production (seeded channels). The `mutateFormDataBeforeSave` approach must handle the case where `credentials` is already a JSON string (edit) vs. `null` (new channel).
- **Platform change on edit**: If admin changes the platform type on an existing channel, old credential keys become stale. Mitigate: clear credential fields when platform changes via `->afterStateUpdated(fn (Set $set) => $set('credentials_raw', null))`.

## Migration Plan

No database migration required — the `channels` table schema already has all necessary columns (`name`, `platform`, `platform_account_id`, `credentials`, `is_active`).

Steps to deploy:
1. Implement `ChannelResource::form()` with conditional credential fields.
2. Enable `CreateChannel` and `EditChannel` pages in `getPages()`.
3. Add `DeleteAction` to `recordActions()`.
4. Run tests; run Pint.
