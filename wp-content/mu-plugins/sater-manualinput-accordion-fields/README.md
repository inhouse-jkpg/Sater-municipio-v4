# Säter Manual Input: Accordion FAQ fields

Extends Modularity **Manuell inmatning** (`mod-manualinput`) so **Visa som → Accordion** has the same row fields as Posts **Manuell inmatning**:

- Titel, Innehåll (already in Modularity)
- Permalink (`link`)
- Bild (`image`)
- Ikon (`box_icon`, cached — no PrefillIconChoice loop)
- Kolumnvärden (`accordion_column_values`, already in Modularity)

## Editor workflow

1. Add module **Manuell inmatning** (not Inlägg).
2. Set **Visa som** to **Accordion**.
3. Fill repeater rows (Titel, Innehåll, Permalink, Bild, Ikon, …).
4. On FAQ pages using Posts manual input today: replace that module and copy content, then disable `sater-posts-manual-input` if no other Posts manual-input modules remain.

## Frontend styling

Accordion modules use the same Blade markup as **Inlägg → Expandable list** (`module.posts.expandablelist` card context, `prepareAccordion`, `data-js-item-id="manual-{moduleId}-{index}"`). Module title stays plain (black on white); accordion row buttons use the red FAQ styling. Do not apply `c-card--panel` here — that maroon header bar is for modules like E-tjänster.

## Performance

- Icon choices build once per admin request (not per repeater row).
- Does not register `box_icon` with Municipio `PrefillIconChoice`.
