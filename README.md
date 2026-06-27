[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/Mdwiki-TD/tdc)

# Mdwiki Translation Dashboard Coordinator (TDC)

The **Translation Dashboard Coordinator (TDC)** is a web-based platform designed to assist coordinators in managing and facilitating translation efforts within the WikiProject Medicine community. It provides tools for monitoring translations, managing users, overseeing projects, and administering campaigns.

## Purpose and Scope

TDC serves as a coordination hub for translation projects, enabling coordinators to:

-   Monitor recent and in-process translations
-   Manage translated pages and translation types
-   Track WikiData QIDs for translated content
-   Coordinate users and translation projects
-   Administer translation campaigns
-   Provide statistics and process monitoring

## System Architecture

The TDC system follows a layered architecture design pattern, separating concerns between presentation, business logic, and data persistence layers.

### Component Organization

-   **Data Access Layer**: Retrieves data either directly from a SQL database or from an external API, determined by the `$use_td_api` global variable.
-   **Language Support System**: Manages language codes, names, and translations through a structured set of tables.

## Main Features

### Translation Management

-   **Translation Dashboard**: View and manage translations.
-   **Recent Translations**: View the most recently translated pages.
-   **In-Process Monitoring**: Track translations currently in progress.
-   **Pages Users to Main**: Move translated pages from user namespaces to the main namespace.

### User and Project Management

-   **User Management**: Manage user email lists and assignments.
-   **Project Management**: Track ongoing translation projects.
-   **Campaign Management**: Organize translation campaigns.

### Content Management

-   **QID Management**: Manage Wikidata QIDs for translated pages.
-   **Translation Type**: Configure translation types.
-   **Settings**: Adjust platform settings and configurations.

## System Tables and Data Structures

The TDC system organizes translation-related data in structured tables, including:

-   Language codes and names
-   Translation types
-   User assignments
-   Project and campaign data

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

# End points

| Endpoint             | Method   | Description                                             |
| -------------------- | -------- | ------------------------------------------------------- |
| `/`                  | GET      | Main entry — recent translations dashboard              |
| `?ty=reports`        | GET      | View publish reports with filters                       |
| `?ty=stat`           | GET      | Per-category article statistics                         |
| `?ty=sidebar`        | GET      | Sidebar HTML (for AJAX reload)                          |
| `?ty=categories`     | GET      | Translation languages with Wikidata category status     |
| `?ty=Campaigns`      | GET/POST | List and edit translation campaigns                     |
| `?ty=Campaigns/post` | POST     | Save/update/delete campaign categories                  |
| `?ty=projects`       | GET/POST | Manage project groups                                   |
| `?ty=projects/post`  | POST     | Save project changes                                    |
| `?ty=settings`       | GET/POST | Manage application settings                             |
| `?ty=settings/post`  | POST     | Save settings changes                                   |
| `sugust.php`         | GET      | JSON endpoint for article suggestions (`?title=&lang=`) |

## Recent translations

| Endpoint                         | Method   | Description                              |
| -------------------------------- | -------- | ---------------------------------------- |
| `?ty=last`                       | GET      | Recent translations table                |
| `?ty=last1`                      | GET      | Recent translations (SQL-based)          |
| `?ty=process`                    | GET      | Translations currently in progress       |
| `?ty=process_total`              | GET      | Per-user translation count summary       |
| `?ty=last_coord`                 | GET      | Coordinator view of recent translations  |
| `?ty=recent_helps`               | GET      | Helper utilities for recent translations |
| `?ty=pages_users_to_main`        | GET      | Pages needing move to main namespace     |
| `?ty=pages_users_to_main/fix_it` | GET/POST | Edit page transfer details               |

## Pages

| Endpoint                     | Method   | Description                            |
| ---------------------------- | -------- | -------------------------------------- |
| `?ty=translated`             | GET      | Paginated list of all translated pages |
| `?ty=translated/edit_page`   | GET/POST | Edit or delete a translated page       |
| `?ty=tt`                     | GET      | List articles by translate type        |
| `?ty=tt/edit_translate_type` | GET/POST | Edit or add translate type             |
| `?ty=tt/post`                | POST     | Save translate type changes            |
| `?ty=add`                    | GET/POST | Add new translation entries            |
| `?ty=add/post`               | POST     | Save new translation rows              |

## Qids

| Endpoint            | Method   | Description             |
| ------------------- | -------- | ----------------------- |
| `?ty=qids`          | GET      | List Wikidata QIDs      |
| `?ty=qids/edit_qid` | GET/POST | Edit or add a QID entry |
| `?ty=qids/post`     | POST     | Save QID changes        |

## Users

| Endpoint                      | Method   | Description                                |
| ----------------------------- | -------- | ------------------------------------------ |
| `?ty=Emails`                  | GET/POST | List users with emails and project filters |
| `?ty=Emails/post`             | POST     | Save user email/wiki/project edits         |
| `?ty=Emails/msg`              | GET/POST | Compose and send email to translator       |
| `?ty=Emails/edit_user`        | GET      | Edit or add a single user                  |
| `?ty=users_no_inprocess`      | GET/POST | Manage users excluded from "in process"    |
| `?ty=users_no_inprocess/post` | POST     | Save exclusion list changes                |

## Roles Management

| Endpoint                    | Method   | Description                     |
| --------------------------- | -------- | ------------------------------- |
| `?ty=admins`                | GET/POST | List, add, delete coordinators  |
| `?ty=admins/post`           | POST     | Save coordinator changes        |
| `?ty=full_translators`      | GET/POST | Manage full article translators |
| `?ty=full_translators/post` | POST     | Save full translator changes    |

## Language Settings

| Endpoint                    | Method   | Description                        |
| --------------------------- | -------- | ---------------------------------- |
| `?ty=wikirefs_options`      | GET      | Per-language fix wikirefs settings |
| `?ty=wikirefs_options/edit` | GET/POST | Edit language settings             |
