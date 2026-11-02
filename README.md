# LabSeat: Live Study Space Finder

A web app that helps ASU students find open study spaces on the Tempe campus. Students post quick availability reports for study zones (how many seats are free, how loud it is, whether outlets are open), and everyone else can see and filter them in one place.

Built for CSE 412 (Database Management), Phase 03.

![LabSeat dashboard](docs/screenshots/frontend_reports.png)

## Features

- Live dashboard with totals for reports, high availability zones, quiet zones and flagged reports
- Filter reports by building, seat availability, noise level and flagged status
- Create, update and delete availability reports straight from the page
- PHP JSON API backed by a normalized PostgreSQL schema (buildings, floors, zones, amenities, users, reports)

| Filtering by noise level | JSON from the read endpoint |
|---|---|
| ![Filtered view](docs/screenshots/3.png) | ![API output](docs/screenshots/backend_json_output.png) |

## Tech Stack

| Layer    | Technology                  |
|----------|-----------------------------|
| Frontend | HTML, CSS, JavaScript       |
| Backend  | PHP (PDO)                   |
| Database | PostgreSQL                  |

## Project Layout

```
frontend/   index.html and style.css (dashboard, filters, CRUD forms)
backend/    PHP API: get_reports, add_report, update_report, delete_report
database/   Schema, seed data and reference queries
docs/       Screenshots
```

## Run It Locally

You need PHP 8 (with the `pdo_pgsql` extension) and PostgreSQL.

1. Create the database and load the schema and sample data:

   ```bash
   createdb labseat
   psql -d labseat -f database/Phase2.sql
   psql -d labseat -f database/labseat_seed_data.sql
   ```

2. Add your database login:

   ```bash
   cp backend/config.example.php backend/config.php
   ```

   Then open `backend/config.php` and fill in `DB_USER` and `DB_PASS`. This file is ignored by Git, so your password stays on your machine.

3. Start PHP from the repo root:

   ```bash
   php -S localhost:8000
   ```

4. Open [http://localhost:8000/frontend/](http://localhost:8000/frontend/) in your browser. You can also check the API directly at [http://localhost:8000/backend/get_reports.php](http://localhost:8000/backend/get_reports.php).

## API Endpoints

| Method | Endpoint                     | What it does              |
|--------|------------------------------|---------------------------|
| GET    | `backend/get_reports.php`    | List all reports with building, floor, zone and user details |
| POST   | `backend/add_report.php`     | Add a new report          |
| PUT, PATCH or POST | `backend/update_report.php`  | Update an existing report |
| DELETE or POST | `backend/delete_report.php`  | Delete a report by ID     |

## Team

| Person | Responsibility |
|--------|---------------|
| Manaswi Makkena | Frontend: index.html, dashboard and live availability display |
| Siri Poluri | Frontend: CRUD forms, styling and JavaScript actions |
| Krutika Anaganti | Backend: database connection and read endpoint |
| Vaishnavi Bhairishetty | Backend: create, update and delete endpoints |
