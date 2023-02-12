# OpenSSl

[A. Create user "teacher"](#A-Create-user-teacher)
- [Create user](#Create-user)
- [Add public key](#Add-public-key)

[B. User permissions](#B-User-permissions)
- [Update permissions](#Update-permissions)

[C. Apache](#C-Apache)
- [Install and start](#Install-and-start)
- [Check if server is running](#Check-if-server-is-running)

[D. Firewall](#D-Firewall)
- [Enable http, https](#Enable-http-https)
- [Restrict access with ssh](#Restrict-access-with-ssh)
- [Remove password for ssh connection on non root](#Remove-password-for-ssh-connection-on-non-root)

[E. Create CA, CSR, SSL certificate](#E-Create-CA-CSR-SSL-certificate)
- [Install OpenSSL](#Install-OpenSSL)
- [Configuring a Certificate Authority (CA)](#Configuring-a-Certificate-Authority-CA)
- [Configuring Apache HTTP Server to use SSL Certificates](#Configuring-Apache-HTTP-Server-to-use-SSL-Certificates)

[F. Configure Apache](#F-Configure-Apache)
- [Creating a Redirect from HTTP to HTTPS](#Creating-a-Redirect-from-HTTP-to-HTTPS)
- [Applying Apache Configuration Changes](#Applying-Apache-Configuration-Changes)

[G. Create a simple website](#G-Create-a-simple-website)
- [Inspect Apache's "httpd.conf" file](#Inspect-Apaches-httpdconf-file)
- [Add html code](#Add-html-code)

# A. Create user "teacher"

> User teacher must connect with ssh key. <br>
(User's public key was provided)

## Create user:
```
$ useradd teacher
$ passwd teacher
```

## Add public key:
```
$ mkdir /home/teacher/.ssh
$ mkdir ~/.ssh/
$ vi ~/.ssh/authorised_keys
```


# B. User permissions

> Give read permission for user "teacher" everywhere in /home and /root (folders, subfolders, and files), and no write permissions in /home (folder, subfolders, and files).

## Update permissions:
```
$ cd ..
$ ls -l
$ chmod -R o+rx /home 
$ chmod -R o+rx /root
$ ls -l
```


# C. Apache

> Install and configure all necessary services for the server to function as a web-server with Apache.

## Install and start:
```
$ yum install httpd
$ systemctl start httpd
```
## Check if server is running:
```
$ systemctl status httpd
$ systemctl status sshd
```


# D. Firewall

> Add the necessary inbound rules to the CentOS FirewallD service, so that http and https are accessible from everywhere. Restrict access with ssh only through AUEB VPN.

## Enable http, https:
```
$ firewall-cmd --permanent --add-service=http
$ firewall-cmd --permanent --add-service=https
$ firewall-cmd --reload
```

---

## Restrict access with ssh:
<u>**Note:**</u> <br>
**<ip_a>** : Teacher's IP using AUEB VPN <br>
**<ip_b>** : My IP using AUEB VPN

```
$ iptables -A INPUT -p tcp -s <ip_a>,<ip_b> --dport 22 -m conntrack --ctstate NEW,ESTABLISHED -j ACCEPT
$ iptables -A OUTPUT -p tcp --sport 22 -m conntrack --ctstate ESTABLISHED -j ACCEPT
```

Also [added](https://wpguru.co.uk/2015/11/how-to-disable-ssh-access-from-everywhere-except-for-certain-ips-in-centos-7/):
```
$ firewall-cmd --zone=internal --add-service=ssh --permanent
$ firewall-cmd --zone=internal --add-source=<ip_a> --permanent
$ firewall-cmd --zone=internal --add-source=<ip_b> --permanent
$ firewall-cmd --zone=public --remove-service=ssh --permanent
$ firewall-cmd --reload
```

---

## Remove password for ssh connection on non root:

### Edit file:
```
$ vi /etc/ssh/sshd_config
```

### Add:
```
PasswordAuthentication no
Match User root
  PasswordAuthentication yes
```

### Restart service:
```
$ systemctl restart sshd
```


# E. Create CA, CSR, SSL certificate

> Using OpenSSL generate a Certificate Authority (CA), CSR and an SSL certificate.

[(source)](https://www.centlinux.com/2019/01/configure-certificate-authority-ca-centos-7.html)

## Install OpenSSL:
```
$ yum install -y openssl
```

---

## Configuring a Certificate Authority (CA):

### OpenSSL package:
```
$ yum install -y openssl
```

### For implementing public encryption, first of all, we need a private key that is later used to generate a CA certificate:
```
$ cd /etc/pki/CA/private/
$ openssl genrsa -aes128 -out ourCA.key 2048
```

### Create a Certificate Authority (CA) certificate using the ourCA.key:
```
openssl req -new -x509 -days 1825 -key /etc/pki/CA/private/ourCA.key -out /etc/pki/CA/certs/ourCA.crt
```

#### Fill out the information:
```
Country Name (2 letter code) [XX]:GR
State or Province Name (full name) []:.
Locality Name (eg, city) [Default City]:Athens
Organization Name (eg, company) [Default Company Ltd]:Witch Certificate Authority
Organizational Unit Name (eg, section) []:<my_AM>
Common Name (eg, your name or your server's hostname) []:<server_hostname>
Email Address []:.
```

---

## Configuring Apache HTTP Server to use SSL Certificates:

### Install mod_ssl:
```
$ yum install -y mod_ssl
```

### Generate a private key for the server <server_hostname>:
```
$ openssl genrsa -out /etc/pki/tls/private/<server_name>.key 1024
```

### Generate a CSR (Certificate Signing Request) for our website:
```
$ openssl req -new -key /etc/pki/tls/private/<server_name>.key -out /etc/pki/tls/<server_name>.csr
```

#### Fill out the information:
```
Country Name (2 letter code) [XX]:GR
State or Province Name (full name) []:.
Locality Name (eg, city) [Default City]:Athena
Organization Name (eg, company) [Default Company Ltd]:Strawberry Frog
Organizational Unit Name (eg, section) []:<my_AM>
Common Name (eg, your name or your server's hostname) []:<server_hostname>
Email Address []:.

Please enter the following 'extra' attributes
to be sent with your certificate request
A challenge password []:<chall_password>
An optional company name []:.
```

### We have generated a CSR. Now we send it to CA for digital signature:
```
$ scp /etc/pki/tls/<server_name>.csr root@<server_name>:~/<server_name>.csr
```

### Connect to ca-01.centlinux.com and digitally signed that CSR:
```
$ openssl x509 -req -in <server_name>.csr -CA /etc/pki/CA/certs/ourCA.crt -CAkey /etc/pki/CA/private/ourCA.key -CAcreateserial -out <server_name>.crt -days 365
```
Our CSR has been digitally signed by our Certificate Authority (CA).

### Transfer <server_name>.crt to <server_hostname>:
```
$ scp <server_name>.crt root@<server_name>:/etc/pki/tls/certs/<server_name>.crt
```
Connect using ssh as root user.

Now, we have a digitally signed SSL certificate.

### Setting Up the Certificate:
```
$ vi /etc/httpd/conf.d/<server_hostname>.conf
```

#### Paste in the following minimal VirtualHost configuration:
```
<VirtualHost *:443>
    ServerName <server_hostname>
    DocumentRoot /var/www/html
    SSLEngine on
    SSLCertificateFile /etc/pki/tls/certs/<server_name>.crt
    SSLCertificateKeyFile /etc/pki/tls/private/<server_name>.key
</VirtualHost>
```

### Restart Apache service:
```
$ systemctl restart httpd.service
```


# F. Configure Apache

> Configure Apache to serve your certificate over https and redirect http to https. Make sure the entire SSL certificate chain is displayed correctly. 

[(source)](https://www.digitalocean.com/community/tutorials/how-to-create-an-ssl-certificate-on-apache-for-centos-7)

## Creating a Redirect from HTTP to HTTPS:

### To redirect all traffic to be SSL encrypted:
```
$ vi /etc/httpd/conf.d/non-ssl.conf
```

### Add:
Inside, create a VirtualHost block to match requests on port 80. Inside, use the ServerName directive to again match your domain name or IP address. Then, use Redirect to match any requests and send them to the SSL VirtualHost. Make sure to include the trailing slash:
```
<VirtualHost *:80>
       ServerName <server_hostname>
        Redirect "/" "https://<server_hostname>/"
</VirtualHost>
```

## Applying Apache Configuration Changes:

### First, check your configuration file for syntax errors by typing:
```
$ apachectl configtest
```

### Restart the Apache server to apply your changes by typing:
```
$ systemctl restart httpd.service
```

### Open your web browser and type https:// followed by your server’s domain name or IP into the address bar:

`https://<server_hostname>`



# G. Create a simple website

> Create a simple website with just a text field with name=”username” and a submit button. If your AM is submitted it should display a success message, while in any other case a failure message (containing the words "success" and "fail" respectively) <br>
You can use any technology. <br>
As DocumentRoot use: /var/www/html

**Note:** AM is a student's registartion number.

[(source)](https://www.linuxquestions.org/questions/linux-networking-3/how-to-add-web-pages-in-apache-webserver-287964/)

## Inspect Apache's "httpd.conf" file:
```
$ vi /etc/httpd/conf/httpd.conf
```
```
DocumentRoot: /var/www/html
```
So /var/www/html will be the location of the pages.

(it was already set up this way)

## Add html code:

### Create file:
```
$ cd /var/www/html
$ vi index.html
```

### Add:
```
<html>
    <head>
        <meta charset="utf-8">
        <title>Login page</title>
        <script>
            function checkSubmit() {
                var username = document.forms["form"]["username"];
                if (username.value) { 
                    if (username.value == "<my_AM>") {
                        alert("Success! :D");
                    } else {
                        alert("Fail! :(");
                    }
                }
                return;
            }
        </script>
    </head>

    <body>
        <form method="post" id="form">
            <label for="username"><b>Username</b></label> <br>
            <input type="text" placeholder="Enter Username" name="username" id="username" required>
            
            <button type="submit" onclick="return checkSubmit();">Login</button>
        </form> 
    </body>
</html>
```
