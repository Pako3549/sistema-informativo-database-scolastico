# School CRUD 📚

A simple school information system built with **PHP + MySQL** and containerized with **Docker Compose**.  
The project provides CRUD operations for the main academic entities (students, teachers, courses, enrollments, tests, and grades), with an optional **Cloudflare Tunnel** for public access.

## Features 🌟

- **Complete CRUD flows** for:
  - Students (`studenti`)
  - Teachers (`docenti`)
  - Courses (`corsi`)
  - Enrollments (`iscrizioni`)
  - Tests (`verifiche`)
  - Grades (`voti`)
- **Relational MySQL schema** with foreign keys and realistic sample data.
- **Simple and clean web UI** with a central home menu.
- **Docker-first setup** for reproducible local environments.
- **Cloudflare Tunnel integration** to expose the app without publishing the web container port.

## Tech Stack 🧱

- PHP 8.2 (Apache)
- MySQL 8.0
- Docker Compose
- Cloudflare Tunnel (`cloudflared`)

## Project Structure 🗂️

```text
.
├── compose.yml
├── .env.example
├── src
│   ├── index.html
│   ├── common/
│   ├── pages/
│   ├── actions/
│   └── mysql-init/
└── README.md
```

## Setup ⚙️

1. Clone the repository.
2. Create your env file:

```bash
cp .env.example .env
```

3. Update credentials and tunnel token in `.env`:
   - `DB_USER`, `DB_PASS`
   - `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`
   - `CLOUDFLARE_TUNNEL_TOKEN`

4. Start services:

```bash
docker compose up -d --build
```

## Access 🌐

- **Database (local)**: `localhost:3307`
- **Web app**: exposed through Cloudflare Tunnel route (for example `https://school-crud.pako.uk`)

> Note: in this setup, the web container is internal-only (`web:80`) and not directly published on host port 8080.

## Usage 🧪

- Open the home page from your public tunnel hostname.
- Navigate to the desired area from the main menu:
  - Gestione voti
  - Gestione iscrizioni
  - Gestione verifiche
  - Gestione studenti
  - Gestione docenti
  - Gestione corsi
- Use create/edit/delete actions and filters on each section.

## Customization ✨

- **Data model**: edit `src/mysql-init/school_lab.sql`.
- **UI/Styling**: edit `src/common/style.css`.
- **Layout helpers**: edit `src/common/layout.php` and `src/common/helpers.php`.
- **Cloudflare routing**: manage public hostname and service route in Cloudflare Zero Trust.

## Contributing 🤝

Contributions are welcome.  
Open an issue or submit a pull request with clear context and test steps.

## License 📄

This project is open source and distributed under the MIT License.  
See [LICENSE](LICENSE) for details.
