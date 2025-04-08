# ChatApp - Modern Real-Time Chat Application

A sleek, real-time chat application Built with Laravel and Vue.js.

## 🌟 Key Features

- Real-time messaging & notifications powered by WebSocket technology
- Clean and intuitive user interface
- Secure user authentication & authorization
- Message history and conversation management
- User presence indicators

## 🛠️ Built With

- **Backend**: Laravel
- **Frontend**: Vue.js
- **Real-time**: Pusher
- **Database**: MySQL
- **Authentication**: Laravel Sanctum

## �� Development Status

This project is currently under active development. i'm working hard to add new features and improve existing ones!

## 🚀 Getting Started

Follow these steps to set up and run the ChatApp locally:

### Prerequisites

Before you begin, ensure you have the following installed on your machine:

- **PHP** (version 8.0 or higher)
- **Composer** (dependency manager for PHP)
- **Node.js** (version 14 or higher)
- **npm** (Node package manager, comes with Node.js)
- **MySQL** (or another compatible database)

### Installation Steps

1. **Clone the repository**:
   Open your terminal and run:
   ```bash
   git clone https://github.com/Yahya-Z/ChatApp
   ```

2. **Navigate to the project directory**:
   ```bash
   cd ChatApp
   ```

3. **Install PHP dependencies**:
   Run the following command to install the required PHP packages:
   ```bash
   composer install
   ```

4. **Install JavaScript dependencies**:
   Use npm to install the necessary JavaScript packages:
   ```bash
   npm install
   ```

5. **Set up your environment variables**:
   - Copy the `.env.example` file to create your own `.env` file:
     ```bash
     cp .env.example .env
     ```
   - Open the `.env` file in a text editor and configure the following settings:
     - **APP_URL**: Set this to your local development URL (e.g., `http://localhost:8000`).
     - **DB_DATABASE**: Set this to the name of your database (e.g., `chatapp`).
     - **DB_USERNAME**: Your database username (default is usually `root`).
     - **DB_PASSWORD**: Your database password (leave it blank if you have no password).

6. **Generate the application key**:
   Run the following command to generate a unique application key:
   ```bash
   php artisan key:generate
   ```

7. **Run database migrations**:
   This command will create the necessary tables in your database:
   ```bash
   php artisan migrate
   ```

8. **Start the development server**:
   Launch the Laravel development server with:
   ```bash
   php artisan serve
   ```
   And in a new terminal window run:
   ```bash
   php run dev
   ```

   By default, this will run the server at `http://localhost:8000`.

9. **Run the frontend build**:
   In a new terminal window, navigate to the project directory again and run:
   ```bash
   npm run dev
   ```
   This will compile your Vue.js assets and enable hot reloading.

10. **Run this command**:
   In a new terminal window, run:
   ```bash
   php artisan queu:work
   ```
   Now your ready to jumb in!

### Accessing the Application

Once the server is running, open your web browser and go to `http://localhost:8000` to access the ChatApp.

This detailed section provides clear instructions on what to install, how to set up the environment, and how to run the application. Let me know if you need any further adjustments!

---

## 🤝 Contributing

Contributions are welcome! Feel free to open issues or submit pull requests.

## 📝 License

MIT License - because sharing is caring.

---

*Note: This application is still in development. Some features might be in their "awkward teenage phase" as i work on improving them.*
