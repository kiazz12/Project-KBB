---
name: writing-plans
description: Write comprehensive implementation plans from specs or requirements. Use when you have a spec and need to plan the implementation before touching code.
---

# Writing Plans

Write comprehensive implementation plans assuming the engineer has zero context for the codebase. Document everything: which files to touch, code, testing, how to test. Give bite-sized tasks. DRY. YAGNI. TDD. Frequent commits.

## Scope Check

If the spec covers multiple independent subsystems, break into separate plans — one per subsystem. Each plan should produce working, testable software on its own.

## File Structure

Before defining tasks, map out which files will be created or modified. Design units with clear boundaries and well-defined interfaces.

## Task Right-Sizing

A task is the smallest unit that carries its own test cycle. Each task ends with an independently testable deliverable.

## Bite-Sized Task Granularity

Each step is one action (2-5 minutes):
- "Write the failing test" — step
- "Run it to make sure it fails" — step
- "Implement the minimal code to make the test pass" — step
- "Run the tests and make sure they pass" — step
- "Commit" — step

## Plan Document Structure

Every plan MUST include:
- **Goal:** One sentence describing what this builds
- **Architecture:** 2-3 sentences about approach
- **Tech Stack:** Key technologies/libraries
- **Global Constraints:** Project-wide requirements
- **Tasks:** Each with Files, Interfaces, and step-by-step implementation

## Task Structure

Each task includes:
- **Files:** Create/Modify/Test paths
- **Interfaces:** Consumes/Produces signatures
- **Steps:** With actual code, commands, and expected output

## No Placeholders

Every step must contain actual content. Never write:
- "TBD", "TODO", "implement later"
- "Add appropriate error handling"
- "Write tests for the above" (without actual test code)
- "Similar to Task N"

## Self-Review

After writing, check:
1. **Spec coverage** — every requirement has a task
2. **Placeholder scan** — no red flags
3. **Type consistency** — signatures match across tasks

## Execution Handoff

After saving the plan, offer execution choice:
1. **Subagent-Driven** — fresh subagent per task, review between tasks
2. **Inline Execution** — execute tasks in this session with checkpoints
