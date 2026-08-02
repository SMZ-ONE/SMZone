# 05 - CODING STANDARDS

## PHP 8.4
- Use constructor property promotion, enums, readonly where needed.
- No logic in Controller/Resource. Move to Service: App\Services\ContentService

## Enum vs Boolean - FINAL RULE
- Boolean: is_active, is_connected, is_verified, is_scheduled
- Enum: status with 3+ states

Correct:
Content::create(['is_active' => true, 'status' => ContentStatus::DRAFT]);

Wrong:
Content::create(['status' => 'draft']); // never string

## Laravel
- All long tasks -> Job: PublishPostJob, SyncProductsJob
- No external API call in Request lifecycle.
- Use Pest for tests.
- Use Form Requests.

## Filament v4
- Check version: composer show filament/filament
- NavigationGroup: "SMZone" for all business modules
- Sort: 1 Dashboard, 2 Accounts, 3 Content, 4 AI Center, 5 Products, 6 Analytics
- Correct namespaces: Filament\Schemas\Components, Filament\Tables\Columns
- No custom CSS in Phase 1.
