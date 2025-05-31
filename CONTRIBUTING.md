# Contributing to Laravel Defender

Thank you for your interest in contributing! This project follows best practices for code, documentation, and security. Please read these guidelines before submitting a pull request.

## Project Structure

- `src/` — Main package code
- `config/` — Configuration files (if needed)
- `database/migrations/` — Database migrations
- `resources/views/` — Blade views for the dashboard
- `tests/` — Tests (Pest or PHPUnit)
- `.github/` — Workflows and issue/PR templates

## Getting Started

1. Fork and clone the repository.
2. Install dependencies with `composer install`.
3. Create a descriptive branch (`feature/feature-name` or `fix/bug-description`).
4. Add tests for every new feature or bugfix.
5. Open a PR to the `main` branch with a clear description.

## Code Style

- Follow PSR-12.
- Use strict typing and docblocks.
- Keep code consistent with the rest of the project.

## Commits & Versioning

- Use clear, English commit messages.
- Release-please manages versions automatically based on commit messages (`feat:`, `fix:`, etc.).
- Versions before 1.0.0 will be `0.x.y` (e.g., `0.1.0`).

## Security

- Do not disclose vulnerabilities publicly. Please email [security@metalinked.dev](mailto:security@metalinked.dev).
- Do not add dependencies without prior discussion.

## Contact

Open an issue or join the discussion if you have questions!