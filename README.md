# Laravel 12 Project

This is a Laravel 12 project developed by [Coveñas Roman Jeferson Grabiel](https://github.com/Jefferson0k), using Docker and PostgreSQL.

> ⚠️ This project is intended for educational and personal use only. Commercial use is prohibited under the current license. See the [LICENSE](./LICENSE) file for details.

---

## 🧰 Prerequisites

- Docker and Docker Compose
- PHP >= 8.3 (if running outside Docker)
- Composer
- Node.js and npm

---

## 🚀 Installation (Docker-based)

1. Clone the repository:

    ```bash
    git clone <repository-url>
    cd prueba_1
    ```

2. Copy the environment file:

    ```bash
    cp .env.example .env
    ```

3. Build and start the containers:

    ```bash
    docker-compose up -d
    ```

4. Install PHP dependencies (inside the container):

    ```bash
    docker-compose exec app composer install
    ```

5. Install JavaScript dependencies:

    ```bash
    npm install
    ```

6. Generate the application key:

    ```bash
    docker-compose exec app php artisan key:generate
    ```

7. Configure your database in the `.env` file (example):

    ```
    DB_CONNECTION=pgsql
    DB_HOST=postgres
    DB_PORT=5432
    DB_DATABASE=your_database
    DB_USERNAME=your_user
    DB_PASSWORD=your_password
    ```

8. Run migrations:

    ```bash
    docker-compose exec app php artisan migrate
    ```

9. Build frontend assets:

    ```bash
    npm run dev
    ```

10. Access the app:

    ```bash
    php artisan serve
    ```

---

## 👨‍💻 Author

- [Coveñas Roman Jeferson Grabiel](https://github.com/Jefferson0k)

---

## 📄 License

This project is licensed under the  
**[Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)](https://creativecommons.org/licenses/by-nc/4.0/legalcode)**

You are free to study and modify the code for non-commercial purposes.  
**Commercial use is strictly prohibited without prior permission.**

See the [LICENSE](./LICENSE) file for full terms.
