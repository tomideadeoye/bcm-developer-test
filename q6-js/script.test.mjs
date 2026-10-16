import { describe, expect, test } from 'bun:test';
import { Window } from 'happy-dom';
import { init, separatorIndices, shuffle } from './script.js';

const MARKUP = `
  <article>
    <h1>Hello, world</h1>
    <p>P1</p>
    <p>P2</p>
    <p>P3 with image <img src="x.jpeg" alt=""></p>
    <button type="button">Hit me</button>
  </article>`;

function mount() {
  const window = new Window();
  window.document.body.innerHTML = MARKUP;
  const doc = window.document;
  const api = init(doc);
  const container = doc.querySelector('article');
  const addButton = doc.querySelector('button');
  return { doc, api, container, addButton, paragraphs: () => doc.querySelectorAll('p'), seps: () => doc.querySelectorAll('hr.sep') };
}

describe('Q6 — pure helpers', () => {
  test('separator indices follow P1,P2,HR,P3,P4,HR,P5 and stop before the last', () => {
    expect(separatorIndices(1)).toEqual([]);
    expect(separatorIndices(2)).toEqual([]);
    expect(separatorIndices(3)).toEqual([1]);
    expect(separatorIndices(4)).toEqual([1]);
    expect(separatorIndices(5)).toEqual([1, 3]);
    expect(separatorIndices(6)).toEqual([1, 3]);
    expect(separatorIndices(9)).toEqual([1, 3, 5, 7]);
  });

  test('shuffle is deterministic under a fixed RNG and preserves elements', () => {
    const rng = () => 0.5;
    const out = shuffle([1, 2, 3, 4], rng);
    expect([...out].sort((a, b) => a - b)).toEqual([1, 2, 3, 4]);
  });
});

describe('Q6 — behaviour', () => {
  test('separators are applied on initial load', () => {
    const { seps } = mount();
    expect(seps().length).toBe(1); // 3 paragraphs -> one HR after P2
  });

  test('each click adds three shuffled paragraphs and re-derives separators', () => {
    const { addButton, paragraphs, seps } = mount();
    addButton.click();
    expect(paragraphs().length).toBe(6);
    expect(seps().length).toBe(2); // [1,3]

    addButton.click();
    expect(paragraphs().length).toBe(9);
    expect(seps().length).toBe(4); // [1,3,5,7]
  });

  test('copies land after the last paragraph, so the buttons stay below the content', () => {
    const { container, addButton } = mount();
    addButton.click();
    const children = [...container.children];
    expect(children.filter((el) => el.tagName === 'P').length).toBe(6);
    expect(children.slice(-2).map((el) => el.tagName)).toEqual(['BUTTON', 'BUTTON']);
  });

  test('the second button is created by JS and toggles image paragraphs', () => {    const { doc, addButton } = mount();
    const buttons = doc.querySelectorAll('button');
    expect(buttons.length).toBe(2);
    const toggle = buttons[1];

    const imageParagraphs = () => [...doc.querySelectorAll('p')].filter((p) => p.querySelector('img'));
    expect(imageParagraphs().length).toBe(1);
    expect(imageParagraphs()[0].hidden).toBe(false);

    toggle.click();
    expect(imageParagraphs().every((p) => p.hidden)).toBe(true);

    // Newly added image paragraphs must stay hidden too.
    addButton.click();
    const after = imageParagraphs();
    expect(after.length).toBeGreaterThan(1);
    expect(after.every((p) => p.hidden)).toBe(true);

    // Toggle back on.
    toggle.click();
    expect(imageParagraphs().every((p) => !p.hidden)).toBe(true);
  });

  test('hidden paragraphs are still counted for separator placement', () => {
    const { doc, addButton } = mount();
    const toggle = doc.querySelectorAll('button')[1];
    toggle.click(); // hide image paragraph(s)
    addButton.click(); // 6 paragraphs total, 2 hidden-or-not
    expect(doc.querySelectorAll('p').length).toBe(6);
    expect(doc.querySelectorAll('hr.sep').length).toBe(2); // counted all 6
  });
});
