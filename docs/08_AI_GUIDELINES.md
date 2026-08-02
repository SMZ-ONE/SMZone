# 08 - AI GUIDELINES

## Phase 1: Mock (NOW)
All AI pages are static mock. No real OpenAI call. Show what it WILL do.

## Phase 3: Brand Brain
- Table: brand_profiles (user_id, settings JSON)
- Settings includes: tone, forbidden_words, campaigns, competitors, audience, past_performance
- AI reads settings before generating any content.

## Prompt Storage
- Table: ai_prompt_templates (key, template, variables JSON)
- Example: product_to_post uses {{product_name}} {{brand_tone}}

## Safety Rule
AI never writes directly to contents table. Flow: AI generates draft -> user approves -> Service creates Content.

## Future: CRM + Knowledge Base
- CRM: Inbox for comments/DMs, AI suggests reply using Brand Brain
- KB: RAG over brand docs (later, Level 5)
