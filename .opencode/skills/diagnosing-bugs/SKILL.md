---
name: diagnosing-bugs
description: Diagnosis loop for hard bugs and performance regressions. Use when the user says "diagnose"/"debug this", or reports something broken/throwing/failing/slow.
---

# Diagnosing Bugs

A discipline for hard bugs. Skip phases only when explicitly justified.

## Phase 1 — Build a feedback loop

**This is the skill.** Build a **tight** pass/fail signal for the bug — one that goes red on _this_ bug.

### Ways to construct one (try in order):

1. **Failing test** at whatever seam reaches the bug
2. **Curl / HTTP script** against a running dev server
3. **CLI invocation** with fixture input, diffing stdout
4. **Headless browser script** (Playwright / Puppeteer)
5. **Replay a captured trace** in isolation
6. **Throwaway harness** — minimal subset exercising the bug path
7. **Property / fuzz loop** — 1000 random inputs looking for failure
8. **Bisection harness** for `git bisect run`
9. **Differential loop** — old vs new version, diff outputs
10. **HITL bash script** — last resort, drive human with structured script

### Tighten the loop

- Faster? (Cache setup, skip unrelated init, narrow scope.)
- Sharper signal? (Assert on specific symptom, not "didn't crash".)
- More deterministic? (Pin time, seed RNG, isolate filesystem.)

### Completion criterion

Phase 1 is done when the loop is **tight** and **red-capable**: one command you have already run at least once, that:
- Drives the actual bug code path and asserts the user's exact symptom
- Is deterministic (or high reproduction rate for flaky bugs)
- Is fast (seconds, not minutes)
- Is agent-runnable

## Phase 2 — Reproduce + minimise

- Confirm the loop produces the failure mode the **user** described
- Confirm reproducibility across multiple runs
- Capture the exact symptom

**Minimise**: shrink to smallest scenario that still goes red. Cut one element at a time, re-running after each.

## Phase 3 — Hypothesise

Generate **3–5 ranked hypotheses** before testing any. Each must be **falsifiable**:

> "If <X> is the cause, then <changing Y> will make the bug disappear / <changing Z> will make it worse."

Show the ranked list to the user before testing.

## Phase 4 — Instrument

Each probe maps to a specific prediction from Phase 3. Change one variable at a time.

Tool preference: debugger > targeted logs > never "log everything and grep".

**Tag every debug log** with unique prefix `[DEBUG-xxxx]`.

## Phase 5 — Fix + regression test

Write the regression test **before the fix** — but only if there is a correct seam.

If no correct seam exists, that itself is the finding. Note it.

## Phase 6 — Cleanup + post-mortem

- [ ] Original repro no longer reproduces
- [ ] Regression test passes (or absence of seam documented)
- [ ] All `[DEBUG-...]` instrumentation removed
- [ ] Throwaway prototypes deleted
- [ ] Correct hypothesis stated in commit/PR message

Ask: what would have prevented this bug? If architectural, hand off to `/improve-codebase-architecture`.
