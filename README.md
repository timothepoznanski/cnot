# CnoT

I created CnoT (from the French “c'est noté,” meaning “noted”) because I wanted a note-taking software that met four main criteria:

- **Simplicity**. No endless options that rarely get used; just a straightforward tool for taking notes, nothing more.
 
- **Self-hosted**. I wanted control over my data, with the ability to export and back it up easily and freely whenever I choose. I didn’t want to rely on any software just to access my notes, even offline. Additionally, I wanted to deploy and redeploy it on any OS, whether locally or on a server, and re-import my data with ease.
 
- **Efficient search**. I wanted to be able to search for multiple words within a note and, especially, to search by multiple tags so I wouldn’t have to organize my notes by folders or notebooks.
 
- **Open source**. I wanted to be able to share it with others, allowing anyone to create their own version or contribute to improving this one.

Please note that this web app is mainly made for desktop since I use it to take technical notes while working on my computer. So, I haven't really focused on mobile optimization. It’s best to use it on a desktop browser, but I do sometimes open it in landscape mode on my phone.

**Disclaimer**

This application was not designed for online use, but rather to run inside a local container (I run it on WSL or Virtualbox). For the sake of simplicity and minimalism, I have deliberately removed typical security features (such as SQL injection protection and authentication) to keep the code as clean and straightforward as possible.
Access control is handled externally — in my case, through the Access List feature of Nginx Proxy Manager.

## Installation
 
```
git clone https://github.com/timothepoznanski/cnot.git
docker compose up -d --build
```

Open your web browser and visit:

`http://YOUT_SERVER_NAME:8077`


## Update app or settings

**If you want to update CNOT version:** 

Just remove the 3 existing containers, git pull the latest repository version and run the application (docker compose command). Your data are normally untouched but always make a backup first (see next section).

**If you want to change the following settings:** 

- JOURNAL_NAME
- HTTP_WEBSERVER_PORT
- HTTP_PHPMYADMIN_PORT

just update your .env file and run the application (docker compose command). This will restart the web container with the new .env config file. Your data are normally untouched but always make a backup first (see next section).

**If you want to change the following settings:** 

- ENTRIES_DATA_PATH
- DB_DATA_PATH

Update your .env file and run the application (docker compose command). ⚠️ This will create a new empty directory, so you won’t be able to access your previous data unless you re-import it (see next section).

**If you want to change some database settings:**

Simply updating the `.env` file and deleting the database container will not be enough, as the settings and data are stored in a volume. You will also need to delete the volume to recreate the database with the new settings, but this will result in data loss. To avoid losing your data, export the database contents first (see next section), then delete the `DB_DATA_PATH` volume. After running the application again to create a new database, you can re-import the data (see next section). 

## Export or backup

### Export your notes ###

Get your html files from the ENTRIES_DATA_PATH directory defined in your .env config file.
You can also export all your notes using the export button. This method adds an index file to the zip export.

### Export your database ###

There are two ways to create a database dump:

**1. Using phpMyAdmin**

Connect with your MYSQL_USER and MYSQL_PASSWORD credentials (from your .env config file) to phpMyAdmin at http://SERVER_NAME:HTTP_PHPMYADMIN_PORT/ and export your database.

**2. Using Git Bash on Windows (preferred over PowerShell due to encoding issues) or bash on Linux**

Create temporarily another container to create a dump where you run the command:

  Get your database container name:

  ```
   $ docker ps -a
  ```

  Export a backup sql of your database: 
  
  ```
   $ docker run --rm --network container:DATABASE_CONTAINER_NAME -e MYSQL_PWD=MYSQL_ROOT_PASSWORD mysql:latest mysqldump -h127.0.0.1 -uroot MYSQL_DATABASE > dump.sql
  ```

## Import or restore

### Import your notes ### 

Copy all your HTML files to your ENTRIES_DATA_PATH directory and ensure that both the user and group ownership are set to www-data for all html files (chown -R www-data: ENTRIES_DATA)

### Import your database ### 

There are two ways to import a database dump:

**1. Using phpMyAdmin**

Connect with your MYSQL_USER and MYSQL_PASSWORD credentials (from your .env config file) to phpMyAdmin at http://SERVER_NAME:HTTP_PHPMYADMIN_PORT/ and import your database.

**2. Using Git Bash on Windows (preferred over PowerShell due to encoding issues) or bash on Linux**

  Get your database container name:

  ```
  $ docker ps -a
  ```

  Copy your dump into the docker instance:

  ```
  $ docker cp dump.sql DATABASE_CONTAINER_NAME:/tmp/dump.sql
  ```

  Enter your database docker instance and import your dump :
  
  ```
  $ docker exec -it DATABASE_CONTAINER_NAME bash
  bash-5.1# mysql -u root -pMYSQL_ROOT_PASSWORD MYSQL_DATABASE < /tmp/dump.sql
  ```

## Possible errors

### When openning Cnot after first install
 
 ```bash
BDD connection error : Connection refused
 ```

or

 ```bash
Connection failed: Connection refused
 ```

or 

 ```bash
Fatal error: Uncaught Error: Call to a member function execute()
 ```

Possible reasons to these errors:

1. The database is still initializing
2. It is a browser cache issue

Solution: Wait a few seconds, visit another web page and come back.

3. The server runs out of memory
   
Solution: Check and increase your server resources if needed.

### When saving a note

![image](https://github.com/user-attachments/assets/ab68d476-68bc-4d16-b5b7-dfc41480bef9)

Solution:

 ```bash
chown -R www-data: ENTRIES_DATA
 ```

## Contributing 

If you want to contribute to the code, don't hesitate to open a pull request. Thanks!

## Inspired from

https://github.com/arkanath/SleekArchive
 
https://github.com/kenshin54/popline
