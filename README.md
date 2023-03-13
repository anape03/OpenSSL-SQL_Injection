# Projects' Description

These are 2 projects regarding ["**OpenSSL**"](OpenSSL.md) and ["**SQL Injection**"](SQL_Injection.md), created as part of the course "*Network Security*".

The goal was to set up a **CentOS7 VM** on the course's project on [okeanos](https://okeanos.grnet.gr), access it using an ssh client (ex. [PuTTY](https://www.putty.org/)), and then set it up as requested, using information we could find ourselves on online sources and/or books.

## ["OpenSSL"](OpenSSL.md) Project:
* Create user "teacher" with appropriate permissions
* Set up **Apache** server
* Set up firewall
* Create **CA**, **CSR**, **SSL certificate**
* Create simple login page

## ["SQL Injection"](SQL_Injection.md) Project:
* Create database "GDPR" and tables "users", "logging", using **MariaDB**
* Explain how to store passwords in database
* Set up simple website with signup/login using **PHP**, protected against **SQL Injection**
    * Includes pages:
        * home
        * sign up
        * login
    * User is locked and required to change password after:
        * 3 consecutive wrong login attempts (for the same user)
        * 5 minutes have passed since last password change 
    **Note:** password change wasn't implemented


# What was implemented

The commands that were used, as well as further details, can be found on the respective markdown files of each project:
* [OpenSSL](OpenSSL.md) \
    The code (simple html) for the website created in this part can be found [here](OpenSSL.md#add-2) in the markdown file mentioned above.
* [SQL Injection](SQL_Injection.md) \
    All the files used to create the website for this part can be found in the folder [/source](/source/).
