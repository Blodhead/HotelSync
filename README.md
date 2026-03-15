To start a localhost server run:
php -S localhost:8000

Now  that the port is listening for an event we can start using it.

Test(using POSTMAN):
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
