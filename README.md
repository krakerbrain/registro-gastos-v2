# Expense Tracker

This is a simple web application for tracking personal expenses. It helps users record their spending, categorize expenses, and view summaries of their financial activity.

## Features

*   **User Authentication:** Users can register for a new account and log in to access their expense data.
*   **Expense Recording:** Easily add new expenses with details such as amount, description, category, and date.
*   **Expense Categorization:** Assign categories to expenses for better organization and analysis.
*   **Frequent Expenses:** Manage a list of frequently incurred expenses for quicker entry.
*   **Expense Summaries:** View summaries of expenses, potentially filtered by different criteria (e.g., by category, by item).

## Technologies Used

*   **Backend:** PHP
*   **Database:** MySQL
*   **Frontend:** HTML, CSS, JavaScript
*   **Framework/Libraries:**
    *   Bootstrap (for styling)
*   **Dependency Management:** Composer
*   **Environment Variables:** phpdotenv

## Setup and Installation

1.  **Clone the repository:**
    ```bash
    git clone https://your-repository-url.git
    cd your-repository-directory
    ```
2.  **Install PHP dependencies:**
    Ensure you have Composer installed. Then run:
    ```bash
    composer install
    ```
3.  **Configure environment variables:**
    *   Create a `.env` file in the root of the project.
    *   Update the `.env` file with your database credentials and other environment-specific settings:
        ```env
        HOST=your_database_host
        BD=your_database_name
        USUARIO=your_database_user
        PASS=your_database_password
        ```
4.  **Set up the database:**
    *   Ensure you have a MySQL server running.
    *   Create the database specified in your `.env` file.
    *   You will need to set up the database schema manually. The required tables and columns can be inferred from the application's PHP code (e.g., by examining the database queries in files like `config.php`, `detalles/conexiones.php`, `form/conexiones.php`, `gastos_frecuentes/conexiones.php`, `items_frecuentes/conexiones.php`, and `tabla_gastos/tabla/conexiones.php`).
5.  **Run the application:**
    *   Configure your web server (e.g., Apache, Nginx) to point to the project's public directory (if applicable, or the root directory if the project is structured that way).
    *   Open the application in your web browser.

## Future Improvements / Contributions

*   **Database Schema:** Include a `database.sql` schema file for easier setup.
*   **.env.example:** Provide an `.env.example` file with placeholder values.
*   **Detailed API Documentation:** For any API endpoints that might be developed.
*   **Unit and Integration Tests:** Implement a testing suite to ensure code quality and stability.
*   **More Advanced Reporting:** Add features for generating charts or more detailed financial reports.
*   **Data Export/Import:** Allow users to export their expense data or import data from other sources.

Contributions are welcome! Please feel free to fork the repository, make improvements, and submit a pull request.
