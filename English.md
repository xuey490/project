![Static Badge](https://img.shields.io/badge/%3E%3Dphp-8.1-green)    ![Static Badge](https://img.shields.io/badge/MIT-License-blue)  ![Static Badge](https://img.shields.io/badge/Symfony_7-green)     [![zread](https://img.shields.io/badge/Ask_Zread-_.svg?style=flat-square&color=00b0aa&labelColor=000000&logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQuOTYxNTYgMS42MDAxSDIuMjQxNTZDMS44ODgxIDEuNjAwMSAxLjYwMTU2IDEuODg2NjQgMS42MDE1NiAyLjI0MDFWNC45NjAxQzEuNjAxNTYgNS4zMTM1NiAxLjg4ODEgNS42MDAxIDIuMjQxNTYgNS42MDAxSDQuOTYxNTZDNS4zMTUwMiA1LjYwMDEgNS42MDE1NiA1LjMxMzU2IDUuNjAxNTYgNC45NjAxVjIuMjQwMUM1LjYwMTU2IDEuODg2NjQgNS4zMTUwMiAxLjYwMDEgNC45NjE1NiAxLjYwMDFaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00Ljk2MTU2IDEwLjM5OTlIMi4yNDE1NkMxLjg4ODEgMTAuMzk5OSAxLjYwMTU2IDEwLjY4NjQgMS42MDE1NiAxMS4wMzk5VjEzLjc1OTlDMS42MDE1NiAxNC4xMTM0IDEuODg4MSAxNC4zOTk5IDIuMjQxNTYgMTQuMzk5OUg0Ljk2MTU2QzUuMzE1MDIgMTQuMzk5OSA1LjYwMTU2IDE0LjExMzQgNS42MDE1NiAxMy43NTk5VjExLjAzOTlDNS42MDE1NiAxMC42ODY0IDUuMzE1MDIgMTAuMzk5OSA0Ljk2MTU2IDEwLjM5OTlaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik0xMy43NTg0IDEuNjAwMUgxMS4wMzg0QzEwLjY4NSAxLjYwMDEgMTAuMzk4NCAxLjg4NjY0IDEwLjM5ODQgMi4yNDAxVjQuOTYwMUMxMC4zOTg0IDUuMzEzNTYgMTAuNjg1IDUuNjAwMSAxMS4wMzg0IDUuNjAwMUgxMy43NTg0QzE0LjExMTkgNS42MDAxIDE0LjM5ODQgNS4zMTM1NiAxNC4zOTg0IDQuOTYwMVYyLjI0MDFDMTQuMzk4NCAxLjg4NjY0IDE0LjExMTkgMS42MDAxIDEzLjc1ODQgMS42MDAxWiIgZmlsbD0iI2ZmZiIvPgo8cGF0aCBkPSJNNCAxMkwxMiA0TDQgMTJaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00IDEyTDEyIDQiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K&logoColor=ffffff)](https://zread.ai/xuey490/novaphp)



# FssPhp 框架

## Official Website: [https://www.phpframe.org/](https://www.phpframe.org/)

Welcome to visit.

## Introduction:

FssPhp is a lightweight, powerful, fast, simple, and secure PHP framework. This project serves as the best practical example of using the FssPhp framework.

## User Documentation

[Zread.Ai](Zread.Ai) is highly recommended, and we would like to express our gratitude to them for providing project documentation analysis and generation services. For more details, please visit: [https://zread.ai/xuey490/project](https://zread.ai/xuey490/project)

## Core Features

🚀 Performance & Security

- Workerman Launcher: Utilizes Workerman, which is compatible with FPM launching. With the same coding approach, it delivers over 10 times the performance of traditional FPM.

- Symfony Component Integration: Leverages Symfony 7.x components to implement HTTP fundamentals, routing, dependency injection, and caching.

- Lightweight Design: Minimizes overhead and enables fast request processing.

- Integrated Middleware: Includes middleware for CSRF protection, XSS filtering, rate limiting, and IP blocking.

- Routing Caching: Optimizes route loading through a file-based caching system, and supports middleware injection via annotated routes.

- Log Inspection: Features robust logging capabilities based on Monolog, with log sharding and segmentation.

- Event Management: Allows full control over every detail of web development.

- 

🔧 Development Experience

- Multiple Routing Options: Supports convention-based automatic routing, manual routing configuration, and attribute-based routing.

- Dependency Injection: Integrates a complete Symfony DI container for service management.

- Template Flexibility: Offers dual template engine support (Twig and ThinkTemplate).

- ORM Integration: Incorporates ThinkORM for database operations.

- Data Validation: Employs ThinkValidate for powerful dataset validation.

- Permission Control: Implements permission settings for routes based on PHP annotations and DOC Comment annotations.

- 

🛠️ Modern PHP Features

- PHP 8.0+ Support: Takes advantage of modern PHP features, including attributes and union types.

- PSR Standards: Complies with PSR-4 autoloading and other relevant standards.

- Composer Ready: Adopts Composer for standard dependency management.

## Code Quality Scan

The code of this framework is highly standardized, with minimal redundancy and duplicate code. It is also suitable for beginners to learn and extend on their own.

[

## Download & Installation:

### 1. Traditional Launch Mode

- Local Environment Requirements: PHP 8.0 or above, Redis, MySQL 5.7, Composer 2.x or above.

- Download the main version from GitHub, extract it to a local directory, and run the following command in the root directory:

`composer install `

- After the component packages are downloaded completely, open the CMD command line window and enter:

`php -S localhost:8000 -t public`

### 2. Workerman Launch Mode

- Local Environment Requirements: PHP 8.0 or above, Redis, MySQL 5.7, Composer 2.x or above.

- Download the main version from GitHub, extract it to a local directory, and run the following command in the root directory:

`composer install `

- After the component packages are downloaded completely, open the CMD command line window and enter:

`php watch.php start `

### 3. Access

- Open a browser and enter the address: [http://localhost:8000](http://localhost:8000)

- It can also be deployed to any Apache or Nginx server that supports PHP runtime.

## Acknowledgments (Standing on the Shoulders of Giants to See Further)

- Workerman: [https://www.workerman.net/](https://www.workerman.net/) (An open-source high-performance PHP application container)

- Symfony: [https://www.symfony.com/](https://www.symfony.com/) (Known as the "Spring of the PHP World," serving as a foundational framework)

- ThinkPHP: [https://thinkphp.cn/](https://thinkphp.cn/) (A top-tier PHP framework in the Chinese Internet ecosystem)
> （注：文档部分内容可能由 AI 生成）