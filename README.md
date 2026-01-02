# Git Config Manager

A powerful NativePHP desktop application designed to simplify managing multiple Git identities, SSH hosts, and repository operations. Built with Laravel, Livewire, and NativePHP.

## Author

- **Name**: Emmanuel saleem
- **Email**: [emmanuelsaleem098765@gmail.com](mailto:emmanuelsaleem098765@gmail.com)
- **LinkedIn**: [Emmanuel Saleem](https://www.linkedin.com/in/es77/?originalSubdomain=pk)
- **GitHub**: [es-77](https://github.com/es-77)

## Features

### 👤 Profile Management
Easily switch between different Git identities (work, personal, etc.).
- **Storage**: Profiles are managed securely via local application storage (`profiles.json`).
- **Capabilities**: Add, update, and remove Git profiles (Name & Email).

### 🔑 SSH Host Manager
Manage your SSH configurations for different Git providers (GitHub, GitLab, Bitbucket) directly from the app.

### ⚡ Git Operations
Perform common Git commands and operations through a user-friendly interface.

### 🔍 Spotlight Search
Quickly access features and switch profiles using the built-in Spotlight window.
- **Shortcut**: `Cmd + L` (macOS) or `Ctrl + L` (Windows/Linux) to toggle.

## Tech Stack

- **Framework**: [Laravel 12](https://laravel.com)
- **Desktop Runtime**: [NativePHP](https://nativephp.com)
- **Frontend**: [Livewire 3](https://livewire.laravel.com)
- **Database**: SQLite (Default)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/es-77/git-config-manager.git
   cd git-config-manager
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   ```

4. **Run the Application**
   Start the NativePHP development server:
   ```bash
   php artisan native:serve
   ```

## Usage

- **Main Dashboard**: Manage your Git profiles.
- **SSH Tab**: Configure SSH hosts.
- **Git Ops**: Execute git commands.
- **Global Shortcut**: Press `Cmd/Ctrl + L` anywhere to open the quick action spotlight.

## License

The MIT License (MIT).
