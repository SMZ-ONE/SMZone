# 04 - ARCHITECTURE - SMZone Constitution

## 1. Core Principle: Design for SaaS, Build for Single User

WRONG: Add team_id to every table on Day 1. Complexity kills velocity.
RIGHT: Write code so that adding team_id in Phase 4 takes 10 minutes per model.

How?
- Always access data via Service or Scope, never Model::all() in Resource.
- Example: Content::query() -> ContentService::getForCurrentUser(). Tomorrow we change service to filter by team.
- Relations: User hasMany SocialAccount. Later Team hasMany SocialAccount.

## 2. State Management

### Boolean vs Enum Rule (Final Decision)
- Boolean: is_active, is_connected, is_verified - binary flag.
- Enum: When 3+ meaningful states exist.

Defined Enums:
- ContentStatus: DRAFT, SCHEDULED, PUBLISHING, PUBLISHED, FAILED
- Platform: INSTAGRAM, FACEBOOK, TIKTOK, LINKEDIN, X, YOUTUBE, PINTEREST

Rule: No Status as String. Never status = 'draft'. Always ContentStatus::DRAFT.

## 3. Filament v4 Architecture

Rule: Check installed version before coding.
composer show filament/filament

v4 Correct Namespaces:
- Filament\Schemas\Components\TextInput (not Forms\Components)
- Filament\Tables\Columns\TextColumn
- Filament\Schemas\Schema for form()
- Filament\Tables\Table for table()

If docs conflict, trust vendor/filament source, not blog posts.

Resource Structure: Resource -> Pages -> Schemas\Form -> Tables\Table
No logic in Resource class. Delegate to Schema/Table.

## 4. Queue First Architecture

Rule: Every external call MUST be queued.

- OAuth token refresh -> Job
- Publishing post -> Job
- AI generation -> Job
- Product sync -> Job

WRONG in Controller: $instagram->publish($post);
RIGHT: PublishPostJob::dispatch($post);

Why? Instagram API 2 sec, 100 posts = timeout. Queue = scalable.

## 5. Media & Files

Decision: Spatie Media Library v11 in Phase 2.

Why not default Filament Upload?
- Need conversions (1080x1080, thumbnail)
- Need collection: product_images, generated_ai_images
- Need future S3 move

Until Phase 2: Use Filament FileUpload with public disk.

## 6. Future Proofing

Brand Knowledge Base (Phase 3):
Table brand_profiles with settings JSON containing tone, forbidden_words, campaigns.

AI reads this JSON before generating.

Do NOT build now, but leave a JSON column ready.

## Summary
Level 1: Simple Eloquent, no team_id, queue ready, enum for status.
Level 4: Add team_id, replace Service methods with team scope.