# 🎮 GamerHub — Gaming E-Commerce Platform

GamerHub is a full-stack gaming e-commerce platform developed with PHP, MySQL, JavaScript and Bootstrap.

The project was created as the final assignment for the Full Stack Web Development course at MasterD. It provides a complete online shopping experience for customers and a dedicated administration dashboard for managing the platform.

## ✨ Features

### Customer Area

* User registration and authentication
* Secure password hashing
* Customer profile management
* Product catalogue
* Product search and filtering
* Product details and related products
* Shopping cart
* Wishlist
* Simulated checkout
* Multiple simulated payment methods
* Order history
* Frequently asked questions
* Privacy policy
* Terms and conditions
* Responsive design

### Administration Dashboard

* Product management
* Category management
* User management
* Order management
* Create and edit products
* Manage product prices and promotions
* Control stock availability
* Assign administrator permissions

## 🛠️ Technologies

* PHP 8
* MySQL / MariaDB
* PDO
* HTML5
* CSS3
* JavaScript
* jQuery
* Bootstrap 5
* Bootstrap Icons
* XAMPP

## 📁 Project Structure

```text
admin/          Administration dashboard
assets/         CSS, JavaScript and image files
config/         Database configuration
includes/       Shared authentication, header and footer files
carrinho.php    Shopping cart
checkout.php    Checkout process
encomendas.php  Customer orders
favoritos.php   Customer wishlist
index.php       Home page
login.php       User authentication
produtos.php    Product catalogue
register.php    User registration
gamerhub.sql    Database structure and sample catalogue
```

## 🚀 Installation

### Requirements

* XAMPP, WAMP or another local PHP environment
* PHP 8 or later
* MySQL or MariaDB
* Apache

### Setup

1. Copy the `gamerhub` folder to your local web server directory:

```text
C:\xampp\htdocs\
```

2. Start Apache and MySQL from the XAMPP Control Panel.

3. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Create a database named:

```text
gamerhub
```

5. Import the `gamerhub.sql` file into the database.

6. Confirm the database settings in `config/database.php`:

```php
private $host = "localhost";
private $dbname = "gamerhub";
private $username = "root";
private $password = "";
```

7. Open the application:

```text
http://localhost/gamerhub
```

## 🔐 Administrator Access

New accounts are created with standard customer permissions.

To grant administrator access:

1. Open phpMyAdmin.
2. Select the `gamerhub` database.
3. Open the `users` table.
4. Edit the desired user.
5. Change the `tipo_utilizador` field from:

```text
cliente
```

to:

```text
admin
```

6.Save the changes.
7.Log out and sign in again.

The administration dashboard will then become available.

## 🔒 Security

The application includes:

* Password hashing with `password_hash()`
* Password verification with `password_verify()`
* PDO prepared statements
* Session regeneration after authentication
* User role verification
* Protected administration routes
* Input validation and sanitization

## 💳 Payment Disclaimer

This is an educational and demonstration project. All payment methods and transactions are simulated.

The application does not process real payments or store genuine banking or card information.

## 🎓 Academic Project

Developed as the final project for the MasterD Full Stack Web Development course.

## 👩‍💻 Author

**Petra Ferreira**
