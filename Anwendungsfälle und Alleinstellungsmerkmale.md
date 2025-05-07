# PregContentFinder: Anwendungsfälle und Alleinstellungsmerkmale

Die `PregContentFinder`-Klasse (ursprünglich `SL5_preg_contentFinder`) ist ein mächtiges PHP-Werkzeug zur Analyse und Manipulation von Textinhalten basierend auf regulären Ausdrücken für Start- und End-Delimiter. Ihre Stärke liegt in der Fähigkeit, auch komplex verschachtelte Strukturen zu verarbeiten und dynamische Transformationen durch benutzerdefinierte Callback-Funktionen zu ermöglichen.

Nach unserem Kenntnisstand gibt es nur wenige (oder möglicherweise keine) öffentlich verfügbaren PHP-Klassen, die eine derart flexible Kombination aus rekursiver, Regex-basierter Inhaltssuche und benutzerdefinierter Callback-Verarbeitung für verschachtelte Strukturen in dieser Ausprägung anbieten. Dies verleiht der Klasse eine gewisse Einzigartigkeit.

## Kernanwendungsfälle

Die Flexibilität der `PregContentFinder`-Klasse eröffnet ein breites Spektrum an Anwendungsmöglichkeiten:

1.  **Präzise Extraktion von Inhalten:**
    *   **Beispiel:** Gezieltes Auslesen von Datenblöcken aus HTML-, XML- oder beliebigen Textdateien (z.B. Inhalte spezifischer HTML-Tags wie `<title>...</title>`, Daten aus Konfigurationsdateien, Log-Einträge).
    *   **Nutzen:** Daten-Scraping, Konfigurationsmanagement, Import von strukturierten Textdaten.

2.  **Verarbeitung und Analyse verschachtelter Strukturen:**
    *   **Beispiel:** Parsen und Interpretieren von hierarchischen oder rekursiven Formaten wie BBCode (`[quote][quote]...[/quote][/quote]`), verschachtelten Funktionsaufrufen oder benutzerdefinierten Template-Sprachen mit Blockstrukturen.
    *   **Nutzen:** Implementierung von Mini-Parsern, Analyse von Code-Strukturen, Verarbeitung von Textformaten mit Tiefenlogik.

3.  **Dynamische Inhalts-Transformation mittels Callbacks:**
    Dies ist eines der mächtigsten Features, realisiert durch `getContent_user_func_recursive`.
    *   **Beispiele:**
        *   **Template-Engines:** Ersetzen von Platzhaltern (`{platzhalter}`) durch dynamische Inhalte, wobei die Platzhalter selbst komplexe Argumente oder weitere verschachtelte Blöcke enthalten können.
        *   **Markup-Konvertierung:** Umwandlung von benutzerdefiniertem Markup (z.B. eine vereinfachte Wiki-Syntax oder BBCode) in HTML oder ein anderes Zielformat, inklusive korrekter Behandlung von Verschachtelungen.
        *   **Code-Formatierung & Pretty-Printing:** Automatische Anpassung der Einrückung und Struktur von Quellcode basierend auf erkannten Blöcken (z.B. `{...}`-Blöcke), wie in den Tests für AutoHotKey-Skripte demonstriert.
        *   **Interpretation domänenspezifischer Sprachen (DSLs):** Extrahieren und Verarbeiten von speziellen Anweisungen innerhalb eines Textes, z.B. SQL-ähnliche Abfragen in Templates (Kontext: doSqlWeb-Projekt).
    *   **Nutzen:** Erstellung hochflexibler Text-Transformer, Code-Generatoren, Daten-Sanitizer und Interpreter für einfache Sprachen.

4.  **Erweiterte Delimiter-Logik (Backreferences):**
    *   **Beispiel:** Korrektes Finden von öffnenden und schließenden HTML/XML-Tags, bei denen der Name des schließenden Tags exakt dem des öffnenden Tags entsprechen muss (z.B. `<div>...</div>`). Der Suchmodus `use_BackReference_IfExists_()$1${1}` ermöglicht dies.
    *   **Nutzen:** Validierung und robustes Parsen von symmetrischen, gepaarten Strukturen in XML/HTML-ähnlichen Formaten.

## Spezifische Anwendungsbeispiele (basierend auf der Entwicklungshistorie)

*   **AutoHotKey-Skript-Bearbeitung:**
    Ein signifikanter Anwendungsfall war die Analyse und automatische Neuformatierung (Pretty-Printing) von AutoHotKey-Skripten. Die Klasse wurde genutzt, um Codeblöcke zu identifizieren und deren Einrückung basierend auf der Verschachtelungstiefe anzupassen, was die Lesbarkeit komplexer Skripte erheblich verbessert.

*   **doSqlWeb Template Engine:**
    Die Klasse ist ein integraler Bestandteil des doSqlWeb-Projekts, einer PHP-basierten Template-Engine. Hier dient sie dazu, spezielle Template-Anweisungen (oft SQL-ähnlich und in eckigen Klammern) aus HTML-Templates zu extrahieren und diese dynamisch zu verarbeiten, beispielsweise um Datenbankinhalte zu laden und darzustellen.

## Alleinstellungsmerkmale (Vermutung)

*   **Kombination aus Rekursion, Regex und Callbacks:** Die Stärke liegt in der nahtlosen Integration dieser drei Konzepte, um komplexe, verschachtelte Textstrukturen nicht nur zu finden, sondern auch flexibel und tiefgreifend zu transformieren.
*   **Konfigurierbare Suchmodi:** Die verschiedenen Suchmodi erlauben eine Anpassung an unterschiedliche Anforderungen, von einfachen String-Delimitern bis hin zu vollwertigen regulären Ausdrücken mit Rückverweisen.
*   **Robuste Handhabung von Verschachtelungen:** Im Gegensatz zu vielen einfacheren String-Suchfunktionen oder Regex-Ansätzen, die bei der ersten schließenden Klammer stoppen, ist `PregContentFinder` darauf ausgelegt, die korrekte Balance von öffnenden und schließenden Delimitern zu finden.

Diese Kombination von Eigenschaften macht die `PregContentFinder`-Klasse zu einem spezialisierten und leistungsfähigen Werkzeug für anspruchsvolle Textverarbeitungsaufgaben, für das uns keine direkte Alternative mit vergleichbarem Funktionsumfang in der PHP-Welt bekannt ist. Sie stellt somit ein Projekt dar, das nicht nur langjährig gepflegt wurde, sondern auch eine Nische mit einem durchdachten Lösungsansatz füllt.
