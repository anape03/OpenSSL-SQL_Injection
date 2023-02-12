# Projects' Desription

These are 2 projects regarding "**OpenSSL**" and "**SQL Injection**", created as part of the course "*Network Security*".

The goal was to set up a **CentOS7 VM** on the course's project on [okeanos](https://okeanos.grnet.gr), access it using an ssh client (ex. [PuTTY](https://www.putty.org/)), and then set it up as requested, using information we could find ourselves on online sources and/or books.

## ["OpenSSL" Project:](OpenSSL.md)
* Create user "teacher" with appropriate permissions
* Set up **Apache** server
* Set up firewall
* Create **CA**, **CSR**, **SSL certificate**
* Create simple login page

## ["SQL Injection" Project:](SQL_Injection.md)
* Create database "GDPR" and tables "users", "logging", using **MariaDB**
* Explain how to store passwords in database
* Set up simple website with signup/login using **PHP**, protected against **SQL Injection**
    * Includes pages:
        * home
        * sign up
        * login
    * User is locked is required to change password (password change wasn't implemented) after:
        * 3 consecutive wrong login attempts (for the same user)
        * 5 minutes have passed since last password change 


# What was implemented

The commands that were used can be found on the respective markdown files of each project:
* [OpenSSL](OpenSSL.md)
* [SQL Injection](SQL_Injection.md)

The folder `/source` includes all the files used to create the website for the Project "SQL Injection".

The project "OpenSSL" also inludes a simple html page, but the code for it is included in the bottom of the respective markdown file.
