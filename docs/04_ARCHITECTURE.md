# 04 - ARCHITECTURE

## Principles
- Design for multi-tenancy, implement in Phase 4.
- Every long operation = Queue Job.
- Never call external API in Request lifecycle.
- State transitions via Enum + Service.

## DB Standards
- Use `is_active`, `is_connected` as boolean.
- Use Enum only for multi-state: ContentStatus, AccountStatus.
- All tables: id, created_at, updated_at. Add team_id only in Phase 4.

## Filament v4 Rule
- Always check installed version docs: `php artisan filament:docs` or official site for that version.
- Use `Schemas` and `Tables` namespaces, not deprecated v3 syntax.
- Resource generation: `php artisan make:filament-resource Model --generate`

## Media
- Spatie Media Library for all uploads (Phase 2)
