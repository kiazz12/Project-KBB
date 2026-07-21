---
name: to-tickets
description: Break a plan, spec, or conversation into tracer-bullet tickets with blocking edges. Use when the user wants to decompose work into implementable slices.
---

# To Tickets

Break a plan, spec, or conversation into **tracer-bullet vertical slices**, each declaring the tickets that **block** it.

## Process

### 1. Gather context
Work from conversation context. If user passes a reference (spec path, issue number), fetch it.

### 2. Explore the codebase
Understand current state. Use domain glossary. Look for prefactoring opportunities.

### 3. Draft vertical slices

Rules:
- Each slice cuts a narrow but COMPLETE path through every layer (schema, API, UI, tests)
- A completed slice is demoable or verifiable on its own
- Each slice fits in a single fresh context window
- Any prefactoring should be done first

Give each ticket **blocking edges** — tickets that must complete first.

### 4. Quiz the user

Present as numbered list. For each ticket show:
- **Title**: short descriptive name
- **Blocked by**: which other tickets
- **What it delivers**: end-to-end behaviour this ticket makes work

Ask: granularity, blocking edges correct, merge/split?

### 5. Publish tickets

- **Local files** → `.scratch/<feature-slug>/issues/<NN>-<slug>.md`
- **Issue tracker** → one issue per ticket, dependency order, `ready-for-agent` label

Work the **frontier**: any ticket whose blockers are all done.
