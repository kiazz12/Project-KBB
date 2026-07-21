---
name: to-spec
description: Turn the current conversation into a spec/PRD and publish it. No interview — just synthesizes what you've already discussed. Use when the user wants to create a spec from the conversation.
---

This skill takes the current conversation context and codebase understanding and produces a spec (PRD). Do NOT interview the user — just synthesize what you already know.

## Process

1. Explore the repo to understand current state. Use domain glossary vocabulary throughout.

2. Sketch out the seams at which you're going to test the feature. Existing seams should be preferred. Use the highest seam possible. Check with the user.

3. Write the spec using this template:

## Problem Statement
The problem the user is facing, from their perspective.

## Solution
The solution to the problem, from the user's perspective.

## User Stories
A LONG, numbered list. Format: `1. As an <actor>, I want a <feature>, so that <benefit>`

## Implementation Decisions
- Modules built/modified
- Interfaces modified
- Architectural decisions
- Schema changes, API contracts

Do NOT include specific file paths or code snippets (they go stale fast).

## Testing Decisions
- What makes a good test
- Which modules will be tested
- Prior art for tests in the codebase

## Out of Scope
Things not included in this spec.

## Further Notes
Any additional context.
