# Ledger Service Project

This project is a Symfony-based application that uses PostgreSQL as its database. Below are the instructions to set up and run the project using Docker.

---

## **Prerequisites**

Ensure you have the following installed on your system:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Git](https://git-scm.com/) (optional, for cloning the repository)

---

## **Getting Started**

### **1️⃣ Clone the Repository**

Clone the repository to your local machine:

```sh
git clone https://github.com/azaykarimli/ledger_service_project.git
cd ledger_service_project
```

---

### **2️⃣ Set Up Environment Variables**

Ensure that the `.env` file in the root of your project contains the correct database configuration. The default configuration should look like this:

```env
DATABASE_URL="postgresql://app:password@database:5432/ledger_service?serverVersion=15&charset=utf8"
```

---

### **3️⃣ Build and Start the Docker Containers**

Run the following command to build and start the Docker containers:

```sh
docker-compose up -d
```

This will start two services:

- **symfony_app**: The Symfony application running on PHP 8.3.
- **database**: A PostgreSQL database.

---

### **4️⃣ Verify the Database Connection**

Once the containers are up, verify that the database is running and accessible:

```sh
docker exec -it ledger_service_project-database-1 psql -U app -d ledger_service
```

You should see the PostgreSQL prompt if the connection is successful.

---

### **5️⃣ Run Database Migrations**

Run the following command to apply the database migrations:

```sh
docker exec -it symfony_app php bin/console doctrine:migrations:migrate
```

This will set up the necessary database tables for the application.

---

### **6️⃣ Access the Application**

The Symfony application should now be running and accessible at:

```
http://localhost:4444
```

---

### **7️⃣ Stopping the Containers**

To stop the containers, run:

```sh
docker-compose down
```

If you want to remove the volumes (including the database data), use:

```sh
docker-compose down -v
```

---

### **8️⃣ Viewing Logs**

To view the logs for the Symfony application:

```sh
docker logs symfony_app
```

To view the logs for the PostgreSQL database:

```sh
docker logs ledger_service_project-database-1
```

---

### **9️⃣ Rebuilding the Containers**

If you make changes to the `Dockerfile` or `docker-compose.yml`, you may need to rebuild the containers:

```sh
docker-compose up -d --build
```

---

## **Additional Notes**

- The PostgreSQL database data is persisted in a Docker volume named `database_data`. If you remove this volume, all database data will be lost.
- The Symfony application is configured to run in `prod` mode by default. If you need to switch to `dev` mode, update the `APP_ENV` variable in the `.env` file.

---

### **Api Endpoints**

```
The following API endpoints are available:

POST /api/ledgers
Create a new ledger.

Request Body:

json
Copy
{
  "name": "Test Ledger",
  "currency": "USD"
}
Response:

json
Copy
{
  "id": 1,
  "name": "Test Ledger",
  "currency": "USD"
}
POST /api/transactions
Record a new transaction.

Request Body:

json
Copy
{
  "ledger": "/api/ledgers/1",
  "balance": "/api/balances/1",
  "type": "credit",
  "amount": 100.00,
  "transaction_id": "txn_123"
}
Response:

json
Copy
{
  "id": 1,
  "ledger": "/api/ledgers/1",
  "balance": "/api/balances/1",
  "type": "credit",
  "amount": 100.00,
  "transaction_id": "txn_123"
}
GET /api/balances/{ledgerId}
Retrieve the current balance of a ledger.

Response:

json
Copy
{
  "id": 1,
  "ledger": "/api/ledgers/1",
  "currency": "USD",
  "balance": 100.00
}
```

---


### **Api documentation**

```
http://localhost:4444/api
```

---
### **Test cases**

```

Test Cases
The project includes the following test cases:

1. Stress Test
Command: app:load-test-transactions

Description: Tests the system's ability to handle a large number of transactions.

Test Class: StressTest

2. Balance Service Test
Description: Tests the BalanceService class for updating balances with credit and debit transactions.

Test Class: BalanceServiceTest

3. Transaction Controller Test
Description: Tests the API endpoints for creating transactions, handling invalid payloads, and retrieving balances.

Test Class: TransactionControllerTest


Run the tests inside the containers


 vendor/bin/phpunit tests/Stress/StressTest.php 
 vendor/bin/phpunit tests/Controller/TransactionControllerTest.php 
 vendor/bin/phpunit tests/Service/BalanceServiceTest.php

```

---

### **License**

This project is licensed under the MIT License - see the LICENSE file for details.

---

### **Author**

[Azay Karimli](https://github.com/azaykarimli)

