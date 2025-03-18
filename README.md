# Movie Recommendation System

A web application that provides personalized movie recommendations based on user preferences and favorite genres. Built with Symfony 7, this application integrates with TMDB API for movie data and uses Redis for caching.

## Features

- **User Authentication System**: Secure login and registration system to manage user accounts.
- **Profile Management**: Users can update their profiles, including uploading profile pictures stored in MinIO (S3-compatible storage).
- **Personalized Movie Recommendations**: Recommendations are generated based on users' favorite genres and preferences.
- **TMDB API Integration**: Fetches up-to-date movie data, including genres, ratings, and posters.
- **Redis Caching**: Improves application performance by caching frequently accessed data.

## Prerequisites

Before you begin, ensure you have the following installed and configured:

- [DDEV](https://ddev.readthedocs.io/en/stable/): A local development environment tool for PHP.
- [Docker](https://www.docker.com/get-started): Required for running DDEV and other containerized services.
- [Composer](https://getcomposer.org/): Dependency manager for PHP.
- [The Movie Database (TMDB) API key](https://www.themoviedb.org/documentation/api): Required to fetch movie data.
- PHP 8.2 or higher: Ensure your system meets the PHP version requirement.
- [MinIO](https://min.io/): S3-compatible storage for profile pictures (optional if not using MinIO).

## Installation

1. Clone the repository
```bash
  git clone git@github.com:jamewell/movie-recommendation.git
  cd movie-recommendation
```

2. Install dependencies
```bash
  ddev start
  ddev composer install
```

3. Create a `.env.local` file and configure the following environment variables
```bash
  cp .env .env.local
```

4. Run the database migrations
```bash
  ddev exec bin/console doctrine:migrations:migrate
```

5. Run commands to load movie genres
```bash
  ddev exec bin/console app:fetch-genres
```


### Testing and Code Quality
- This section is clear, but you could add a brief explanation of what each tool does for users who may not be familiar with them.

1. **Run PHPUnit tests**: Execute the test suite to ensure the application is functioning as expected.
```bash
  ddev exec bin/phpunit
```

2. **Run PHPStan**: Analyze the codebase for potential errors and bugs.
```bash
  ddev exec bin/phpstan analyse
```

3. **Run PHP CS Fixer**: Automatically fix coding standards issues in the codebase.
```bash
  ddev exec bin/php-cs-fixer fix
```
