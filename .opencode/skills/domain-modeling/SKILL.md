---
name: domain-modeling
description: Build and sharpen a project's domain model — challenge terms, record ADRs, update CONTEXT.md. Use when the user wants to pin down domain terminology or record architectural decisions.
---

# Domain Modeling

Actively build and sharpen the project's domain model as you design.

## File structure

```
/
├── CONTEXT.md          # Glossary — nothing else
├── docs/
│   └── adr/            # Architectural Decision Records
│       ├── 0001-*.md
│       └── 0002-*.md
```

Create files lazily — only when you have something to write.

## During the session

### Challenge against the glossary
When the user uses a term conflicting with `CONTEXT.md`, call it out immediately.

### Sharpen fuzzy language
Propose precise canonical terms for vague or overloaded terms.

### Discuss concrete scenarios
Stress-test domain relationships with specific edge-case scenarios.

### Cross-reference with code
Check whether code agrees with stated behavior. Surface contradictions.

### Update CONTEXT.md inline
Capture resolved terms immediately. `CONTEXT.md` is a glossary and nothing else — no implementation details.

### Offer ADRs sparingly
Only when all three are true:
1. **Hard to reverse** — meaningful cost of changing mind later
2. **Surprising without context** — future reader will wonder "why?"
3. **Real trade-off** — genuine alternatives existed
