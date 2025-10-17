# Custom Barcode Generator

This directory contains a custom-built PHP library for generating barcodes.

## Functionality

The main entry point for the user is the page located at `/public/barcode/index.html`. This page provides a simple interface with:

-  An input field to enter the data to be encoded.
-  A "Generate" button.

When the user enters text into the field and clicks the button, the page sends a request to a backend script (`/public/generator.php`). This script then uses the `BarcodeGenerator.php` class in this directory to create an SVG image of the corresponding barcode, which is then displayed on the page.

## Supported Barcode Type

Currently, this generator is implemented to produce **Code 128** barcodes. The implementation specifically handles characters within the **Code Set B** range (ASCII characters 32 to 127), which includes uppercase letters, lowercase letters, numbers, and common symbols.

exemple : ` !"#$%&'()*+,...~`

```bash
json = [
{"code":32,"char":" ","name":"Espace"},
{"code":33,"char":"!","name":"Point d’exclamation"},
{"code":34,"char":"\"","name":"Guillemets doubles"},
{"code":35,"char":"#","name":"Dièse"},
{"code":36,"char":"$","name":"Dollar"},
{"code":37,"char":"%","name":"Pourcentage"},
{"code":38,"char":"&","name":"Esperluette"},
{"code":39,"char":"'","name":"Apostrophe"},
{"code":40,"char":"(","name":"Parenthèse ouvrante"},
{"code":41,"char":")","name":"Parenthèse fermante"},
{"code":42,"char":"\*","name":"Astérisque"},
{"code":43,"char":"+","name":"Plus"},
{"code":44,"char":",","name":"Virgule"},
{"code":45,"char":"-","name":"Tiret"},
{"code":46,"char":".","name":"Point"},
{"code":47,"char":"/","name":"Slash"},
{"code":48,"char":"0","name":"Chiffre 0"},
{"code":49,"char":"1","name":"Chiffre 1"},
{"code":50,"char":"2","name":"Chiffre 2"},
{"code":51,"char":"3","name":"Chiffre 3"},
{"code":52,"char":"4","name":"Chiffre 4"},
{"code":53,"char":"5","name":"Chiffre 5"},
{"code":54,"char":"6","name":"Chiffre 6"},
{"code":55,"char":"7","name":"Chiffre 7"},
{"code":56,"char":"8","name":"Chiffre 8"},
{"code":57,"char":"9","name":"Chiffre 9"},
{"code":58,"char":":","name":"Deux-points"},
{"code":59,"char":";","name":"Point-virgule"},
{"code":60,"char":"<","name":"Inférieur à"},
{"code":61,"char":"=","name":"Égal"},
{"code":62,"char":">","name":"Supérieur à"},
{"code":63,"char":"?","name":"Point d’interrogation"},
{"code":64,"char":"@","name":"Arobase"},
{"code":65,"char":"A","name":"Lettre majuscule A"},
{"code":66,"char":"B","name":"Lettre majuscule B"},
{"code":67,"char":"C","name":"Lettre majuscule C"},
{"code":68,"char":"D","name":"Lettre majuscule D"},
{"code":69,"char":"E","name":"Lettre majuscule E"},
{"code":70,"char":"F","name":"Lettre majuscule F"},
{"code":71,"char":"G","name":"Lettre majuscule G"},
{"code":72,"char":"H","name":"Lettre majuscule H"},
{"code":73,"char":"I","name":"Lettre majuscule I"},
{"code":74,"char":"J","name":"Lettre majuscule J"},
{"code":75,"char":"K","name":"Lettre majuscule K"},
{"code":76,"char":"L","name":"Lettre majuscule L"},
{"code":77,"char":"M","name":"Lettre majuscule M"},
{"code":78,"char":"N","name":"Lettre majuscule N"},
{"code":79,"char":"O","name":"Lettre majuscule O"},
{"code":80,"char":"P","name":"Lettre majuscule P"},
{"code":81,"char":"Q","name":"Lettre majuscule Q"},
{"code":82,"char":"R","name":"Lettre majuscule R"},
{"code":83,"char":"S","name":"Lettre majuscule S"},
{"code":84,"char":"T","name":"Lettre majuscule T"},
{"code":85,"char":"U","name":"Lettre majuscule U"},
{"code":86,"char":"V","name":"Lettre majuscule V"},
{"code":87,"char":"W","name":"Lettre majuscule W"},
{"code":88,"char":"X","name":"Lettre majuscule X"},
{"code":89,"char":"Y","name":"Lettre majuscule Y"},
{"code":90,"char":"Z","name":"Lettre majuscule Z"},
{"code":91,"char":"[","name":"Crochet ouvrant"},
{"code":92,"char":"\","name":"Antislash"},
{"code":93,"char":"]","name":"Crochet fermant"},
{"code":94,"char":"^","name":"Accent circonflexe"},
{"code":95,"char":"\_","name":"Tiret bas"},
{"code":96,"char":"`","name":"Accent grave"},
{"code":97,"char":"a","name":"Lettre minuscule a"},
{"code":98,"char":"b","name":"Lettre minuscule b"},
{"code":99,"char":"c","name":"Lettre minuscule c"},
{"code":100,"char":"d","name":"Lettre minuscule d"},
{"code":101,"char":"e","name":"Lettre minuscule e"},
{"code":102,"char":"f","name":"Lettre minuscule f"},
{"code":103,"char":"g","name":"Lettre minuscule g"},
{"code":104,"char":"h","name":"Lettre minuscule h"},
{"code":105,"char":"i","name":"Lettre minuscule i"},
{"code":106,"char":"j","name":"Lettre minuscule j"},
{"code":107,"char":"k","name":"Lettre minuscule k"},
{"code":108,"char":"l","name":"Lettre minuscule l"},
{"code":109,"char":"m","name":"Lettre minuscule m"},
{"code":110,"char":"n","name":"Lettre minuscule n"},
{"code":111,"char":"o","name":"Lettre minuscule o"},
{"code":112,"char":"p","name":"Lettre minuscule p"},
{"code":113,"char":"q","name":"Lettre minuscule q"},
{"code":114,"char":"r","name":"Lettre minuscule r"},
{"code":115,"char":"s","name":"Lettre minuscule s"},
{"code":116,"char":"t","name":"Lettre minuscule t"},
{"code":117,"char":"u","name":"Lettre minuscule u"},
{"code":118,"char":"v","name":"Lettre minuscule v"},
{"code":119,"char":"w","name":"Lettre minuscule w"},
{"code":120,"char":"x","name":"Lettre minuscule x"},
{"code":121,"char":"y","name":"Lettre minuscule y"},
{"code":122,"char":"z","name":"Lettre minuscule z"},
{"code":123,"char":"{","name":"Accolade ouvrante"},
{"code":124,"char":"|","name":"Barre verticale"},
{"code":125,"char":"}","name":"Accolade fermante"},
{"code":126,"char":"~","name":"Tilde"},
{"code":127,"char":"⌂","name":"DEL (non imprimable)"}
]
```
