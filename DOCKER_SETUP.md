# Docker Setup voor KotKompas

## Eerste instelling

### 1. Lokale dependencies installeren

```bash
# Composer dependencies
composer install

# Node dependencies
npm install
```

### 2. Docker starten

```bash
docker-compose up -d --build
```

Klaar! Jouw lokale `vendor/` en `node_modules/` worden via Volume gesynchroniseerd in Docker.

## Workflow

**ALLES gebeurt LOKAAL** - Docker is alleen de runtime omgeving.

- **Code schrijven**: Lokaal in jouw editor
- **Composer packages toevoegen**: Lokaal met `composer require package/name`
- **npm packages toevoegen**: Lokaal met `npm install package-name`
- **Dependencies updaten**: Lokaal met `composer install` / `npm install`
- **App draait**: In Docker (op http://localhost:8080)

### Belangrijk
Je hoeft **NOOIT** `docker-compose exec` te gebruiken voor composer/npm commands! Alles wat je lokaal doet wordt automatisch gesynchroniseerd in Docker via de Volume.

## Workflow: Dependencies toevoegen

1. **Lokaal** composer/npm command uitvoeren:
   ```bash
   composer require package/name
   npm install package-name
   ```

2. **Docker rebuild** (dit kopieert de nieuwe bestanden):
   ```bash
   docker-compose up -d --build
   ```

Dat's het! Docker haalt de nieuwe dependencies van jouw lokale `vendor/` en `node_modules/`.

## Windows (WSL2) tips

- Clone het project in het WSL bestandssysteem: `cd ~/projects/kotkompas` (niet onder `/mnt/c/...`)
- Docker Desktop met WSL2 backend
- Daarna normaal: `composer install`, `npm install`, `docker-compose up -d`

## macOS / Linux

Alles draait standaard, `:cached` flag helpt met performance.
