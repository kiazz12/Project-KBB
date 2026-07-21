---
name: resolving-merge-conflicts
description: Resolve an in-progress git merge/rebase conflict. Use when there are unresolved merge or rebase conflicts.
---

1. **See the current state** of the merge/rebase. Check git history and conflicting files.

2. **Find the primary sources** for each conflict. Understand why each change was made. Read commit messages, check PRs, check original issues.

3. **Resolve each hunk.** Preserve both intents where possible. Where incompatible, pick the one matching the merge's stated goal and note the trade-off. Do **not** invent new behaviour. Always resolve; never `--abort`.

4. Run the project's **automated checks** — typecheck, tests, format. Fix anything the merge broke.

5. **Finish the merge/rebase.** Stage everything and commit.
