# CnoT

I created CnoT as a simple, self-hosted, open-source, full-responsive, note-taking tool with powerful search and full control over your data. 🤩

CnoT runs in Docker and works seamlessly on both Windows and Linux. The interface is fully responsive across all devices.

⚠️ If you run the app online, make sure to secure it properly by handling access control and HTTPS yourself. Personally, I use Nginx Proxy Manager with its Access List and SSL certificate features (https://nginxproxymanager.com).

## Installation

```bash
mkdir my-cnot
cd my-cnot
git clone https://github.com/timothepoznanski/cnot.git
cd cnot
docker compose up -d --build
```

Open your web browser and visit: `http://YOUR_SERVER_NAME:8077`

## Update app or settings

**If you want to update CNOT version:** 

Two ways possible: 

Your data are normally untouched but always make a backup first (see next section).

1. Just remove the 3 existing containers, git pull the latest repository version and run the application (docker compose command).
   
2. You can also git pull the latest repository version and run the following docker commands: 

```
  docker compose build --no-cache
  docker compose up -d --force-recreate
```

**If you want to change the following settings:** 

- JOURNAL_NAME
- HTTP_WEBSERVER_PORT
- HTTP_PHPMYADMIN_PORT

just update your `.env` file and run the application (docker compose command). This will restart the web container with the new .env config file. Your data are normally untouched but always make a backup first (see next section).

**If you want to change the following settings:** 

- ENTRIES_DATA_PATH
- DB_DATA_PATH
- ATTACHMENTS_DATA_PATH

Update your `.env` file and run the application (docker compose command). ⚠️ This will create a new empty directory, so you won’t be able to access your previous data unless you re-import it (see next section).

**If you want to change some database settings:**

Simply updating the `.env` file and deleting the database container will not be enough, as the settings and data are stored in a volume. You will also need to delete the volume to recreate the database with the new settings, but this will result in data loss. To avoid losing your data, export the database contents first (see next section), then delete the `DB_DATA_PATH` volume. After running the application again to create a new database, you can re-import the data (see next section). 

## Export or backup

### Export your notes ###

Get your html files from '../ENTRIES_DATA'. You can also export all your notes using the export button. This method adds an index file to the zip export.

### Export your attachements ###

Get your html files from '../ATTACHEMENTS_DATA'. You can also export all your notes using the export button. This method adds an index file to the zip export.

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



## API

### List notes

- **URL**: `/api_list_notes.php`
- **Method**: `GET`
- **Response**:
    ```json
    [
      {
        "id": 1,
        "heading": "Title",
        "tags": "tag1,tag2",
        "updated": "2025-07-14 20:00:00"
      }
    ]
    ```

### Create a note

- **URL**: `/api_create_note.php`
- **Method**: `POST`
- **Body (JSON)**:
    ```json
    {
      "heading": "Note title",
      "tags": "tag1,tag2"
    }
    ```
- **Response (success)**:
    ```json
    { "success": true, "id": 2 }
    ```
- **Response (error)**:
    ```json
    { "error": "The heading field is required" }
    ```

### Example curl

```bash
curl -X POST http://YOUR_SERVER_NAME:8077/api_create_note.php \
  -H "Content-Type: application/json" \
  -d '{"heading": "My new note", "tags": "personal,important"}'
```

---
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
