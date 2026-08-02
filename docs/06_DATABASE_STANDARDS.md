# 06 - DATABASE STANDARDS

## Naming
- Tables plural: social_accounts, contents, products
- Models singular: SocialAccount, Content, Product
- Foreign keys: user_id, social_account_id
- Index: all FKs and status columns

## Columns
- Always: id, created_at, updated_at
- Add team_id ONLY in Phase 4 (nullable before)
- JSON: brand_settings, metadata for flexible data

## Multi-tenancy Prep
Do NOT add team_id now. But design Service to be team-ready:
ContentService::getForUser(User) -> later getForTeam(Team)

## Example Migration
- boolean is_active default true
- string status indexed (will be cast to Enum in Model)
- foreignId user_id constrained
- json meta nullable
