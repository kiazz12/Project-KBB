---
name: code-review
description: Review the changes since a fixed point (commit, branch, tag, or merge-base) along two axes — Standards (coding standards + Fowler smells) and Spec (does the code match the originating issue/PRD?). Use when the user wants to review a branch, PR, work-in-progress changes, or asks to "review since X".
---

Two-axis review of the diff between `HEAD` and a fixed point the user supplies:

- **Standards** — does the code conform to documented coding standards?
- **Spec** — does the code faithfully implement the originating issue / PRD / spec?

## Process

### 1. Pin the fixed point

Whatever the user said is the fixed point — a commit SHA, branch name, tag, `main`, `HEAD~5`, etc. If they didn't specify one, ask for it.

Capture the diff command once: `git diff <fixed-point>...HEAD` (three-dot). Also note commits via `git log <fixed-point>..HEAD --oneline`.

Before going further, confirm the fixed point resolves (`git rev-parse <fixed-point>`) and the diff is non-empty.

### 2. Identify the spec source

Look for the originating spec, in this order:

1. Issue references in commit messages (`#123`, `Closes #45`, etc.)
2. A path the user passed as an argument
3. A PRD/spec file under `docs/`, `specs/`, or `.scratch/`
4. If nothing found, ask the user. If they say there isn't one, the **Spec** axis skips.

### 3. Identify the standards sources

Anything in the repo that documents how code should be written (`CODING_STANDARDS.md`, `CONTRIBUTING.md`, etc.).

**Smell baseline** (Fowler code smells from _Refactoring_, ch.3):

- **Mysterious Name** — name doesn't reveal what it does → rename
- **Duplicated Code** — same logic in multiple hunks → extract shared shape
- **Feature Envy** — method reaches into another object's data → move method
- **Data Clumps** — same few fields travel together → bundle into one type
- **Primitive Obsession** — primitive standing in for domain concept → give it its own type
- **Repeated Switches** — same cascade on same type recurs → replace with polymorphism
- **Shotgun Surgery** — one change forces scattered edits → gather into one module
- **Divergent Change** — file edited for unrelated reasons → split modules
- **Speculative Generality** — abstraction for needs the spec doesn't have → delete
- **Message Chains** — long navigation caller shouldn't depend on → hide behind one method
- **Middle Man** — class mostly delegates → cut it, call target direct
- **Refused Bequest** — subclass ignores most of what it inherits → use composition

### 4. Run both review axes

**Standards review** — per file/hunk:
- Every place the diff violates a documented standard (cite file + rule)
- Any baseline smell (name it, quote the hunk)
- Distinguish hard violations from judgement calls
- Documented repo standard overrides baseline smells
- Skip anything tooling enforces

**Spec review**:
- Requirements the spec asked for that are missing or partial
- Behaviour in the diff that wasn't asked for (scope creep)
- Requirements that look implemented but where the implementation looks wrong
- Quote the spec line for each finding

### 5. Report

Present under `## Standards` and `## Spec` headings. Do not merge or rerank — the axes are deliberately separate.

End with: total findings per axis, worst issue within each axis.
