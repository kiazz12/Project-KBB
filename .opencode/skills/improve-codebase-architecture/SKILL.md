---
name: improve-codebase-architecture
description: Scan a codebase for deepening opportunities, present findings, then grill through the chosen candidate. Use when the user wants to improve architecture or find refactoring opportunities.
---

# Improve Codebase Architecture

Surface architectural friction and propose **deepening opportunities** — refactors that turn shallow modules into deep ones.

## Process

### 1. Explore

**Scope before you scan — YAGNI.** Put extra weight on recently changed code (hot spots).

Walk commit history (`git log --oneline`) to find hot spots. Read `CONTEXT.md` and ADRs in the area.

Look for:
- Where understanding one concept requires bouncing between many small modules
- Where modules are **shallow** — interface nearly as complex as implementation
- Where tightly-coupled modules leak across their seams
- Which parts are untested or hard to test

Apply the **deletion test**: would deleting it concentrate complexity?

### 2. Present candidates

For each candidate provide:
- **Files** — which files/modules involved
- **Problem** — why current architecture causes friction
- **Solution** — plain English description of change
- **Benefits** — explained in terms of locality and leverage
- **Recommendation strength** — `Strong`, `Worth exploring`, or `Speculative`

End with a **Top recommendation** section.

### 3. Grilling loop

Once user picks a candidate, walk the decision tree — constraints, dependencies, shape of the deepened module, what sits behind the seam, what tests survive.
