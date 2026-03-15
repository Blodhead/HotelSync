## Instructions for project running:

## Prerequisites
- PHP 8.0 or higher
- Postman
- Any SQL client / SQL management tool

## Used
- XAMPP that comes with PHP 8.0.30
- Postman
- MySQL Workbench

## Database setup
Take schema.sql and copy-paste-execute in SQL management tool of choice
Note: database.php and env.php were in .gitignore but added for this interview, fill them with adequate information

## Task 1 – Authentication i Catalog Sync
run "php sync_catalog.php" in CLI

check app.log

## Task 2 – Reservation Import
run "php sync_reservations.php --from=YYYY-MM-DD --to=YYYY-MM-DD" in CLI
example 1: php sync_reservations.php --from=2026-01-01 --to=2026-01-31

check app.log

## Task 3 – Reservation Update / Cancel
php update_reservation.php --reservation_id=XXXX
example 1: php update_reservation.php --reservation_id=2507793
check app.log

## Task 4 – Invoice Creation
php generate_invoice.php --reservation_id=XXXX
example 1: php generate_invoice.php --reservation_id=2507793
check app.log

## Task 5 – Webhook Endpoint

To start a localhost server run:
php -S localhost:8000

Now  that the port is listening for an event we can start using it.

Test(using POSTMAN):
Request Type: POST
Url: http://localhost:8000/webhooks/otasync.php
Request:
{
    "event_id":"test_223",
    "type":"new",
    "reservation":
    {
        "id_reservations":99999,
        "first_name":"Test",
        "last_name":"User",
        "date_arrival":"2026-03-15",
        "date_departure":"2026-03-20",
        "status":"confirmed",
        "id_pricing_plans":1
    }
}

Response:
{
"status":"already_processed"
}
