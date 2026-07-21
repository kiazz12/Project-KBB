---
name: tdd
description: Test-driven development with red-green-refactor loop. Use when the user wants to build features or fix bugs test-first, mentions "red-green-refactor", or wants integration tests.
---

# Test-Driven Development

TDD is the red -> green loop. Every section applies on every cycle.

## What a good test is

Tests verify behavior through public interfaces, not implementation details. A good test reads like a specification and survives refactors.

## Seams — where tests go

A **seam** is the public boundary you test at. Tests live at seams, never against internals.

**Test only at pre-agreed seams.** Before writing any test, confirm the seams with the user.

## Anti-patterns

- **Implementation-coupled** — mocks internals, tests private methods. Test breaks on refactor without behavior change.
- **Tautological** — assertion recomputes expected value the same way the code does. Expected values must come from independent source of truth.
- **Horizontal slicing** — writing all tests first, then all implementation. Work in vertical slices instead.

## Rules of the loop

- **Red before green.** Write the failing test first, then only enough code to pass it.
- **One slice at a time.** One seam, one test, one minimal implementation per cycle.
- **Refactoring is not part of the loop.** It belongs to the review stage.
