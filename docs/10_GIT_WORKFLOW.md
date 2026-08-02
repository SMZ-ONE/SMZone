# 10 - GIT WORKFLOW

## Branches
- main = production ready, always deployable
- feature branches optional for big tasks, else direct to main in Phase 1

## Commit Messages (Conventional)
- feat: new feature
- fix: bugfix
- docs: documentation only
- refactor: code improvement without feature
- chore: tooling, composer, etc

Example: feat: add filament resource for products

## Push Rule
Push after every commit. No local-only commits.

## Tags
Tag after each Phase:
git tag phase-1-skeleton
git push --tags
