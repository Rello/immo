**Immo App – Anforderungsdokument V1**

---

## Ziel

Die Domus Immobilien Verwaltung ist eine App für Nextcloud, die kleine bis mittelgroße Immobilienverwalter, Vermieter und Eigentümergemeinschaften bei der Verwaltung von Immobilien, Mietobjekten, Mietern und Mietverhältnissen unterstützt.  
Sie nutzt ausschließlich bestehende Nextcloud-Funktionalitäten (Benutzer, Authentifizierung, Datenbank, Dateisystem) und stellt ein zentrales, strukturiertes System zur Erfassung von Stammdaten, Einnahmen, Ausgaben und zur Erstellung von Jahresabrechnungen bereit.

Der Name der App ist "Domus"
Der Namespace ist "Domus"
Die Nextcloud-id der App ist "domus"

Die erste Version (MVP) gilt als erfolgreich, wenn:

- ein nutzbares Dashboard angezeigt wird,
- alle relevanten Erfassungsmasken funktionieren (Immobilien, Mietobjekte, Mieter, Mietverhältnisse, Einnahmen, Ausgaben),
- Dokumente aus dem Nextcloud-Dateisystem mit Datensätzen verknüpft werden können,
- mindestens eine Jahresabrechnung erzeugt und als Datei im Nextcloud-Dateisystem abgelegt werden kann.
- In V1 wird nur eine Währung Euro unterstützt
- Alle CRUD Prozesse werden im Frontend grundlegend bereitgestellt

---

## Nutzergruppen

1. **Verwalter / Vermieter (Primärnutzer)**
   - Rolle: legt Stammdaten an, pflegt Einnahmen und Ausgaben, verwaltet Mietverhältnisse, erstellt Abrechnungen.
   - Bedürfnisse:
     - Schneller Überblick über Immobilienbestand und Wirtschaftlichkeit.
     - Einfache Eingabe und Pflege von Daten.
     - Strukturiertes Ablegen und Wiederfinden von Dokumenten (Verträge, Rechnungen, Bescheide).
     - Erstellung von Jahresabrechnungen pro Immobilie / Mietobjekt / Mieter.
   - Es kann beliebig viele Verwalter geben, die nur ihre eigenen Objekte sehen.

2. **Mieter (Sekundärnutzer)**
   - Rolle: hat lesenden Zugriff auf eigene Daten und Abrechnungen.
   - Bedürfnisse:
     - Zugriff auf aktuelle und historische Abrechnungen.
     - Einsicht in relevante Stammdaten zum eigenen Mietverhältnis (z. B. Mietobjekt, Konditionen).
     - Zugriff auf hinterlegte Dokumente wie Mietvertrag.

3. **Administrator der Nextcloud-Instanz (technische Rolle)**
   - Rolle: installiert und konfiguriert die App innerhalb von Nextcloud.
   - Bedürfnisse:
     - Einfache Installation und Konfiguration ohne tiefgreifende Systemänderungen.
     - Keine Erweiterung der Nextcloud-Kernfunktionen.
     - Klare Rechte- und Rollentrennung über bestehende Nextcloud-User.

---

## Funktionen

### 1. Allgemeines & Integration in Nextcloud

1.1 **Anbindung an Nextcloud-Benutzerverwaltung**
- Nutzung bestehender Nextcloud-Benutzerkonten für Verwalter und Mieter.
- Rollenmodell innerhalb der App: mind. „Verwalter“ und „Mieter“ (z. B. via Gruppen oder App-Konfiguration).

1.2 **Navigation & Layout**
- Zugriff auf die App über einen Eintrag in der Nextcloud-Hauptnavigation.
- Einheitliche Nutzung von Nextcloud-Layout und -Designrichtlinien.
- Navigation auf der Linken Seite; Content im Hauptfenster
- Client-seitige Render-Templates für alle Seiten.
- Einsatz von Vanilla JavaScript für dynamische UI-Funktionen.
- Bei Navigationen soll der Inhalt dynamisch neu geladen werden. 
- Bei Navigationen soll die App nicht neu geladen werden.

1.3 **Dateisystem-Anbindung**
- Zugriff auf das Nextcloud-Dateisystem des jeweiligen Benutzers.
- Verknüpfung vorhandener Dateien mit Datensätzen (Immobilie, Mietobjekt, Mieter, Mietverhältnis, Einnahme/Ausgabe).
- Ablage generierter Abrechnungen als Datei in einem definierbaren Ordner (z. B. `/DomusApp/Abrechnungen/<Jahr>/`).

---

### 2. Stammdatenverwaltung

#### 2.1 Immobilien

- Anlegen, Bearbeiten, Löschen von Immobilien.
- Mögliche Felder:
  - Name / Bezeichnung der Immobilie
  - Adresse (Straße, PLZ, Ort, Land)
  - Objektart (z. B. Mehrfamilienhaus, ETW, Gewerbe) – optional
  - Notizen / Beschreibung – optional
  - Verknüpfte Dokumente (z. B. Kaufvertrag, Grundbuchauszug)
- Anzeige:
  - Liste aller Immobilien mit Basisdaten und Kennzahlen (z. B. Anzahl Mietobjekte).
  - Detailansicht pro Immobilie mit:
    - Liste der zugehörigen Mietobjekte
    - zentrale Kennzahlen (z. B. Gesamtmiete/Jahr, Rendite/Kostendeckung pro Jahr – soweit berechnet).
- Eine Immobilie ist nur einem Verwalter zugeordnet.
- Jeder Verwalter sieht nur seine eignen Immobilien.

#### 2.2 Mietobjekte

- Mietobjekte sind einer Immobilie zugeordnet.
- Anlegen, Bearbeiten, Löschen von Mietobjekten.
- Mögliche Felder:
  - Zuordnung zur Immobilie (Pflicht)
  - Bezeichnung (z. B. „Whg. 3. OG links“)
  - Lage/Nummer (z. B. Wohnungsnummer, Türnummer)
  - Grundbuch Eintrag
  - Wohnfläche (m²)
  - Nutzfläche (optional)
  - Art (Wohnung, Gewerbe, Stellplatz etc.) – optional
  - Notizen
  - Verknüpfte Dokumente (z. B. Grundriss)
- Anzeige:
  - Liste der Mietobjekte pro Immobilie.
  - Detailansicht je Mietobjekt mit:
    - Stammdaten
    - Aktuelle und historische Mietverhältnisse
    - Relevante Einnahmen/Ausgaben
    - Kennzahlen (z. B. Miete pro m²).

#### 2.3 Mieter

- Anlegen, Bearbeiten, Löschen von Mietern (personenbezogene Stammdaten).
- Mögliche Felder:
  - Name (Pflicht)
  - Kontaktdaten (Adresse, E-Mail, Telefon) – optional
  - Kundennummer/Referenz – optional
  - Notizen
  - Verknüpfte Dokumente (z. B. Ausweiskopie, Schriftverkehr)
- Anzeige:
  - Liste aller Mieter mit Such- und Filteroptionen.
  - Detailansicht je Mieter mit:
    - Stammdaten
    - Aktuelle und historische Mietverhältnisse
    - Übersicht über ihm zugeordnete Abrechnungen/Dokumente.

---

### 3. Mietverhältnisse

- Mietverhältnis verknüpft:
  - genau einen Mieter
  - mit genau einem Mietobjekt
  - für einen definierten Zeitraum (Startdatum bis Enddatum oder offen).

#### 3.1 Erfassung & Pflege

- Anlegen, Bearbeiten, Beenden von Mietverhältnissen.
- Felder:
  - Mieter (Auswahl aus Mieterstammdaten)
  - Mietobjekt (Auswahl aus Mietobjekten)
  - Startdatum (Pflicht)
  - Enddatum (optional, leer = offen laufend)
  - Vereinbarte Kaltmiete (Betrag, Pflicht)
  - Vereinbarte Nebenkosten oder Nebenkostenvorauszahlung (Betrag, optional, Feld/Flag zur Differenzierung möglich)
  - Kaution (optional, Betrag)
  - Weitere Konditionen (Freitext, z. B. Staffelmiete, Indexmiete)
  - Verknüpfte Dokumente (z. B. Mietvertrag, Übergabeprotokoll)
- Statussteuerung:
  - „aktiv“ (aktuelles Mietverhältnis, Zeitpunkt liegt zwischen Startdatum und Enddatum/offen)
  - „historisch“ (abgeschlossen, Enddatum in der Vergangenheit)
  - „zukünftig“ (Startdatum in der Zukunft).

#### 3.2 Ansichten

- Pro Mietobjekt: Liste der dazugehörigen Mietverhältnisse (aktuell, historisch, zukünftig).
- Pro Mieter: Liste der Mietverhältnisse (analog).
- Detailansicht Mietverhältnis:
  - Stammdaten laut oben
  - Zugeordnete Einnahmen/Ausgaben
  - Verknüpfte Dokumente
  - Zeitraumbezogene Kennzahlen (z. B. Summe Mieten im Jahr).

---

### 4. Einnahmen- und Ausgabenverwaltung

#### 4.1 Allgemein

- Erfassung von Einnahmen (z. B. Mieten, Nebenkosten) und Ausgaben (z. B. Reparaturen, Kreditzinsen, Versicherungen).
- Zuordnung mindestens zu:
  - Jahr (Pflicht)
  - Immobilie (Pflicht; alternativ direkter Bezug zu Mietobjekt, aber Immobilie muss ableitbar sein)
- Optionale Zuordnung zu:
  - Mietobjekt
  - Mietverhältnis
- Mehrfachzuordnung/Verteilregeln werden in V1 nur soweit umgesetzt, wie für Jahresverteilungen erforderlich ist.

#### 4.2 Felder Einnahme/Ausgabe

- Typ: Einnahme oder Ausgabe (Pflicht).
- Kategorie (z. B. Miete, Nebenkosten, Hausgeld umlagefähig, Hausgeld nicht umlagefähig, Zuführung Rücklage, Grundsteuer, Kreditzinsen, Abschreibung, Instandhaltung, Verwaltung, Sonstiges).
- Datum (Pflicht).
- Betrag (Pflicht).
- Beschreibung/Verwendungszweck (Freitext).
- Bezug:
  - Immobilie (Pflicht)
  - Mietobjekt (optional; Auswahl eingeschränkt auf Mietobjekte der Immobilie)
  - Mietverhältnis (optional; Auswahl eingeschränkt auf Mietverhältnisse des Mietobjekts)
  - Jahr (automatisch aus Datum ableitbar, aber im Modell als Feld für Auswertungen verfügbar).
- Verknüpfte Dokumente (z. B. Rechnung, Kontoauszug als Datei aus Nextcloud).

#### 4.3 Verteilung bei unterjährigen Mietwechseln

- Jahresbeträge (z. B. Kreditzinsen, Versicherungsprämien) können einer Immobilie zugeordnet und dann:
  - proportional nach Monaten auf vorhandene Mietverhältnisse eines Jahres verteilt werden.
- Logik:
  - Für ein Jahr werden alle aktiven Mietverhältnisse einer Immobilie betrachtet.
  - Monate je Mietverhältnis im Jahr ermittelt.
  - Jahresbetrag anteilig nach belegten Monaten verteilt.
- In V1 ausreichend, wenn:
  - Die Verteilung erfolgt synchron beim speichern.
  - Das Ergebnis in einer Auswertung/Statistik sichtbar ist (nicht zwingend als einzelne Buchung).

---

### 5. Abrechnungen

#### 5.1 Erstellung von Jahresabrechnungen

- Erstellung von Abrechnungen pro Jahr, typischerweise:
  - pro Immobilie und/oder
  - pro Mietobjekt und/oder
  - pro Mieter/Mietverhältnis.
- Minimalumfang V1:
  - Erstellung einer Jahresabrechnung pro Immobilie mit aufgeschlüsselten Einnahmen/Ausgaben und Kennzahlen, sodass daraus z. B. Rendite/Kostendeckung erkennbar ist.
  - Optional: einfache Mieterabrechnung (Mietverhältnis-bezogen), wenn der Datenstand es erlaubt.

#### 5.2 Inhalt der Abrechnung (Mindestsatz)

- Zeitraum (Jahr).
- Immobilie (und ggf. Mietobjekt, Mietverhältnis, Mieter).
- Summen:
  - Gesamteinnahmen pro Kategorie.
  - Gesamtausgaben pro Kategorie.
  - Netto-Ergebnis (Einnahmen – Ausgaben).
- Kennzahlen (sofern berechenbar):
  - Miete pro m² (für Mietobjekt/Mietverhältnis).
  - Rendite oder Kostendeckung (z. B. Netto-Ergebnis / Gesamtausgaben oder / Kaufpreis, falls hinterlegt).
- Auflistung relevanter Positionen (mindestens kategorisierter Summenblock; Detailpositionen in V1 einfach gehalten).

#### 5.3 Speicherung & Zugriff

- Abrechnung wird serverseitig generiert (Textdatei in V1).
- Ablage im Nextcloud-Dateisystem in einem festgelegten Ordner mit strukturierter Benennung:
  - z. B. `/DomusApp/Abrechnungen/<Jahr>/<Immobilie>/<Dateiname>.md`.
- Verknüpfung der erzeugten Abrechnungsdatei mit:
  - Immobilie
  - ggf. Mietobjekt/Mietverhältnis/Mieter.
- Anzeige in der App:
  - Liste historischer Abrechnungen je Immobilie und je Mieter/Mietverhältnis.
  - Download-Link zur Datei aus Nextcloud.

---

### 6. Dashboard & Auswertungen

#### 6.1 Dashboard (Startansicht Verwalter)

- Zentrale Kennzahlen und Statusinformationen, u. a.:
  - Anzahl Immobilien.
  - Anzahl Mietobjekte, Anzahl aktuell vermieteter Mietobjekte, Leerstand.
  - Gesamtmiete pro Monat/Jahr.
  - Miete pro m² auf Ebene:
    - Gesamt
    - Immobilie
    - ggf. Mietobjekt.
  - Kennzahlen zur Kostendeckung/Rendite pro Jahr (summarisch).
  - Liste „offene Punkte“, z. B.:
    - Mietverhältnisse mit nahendem Start/Ende.
    - Buchungen ohne zugeordnetes Mietverhältnis.
    - Ausgaben ohne Kategorie (Datenqualitäts-Hinweise).

- UI-Anforderungen:
  - Client-seitiges Rendering.
  - Optionale Filter (Jahr, Immobilie).

#### 6.2 Listen & Statistiken

- Listen:
  - Immobilien, Mietobjekte, Mieter, Mietverhältnisse, Einnahmen, Ausgaben.
  - Filter: Jahr, Immobilie, Kategorie etc.
- Statistiken pro Jahr:
  - Einnahmen/Ausgaben je Immobilie.
  - Rendite/Kostendeckung je Immobilie.
  - Miete pro m² je Mietobjekt und ggf. je Immobilie.

---

## Abgrenzung (Nicht-Ziele / Scope-Exclusions)

- **Keine Erweiterung der Nextcloud-Kernfunktionen**:
  - Keine Änderungen am Authentifizierungsmechanismus.
  - Keine neuen Dateisystemfunktionen außerhalb der App-Integration.
- **Keine Zahlungsabwicklung**:
  - Kein Einzug von Mieten oder Nebenkosten.
  - Keine Integration von Payment Providern.
- **Kein Bankzugang**:
  - Kein Zugriff auf Bankkonten, keine Kontoauszugssynchronisation.
- **Keine vollständige Buchhaltungssoftware**:
  - Nur einfache Einnahmen-/Ausgabenverwaltung.
  - Keine doppelte Buchführung, keine Kontenrahmen, keine FiBu-Exporte.
- **Keine Erstellung von Steuererklärungen**:
  - App unterstützt nicht bei der automatisierten Erstellung oder Übermittlung von Steuererklärungen.
- **Keine komplexe Rechteverwaltung über die Standard-Nextcloud-User hinaus** in V1:
  - Feingranulare Objektberechtigungen (z. B. einzelne Immobilien pro Verwalter) können in späteren Versionen ergänzt werden.
- **Kein Offline-Betrieb**:
  - Nutzung nur im Rahmen der webbasierten Nextcloud-Umgebung.

---

## Akzeptanzkriterien (für V1/MVP)

### 1. Dashboard

1.1 Ein Verwalter kann die App über die Nextcloud-Navigation öffnen und sieht ein Dashboard.  
1.2 Das Dashboard zeigt mindestens:
- Anzahl der verwalteten Immobilien.
- Anzahl der Mietobjekte.
- Anzahl der aktiven Mietverhältnisse.
- Summe der Soll-Miete (aus Kaltmieten aktiver Mietverhältnisse) für das aktuelle Jahr.
- Miete pro m² für mindestens ein Mietobjekt mit hinterlegter Fläche und Kaltmiete.

### 2. Stammdaten: Immobilien, Mietobjekte, Mieter

2.1 Verwalter kann mindestens eine Immobilie anlegen, bearbeiten und löschen.  
2.2 Verwalter kann zu einer Immobilie mindestens ein Mietobjekt anlegen, bearbeiten und löschen.  
2.3 Verwalter kann mindestens einen Mieter anlegen, bearbeiten und löschen.  
2.4 Listenansichten für Immobilien, Mietobjekte und Mieter sind erreichbar und zeigen die erfassten Einträge korrekt an.

### 3. Mietverhältnisse

3.1 Verwalter kann für ein bestehendes Mietobjekt und einen bestehenden Mieter ein Mietverhältnis mit Startdatum und Kaltmiete anlegen.  
3.2 Verwalter kann ein Mietverhältnis bearbeiten (z. B. Enddatum setzen, Miete anpassen).  
3.3 Verwalter kann ein Mietverhältnis beenden (Enddatum setzen) und der Status wird als historisch erkennbar.  
3.4 In der Detailansicht eines Mietobjekts werden alle aktuellen und historischen Mietverhältnisse angezeigt.  
3.5 In der Detailansicht eines Mieters werden alle aktuellen und historischen Mietverhältnisse angezeigt.

### 4. Einnahmen und Ausgaben

4.1 Verwalter kann Einnahmen erfassen, mindestens mit Feldern: Datum, Betrag, Kategorie, Immobilie.  
4.2 Verwalter kann Ausgaben erfassen, mindestens mit denselben Pflichtfeldern.  
4.3 Einnahmen/Ausgaben können optional einem Mietobjekt und/oder Mietverhältnis zugeordnet werden.  
4.4 Die Jahreszugehörigkeit wird aus dem Datum korrekt abgeleitet und in Listen/Statistiken genutzt.  
4.5 Listenansichten für Einnahmen und Ausgaben sind verfügbar und filterbar nach Jahr und Immobilie.

### 5. Dokumentenverknüpfung

5.1 Verwalter kann bei folgenden Datensätzen eine Datei aus dem Nextcloud-Dateisystem auswählen und verknüpfen:
- Immobilie
- Mietobjekt
- Mieter
- Mietverhältnis
- Einnahme/Ausgabe
5.2 Verknüpfte Dateien werden in der Detailansicht des Datensatzes als Liste mit Link angezeigt.  
5.3 Ein Klick auf den Link öffnet die Datei über Nextcloud (gemäß den Nextcloud-Berechtigungen).

### 6. Abrechnungen

6.1 Verwalter kann für eine Immobilie und ein Jahr eine Abrechnung erstellen.  
6.2 Die Abrechnung aggregiert Einnahmen und Ausgaben des gewählten Jahres für die Immobilie und zeigt mindestens:
- Summe Einnahmen
- Summe Ausgaben
- Differenz (Netto-Ergebnis)
6.3 Die Abrechnung wird als Datei (z. B. PDF) im Nextcloud-Dateisystem in einem vorgegebenen Ordner gespeichert.  
6.4 Die gespeicherte Abrechnung wird in der Immo App in einer Liste historischer Abrechnungen zu der Immobilie angezeigt.  
6.5 Die Abrechnungsdatei kann über die App heruntergeladen/geöffnet werden.

### 7. Berechnungen & Verteilungen

7.1 Für mindestens ein Mietobjekt mit hinterlegter Fläche und einem aktiven Mietverhältnis wird die Miete pro m² korrekt berechnet (Kaltmiete / Fläche).  
7.2 Die App berechnet für ein Jahr pro Immobilie die Summe aller Einnahmen und Ausgaben korrekt.  
7.3 Für mindestens einen als Jahresbetrag erfassten Aufwand (z. B. Kreditzinsen) wird eine anteilige Verteilung nach Monaten auf die aktiven Mietverhältnisse des Jahres erzeugt und in einer Statistik sichtbar gemacht (z. B. als Anteil pro Mietverhältnis).

### 8. Nutzerrollen

8.1 Ein Verwalter kann alle oben beschriebenen Funktionen ausführen.  
8.2 Ein Mieter kann sich in Nextcloud anmelden und:
- seine eigenen Mietverhältnisse einsehen,
- seine eigenen Abrechnungen einsehen und herunterladen,
- zugeordnete Dokumente sehen (z. B. Mietvertrag).
8.3 Ein Mieter sieht keine Stammdaten, Mietverhältnisse oder Abrechnungen anderer Mieter.

### 9. Technische und Integrationskriterien

9.1 Die App ist als Nextcloud-App installierbar und nutzt ausschließlich:
- Nextcloud-Benutzerkonten,
- Nextcloud-Datenbank (über das App-Framework),
- Nextcloud-Dateisystem.
9.2 Alle Seiten verwenden serverseitige Templates; dynamische Funktionen verwenden nur Vanilla JS.  
9.3 Die App führt keinerlei Zahlungsabwicklung durch und greift nicht auf Bankkonten zu.  
9.4 Die App verändert keine Kernfunktionen von Nextcloud (kein Override von Login, Files, Sharing, etc.).

---

Dieses Dokument bildet die Basis für Konzeption, Implementierung und Abnahme der ersten Version der App.
