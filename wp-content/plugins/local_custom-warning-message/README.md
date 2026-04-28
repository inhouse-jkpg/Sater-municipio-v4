# Custom warning message

Detta plugin skapades av Jonas Hultenius och har redigerats av Annelie Viklund.
## Intro

 Pluginet visar ett meddelande för allmänheten högst upp på hemsidan. I adminpanelen kan användaren välja mellan två typer, info eller varning. Man kan enkelt ändra färg samt ikon för de olika meddelandetyperna med sass. 

## Adminpanelen

Hur pluginet visas för admin i Wordpress ändras i source/php/warning-message/settings-page. Här ändrar du även ikonerna som syns i meddelandet.

## Sass

Sass-filen är inte aktiv, utan implementeras i ditt eget projekt. Här ändrar du färger utefter din kunds grafiska profil.

## js

Om din hemsida använder sig utav en genomskinlig meny behöver du lägga till denna javascript-kod på lämplig plats i ditt projekt för att "knuffa ner" menyn så att meddelandet är synligt ovanför den.

## Testmiljö

There's no place like 127.0.0.1 - det är alltså localhost som gäller

## Se upp med...
Om du använder WP-super cache kan det vara bra att avkommentera raderna 32-40 i source/php/warning-message/settings-page, då användaren måste rensa cachen innan meddelandet visas/döljs.

## Vem vet mer om detta?
Jonas (jonas.hultenius@sogeti.se) skapade pluginet och vet mest om dess funktionalitet. Nellie (annelie.viklund@sogeti.se) har gjort ändringar i pluginet utefter kundförfrågningar och har även skapat sass- och jsfilerna.


