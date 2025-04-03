
# Stock Market Web Application

![Laravel](https://img.shields.io/badge/Laravel-v10.x-red.svg)  
![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)  
![Docker](https://img.shields.io/badge/Docker-Supported-blue.svg)  
![License](https://img.shields.io/badge/License-MIT-green.svg)

A stock market web application developed as an internship project by a team of 7 interns over 15-20 days. This Laravel-based backend powers a full-stack application designed to provide real-time stock data, portfolio management, simulated trading, and secure user authentication. The project emphasizes collaborative development, security, and practical full-stack experience, with Docker support for easy setup.

## Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
  - [Standard Installation](#standard-installation)
  - [Docker Installation (Linux)](#docker-installation-linux)
  - [Docker Installation (Windows)](#docker-installation-windows)
- [Configuration](#configuration)
- [API Endpoints](#api-endpoints)
- [Database Schema](#database-schema)
- [Usage](#usage)
- [Testing](#testing)
- [Team Structure](#team-structure)
- [Contributing](#contributing)
- [License](#license)

## Features

### Core Features
- **User Authentication:**
  - Registration, login, logout, and password reset.
  - Secure JWT-based authentication with token expiration and refresh.
  - Optional 2FA (Time-Based One-Time Password).
- **Stock Data:**
  - Real-time stock quotes via WebSocket integration.
  - Historical stock data with charts.
  - Stock search functionality.
- **Portfolio Management:**
  - Add/remove stocks to/from portfolio.
  - View portfolio and track performance.
- **Simulated Trading:**
  - Buy/sell stocks with virtual money.
  - Transaction history tracking.
- **Watchlist:**
  - Add/remove stocks to/from watchlist.
  - View watchlist.
- **IPO Features:**
  - View IPO details.
  - Apply for IPOs.
- **Holdings:**
  - Track user holdings.

### Security Features
- JWT for authentication and authorization.
- Password hashing with bcrypt.
- Input validation and rate limiting.
- Optional 2FA implementation.

*Note:* Additional features beyond the initial scope have been implemented—refer to the codebase for details.

## Requirements
- PHP >= 8.2.28x 
- Composer >= 2.8.6x
- Laravel >= 12.x
- PostgreSQL
- WebSocket support (e.g., Laravel WebSockets or Pusher)
- Git for version control
- Docker ( for containerized setup)

## Installation

### Standard Installation
1. **Clone the repository:**
   ```bash
   git clone https://github.com/username/stock-market-web-app.git
   cd stock-market-web-app
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up the environment file:**
   ```bash
   cp .env.example .env
   ```

4. **Generate an application key:**
   ```bash
   php artisan key:generate
   ```

5. **Configure the database:**
   - Update `.env` with your database credentials:
     ```
     DB_CONNECTION=pgsql
     DB_HOST=postgres
     DB_PORT=5432
     DB_DATABASE=stock-sense
     DB_USERNAME=myuser
     DB_PASSWORD=mypassword
     PGADMIN_PORT=8081
     PGADMIN_EMAIL=admin@example.com
     PGADMIN_PASSWORD=adminpassword

     ```
   - Run migrations:
     ```bash
     php artisan migrate
     ```

6. **(Optional) Seed the database:**
   ```bash
     php artisan db:seed
     ```

7. **Start the Laravel server:**
   ```bash
   php artisan serve
   ```
   The backend will be available at `http://localhost:8000`.

8. **Set up WebSockets (if applicable):**
   - Install Laravel WebSockets:
     ```bash
     composer require beyondcode/laravel-websockets
     php artisan websockets:serve
     ```

### Docker Installation (Linux)
1. **Install Docker and Docker Compose:**
   - Update package list:
     ```bash
     sudo apt update
     sudo apt install docker.io docker-compose -y
     ```
   - Start and enable Docker:
     ```bash
     sudo systemctl start docker
     sudo systemctl enable docker
     ```

2. **Clone the repository:**
   ```bash
   git clone https://github.com/username/stock-market-web-app.git
   cd stock-market-web-app
   ```

3. **Set up the environment file:**
   ```bash
   cp .env.example .env
   ```
   - Update `.env` with Docker-specific database settings:
     ```
     DB_CONNECTION=pgsql
     DB_HOST=postgres
     DB_PORT=5432
     DB_DATABASE=stock-sense
     DB_USERNAME=myuser
     DB_PASSWORD=mypassword
     PGADMIN_PORT=8081
     PGADMIN_EMAIL=admin@example.com
     PGADMIN_PASSWORD=adminpassword
     ```

4. **Build and run the containers:**
   ```bash
   docker-compose up -d --build
   ```
   - This starts the app, database, and WebSocket services (assuming a `docker-compose.yml` is included).

5. **Run migrations inside the container:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. **Access the application:**
   - Backend: `http://localhost:8080`
   - WebSockets (if configured): `ws://localhost:6001`

7. **Stop the containers:**
   ```bash
   docker-compose down
   ```

### Docker Installation (Windows)
1. **Install Docker Desktop:**
   - Download and install [Docker Desktop](https://www.docker.com/products/docker-desktop) for Windows.
   - Launch Docker Desktop and ensure it’s running.

2. **Clone the repository:**
   - Open a terminal (e.g., PowerShell or Command Prompt):
     ```bash
     git clone https://github.com/username/stock-market-web-app.git
     cd stock-market-web-app
     ```

3. **Set up the environment file:**
   ```bash
   copy .env.example .env
   ```
   - Edit `.env` with Docker-specific database settings:
     ```
     DB_CONNECTION=pgsql
     DB_HOST=postgres
     DB_PORT=5432
     DB_DATABASE=stock-sense
     DB_USERNAME=myuser
     DB_PASSWORD=mypassword
     PGADMIN_PORT=8081
     PGADMIN_EMAIL=admin@example.com
     PGADMIN_PASSWORD=adminpassword
     ```

4. **Build and run the containers:**
   ```bash
   docker-compose up -d --build
   ```

5. **Run migrations inside the container:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. **Access the application:**
   - Backend: `http://localhost:8080`
   - WebSockets (if configured): `ws://localhost:6001`

7. **Stop the containers:**
   ```bash
   docker-compose down
   ```

*Note:* Ensure a `docker-compose.yml` file is present in the repository. Example configuration:
```yaml
version: 
laravel.test:
        build:
            context: './vendor/laravel/sail/runtimes/8.4'
            dockerfile: Dockerfile
            args:
                WWWGROUP: '${WWWGROUP}'
        image: 'sail-8.4/app'
        extra_hosts:
            - 'host.docker.internal:host-gateway'
        ports:
            - '${APP_PORT:-80}:80'
            - '${VITE_PORT:-5173}:${VITE_PORT:-5173}'
        environment:
            WWWUSER: '${WWWUSER}'
            LARAVEL_SAIL: 1
            XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
            XDEBUG_CONFIG: '${SAIL_XDEBUG_CONFIG:-client_host=host.docker.internal}'
            IGNITION_LOCAL_SITES_PATH: '${PWD}'
        volumes:
            - '.:/var/www/html'
        networks:
            - sail
        depends_on:
            - postgres
            - redis
            - meilisearch
            - mailpit
```

## Configuration
- Update `.env` with:
  - `APP_URL` for the application base URL.
  - `JWT_SECRET` for JWT authentication (generate via `php artisan jwt:secret` if using tymon/jwt-auth).
  - WebSocket settings (e.g., `PUSHER_APP_*` or Laravel WebSockets config).
  - Mail settings (`MAIL_*`) for password reset emails.
- Configure queues for real-time updates:
  ```bash
  php artisan queue:work
  ```

## API Endpoints

### Authentication API
| Method | Endpoint                     | Description                | Parameters            |
|--------|------------------------------|----------------------------|-----------------------|
| POST   | `/api/auth/register`         | Register a new user        | `username`, `email`, `password` |
| POST   | `/api/auth/login`            | Log in a user              | `email`, `password`   |
| POST   | `/api/auth/logout`           | Log out a user             | `Authorization: Bearer <token>` |
| POST   | `/api/auth/reset-password`   | Request password reset     | `email`               |
| PUT    | `/api/auth/reset-password/{token}` | Reset password       | `password`            |
| POST   | `/api/auth/2fa/enable`       | Enable 2FA                 | `Authorization: Bearer <token>` |
| POST   | `/api/auth/2fa/verify`       | Verify 2FA code            | `token` (TOTP code)   |
| POST   | `/api/auth/2fa/disable`      | Disable 2FA                | `token` (TOTP code)   |

### Stock Data API
| Method | Endpoint                     | Description                | Parameters            |
|--------|------------------------------|----------------------------|-----------------------|
| GET    | `/api/stocks/{symbol}`       | Get real-time stock quote  | `symbol`              |
| GET    | `/api/stocks/{symbol}/history` | Get historical data      | `from`, `to` (query)  |
| GET    | `/api/stocks/search`         | Search for stocks          | `q` (query)           |

### Portfolio API
| Method | Endpoint                     | Description                | Parameters            |
|--------|------------------------------|----------------------------|-----------------------|
| GET    | `/api/portfolio`             | Get user’s portfolio       | `Authorization: Bearer <token>` |
| POST   | `/api/portfolio/buy`         | Buy stocks                 | `symbol`, `quantity`  |
| POST   | `/api/portfolio/sell`        | Sell stocks                | `symbol`, `quantity`  |
| GET    | `/api/portfolio/transactions`| Get transaction history    | `Authorization: Bearer <token>` |

### Watchlist API
| Method | Endpoint                     | Description                | Parameters            |
|--------|------------------------------|----------------------------|-----------------------|
| GET    | `/api/watchlist`             | Get user’s watchlist       | `Authorization: Bearer <token>` |
| POST   | `/api/watchlist/add`         | Add stock to watchlist     | `symbol`              |
| POST   | `/api/watchlist/remove`      | Remove stock from watchlist| `symbol`              |

*Note:* All endpoints requiring authentication expect an `Authorization: Bearer <token>` header.

## Database Schema
Below are the custom tables used in this project (default Laravel tables like `migrations`, `notifications`, `cache`, `jobs`, `personal_access_tokens` are excluded):

- **Users:**
  - `id` (INT, PRIMARY KEY)
  - `name` (VARCHAR)
  - `email` (VARCHAR)
  - `password` (VARCHAR)
  - `created_at` (TIMESTAMP)
  - `updated_at` (TIMESTAMP)
  - `is_active` (BOOLEAN)
  - `role` (VARCHAR)

- **User Settings:**
  - `id` (INT, PRIMARY KEY)
  - `user_id` (INT, FOREIGN KEY to Users(id))
  - `notification_enabled` (BOOLEAN)
  - `mode` (ENUM)

- **Portfolios:**
  - `id` (INT, PRIMARY KEY)
  - `user_id` (INT, FOREIGN KEY to Users(id))
  - `created_at` (TIMESTAMP)

- **Holdings:**
  - `id` (INT, PRIMARY KEY)
  - `portfolio_id` (INT, FOREIGN KEY to Portfolios(id))
  - `stock_id` (INT, FOREIGN KEY to Stocks(id))
  - `quantity` (INT)
  - `average_price` (DECIMAL)

- **Watchlists:**
  - `id` (INT, PRIMARY KEY)
  - `user_id` (INT, FOREIGN KEY to Users(id))
  - `stock_id` (INT, FOREIGN KEY to Stocks(id))

- **Transactions:**
  - `id` (INT, PRIMARY KEY)
  - `user_id` (INT, FOREIGN KEY to Users(id))
  - `stock_id` (INT, FOREIGN KEY to Stocks(id))
  - `quantity` (INT)
  - `price` (DECIMAL)
  - `type` (ENUM)
  - `date` (TIMESTAMP)
  - `transaction_fee` (DECIMAL)

- **Stocks:**
  - `id` (INT, PRIMARY KEY)
  - `sector_id` (INT, FOREIGN KEY to Sectors(id))
  - `symbol` (VARCHAR)
  - `company_name` (VARCHAR)

- **Sectors:**
  - `id` (INT, PRIMARY KEY)
  - `name` (VARCHAR)

- **Stock Prices:**
  - `id` (INT, PRIMARY KEY)
  - `stock_id` (INT, FOREIGN KEY to Stocks(id))
  - `date` (TIMESTAMP)
  - `open_price` (DECIMAL)
  - `close_price` (DECIMAL)
  - `high_price` (DECIMAL)
  - `low_price` (DECIMAL)
  - `volume` (INT)

- **IPO Details:**
  - `id` (INT, PRIMARY KEY)
  - `stock_id` (INT, FOREIGN KEY to Stocks(id))
  - `issue_price` (DECIMAL)
  - `total_shares` (INT)
  - `open_date` (TIMESTAMP)
  - `close_date` (TIMESTAMP)
  - `ipo_status` (VARCHAR)

- **IPO Applications:**
  - `id` (INT, PRIMARY KEY)
  - `user_id` (INT, FOREIGN KEY to Users(id))
  - `ipo_id` (INT, FOREIGN KEY to IPO_Details(id))
  - `applied_shares` (INT)
  - `status` (VARCHAR)
  - `applied_date` (TIMESTAMP)
  - `alloted_shares` (INT)

## Usage
- Access the API at `http://localhost:8000/api/`.
- Use tools like Postman or cURL to test endpoints. Example:
  ```bash
  curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
  ```
- Real-time stock updates are pushed via WebSockets—connect using a frontend client.

## Testing
- Run PHPUnit tests:
  ```bash
  php artisan test
  ```
- Unit tests cover backend logic (e.g., authentication, stock data processing).
- Integration tests ensure API endpoints work as expected.

## Team Structure
- **Backend Developers (4):** Built server-side logic, APIs, and database interactions using Laravel.
- **Frontend Developers (2):** Designed and implemented the UI, consuming backend APIs.
- **QA (1):** Tested the application, created test cases, and ensured quality.

## Contributing
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature-name`).
3. Commit changes (`git commit -m "Add feature"`).
4. Push to the branch (`git push origin feature-name`).
5. Open a pull request.

We used a feature-branch workflow with pull requests for code reviews during development.

## License
This project is licensed under the [MIT License](LICENSE).

---


