## Why

Currently, channels can only be created via database seeders — there is no UI for admins to connect social platforms or configure API credentials. This blocks real deployment: a business cannot onboard itself without developer intervention. The Reply Engine is also blocked on production until an admin can supply valid credentials through the interface.

## What Changes

- **Add Channel Create/Edit UI** to `ChannelResource` in Filament, replacing the existing read-only restriction.
- **Platform-specific credential fields**: form inputs that adapt dynamically based on the selected platform (LINE shows `channel_secret` + `channel_access_token`; Facebook/Instagram shows `verify_token` + `page_access_token`; Google Business shows `access_token` + `account_id`).
- **Secure credential handling**: credentials are never exposed in list columns or view panels — only editable via the form.
- **Remove the read-only constraint** currently enforced by `disableCreateButton()` / `disableEditButton()` in `ChannelResource`.
- **Activate/deactivate toggle** available on the edit form.

## Capabilities

### New Capabilities
- `channel-credentials-form`: Platform-specific credentials form with conditional field rendering per platform type; credentials encrypted at rest and never exposed in list or detail views.

### Modified Capabilities
- `channel-management`: Remove "Channel list is read-only (no Create/Edit/Delete)" requirement; replace with Create and Edit operations permitted for workspace admin; Delete permitted with confirmation.

## Impact

- `app/Filament/Resources/ChannelResource.php` — add form schema, enable create/edit/delete actions
- `openspec/specs/channel-management/spec.md` — delta: remove read-only restriction, add CRUD requirements
- `openspec/specs/channel-credentials-form/spec.md` — new spec for platform-conditional credential form
- No migration needed (schema already supports `credentials` encrypted column)
- No dependency changes
