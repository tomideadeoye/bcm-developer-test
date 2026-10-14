# Q4 — Desktop and mobile layout

One HTML file (`index.html`) and one stylesheet (`styles.css`). No JavaScript,
no duplicated sections.

## The layouts

**Desktop** — three equal columns; `A` spans two columns and `B` fills the third:

```
A A B
C D E
F G H
```

**Mobile** — a single column, ordered `A → D → B → C → G`. `E`, `F` and `H` are
hidden.

Implemented with CSS Grid and `grid-template-areas` / `grid-area`, so the *same*
eight elements are re-arranged rather than duplicated.

## Breakpoint

**`max-width: 640px`** switches to the single-column layout. 640px is a common
phone-landscape / small-tablet boundary and, for this content, is where three
columns stop being readable; the grid also collapses gracefully in between
because the columns are `1fr`.

## HTML order and its effect on keyboard / screen readers

DOM order is the natural reading order **A → H**, which matches the *desktop*
visual order. The mobile layout deliberately changes the *visual* order
(A, D, B, C, G) with CSS only, which decouples visual position from source
order. Two consequences worth stating:

- **Keyboard users tab in DOM order (A → H), not the mobile visual order.**
  Where the visual order diverges, focus can appear to jump (e.g. tabbing from
  the `A` tile goes to `B`, which is *below* `C` on screen). This is the classic
  CSS-reorder accessibility pitfall.
- **Screen readers follow DOM order too**, so a screen-reader user hears
  A, B, C, D, E, F, G, H — a coherent sequence — while a sighted mobile user sees
  A, D, B, C, G.

The visual re-order on mobile is a layout requirement here, so the mitigation is
to keep the **source order meaningful and self-contained** (each tile is a
labelled landmark with `aria-label`), so the reading order still makes sense even
though it differs from the painted order. With real content, if the visual
order were to matter semantically, the honest fix is to author the DOM in that
order and re-order for desktop instead — I kept DOM = reading order because it
is the more defensible default and the desktop grid is the primary layout.

## Evidence

`screenshots/desktop.png` (1100px) and `screenshots/mobile.png` (420px), both
captured headless from `index.html`. The long heading in `index.html` is included
to check wrapping at both sizes.
