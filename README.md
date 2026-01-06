# Git Config Manager

A powerful NativePHP desktop application designed to simplify managing multiple Git identities, SSH hosts, and repository operations. Built with Laravel, Livewire, and NativePHP.

## Author

- **Name**: Emmanuel saleem
- **Email**: [emmanuelsaleem098765@gmail.com](mailto:emmanuelsaleem098765@gmail.com)
- **LinkedIn**: [Emmanuel Saleem](https://www.linkedin.com/in/es77/?originalSubdomain=pk)
- **GitHub**: [es-77](https://github.com/es-77)

## Features & Problem Solving

### 1. Git Identity Manager
**The Problem:** managing multiple git accounts (e.g., Personal vs Work) on the same machine often leads to commits being made with the wrong email or name, messing up contribution graphs and commit history.
**The Solution:**
- **Profile Switching:** Define multiple profiles (Name + Email) and switch between them instantly.
- **Local Config Enforcement:** Apply specific identities to specific repositories without affecting your global git config.

### 2. SSH Host Manager
**The Problem:** Configuring `~/.ssh/config` for multiple hosts (e.g., `github-work`, `gitlab-personal`) is tedious and error-prone. Generating keys and mapping them manually is complex.
**The Solution:**
- **Visual Interface:** Add and manage SSH hosts through a clean UI.
- **Key Generation:** Generate secure ED25519 SSH keys directly within the app.
- **Auto-Config:** Automatically updates your SSH config file with the correct Host, HostName, User, and IdentityFile.

### 3. Project Setup Helper
**The Problem:** Cloning a project and setting it up with the correct identity and origin often involves multiple terminal commands (`git clone`, `cd`, `git config user.name`, `git remote set-url`).
**The Solution:**
- **Clone Assistant:** Paste a repo URL, select your target profile, and the app clones it *and* automatically sets the correct local user config immediately.
- **Remote Helper:** Easily configure remote origins for existing folders without memorizing commands.

### 4. Git Operations Hub
**The Problem:** For basic daily tasks, using the terminal for every command (`pull`, `push`, `status`, `switch branch`) can be repetitive, while full GUIs can be bloated.
**The Solution:**
- **Essential Actions:** dedicated buttons for Pull, Push, Fetch, Status, and more.
- **Branch Management:** Create, Rename, and Switch branches with a single click.
- **Safety Nets:** "Destructive" actions like Force Push or Hard Reset come with clear warnings to prevent accidents.
- **Visual Feedback:** Full-page loaders and console logs show you exactly what is happening in real-time.

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

## License

The MIT License (MIT).