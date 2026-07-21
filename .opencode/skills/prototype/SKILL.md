---
name: prototype
description: Build a throwaway prototype to answer a design question. Use when the user wants to sanity-check a state model, logic, or UI design.
---

# Prototype

A prototype is **throwaway code that answers a question**.

## Pick a branch

- **"Does this logic / state model feel right?"** → Build a tiny interactive terminal app that pushes the state machine through hard-to-reason cases.
- **"What should this look like?"** → Generate several radically different UI variations on a single route.

## Rules

1. **Throwaway from day one.** Named clearly as prototype, not production.
2. **One command to run.** Use the project's existing task runner.
3. **No persistence by default.** State lives in memory.
4. **Skip the polish.** No tests, no error handling beyond what makes it runnable.
5. **Surface the state.** After every action, print/render full relevant state.
6. **Capture it when done.** Fold validated decisions into real code, commit prototype to throwaway branch.
