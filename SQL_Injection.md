# SQL Injection

[Part 1](#Part-1)
- [Instal MySQL](#Instal-MySQL)
- [Start MySQL](#Start-MySQL)
- [Upgrade Mariadb Version to 10.4](#Upgrade-Mariadb-Version-to-104)
- [Create Database](#Create-Database)

[Part 2](#Part-2)
- [How to securily store passwords](#How-to-securily-store-passwords)
- [Example with SQL trigger](#Example-with-SQL-trigger)

[Part 3](#Part-3)
- [Install PHP](#Install-PHP)
- [Edit files](#Edit-files)

[Part 4](#Part-4)
- [Create table logging](#Create-table-logging)
- [Edit login/authentication.php](#Edit-loginauthenticationphp)

# Part 1

> Create a relational database named GDPR in a MySQL or MariaDB or PostgreSQL database management system and a table "users" in the database that stores the necessary data for the basic operation of the Login Mechanism with fields (columns) username, password, user description (description) using the appropriate SQL queries required not only to create the database but also the table in terms of both defining the data type of the table's fields and defining an appropriate field to play the role primary search key (records) in the table. In case you use a framework there is no need to provide us with the SQL code as it is automatically generated through the code and through the code we can check its correctness. You will enter in the users table with the appropriate SQL commands two (2) users, the first will have a username of your registration number (AM) and the second will have a username of admin.

## Instal MySQL:

### Download:
```
$ curl -sSLO https://dev.mysql.com/get/mysql80-community-release-el7-7.noarch.rpm
```

### Verify integrity:
```
$ md5sum mysql80-community-release-el7-7.noarch.rpm
```

### Install:
```
$ yum install mariadb-server mariadb-libs mariadb
```

(used mariadb because "*In RHEL 7, and consequently in CentOS7, the mysql- packages (or most of them, anyway) have been replaced with mariadb- packages due to an upstream rename/fork. Simply yum install mariadb-server mariadb-libs mariadb and you should be okay - the command names themselves are still mostly mysql related.*"
[(source)](https://serverfault.com/questions/662741/yum-no-package-mysql-server-available-in-cent-os-7))

## Start MySQL:

```
$ sudo systemctl start mariadb
$ sudo systemctl status mariadb
```
### Run the security script:
```
$ sudo mysql_secure_installation
```

### Verify installation:
```
$ mysqladmin -u root -p version
```

## Upgrade Mariadb Version to 10.4:

[(source)](https://www.ezeelogin.com/kb/article/how-to-upgrade-mariadb-5-5-to-10-x-in-centos-7-486.html)

## Create Database:

### Login:
Enter your current password once prompted to complete the login.
```
$ mysql -u root -p
```

### Create Database:

```
MariaDB [(none)]> CREATE DATABASE GDPR;
```
```
MariaDB [(none)]> SHOW DATABASES;
```

Use a database with use command:
```
MariaDB [(none)]> USE GDPR;
```

### Create table "users":
```
MariaDB [(GDPR)]> CREATE TABLE users (
    username VARCHAR(40) NOT NULL,
    password VARCHAR(64) NOT NULL,
    description JSON DEFAULT (JSON_OBJECT('locked', '0', 'last_pass_change', CURRENT_TIMESTAMP)), 
    PRIMARY KEY(username)
    );
```
```
MariaDB [(GDPR)]> SHOW TABLES;
MariaDB [(GDPR)]> SHOW COLUMNS FROM users;
```


# Part 2

> What are the recommended solutions for storing the user's password in the database to ensure their privacy in case of illegal extraction of data from the database? List the SQL or high-level programming language commands (including the use of frameworks) required to properly transform the password field with a higher level of security.

## How to securily store passwords:

[(source1)](https://stackoverflow.com/questions/1054022/best-way-to-store-password-in-database)
[(source2)](https://security.stackexchange.com/questions/17421/how-to-store-salt/17435#17435)

In order to store passwords securely, they should not be stored in the database as-is. Instead, the result of a hashing algorithm with the user's code as an argument should be stored in their place. For further security though, "salt", a random value added to the code before hashing, should also be used. This way, in case the data is leaked, even with the use of rainbow tables, it is not possible to immediately decrypt the codes.
However, the salt is also stored in the base, and therefore becomes known in the event of a data breach. A workaround for this is to use "pepper" which is not stored in the database, and is useful in preventing SQL Injection Attacks. However, this model is no longer used so often in place of parameterized queries. <br>
MD5 and SHA-1 are not considered particularly secure hashes, while SHA-2 is considered relatively better. <br>
For further security, we could run the hash algorithm(s) multiple times on the resulting result each time.

## Example with SQL trigger:

(**Note:** Must add column salt to table "users") <br>
Hash password (using SHA2 and random salt):

```
MariaDB [(GDPR)]> DELIMITER //
    CREATE TRIGGER before_user_insert BEFORE INSERT 
    ON users
    FOR EACH ROW 
    BEGIN
        DECLARE salt varchar(64);
        DECLARE hash_value varchar(64);
        SELECT 
            CAST(SUBSTRING(SHA1(RAND()), 1, 6) AS CHAR CHARACTER SET utf8) 
        INTO salt;
        SELECT 
            SHA2(CONCAT(salt, NEW.password), 256) 
        INTO hash_value;
        SET NEW.password = hash_value;
        SET NEW.salt = salt;
    END; //
    DELIMITER ;
```

# Part 3

> In a programming language of your choice Java, Python, PHP and if you wish using a framework (recommended) you will create a simple web application through which the users you have registered will be able to Login using a suitable endpoint. Your code must be properly structured so that an SQL injection attack cannot be performed.

## Install PHP:

```
$ yum install php
```

[Update php to 7.4](https://techglimpse.com/install-update-php-centos7/)
```
$ rpm -qa | grep php > php_rpm.txt
$ yum remove "php*" -y
$ yum install -y http://rpms.remirepo.net/enterprise/remi-release-7.rpm
$ yum --enablerepo=remi-php74 install php php-pdo php-fpm php-gd php-mbstring php-mysql php-curl php-mcrypt php-json -y
$ php -v
```
```
PHP 7.4.33 (cli) (built: Dec 19 2022 13:32:43) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
```

Restart Apache:
```
$ systemctl restart httpd
```

## Edit files:
```
$ cd /var/www/html
```
(see [source code](/source/))

### Directory tree:
```
.
├── connection.php
├── index.html
├── login
│   ├── authentication.php
│   ├── login.html
│   └── login.js
├── signup
│   ├── signup.html
│   ├── signup.js
│   └── signup.php
└── style.css
```

# Part 4

> Using the appropriate SQL queries, either through a high-level language or a framework, design a table named "logging" in the GDPR database that will record successful login attempts with an appropriate timestamp field. You should either add sql procedures or set controls through your application on how many times a user will be allowed to make a login attempt until you "lock" them and display an appropriate message via alert on the front end. Also, implement checks that will check when was the user's password last changed and define a period of time after which you should ask the user to change his password (the password change as a function does not need to be implemented). Give detailed examples with screenshots from your implementation for the above.

## Create table logging:
```
$ mysql -u root -p
MariaDB [(none)]> USE GDPR;
```
```
MariaDB [(GDPR)]> CREATE TABLE logging (
    id int NOT NULL AUTO_INCREMENT,
    success BOOLEAN NOT NULL,
    username VARCHAR(40) NOT NULL,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id)
    );
```
```
MariaDB [(GDPR)]> SHOW TABLES;
MariaDB [(GDPR)]> SHOW COLUMNS FROM logging;
```

[(source for timestamp)](https://stackoverflow.com/questions/34418077/how-to-create-mysql-table-with-column-timestamp-default-current-date)


## Edit login/authentication.php:

(see [source code](/source/login/authentication.php))