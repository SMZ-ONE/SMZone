import pathlib
p = pathlib.Path('docs/03_ROADMAP.md')
p.write_text('# 03 - ROADMAP\n\nLevel 1: Working Product - NOW\n- Filament skeleton fully navigable\n- Dashboard real counts\n- No OAuth, No AI, No billing\n\nLevel 2: Real Data Flow - Weeks 3-5\n- Product import manual then Shopify sync via Jobs\n- Social OAuth Instagram FB via Socialite\n- Media Library Spatie\n- DoD: Real product can become draft content\n\nLevel 3: Brand Brain - Weeks 6-10\n- brand_profiles with JSON settings\n- AI generates based on Brand Brain\n- CRM inbox\n\nLevel 4: Sellable SaaS - Weeks 11-14\n- NOW add team_id\n- Teams, Roles, Billing via Cashier\n\nLevel 5: Automation - ROI reporting\n', encoding='utf-8')

pathlib.Path('docs/05_CODING_STANDARDS.md').write_text('# 05 - CODING STANDARDS\n\nBoolean: is_active, is_connected, is_verified\nEnum: ContentStatus DRAFT SCHEDULED PUBLISHED FAILED, Platform INSTAGRAM FACEBOOK TIKTOK\n\nRule: No logic in Resource. Use Service classes.\nFilament v4: Use Schemas and Tables namespaces, not v3.\nQueue: All external calls must be Job.\n', encoding='utf-8')

pathlib.Path('docs/06_DATABASE_STANDARDS.md').write_text('# 06 - DATABASE STANDARDS\n\nTables plural, Models singular\nAlways id, created_at, updated_at\nAdd team_id ONLY in Phase 4\nIndex foreign keys and status\nJSON column: brand_settings, metadata\n', encoding='utf-8')

pathlib.Path('docs/07_UI_UX_GUIDELINES.md').write_text('# 07 - UI UX GUIDELINES\n\nFilament default theme, no custom CSS Phase 1\nEvery list: search + filter\nEmpty state with CTA\nStatus colors: draft gray, scheduled blue, published green, failed red\n', encoding='utf-8')

pathlib.Path('docs/08_AI_GUIDELINES.md').write_text('# 08 - AI GUIDELINES\n\nPhase 1: All AI pages MOCK\nPhase 3: brand_profiles table with tone, forbidden_words, campaigns\nAI never writes directly to DB, always via Service + approval\nPrompt templates stored in DB\n', encoding='utf-8')

pathlib.Path('docs/09_SPRINT_RULES.md').write_text('# 09 - SPRINT RULES\n\nTemplate:\nHedef: 2 sentences\nKomutlar: max 3 artisan\nTest: Which URL\nCommit: message\nPush\n\nNo task without test\n', encoding='utf-8')

pathlib.Path('docs/10_GIT_WORKFLOW.md').write_text('# 10 - GIT WORKFLOW\n\nmain = production ready\nfeat: new feature, fix: bug, docs: doc, refactor, chore\nPush after every commit\nTag after each Phase\n', encoding='utf-8')

print('OK - 6 files expanded')
