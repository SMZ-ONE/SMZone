# 05 - CODING STANDARDS

## PHP / Laravel
- PHP 8.4 features allowed.
- Use Pest for tests (not PHPUnit syntax).
- Use Enums for status, boolean for flags.

## When to use Enum vs Boolean
- Boolean: is_active, is_verified, is_connected
- Enum: ContentStatus [draft, scheduled, published, failed], Platform [instagram, facebook...]

## Filament
- NavigationGroup = "SMZone" for all business modules.
- NavigationSort: 1 Dashboard, 2 Accounts, 3 Content, 4 AI Center, 5 Products, 6 Analytics.

## Services
- No logic in Resource. Move to Service class: `App\Services\ContentService`
