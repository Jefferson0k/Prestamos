# Prestamos – Laravel 12 + PrimeVue + Docker + PostgreSQL

**Prestamos** is a full-stack application developed by [Coveñas Roman Jeferson Grabiel](https://github.com/Jefferson0k), built using **Laravel 12**, **Docker**, **PostgreSQL**, and **[PrimeVue](https://primevue.org/)** for the user interface.

> ⚠️ This project is licensed for **educational and personal use only**. Commercial use is **not permitted**. See the [LICENSE](./LICENSE) file for more information.

---

## 🧰 Prerequisites

- Docker and Docker Compose
- PHP >= 8.3 (if running outside Docker)
- Composer
- Node.js and npm

---

## 🚀 Installation (Docker)

1. Clone the repository:

    ```bash
    git clone https://github.com/Jefferson0k/Prestamos.git
    cd Prestamos
    ```

2. Copy the environment configuration:

    ```bash
    cp .env.example .env
    ```

3. Start the Docker containers:

    ```bash
    docker-compose up -d
    ```

4. Install PHP dependencies:

    ```bash
    docker-compose exec app composer install
    ```

5. Install frontend dependencies:

    ```bash
    npm install
    ```

6. Generate application key:

    ```bash
    docker-compose exec app php artisan key:generate
    ```

7. Configure the database in `.env`:

    ```
    DB_CONNECTION=pgsql
    DB_HOST=postgres
    DB_PORT=5432
    DB_DATABASE=your_database
    DB_USERNAME=your_user
    DB_PASSWORD=your_password
    ```

8. Run database migrations:

    ```bash
    docker-compose exec app php artisan migrate
    ```

9. Build frontend assets:

    ```bash
    npm run dev
    ```

10. Start the development server:

    ```bash
    php artisan serve
    ```

---

## 🎨 UI Framework

This project uses [PrimeVue](https://primevue.org/) to build responsive and elegant user interfaces with Vue 3 components.

---

## 👨‍💻 Author

Developed entirely by:

**Coveñas Roman Jeferson Grabiel**  
🔗 [GitHub - Jefferson0k](https://github.com/Jefferson0k)

---

## 📄 License

This project is licensed under the  
**[Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)](https://creativecommons.org/licenses/by-nc/4.0/legalcode)**

You may study, copy, and modify this code for **non-commercial** purposes only.  
**Commercial use is prohibited** without express permission from the author.

See the [LICENSE](./LICENSE) file for full legal terms.
