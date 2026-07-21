---
name: codebase-design
description: Shared vocabulary for designing deep modules — small interface, lots of behaviour. Use when designing or improving a module's interface, finding deepening opportunities, or deciding where a seam goes.
---

# Codebase Design

Design **deep modules**: a lot of behaviour behind a small interface, placed at a clean seam, testable through that interface.

## Glossary

**Module** — anything with an interface and an implementation. Scale-agnostic: function, class, package, tier-spanning slice.

**Interface** — everything a caller must know: type signature, invariants, ordering constraints, error modes, required config, performance characteristics.

**Implementation** — what's inside a module.

**Depth** — leverage at the interface: amount of behaviour a caller can exercise per unit of interface they learn.

**Seam** — a place where you can alter behaviour without editing in that place; where a module's interface lives.

**Adapter** — a concrete thing that satisfies an interface at a seam.

**Leverage** — what callers get from depth: more capability per unit of interface.

**Locality** — what maintainers get from depth: change, bugs, knowledge concentrate in one place.

## Deep vs shallow

**Deep** = small interface + lots of implementation.
**Shallow** = large interface + little implementation (avoid).

When designing, ask:
- Can I reduce the number of methods?
- Can I simplify the parameters?
- Can I hide more complexity inside?

## Principles

- **Depth is a property of the interface**, not the implementation.
- **The deletion test.** If complexity vanishes when you delete the module, it was a pass-through. If it reappears across N callers, it was earning its keep.
- **The interface is the test surface.** Callers and tests cross the same seam.
- **One adapter means a hypothetical seam. Two adapters means a real one.**

## Designing for testability

1. **Accept dependencies, don't create them.**
2. **Return results, don't produce side effects.**
3. **Small surface area.** Fewer methods = fewer tests.
