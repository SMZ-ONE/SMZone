# 03 - ROADMAP - 3 Levels to Sellable SaaS

## Level 1: Working Product (NOW - 2 weeks) - CURRENT
Goal: Filament skeleton fully navigable, looks like a real product.
- Resources: SocialAccounts, Contents, Products, AI Center (mock), Analytics (mock)
- Dashboard with real counts from DB
- No real OAuth, No real AI, No billing
- Definition of Done: User can login and see empty modules with proper navigation.

## Level 2: Real Data Flow (Weeks 3-5)
- Product import: manual first, then Shopify/Woo sync via Queued Jobs
- Social OAuth: Instagram/FB via Laravel Socialite
- Media Library: Spatie Media Library v11 with conversions
- Content Calendar: drag-drop publish dates
- DoD: Real product from Shopify can be turned into draft content.

## Level 3: Brand Brain & AI Platform (Weeks 6-10)
- Table brand_profiles with JSON settings: tone, forbidden_words, campaigns, competitors, audience
- AI generates text/image based on Brand Brain, not generic prompt
- Prompt templates stored in DB: ai_prompt_templates
- CRM: Inbox for comments/DMs, AI suggests reply
- DoD: Type "New summer collection" and AI creates 3 on-brand posts.

## Level 4: Sellable SaaS (Weeks 11-14)
- NOW we add team_id to all tables (migration)
- Teams, Roles (owner/admin/member), Invites
- Billing via Laravel Cashier (Stripe)
- DoD: 2 different companies can use SMZone isolated.

## Level 5: Scale & Automation
- AI Agents, Workflows, Advanced ROI: "This post sold 5 units" attribution
