## Instruction
First, clone the repository git@github.com:alamincse/food-delivery-app.git into your local server. Then, open the terminal, navigate to the project folder, and follow the commands below. Make sure to create a new MySQL database for the database connection.

- **Ensure your database connection is correctly configured** (in the project's .env file)
- **composer install**
- **php artisan migrate**
- **php artisan db:seed**
- **php artisan serve**
- **php artisan queue:work (For notification)**

**Node:** You must import the API collection into Postman, this will allow you to view the backend features through Postman.

## PHPUnit

- **php artisan test** 


## Key Features

- **Polygon and radius-based delivery zones create**
- **Order validation**
- **Real-time nearest delivery person assignment**
- **Notification system for delivery assignment**
- **Delivery man can confirm his own assigned order**
- **Laravel Sanctum authentication**
- **Fully tested with PHPUnit**

## Technology Used:
- **Laravel 12**
- **Sanctum**

## Author

Al-Amin Sarker
