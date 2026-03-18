Project Context:
This repository is for "MediaVault". It is a system designed for a physical media retail shop to process unstructured, messy product data (like raw strings of DVDs and CDs). The core functionality relies on processing batches of this data through background queued jobs, which will communicate with an LLM to categorize and structure the data into clean JSON.


1. This is a Laravel 11 project called 'media-vault-laravel'.
2. All PHP code must enforce strict type-hinting and strictly follow PSR-12 coding standards.
3. All background processing must utilize Laravel Queues specifically configured with the database driver.
4. External API calls must exclusively use Laravel's built-in Http facade; raw cURL is strictly prohibited.
5. Architecture (Skinny Controllers): Controllers must only handle receiving the HTTP request and returning a response or dispatching a job. All core business logic and LLM communication must reside in dedicated Service Classes (e.g., MediaEnrichmentService).
6. Dependency Injection: All Service classes must be injected via the constructor of Controllers or Jobs utilizing Laravel's Service Container. Do not manually instantiate classes with the new keyword.
7. Database Integrity & Security: Database tables must exclusively be created via Migrations; never manually. All Eloquent models must explicitly define the $fillable array to prevent mass assignment vulnerabilities.
8. Queue Resilience: The architecture must include a failed_jobs table. Any generated Job class must implement a failed() method that specifically logs the reason for failure (e.g., API timeouts or malformed JSON).
9. AI Output Constraints: When prompting the LLM, the system must enforce a strict JSON schema expectation to prevent hallucinations and ensure consistent parsing for keys like product_name, artist_or_director, media_format, genre, and condition.