# Säter Publiceringsvalidering

Must-use-plugin som granskar innehåll när en redaktör klickar **Publicera** eller **Uppdatera**. Målet är samma typ av grind som Sitevisions publiceringsregler: fånga WCAG- och strukturfel innan sidan går live.

Den körs bara i admin vid publicering. Besökarsidor och cache påverkas inte.

## Vad som händer för redaktören

1. Redaktören klickar Publicera (klassisk editor).
2. Pluginet kontrollerar sidans data (titel, brödtext, bilder, manuell inmatning).
3. **Fel** stoppar publicering. En dialog visas: "Sidan kan inte publiceras" plus en lista. Enda knappen är Stäng. Utkast går fortfarande att spara.
4. **Varningar** stoppar inte. Dialogen "Kontrollera innan publicering" förklarar vad som bör åtgärdas. Publicera finns kvar.

Om JavaScript inte körs finns en serversidespärr på samma fel (status går inte till publicerad).

## Vad som kontrolleras

| Regel | Allvar | Innebörd |
|---|---|---|
| Titel | Fel | Tom titel stoppas. Minst 3 tecken (korta svenska ord ska gå). Över 60 tecken ger varning (SEO). |
| Rubriker H1–H6 | Fel | Sidtiteln räknas som H1. Extra H1 i innehållet stoppas. Hoppade nivåer (t.ex. H2 till H4) stoppas. |
| Alt-text | Fel | Utvald bild, bilder i brödtexten utan `alt`, och bilder i ACF-fält utan alt i mediabiblioteket. Tom `alt=""` räknas som dekorativ och är tillåten. |
| Otydlig länktext | Varning | Hela länktexten är exakt `klicka här`, `här`, `läs mer`, `mer info` eller `länk`. "Läs mer om förskola" går igenom. |

Rubrik- och länkreglerna tittar även på **Manuell inmatning** (`mod-manualinput`):

- Vid publicering av **sidan**: sparade moduler på sidan, i den ordning de ligger.
- Vid publicering av **modulen**: det redaktören just redigerat.
- Modultiteln räknas som H2 om den inte är dold.
- Korttitlar som mallen själv gör till `h2` flaggas inte. Det är bara H-taggar i redaktörens innehållsfält, plus länktexter på rader som har URL.

## Vad som inte görs

- Ingen rendering av hela sidan. Ingen 404-koll av länkar (för långsamt, för många falska larm).
- Gutenberg-flödet har ingen egen panel (REST-spärr finns om någon slår på Gutenberg).
- Include-modulen och andra Modularity-typer än manuell inmatning scannas inte.

## Posttyper

`page`, `post`, `news`, `events` och `mod-manualinput`. Ändras med filtret `sater_publish_validation_post_types`.

## Utöka

Egna regler: filter `sater_publish_validation_rules` (ny klass som implementerar `RuleInterface`) eller `sater_publish_validation_violations`.

Otydliga länkfraser: `sater_publish_validation_vague_link_phrases`.

Nödavstängning: konstanten `SATER_PUBLISH_VALIDATION_BYPASS` eller filtret `sater_publish_validation_bypass`.

## Så här testar du

Använd klassisk editor (Gutenberg av). Ladda om wp-admin hårt (cmd+shift+R) så JS/CSS inte är cachad. Testa på en **ny utkast-sida** så du inte rör publicerat innehåll.

Efter varje negativt test: **Spara utkast** ska alltid gå, även när Publicera spärras.

### 1. Dialog och spärr

1. Öppna en ny sida, lämna titeln tom, klicka Publicera.
2. Förväntat: dialog **"Sidan kan inte publiceras"**, felet "Sidan måste ha en titel.", bara knappen **Stäng**. Ingen **Publicera**.
3. Klicka Stäng. Sidan ska fortfarande vara utkast.

### 2. Titel

| Steg | Förväntat |
|---|---|
| Titel `Hej`, Publicera | Spärr: minst 3 tecken. |
| Titel `Bo` eller `Val`, i övrigt giltig sida, Publicera | Går igenom (inga titelfel). |
| Titel på över 60 tecken, i övrigt giltig | Dialog **"Kontrollera innan publicering"** (varning). **Publicera** syns och fungerar. |

### 3. Rubriker i brödtexten

Sidtiteln räknas som H1. I TinyMCE, byt till textläge om det är enklare att skriva taggar.

| Steg | Förväntat |
|---|---|
| En `h1` i brödtexten | Spärr: extra H1. |
| En `h4` utan `h2`/`h3` ovanför | Spärr: nivå hoppas över (H1 till H4). |
| `h2` sedan `h3`, ingen extra H1 | Går igenom. |
| Inga rubriker alls i brödtexten | Går igenom. |

### 4. Alt-text

| Steg | Förväntat |
|---|---|
| Sätt utvald bild som saknar alt i mediabiblioteket | Spärr. |
| Infoga en bild i brödtexten och ta bort `alt`-attributet | Spärr. |
| Samma bild med `alt=""` | Går igenom (dekorativ). |
| Samma bild med beskrivande alt | Går igenom. |

### 5. Otydlig länktext (varning, inte spärr)

I brödtexten, länka ordet som hela länktexten.

| Steg | Förväntat |
|---|---|
| Länktext `klicka här`, `här`, `läs mer`, `mer info` eller `länk` | Varning. Publicera går. |
| Länktext `Läs mer.` (punkt, versaler) | Samma varning. |
| Länktext `Läs mer om förskola` | Ingen varning. |

### 6. Manuell inmatning

Två vägar, båda ska ge samma typ av träff.

**A. Publicera själva modulen**

1. Skapa **Manuell inmatning**, ge den en titel.
2. I en rads innehållsfält: en `h4` utan `h2`/`h3`. Publicera modulen. Förväntat: spärr för hoppad rubriknivå.
3. Byt till en `h1` i innehållet. Förväntat: spärr för extra H1.
4. Sätt radens titel till `Läs mer` och lägg till en länk-URL. Förväntat: varning för otydlig länktext.
5. Ta bort H-taggarna, använd en vanlig titel med länk. Förväntat: går att publicera.

**B. Publicera sidan som använder modulen**

1. Spara modulen med ett medvetet fel (t.ex. `h1` i innehållet).
2. Lägg modulen på en i övrigt giltig testsida.
3. Publicera **sidan**. Förväntat: samma spärr, även om sidans egen brödtext är tom.

Korttitlar som bara är textfält (utan H-tagg i innehållet) ska **inte** ge rubrikfel. Det är mallens `h2`.

### 7. Snabbkoll att det inte är för strikt

Publicera en normal sida med titel, en `h2` i texten, en bild med alt och en länk som "Ansök om förskola". Den ska gå igenom utan dialog.
