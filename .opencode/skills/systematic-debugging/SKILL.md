---
name: systematic-debugging
description: Find root cause before attempting fixes. Use when encountering any bug, test failure, or unexpected behavior, before proposing fixes.
---

# Systematic Debugging

Random fixes waste time and create new bugs. Quick patches mask underlying issues.

**Core principle:** ALWAYS find root cause before attempting fixes.

## The Iron Law

```
NO FIXES WITHOUT ROOT CAUSE INVESTIGATION FIRST
```

## The Four Phases

### Phase 1: Root Cause Investigation

BEFORE attempting ANY fix:

1. **Read Error Messages Carefully** — don't skip past errors, read stack traces completely
2. **Reproduce Consistently** — exact steps, every time
3. **Check Recent Changes** — git diff, recent commits, config changes
4. **Gather Evidence** — add diagnostic instrumentation at component boundaries
5. **Trace Data Flow** — where does bad value originate? Trace up to source.

### Phase 2: Pattern Analysis

1. **Find Working Examples** — similar working code in same codebase
2. **Compare Against References** — read reference implementation COMPLETELY
3. **Identify Differences** — every difference, however small
4. **Understand Dependencies** — what other components, settings, config?

### Phase 3: Hypothesis and Testing

1. **Form Single Hypothesis** — "I think X is the root cause because Y"
2. **Test Minimally** — SMALLEST possible change, one variable at a time
3. **Verify Before Continuing** — worked? Phase 4. Didn't? New hypothesis.
4. **When You Don't Know** — say so, ask for help, research more

### Phase 4: Implementation

1. **Create Failing Test Case** — simplest reproduction, automated if possible
2. **Implement Single Fix** — root cause only, ONE change at a time
3. **Verify Fix** — test passes, no other tests broken
4. **If Fix Doesn't Work** — if < 3 fixes: return to Phase 1. If ≥ 3: question architecture.

## If 3+ Fixes Failed: Question Architecture

Each fix reveals new coupling? Fixes require "massive refactoring"? STOP and question fundamentals. This is NOT a failed hypothesis — this is a wrong architecture.

## Red Flags — STOP

- "Quick fix for now, investigate later"
- "Just try changing X and see if it works"
- "I don't fully understand but this might work"
- Proposing solutions before tracing data flow
- **"One more fix attempt" (when already tried 2+)**

**ALL of these mean: STOP. Return to Phase 1.**
