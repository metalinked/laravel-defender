# Contributing to Laravel Defender

> **Note:**  
> This package is 100% open source and does not include any SaaS integration code.  
> All SaaS-related features and connectors will be maintained in a separate package.  
> **Please do not add or modify code for SaaS integration in this repository.**

Thank you for your interest in contributing! This project follows best practices for code, documentation, and security. Please read these guidelines before submitting a pull request.

## Project Structure

- `src/` — Main package code
- `config/` — Configuration files (if needed)
- `database/migrations/` — Database migrations
- `resources/views/` — Blade views for local package features (not SaaS dashboard)
- `tests/` — Tests (Pest or PHPUnit)
- `.github/` — Workflows and issue/PR templates

## Getting Started

1. Fork and clone the repository.
2. Install dependencies with `composer install`.
3. Create a descriptive branch (`feature/feature-name` or `fix/bug-description`).
4. Add tests for every new feature or bugfix.
5. Open a PR to the `main` branch with a clear description.

## Code Style

- Follow PSR-12, except for brace placement: **use the "One True Brace Style" (OTBS)**, where the opening brace `{` is placed at the end of the line for control structures, functions, and classes.
- Use strict typing and docblocks.
- Keep code consistent with the rest of the project.

## Commits & Versioning

- Use clear, English commit messages.
- Release-please manages versions automatically based on commit messages (`feat:`, `fix:`, etc.).
- Versions before 1.0.0 will be `0.x.y` (e.g., `0.1.0`).

## Security

- Do not disclose vulnerabilities publicly. Please email [security@metalinked.net](mailto:security@metalinked.net).
- Do not add dependencies without prior discussion.

## Contact

Open an issue or join the discussion if you have questions!