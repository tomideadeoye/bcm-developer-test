// Q6 — behaviour built on the corrected HTML from Q5.
// Pure helpers are exported so they can be unit-tested without a DOM.

/**
 * 0-based indices AFTER which an <hr> should be inserted for `count` paragraphs.
 * Pattern required by the brief: P1, P2, HR, P3, P4, HR, P5 — i.e. after every
 * second paragraph, but only when another paragraph follows.
 *   count 5 -> [1, 3]
 *   count 3 -> [1]
 *   count 2 -> []
 */
export function separatorIndices(count) {
  const out = [];
  for (let i = 1; i < count; i += 2) {
    if (i < count - 1) out.push(i);
  }
  return out;
}

/** Fisher–Yates shuffle. Returns a new array; accepts an RNG for testing. */
export function shuffle(list, rng = Math.random) {
  const a = list.slice();
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(rng() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

/**
 * Wire up the behaviour. Returns a small API (handy for tests) or null if the
 * expected markup is absent.
 */
export function init(doc = document) {
  const container = doc.querySelector('article') || doc.body;
  const addButton = doc.querySelector('button'); // the original "Hit me" button
  if (!addButton) return null;

  // Snapshot the three ORIGINAL paragraphs before any separators are added.
  const originals = Array.from(container.querySelectorAll('p'));
  let hideImages = false;

  // All paragraphs, including hidden ones (the brief requires counting them).
  const paragraphs = () => Array.from(container.querySelectorAll('p'));

  function clearSeparators() {
    container.querySelectorAll('hr.sep').forEach((hr) => hr.remove());
  }

  function addSeparators() {
    const ps = paragraphs();
    for (const i of separatorIndices(ps.length)) {
      const hr = doc.createElement('hr');
      hr.className = 'sep';
      ps[i].after(hr);
    }
  }

  function applyImageVisibility() {
    // Applies the CURRENT toggle state to every image paragraph — so newly
    // added image paragraphs stay hidden when images are toggled off.
    paragraphs().forEach((p) => {
      if (p.querySelector('img')) p.hidden = hideImages;
    });
  }

  function layout() {
    clearSeparators();
    addSeparators();
    applyImageVisibility();
  }

  // Original button: add three shuffled copies after the last paragraph.
  addButton.addEventListener('click', () => {
    const clones = shuffle(originals.map((p) => p.cloneNode(true)));
    clones.forEach((c) => container.append(c));
    layout();
  });

  // Second button — created through JavaScript only.
  const toggle = doc.createElement('button');
  toggle.type = 'button';
  toggle.textContent = 'Toggle image paragraphs';
  toggle.addEventListener('click', () => {
    hideImages = !hideImages;
    applyImageVisibility();
  });
  addButton.after(toggle);

  layout();

  return {
    layout,
    applyImageVisibility,
    isImagesHidden: () => hideImages,
  };
}

if (typeof document !== 'undefined' && document.currentScript) {
  // Loaded via a classic <script> tag: initialise immediately.
  init(document);
}
