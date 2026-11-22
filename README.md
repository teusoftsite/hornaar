# MeldHoornaar.nl

Platform dat inwoners, gemeenten en bestrijders koppelt voor het melden en bestrijden van nesten van de Aziatische hoornaar.
Gebouwd volgens **Bouwontwerp v1** (blokken 1 t/m 6).

## Stack

- PHP 8.3, **Laravel 12**, MySQL 8 (SQLite voor tests)
- Laravel Fortify: inloggen, wachtwoord vergeten/instellen, 2FA (geen registratie: accounts maakt de beheerder of de bestrijder-eigenaar)
- Blade + gewone CSS (`public/css/app.css`, tokens uit het design system **EasySoft-stijl**) + klein beetje vanilla JS. **Geen build-stap** (geen npm nodig op de server)
- Huisstijl: EasySoft-stijl (oranje `#F38B12`, bijna-zwart `#17181B`, Space Grotesk + Inter, donkere menubalk 64 px). Logo N3 (nest in locatiepin): `resources/views/partials/logo.blade.php`, `public/logo.svg`, `public/logo-dark.svg`, `public/favicon.svg`
- Publieke pagina's zijn omgezet vanaf het design-canvas: home, zakelijk, `/info` (het verhaal) en `/info/herkennen`, `/jaarcyclus`, `/nesten-zoeken`, `/achtergrond`, `/vragen`. De foto's horen in `public/img/info/` (zie `LEESMIJ.md` daar); ontbreekt een foto, dan verbergt de pagina hem
- Leaflet + PDOK BRT-achtergrondkaart, PDOK Locatieserver voor adres en gemeente
- barryvdh/laravel-dompdf voor factuur-PDF's
- Pest 3: `./vendor/bin/pest` (33 tests, dekken de acceptatiecriteria van alle blokken)

## Afwijkingen van het bouwontwerp

| Bouwontwerp | Gebouwd | Waarom |
|---|---|---|
| Laravel 11 | Laravel 12 | Laravel 11 had openstaande security advisories (`composer audit`) |
| Livewire + Tailwind | Blade + gewone CSS met huisstijl-tokens | Geen build-stap op Plesk, minder bewegende delen |
| MySQL spatial | Grenzen als GeoJSON + bbox-voorfilter + point-in-polygon in PHP | Werkt op elke MySQL/SQLite, makkelijk te testen |
| Route via OSRM | Nearest neighbour + 2-opt op hemelsbrede afstand | Voor pilot ruim voldoende; `RouteOptimizer` is vervangbaar |
| Queue-worker | Mail synchroon (`QUEUE_CONNECTION=sync`) | Geen worker nodig op Plesk; bij volume overschakelen naar `database` + worker |

## Rollen

| Rol | Ziet | 2FA |
|---|---|---|
| beheerder | /beheer: organisaties, gebruikers, contracten, meldingen zonder deelnemende gemeente | verplicht |
| gemeente | /gemeente: alleen nesten van de eigen gemeente; beoordelen, samenvoegen, opdracht geven, rapportage | verplicht |
| bestrijder_eigenaar | /bestrijder: opdrachten, plannen, route, werkbonnen, facturen, instellingen | verplicht |
| bestrijder_medewerker | /bestrijder: eigen/onverdeelde opdrachten, route, werkbonnen | optioneel |

Organisatiescheiding zit in de controllers (404 bij andermans gegevens) en is getest (`Blok3`, `Blok4`, `Blok5`).

## Belangrijkste stromen

1. **Melden** (`/melden`, `POST /api/reports`, max 5/IP/uur, optioneel Cloudflare Turnstile): foto (verkleind naar 1600 px, EXIF eruit), locatie, adres + gemeente via PDOK. Melder krijgt statuslink `/m/{token}`.
2. **Gemeente** beoordeelt → goedkeuren/afwijzen; meldingen binnen 25 m worden als dubbel voorgesteld en samengevoegd; opdracht via actief contract.
3. **Bestrijder** plant (datum + medewerker), optimaliseert de dagroute, vult de werkbon in (werkt offline: IndexedDB-wachtrij, idempotent op `client_uuid`).
4. **Facturatie**: werkbon → factuurregel (bestreden = tarief + toeslagen, geen toegang = voorrijkosten, geen nest = niets). De 1e van de maand om 06:00 → conceptfactuur per contract. Versturen geeft nummer `JJJJ-NNNN` per bestrijder, PDF, mail aan gemeente. Verstuurd = onveranderbaar → creditfactuur. Te laat + herinneringen na 7 en 14 dagen.
5. **AVG**: e-mail melder gewist 90 dagen na afsluiten, IP alleen gehasht, foto's alleen via ondertekende URL (1 uur).

## Lokaal draaien

```bash
composer install
cp .env.example .env     # zet DB_CONNECTION=sqlite voor lokaal
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DemoSeeder   # demo: gemeente@demo.nl / eigenaar@demo.nl / medewerker@demo.nl, wachtwoord "demo-wachtwoord"
php artisan serve
./vendor/bin/pest
```

## Productie (Plesk, meldhoornaar.nl)

1. **Database**: maak in Plesk een MySQL-database `meldhoornaar` + gebruiker.
2. **Code**: Plesk → Git → deze repository, deploy naar bijvoorbeeld `/meldhoornaar-app`. Zet de **document root** van meldhoornaar.nl op `/meldhoornaar-app/public`.
   (De huidige statische site in `/meldhoornaar.nl` kan blijven staan tot de omschakeling.)
3. **PHP**: 8.3, extensies `gd`, `exif`, `pdo_mysql`, `mbstring`, `intl`.
4. **Deploy-acties** (Plesk Git → "Additional deployment actions"):
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
5. **.env**: kopieer `.env.example`, vul `APP_KEY` (`php artisan key:generate`), DB-gegevens en SMTP (mailbox `noreply@meldhoornaar.nl` in Plesk). Optioneel `TURNSTILE_SITE_KEY`/`TURNSTILE_SECRET_KEY`.
6. **Eerste beheerder**: `php artisan db:seed --force` → geeft een link om het wachtwoord te kiezen (en stelt bij eerste login 2FA in).
7. **Gemeentegrenzen**: `php artisan grenzen:import` (CBS via PDOK; jaarlijks rond 1 januari opnieuw). Zonder import valt het systeem terug op de gemeentecode van PDOK Locatieserver.
8. **Cron** (Plesk → Geplande taken, elke minuut):
   `cd /var/www/vhosts/infraeasy.nl/meldhoornaar-app && php artisan schedule:run >> /dev/null 2>&1`
   Dit draait `facturen:maand` (1e, 06:00), `facturen:herinneringen` (dagelijks 07:00) en `privacy:opschonen` (dagelijks 03:00).
9. **SSL**: Let's Encrypt in Plesk, HTTPS-redirect aan.
10. **Back-up**: Plesk Backup Manager dagelijks (database + `storage/app`, daar staan foto's en PDF's).

## Artisan-commando's

| Commando | Doel |
|---|---|
| `grenzen:import {bron?} --jaar=` | Gemeentegrenzen importeren (GeoJSON, WGS84) |
| `facturen:maand --datum=` | Conceptfacturen maken voor de vorige maand |
| `facturen:herinneringen` | Te laat markeren + herinneringen |
| `privacy:opschonen` | E-mailadressen van melders wissen |

## Mappen

- `app/Services` — domeinlogica: `ReportService`, `NestService` (statusflow, dubbelen, samenvoegen), `InvoiceService`, `RouteOptimizer`, `Geocoder`, `MunicipalityLocator`, `PhotoStore`, `Turnstile`
- `app/Support` — `NestStatus` (statussen + toegestane overgangen), `Role`, `Money`, `Geo`, `Photo`
- `resources/views` — `public/`, `auth/`, `gemeente/`, `bestrijder/`, `beheer/`, `pdf/`
- `public/js` — `map.js` (kaart), `app.js` (service worker + offline wachtrij), `public/sw.js`
- `tests/Feature/Blok1..6*` — acceptatietests per bouwblok
