---
name: code-review-advanced
description: Comprehensive code review for PHP/Laravel, covering architecture, performance, security, and code quality. Use when reviewing PRs, conducting architecture reviews, security audits, or checking code quality.
---

# Advanced Code Review

Transform code reviews from gatekeeping to knowledge sharing through constructive feedback, systematic analysis, and collaborative improvement.

## Core Principles

### The Review Mindset

**Goals:** Catch bugs, ensure maintainability, share knowledge, enforce standards, improve design.
**Not the goals:** Show off, nitpick formatting (use linters), block progress unnecessarily.

### Effective Feedback

- Specific and actionable
- Educational, not judgmental
- Focused on the code, not the person
- Balanced (praise good work too)
- Prioritized (critical vs nice-to-have)

### Severity Levels

- `[blocking]` — Must fix before merge
- `[important]` — Should fix, discuss if disagree
- `[nit]` — Nice to have, not blocking
- `[suggestion]` — Alternative approach to consider
- `[praise]` — Good work!

## Review Process

### Phase 1: Context Gathering (2-3 minutes)

1. Read PR description and linked issue
2. Check PR size (>400 lines? Ask to split)
3. Review CI/CD status (tests passing?)
4. Understand the business requirement

### Phase 2: High-Level Review (5-10 minutes)

1. **Architecture & Design** — Does the solution fit the problem?
   - SOLID principles, coupling/cohesion, anti-patterns
2. **Performance Assessment** — N+1 queries, algorithm complexity, memory usage
3. **File Organization** — Are new files in the right places?
4. **Testing Strategy** — Are there tests covering edge cases?

### Phase 3: Line-by-Line Review (10-20 minutes)

For each file, check:
- **Logic & Correctness** — Edge cases, off-by-one, null checks
- **Security** — Input validation, injection risks, XSS
- **Performance** — N+1 queries, unnecessary loops, memory leaks
- **Maintainability** — Clear names, single responsibility
- **Reuse** — Search for existing utilities that could replace new code

### Phase 4: Summary & Decision (2-3 minutes)

1. Summarize key concerns
2. Highlight what you liked
3. Make clear decision: Approve / Comment / Request Changes

## PHP/Laravel Specific Checks

- Eloquent N+1 queries (check for missing `with()`)
- Mass assignment vulnerabilities
- Proper use of form requests vs inline validation
- Route model binding vs manual lookup
- Queue job failure handling
- Proper use of policies vs middleware for authorization
- Database migration safety (column types, indexes)
- Blade template injection risks
- CSRF protection on state-changing routes
- Sanctum token management
