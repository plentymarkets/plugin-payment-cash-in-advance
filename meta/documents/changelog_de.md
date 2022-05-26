# Release Notes für Vorkasse

## 3.0.7

### Behoben
- Aktualisierungen beim Abrufen von Bankverbindungsdaten.

## 3.0.6

### Behoben
- Die URL der internen Infoseite wird nun für Fremdsprachen korrekt erzeugt.

## 3.0.5

### Behoben
- Der Link zu einer internen Infoseite wird nun wieder korrekt angezeigt.

## 3.0.4

### Behoben
- Das Overlay mit den Bankdaten wird jetzt korrekt übersetzt

## 3.0.3

### Behoben
- Assistenten kann wieder abgeschlossen werden

## 3.0.2

### Geändert
- Icon für das Backend hinzugefügt

## 3.0.1

### Geändert
- Funktionalitäten hinzugefügt für Backend-Sichtbarkeiten und Backend-Name

### Behoben
- Einige Texte wurden angepasst

## 3.0.0

### Hinweis 
- Die Einstellungen für das Vorkasse-Plugin wurden in einen Assistenten überführt und sind nun unter **Einrichtung » Assistenten » Payment** zu finden.

### Geändert
- Die Beschreibung und der Name der Zahlungsart wird nun auch über **CMS » Mehrsprachigkeit** gepflegt.

## 2.1.0 

### Behoben
- Der Button für "Bankdetails anzeigen" wird nun korrekt für die Vorkasse-Zahlungsarten angezeigt.

## 2.0.13

### Behoben
- Ein Javascript-Fehler, der auf der Bestellbestätigungsseite auftreten konnte, wurde behoben.

## 2.0.12

### Geändert
- Die Einstellungen für Lieferländer wurden optimiert.

## 2.0.11

### Behoben
- Ein eventuell auftretendes Problem beim Bereitstellen des Plugins wurde behoben.

## 2.0.10

### Geändert
- Support Informationen ergänzt

## 2.0.9

### Geändert
- Der Plugin User Guide wurde überarbeitet.

## 2.0.8

### Hinzugefügt
- Weitere Sprachen für die Plugin-UI wurden hinzugefügt.

### Geändert
- Sprachabhängige Texte können nun über das Mehrsprachigkeits-Interface angepasst werden.

## 2.0.5

### Behoben
- Probleme bei der Anzeige und dem Speichern von Einstellungen der Multimandanten in der Plugin-UI wurden behoben.

## 2.0.4

### Behoben
- Die Anzeige der Bankdaten auf der Bestellbestätigungsseite wird nicht mehr doppelt dargestellt.

## 2.0.3

### Behoben
- Die Anzeige der Bankdaten ist nun responsive.

## 2.0.2

### Behoben
- Ein Problem mit der Anzeige der Bankdaten im Mein-Konto Bereich wurde behoben.

## 2.0.1

### Behoben
- Es wird nun der korrekte Pfad für die Anzeige des Logos der Zahlungsart verwendet.

## 2.0.0

### Geändert
- Der Plugin-Name wurde von **PrePayment** in **CashInAdvance** geändert. Die Funktionalität ist nicht betroffen.
- Die Bankdaten werden in der Kaufabwicklung und auf der Bestellbestätigungsseite wieder korrekt angezeigt.

## 1.3.2

### Geändert
- User guide erweitert.

## 1.3.1

### Geändert
- Neuer Menüpfad **System&nbsp;» Aufträge&nbsp;» Zahlung » Plugins » Vorkasse**.

## 1.3.0

### Geändert
- Kompabilität zu Ceres 2.0 hergestellt.

## 1.2.4

### Behoben
- Die Variable `$MethodOfPaymentName` in E-Mail-Vorlagen wird nun sprachabhängig ausgegeben.

## 1.2.3

### Geändert
- Update User guide

## 1.2.2

### Geändert
- Der Einhängepunkt im Systembaum ist nun **System » Aufträge » Zahlung » PrePayment » Vorkasse**.

## 1.2.1

### Hinzugefügt
- Die Auftrags-ID kann direkt im Verwendungszweck angezeigt werden. Dafür muss der Platzhalter **%s** im Textfeld der Plugin-Einstellungen eingegeben werden.

## 1.2.0

### Hinzugefügt
- Die Bankdaten werden in der Kaufabwicklung und auf der Bestellbestätigungsseite direkt angezeigt.

### TODO
- Unter **PrePayment Scripts** muss die Container-Verknüpfung von **Script loader: Register/load JS** auf **Script loader: After scripts loaded** geändert werden.

## 1.1.0

### Hinzugefügt
- Einstellungen für **Infoseite** wurden hinzugefügt.
- Einstellungen für **Beschreibung** wurden hinzugefügt.
- Die Funktionalität **Zahlungsart neu ausführen** wurde hinzugefügt.

### Geändert
- Aufpreise der Zahlungsart wurden entfernt.

## 1.0.4

### Hinzugefügt
- Es wurde eine Methode hinzugefügt, um festzulegen, ob ein Kunde von dieser Zahlungsart auf eine andere wechseln kann.
- Es wurde eine Methode hinzugefügt, um festzulegen, ob ein Kunde von einer anderen Zahlungsart auf diese wechseln kann.

## 1.0.3

### Behoben
- Es wird nun der korrekte Pfad für die Anzeige des Logos der Zahlungsart verwendet.

## 1.0.2

### Behoben
- Das CSS der Einstellungen im Backend wurde angepasst, so dass die Einstellungen nun über die ganze Seitenbreite angezeigt werden.

### Bekannte Probleme
- Die Einstellungen für **Aufpreise** haben derzeit noch keine Funktion bei der Preisberechnung in der Kaufabwicklung (Checkout)

## 1.0.1

### Geändert
- Es wird die ID der Zahlungsart "Vorkasse" aus dem System verwendet.

## 1.0.0

### Funktionen
- Zahlungsart **Vorkasse** für plentymarkets Webshops
- Anzeige von Verwendungszweck und Bankdaten auf der Bestellbestätigungsseite
