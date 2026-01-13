<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Project Overview

This project was developed as a technical task to demonstrate backend and full-stack capabilities using **Laravel 12**, with a primary focus on **database design**, **API development**, and **data integrity**.

As the task did **not require a frontend**, the implementation is **API-only**, prioritizing correctness, validation, and transactional safety over UI concerns.

For simplicity and portability, **SQLite** was chosen as the database engine. This allows the project to run out-of-the-box without additional setup while still fully supporting migrations, transactions, and relational constraints.


## Setup & Installation

### Requirements

- PHP 8.2+
- Composer
- SQLite (included with PHP by default)
- Git

---

### Installation

Clone the repository and install dependencies

```bash
git clone https://github.com/your-username/latvenergo-warehouse-api.git
cd latvenergo-warehouse-api
composer install
```

## Migrations & Seeding

The database schema is managed entirely through Laravel migrations to ensure a consistent and reproducible setup.

To create all required tables (`products`, `orders`, `order_items`), run

```bash
php artisan migrate
```

To populate the database with initial product data, run the database seeders
```bash
php artisan db:seed
```

The ProductSeeder inserts a predefined set of products, each with a name, description, price, and available quantity.
This allows the API to be tested immediately without manual data entry.

## API Endpoints

This project exposes two RESTful API endpoints that cover the core requirements of the task.

### Get Products

Returns a list of all available products along with their current stock levels.

**GET /api/products**
```bash
Response example

[
  {
    "id": 1,
    "name": "Solar Panel 400W",
    "description": "High-efficiency photovoltaic solar panel",
    "price": 249.99,
    "quantity": 50
  }
]
```
### Create Orders
Creates a new order consisting of one or more products.

**POST /api/orders**
```bash
Request body

{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 1 }
  ]
}
```
**Successful response (201)s**
```bash
{
  "order_id": 1,
  "items": [
    { "product_id": 1, "quantity": 2, "price": 249.99 },
    { "product_id": 2, "quantity": 1, "price": 899.00 }
  ],
  "total": 1398.98
}
```
**422** - Validation error

**409** - Insufficient product stock

**500** - Unexpected server error

## Testing

The project includes **feature tests** to validate the API behavior end-to-end, focusing on correctness, edge cases, and data integrity.

SQLite is used in **in-memory mode** during testing to ensure fast, isolated, and repeatable test runs.

Run the full test suite with

```bash
php artisan test
```