[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/Mdwiki-TD/tdc)

# Mdwiki Translation Dashboard Coordinator (TDC)

The **Translation Dashboard Coordinator (TDC)** is a web-based platform designed to assist coordinators in managing and facilitating translation efforts within the WikiProject Medicine community. It provides tools for monitoring translations, managing users, overseeing projects, and administering campaigns.

## Purpose and Scope

TDC serves as a coordination hub for translation projects, enabling coordinators to:

- Monitor recent and in-process translations
- Manage translated pages and translation types
- Track WikiData QIDs for translated content
- Coordinate users and translation projects
- Administer translation campaigns
- Provide statistics and process monitoring

## System Architecture (Refactored)

The TDC system has been refactored to separate the frontend and backend concerns, following a modern API-driven architecture.

- **Backend**: A PHP-based API that provides data to the frontend.
- **Frontend**: A set of HTML, CSS, and JavaScript files that consume the backend API and render the user interface.

### Project Structure

The project is organized into the following main directories:

- `/backend`: Contains all the server-side PHP code.
  - `/controllers`: Houses the business logic for each feature. This includes an `/admin` subdirectory for admin-specific controllers.
  - `router.php`: The single, data-driven entry point for all API requests. It uses a routes array to map actions to controllers.
  - `bootstrap.php`: Initializes the backend application, including the PSR-4 autoloader.
- `/frontend`: Contains all the client-side code.
  - `index.php`: The main landing page (last edits).
  - `stat.php`: The page for viewing statistics.
  - `process.php`: The page for viewing in-process translations.
  - `/admin/reports.php`: The page for viewing reports (admin-only).
  - `/css`: Contains all the CSS stylesheets.
  - `/js`: Contains all the JavaScript files.
- `/legacy`: The original codebase, preserved for reference during the migration.
- `/tests`: Contains test scripts for the application.

### How to Run the Application

1.  **Serve the project root directory** using a web server (e.g., Apache, Nginx).
2.  **Access the frontend pages** directly in your browser (e.g., `http://localhost/frontend/index.php`).
3.  The frontend pages will automatically make API calls to the backend to fetch the necessary data.

### How to Add a New Feature

1.  **Create a new controller** in the `backend/controllers` directory to handle the business logic for the new feature.
2.  **Add a new route** to `backend/router.php` that points to your new controller method.
3.  **Create a new frontend page** in the `frontend` directory. This page should use JavaScript to fetch data from your new API endpoint and render it.

## Main Features

### Translation Management

- **Translation Dashboard**: View and manage translations.
- **Recent Translations**: View the most recently translated pages.
- **In-Process Monitoring**: Track translations currently in progress.
- **Pages Users to Main**: Move translated pages from user namespaces to the main namespace.

### User and Project Management

- **User Management**: Manage user email lists and assignments.
- **Project Management**: Track ongoing translation projects.
- **Campaign Management**: Organize translation campaigns.

### Content Management

- **QID Management**: Manage Wikidata QIDs for translated pages.
- **Translation Type**: Configure translation types.
- **Settings**: Adjust platform settings and configurations.

## System Tables and Data Structures

The TDC system organizes translation-related data in structured tables, including:

- Language codes and names
- Translation types
- User assignments
- Project and campaign data

## Data Flow

The typical data flow through the TDC system follows this pattern:

1. User (coordinator) accesses the web interface.
2. Request is routed through `index.php` to the appropriate module.
3. Module authenticates user and performs security checks.
4. Module requests data through the data access layer.
5. Data is retrieved from the SQL database or external API.
6. Module processes data and renders the response.

## Security

The TDC system includes security features such as CSRF protection and user authentication to ensure safe and authorized access to the platform.

## Development and Deployment

The project is open-source and available on GitHub: [Mdwiki-TD/tdc](https://github.com/Mdwiki-TD/tdc). Developers can contribute to the project, report issues, and suggest enhancements through the GitHub repository.
