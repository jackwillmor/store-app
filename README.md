# Store App

A simple store application built using Laravel. It provides basic store functionalities and API endpoints for managing shops and postcodes.


## Requirements
Ensure you have the following installed:
- PHP >= 8.1
- Composer
- MySQL

## Installation

Follow these steps to set up the project:


1. Clone the repository:
```bash
git clone https://github.com/jackwillmor/store-app
```

2. Navigate to the project directory:
```bash
cd store-app
```

3. Install the required PHP dependencies:
```bash
composer install
```

4. Copy the .env.example file to create a new .env file:
```bash
cp .env.example .env
```

5. Generate the application key:
```bash
php artisan key:generate
```

6. Update .env to include database details:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=store-app
DB_USERNAME=USER_NAME
DB_PASSWORD=PASS_WORD
```

7. Run the database migrations:
```bash
php artisan migrate
```

## Running the Application

### Import postcode data:
To import postcode data into the application, use the following command:
```bash
php artisan app:import-postcode-data --batchSize=1000 --maxRecords=2345
```
- `--batchSize` specifies the number of records to insert in each batch.
- `--maxRecords` limits the total number of records to import.
*It is recommended to use `--batchSize=1000` and `--maxRecords=2345` for purposes of this demo*

### Serve the Application
Start the Laravel development server:
```
php artisan serve
```
Your application will be available at http://127.0.0.1:8000.

## Unit Tests
To run the unit tests for the application, use the following command:
```bash
php artisan test
```

## API Documentation
The API Postman Collection is available for testing the API endpoints:

[Postman API Collection](https://www.postman.com/jackwillmor/shop-api/example/42332931-cc89344c-1c4e-4be5-99c3-46aaf51964cc)

You can import this collection into your Postman app to easily test all available API endpoints.

## Considerations
- **Authentication:** Consider implementing authentication using Tokens to enhance security for API requests, ensuring that only authorized users can access certain endpoints.
- **CSV Optimization:** The current implementation of CSV data processing can be improved. For large datasets, it is recommended to use MySQL's LOAD DATA INFILE for faster and more efficient imports, assuming the CSV file is trusted and not user-uploaded.
- **Full-Text Search:** As users may input partial postcodes, we could use full-text indexing to allow partial matches or fuzzy searching.
- **Caching:** Implement caching so subsequent requests for the same postcode don’t hit the database.
- **Security Improvements:** There are quite a few security improvements to be made such as implementing CSP headers.
- **Database Optimisations:** To improve the performance and efficiency of the database, there are a few considerations to make such as adding proper indexes, implement database query caching, optimise MySQL configuration such as using Spatial Indexes.
- **Code Structure:** Implement proper error handling and logging, setup error tracking and performance monitoring.
- **Pagination:** Due to the size of the shops data, we should implement pagination in the results so that we don't try and load too much in a single request.
- **Testing:** Expand testing by including API testing to make sure endpoints are returning the correct JSON valid responses.
