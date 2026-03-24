# GMS SpaKaas

GMS SpaKaas is een webapplicatie ontwikkeld in PHP voor het beheren van een spa resort.  
Gebruikers kunnen eenvoudig spa-arrangementen bekijken, reserveringen maken en gegevens beheren via een overzichtelijke interface.

## Functionaliteiten

Hier zijn de basis functionaliteiten wat in dit programma zit:

- Overzicht van spa-arrangementen en lodges
- Inlog/registratie functionaliteit, inclusief 2FA
- Reserveringen maken en beheren
- Klantgegevens aanmaken en aanpassen
- Overzicht van gemaakte boekingen (kalendar)
- Mogelijkheid om favoriete lodges op te slaan
- Recente bekeken items bijhouden (cookies)
- Filtering / selectie van aanbod
- Validatie en foutafhandeling bij formulieren
- Onderhoudsrapportage voor de monteurs
- Omzet en rapportage voor managers
- Medewerkers overzicht (uren, omzet etc)
- Databasekoppeling (MySQL)

## Gebruikte technologieën

- **PHP (Back-End)**
- **MySQL (Database Query's)**
- **HTML, CSS, JavaScript (Frond-End)**
- **phpMyAdmin (Databasebeheer)**

## Doel van het project

Dit project is ontwikkeld als onderdeel van onze software development opleiding.  
De focus lag hierbij op:

- Het bouwen van een dynamische webapplicatie
- Werken met databases en migrations
- Het ontwikkelen van een reserveringssysteem
- Backend en frontend laten samenwerken
- Praktische programmeervaardigheden verbeteren
- In een teamsverband werken
- Werken met versiebeheer d.m.v Git en Github

## Installatie

1. Clone de repository:
   git clone https://github.com/Munderien/gms-SpaKaas.git

2. Maak een nieuwe database aan genaamd dms-spakaas (gebruik een InnoDB engine voor relaties)

3. Run in je project terminal de volgende commando: php migrate.php (staat een handleiding in de migrations map onder de naam: "RunMigration.md)


