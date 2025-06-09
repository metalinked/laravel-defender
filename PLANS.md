# Plans & Features – Laravel Defender

> **Note:**  
> SaaS integration and premium features will be provided by a separate connector package in the future.  
> This package is 100% OSS and does not connect to any external service.

## Overview

This document describes which features are available in each plan and how the SaaS integration works.

---

## Plans

| Feature                                      | Offline Freemium | SaaS Freemium (1 project) | SaaS Premium (multi-project) |
|-----------------------------------------------|:----------------:|:-------------------------:|:----------------------------:|
| IP logging & alert manager                    |        ✔         |            ✔              |             ✔                |
| Honeypot spam protection                      |        ✔         |            ✔              |             ✔                |
| Local notifications (mail, Slack, Telegram)   |        ✔         |            ✔              |             ✔                |
| Security audit (env, debug, CORS, etc.)       |        ✔         |            ✔              |             ✔                |
| Centralized dashboard                         |                  |            ✔              |             ✔                |
| Advanced attack signatures (global rules)     |                  |                            |             ✔                |
| AI-powered risk scoring                       |                  |                            |             ✔                |
| Integration with global IP reputation         |                  |                            |             ✔                |
| API access for enterprise                     |                  |                            |             ✔                |
| Multi-project management                      |                  |                            |             ✔                |

---

## SaaS Activation Flow

- Users register on the SaaS platform.
- Each project gets a unique token.
- Each SaaS token is linked to a specific domain/project. Using the same token on multiple domains is not allowed and will be detected by the SaaS.
- The token is added to the Laravel Defender config (via the connector package).
- The package enables premium features according to the plan.

---

## Notes

- Offline freemium: no registration, all features are local.
- SaaS freemium: registration required, limited to 1 project, basic dashboard.
- SaaS premium: paid plan, unlocks all features and multi-project support.