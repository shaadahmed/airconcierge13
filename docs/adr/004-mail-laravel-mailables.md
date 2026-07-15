# ADR-004: Mail — Laravel Mail / Mailables (retire PHPMailer)

**Status:** Accepted (implementation in Phase 3)  
**Date:** 2026-07-12

## Decision

Replace direct **PHPMailer** usage with **Laravel Mail**, Mailables, and Notifications. Local/dev mail capture via Mailpit on Sail.

## Alternatives considered

1. Keep PHPMailer wrappers in L13
2. Laravel Mail / Mailables / Notifications (chosen)
3. Third-party mail SDK only

## Rationale

The old app sends mail via PHPMailer outside Laravel’s mail stack, which fights queues, testing, and configuration. Laravel Mail integrates with Redis queues, notifications (including Slack later), and Mailpit for local verification. Deep migration lives in Phase 3 (Email / Chronology).
