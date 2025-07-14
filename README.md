# CnoT

I created CnoT (from the French “c'est noté,” meaning “noted”) because I wanted a note-taking software that met four main criteria:

- **Simplicity**. No endless options that rarely get used; just a straightforward tool for taking notes, nothing more.
 
- **Self-hosted**. I wanted control over my data, with the ability to export and back it up easily and freely whenever I choose. I didn’t want to rely on any software just to access my notes, even offline. Additionally, I wanted to deploy and redeploy it on any OS, whether locally or on a server, and re-import my data with ease.
 
- **Efficient search**. I wanted to be able to search for multiple words within a note and, especially, to search by multiple tags so I wouldn’t have to organize my notes by folders or notebooks.
 
- **Open source**. I wanted to be able to share it with others, allowing anyone to create their own version or contribute to improving this one.

📱Please note that the mobile version is for viewing only.

🔒 This application can be used in two different ways:

- **Locally**, in a container (e.g., Docker on WSL or VirtualBox), without being exposed to the internet. In this scenario, since the notes are only accessible from the local environment, there's no need for authentication or protection against common security threats like SQL injection.

- **Online**, by exposing the application to the internet. In this case, security and access control become necessary — but instead of embedding these protections in the app itself, they are intentionally left out to keep the codebase clean and simple. You're expected to handle authentication and security externally using whatever method suits your setup best. For example, I run one instance of CnoT online and secure it using Nginx Proxy Manager’s Access List feature.

📢 Because external authentication (like Nginx Proxy Manager) acts as the primary gatekeeper, the application itself does not implement protections against SQL injection or similar attacks. This is acceptable only if all access points are properly secured by the external authentication layer and users are trusted. If this layer is bypassed or misconfigured, the application could become vulnerable.

In both cases, standard security features have been intentionally removed to focus on simplicity and ease of understanding. If you choose to expose the app online, make sure to implement appropriate access controls on your side.

## Installation
 
```
git clone https://github.com/timothepoznanski/cnot.git
docker compose up -d --build
```

Open your web browser and visit:

`http://YOUR_SERVER_NAME:8077`


## Update app or settings

**If you want to update CNOT version:** 

Just remove the 3 existing containers, git pull the latest repository version and run the application (docker compose command). Your data are normally untouched but always make a backup first (see next section).

**If you want to change the following settings:** 

- JOURNAL_NAME
- HTTP_WEBSERVER_PORT
- HTTP_PHPMYADMIN_PORT

just update your `.env` file and run the application (docker compose command). This will restart the web container with the new .env config file. Your data are normally untouched but always make a backup first (see next section).

**If you want to change the following settings:** 

- ENTRIES_DATA_PATH
- DB_DATA_PATH

Update your `.env` file and run the application (docker compose command). ⚠️ This will create a new empty directory, so you won’t be able to access your previous data unless you re-import it (see next section).

**If you want to change some database settings:**

Simply updating the `.env` file and deleting the database container will not be enough, as the settings and data are stored in a volume. You will also need to delete the volume to recreate the database with the new settings, but this will result in data loss. To avoid losing your data, export the database contents first (see next section), then delete the `DB_DATA_PATH` volume. After running the application again to create a new database, you can re-import the data (see next section). 

## Export or backup

### Export your notes ###

Get your html files from '../ENTRIES_DATA'. You can also export all your notes using the export button. This method adds an index file to the zip export.

### Export your database ###

There are two ways to create a database dump:

**1. Using phpMyAdmin**

Connect with user `mysqluser` and password `mysqlpassword` to phpMyAdmin at `http://YOUR_SERVER_NAME:8078/` and export your database.

**2. Using Git Bash on Windows (preferred over PowerShell due to encoding issues) or bash on Linux**

Create temporarily another container to create a dump where you run the command:

  Export a backup sql of your database: 
  
  ```
   $ docker run --rm --network container:dbservercnot -e MYSQL_PWD=mysqrootpassword mysql:latest mysqldump -h127.0.0.1 -uroot cnot_db > dump.sql
  ```

## Import or restore

### Import your notes ### 

Copy all your HTML files to `../ENTRIES_DATA` directory and ensure that both the user and group ownership are set to `www-data` for all html files (chown -R www-data: ENTRIES_DATA)

### Import your database ### 

There are two ways to import a database dump:

**1. Using phpMyAdmin**

Connect with `mysqluser` and `mysqlpassword` to phpMyAdmin at `http://YOUR_SERVER_NAME:8078/` and import your database.

**2. Using Git Bash on Windows (preferred over PowerShell due to encoding issues) or bash on Linux**

  Copy your dump into the docker instance:

  ```
  $ docker cp dump.sql dbservercnot:/tmp/dump.sql
  ```

  Enter your database docker instance and import your dump :
  
  ```
  $ docker exec -it dbservercnot bash
  bash-5.1# mysql -u root -pmysqlrootpassword cnot_db < /tmp/dump.sql
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
