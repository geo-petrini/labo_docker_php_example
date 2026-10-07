# Stack PHP + MySQL — CRUD

Template minimale per avviare un'applicazione **PHP 8.3 (Apache)** con un database **MySQL**, e una piccola CRUD su una risorsa `items`.

## Struttura

```
php/
├── README.md
├── docker-compose.yml   # servizi app + db
├── Dockerfile           # immagine PHP + Apache + estensione pdo_mysql
└── app/
    ├── index.html       # pagina HTML che usa l'API via JS
    ├── api.php          # endpoint CRUD (azione via query string)
    └── db.php           # connessione PDO + creazione tabella iniziale
```

## docker-compose.yml

```yaml
services:
  app:
    build: .
    container_name: php-mysql-app
    ports:
      - "8010:80"
    environment:
      DB_HOST: db
      DB_PORT: 3306
      DB_NAME: esempio
      DB_USER: root
      DB_PASS: segreta
    depends_on:
      db:
        condition: service_healthy

  db:
    image: mysql:8.4
    container_name: php-mysql-db
    ports:
      - "3316:3306"
    environment:
      MYSQL_ROOT_PASSWORD: segreta
      MYSQL_DATABASE: esempio
    volumes:
      - mysql-data:/var/lib/mysql
    healthcheck:
      test: ["CMD-SHELL", "mysqladmin ping -h localhost -p$$MYSQL_ROOT_PASSWORD --silent"]
      interval: 5s
      timeout: 5s
      retries: 10

volumes:
  mysql-data:
```

## Concetti toccati

- **Build** — `build: .` compila il `Dockerfile` (Apache + estensione `pdo_mysql`).
- **Port mapping** — `"8010:80"` espone Apache sull'host; `"3316:3306"` espone MySQL.
- **Variabili d'ambiente** — le credenziali di connessione vengono passate al container e lette da `db.php`.
- **Volume nominato** — `mysql-data` garantisce la persistenza dei dati.
- **Healthcheck** — l'app parte solo quando MySQL risulta pronto (`service_healthy`).

## API CRUD (items)

| Azione | Endpoint |
|---|---|
| Lista | `GET api.php?action=get_all` |
| Singolo | `GET api.php?action=get&id=1` |
| Crea | `POST api.php?action=create` (field `title`, `note`) |
| Modifica | `POST api.php?action=update&id=1` (field `title`, `note`) |
| Elimina | `POST api.php?action=delete&id=1` |

Al primo avvio la tabella `items` viene creata automaticamente e popolata con un elemento di esempio.

## Comandi

```bash
# avviare (compila l'immagine la prima volta)
docker compose up --build

# aprire l'interfaccia web
# http://localhost:8010

# esempi con curl
curl http://localhost:8010/api.php?action=get_all
curl -X POST -d "title=Ciao&note=da curl" http://localhost:8010/api.php?action=create
curl "http://localhost:8010/api.php?action=get&id=1"
curl -X POST -d "title=Aggiornato&note=ok" "http://localhost:8010/api.php?action=update&id=1"
curl -X POST "http://localhost:8010/api.php?action=delete&id=1"

# stato e log
docker compose ps
docker compose logs -f

# fermare tutto
docker compose down

# fermare ed eliminare anche i volumi (perde i dati)
docker compose down -v
```