## 1. ChannelResource Form Schema

- [x] 1.1 Add `platform` Select with `->live()` to `ChannelResource::form()`, using `Platform` enum options
- [x] 1.2 Add `name` TextInput and `platform_account_id` TextInput to the form
- [x] 1.3 Add LINE credential fields (`channel_secret`, `channel_access_token`, `destination`) with `->visible()` conditional on platform = LINE; mark token fields as `->password()->revealable()`
- [x] 1.4 Add Facebook/Instagram credential fields (`verify_token`, `page_access_token`) with `->visible()` conditional on platform ∈ {Facebook, Instagram}; mark `page_access_token` as `->password()->revealable()`
- [x] 1.5 Add Google Business credential fields (`access_token`, `account_id`) with `->visible()` conditional on platform = Google Business; mark `access_token` as `->password()->revealable()`
- [x] 1.6 Add `is_active` Toggle to the form

## 2. Credential Encode / Decode

- [x] 2.1 Implement `mutateFormDataBeforeCreate()` on `CreateChannel` page to `json_encode` flat credential fields into `credentials` key and remove the individual flat keys
- [x] 2.2 Implement `mutateFormDataBeforeFill()` on `EditChannel` page to `json_decode` the stored `credentials` JSON back into flat form fields
- [x] 2.3 Implement `mutateFormDataBeforeSave()` on `EditChannel` page (same as 2.1) to re-encode on save
- [x] 2.4 Clear credential fields when platform changes via `->afterStateUpdated()` on the platform Select

## 3. Enable CRUD Pages & Actions

- [x] 3.1 Register `CreateChannel` and `EditChannel` in `ChannelResource::getPages()`
- [x] 3.2 Add `DeleteAction` to `recordActions()` in the table definition
- [x] 3.3 Add `EditAction` to `recordActions()` in the table definition
- [x] 3.4 Restore the toolbar create button (remove empty `toolbarActions([])` or add `CreateAction`)

## 4. Validation

- [x] 4.1 Add `->unique(ignoreRecord: true)` validation combining `workspace_id`, `platform`, `platform_account_id` to prevent duplicate channels
- [x] 4.2 Add `->required()` to `name`, `platform`, and `platform_account_id` fields

## 5. Tests

- [x] 5.1 Write `tests/Feature/Channels/ChannelResourceCreateTest.php`: creating a LINE channel saves correct encrypted credentials
- [x] 5.2 Write `tests/Feature/Channels/ChannelResourceEditTest.php`: editing a channel restores credential fields and re-encodes on save
- [x] 5.3 Write `tests/Feature/Channels/ChannelResourceValidationTest.php`: duplicate `(workspace_id, platform, platform_account_id)` shows validation error; required fields validated
- [x] 5.4 Write `tests/Feature/Channels/ChannelResourceDeleteTest.php`: deleting a channel removes the record
- [x] 5.5 Write `tests/Feature/Channels/ChannelListTest.php`: channel list shows no credentials column; list is scoped to current workspace

## 6. Formatting & Final Checks

- [x] 6.1 Run `vendor/bin/pint --dirty --format agent` on all modified PHP files
- [x] 6.2 Run `php artisan test --compact` and confirm all tests pass
