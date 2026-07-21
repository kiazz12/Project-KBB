---
name: requesting-code-review
description: Dispatch a code reviewer to catch issues before they cascade. Use when completing tasks, implementing major features, or before merging.
---

# Requesting Code Review

Dispatch a code reviewer to catch issues before they cascade. The reviewer gets precisely crafted context — never your session's history.

## When to Request Review

**Mandatory:**
- After each task in subagent-driven development
- After completing major feature
- Before merge to main

**Optional but valuable:**
- When stuck (fresh perspective)
- Before refactoring (baseline check)
- After fixing complex bug

## How to Request

1. **Get git SHAs:**
   ```bash
   BASE_SHA=$(git rev-parse HEAD~1)
   HEAD_SHA=$(git rev-parse HEAD)
   ```

2. **Dispatch reviewer** with:
   - Brief summary of what you built
   - What it should do (plan/requirements)
   - Base and HEAD SHAs

3. **Act on feedback:**
   - Fix Critical issues immediately
   - Fix Important issues before proceeding
   - Note Minor issues for later
   - Push back if reviewer is wrong (with reasoning)

## Red Flags

**Never:**
- Skip review because "it's simple"
- Ignore Critical issues
- Proceed with unfixed Important issues
