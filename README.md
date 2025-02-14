# Kupujelive - Docker Setup

This project uses **Docker Compose** to set up a **Symfony backend**, **MySQL database**, and **RabbitMQ message broker**.

## 🚀 Getting Started

### Prerequisites

Ensure you have the following installed:

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

---

### Clone the Repository

```bash
git https://github.com/arturbanachowski/symfony-7-z-rabbitmq-1739483493.git
cd symfony-7-z-rabbitmq-1739483493
```

---

### Start the Services

To **build and start** the containers, run:

```bash
docker-compose up -d --build
```

This will start:

- **MySQL** on port `2306`
- **Symfony Backend** on port `2006`
- **RabbitMQ** on ports `2672` (AMQP) and `25672` (management UI)

To **stop** the containers:

```bash
docker-compose down
```

---

### Environment Configuration

`.env` file in the Symfony project with:

```env
DATABASE_URL="mysql://mysql:mysql@172.30.0.4:3306/kupujelive"
MESSENGER_TRANSPORT_DSN="amqp://admin:pass@172.30.0.9:5672/%2f/messages"
```

---

### Access the Services

- Symfony backend: [http://localhost:2006](http://localhost:2006)
- RabbitMQ UI: [http://localhost:25672](http://localhost:25672) (Login: `admin` / `pass`)

---

### Logs & Debugging

To check logs for Symfony:

```bash
docker logs kupujelive-symfony -f
```

To access MySQL inside the container:

```bash
docker exec -it kupujelive-mysql mysql -u mysql -p
```
(Enter the password: `mysql`)

---

### Stop and Restart Services

To stop all containers:

```bash
docker-compose down
```

To restart with fresh builds:

```bash
docker-compose up -d --build
```

Enjoy using **Kupujelive**! 🎉

