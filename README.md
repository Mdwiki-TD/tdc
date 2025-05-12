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

## System Architecture

The TDC system follows a layered architecture design pattern, separating concerns between presentation, business logic, and data persistence layers.

### Component Organization

- **Data Access Layer**: Retrieves data either directly from a SQL database or from an external API, determined by the `$use_td_api` global variable.
- **Language Support System**: Manages language codes, names, and translations through a structured set of tables.

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
