# CnoT

I created CnoT (from the French “c'est noté,” meaning “noted”) because I wanted a note-taking software that met four main criteria:

- **Simplicity**. No endless options that rarely get used; just a straightforward tool for taking notes, nothing more.
 
- **Self-hosted**. I wanted control over my data, with the ability to export and back it up easily and freely whenever I choose. I didn’t want to rely on any software just to access my notes, even offline. Additionally, I wanted to deploy and redeploy it on any OS, whether locally or on a server, and re-import my data with ease.
- **Efficient search**. I wanted to be able to search for multiple words within a note and, especially, to search by multiple tags so I wouldn’t have to organize my notes by folders or notebooks.
- **Open source**. I wanted to be able to share it with others, allowing anyone to create their own version or contribute to improving this one.

It’s far from perfect, of course, but I’ve been using it long enough now to see that it meets most of my essential needs. And maybe yours. 😉

## Try it!

Please note that this application is primarily designed for desktop use, as I rely on it exclusively for taking technical notes when working on my computer. For this reason, I haven't prioritized mobile optimization. Please open it in a desktop browser.

## Installation

CnoT can be installed on Windows via Docker Desktop for offline note-taking or deployed on a Linux server using Docker for online access.

1. **Clone the repository and configure the application:**
 
    ```
    git clone https://github.com/timothepoznanski/cnot.git
    cd cnot
    cp env_template .env
    vi .env  # Edit following the instructions provided in the file's comments.
    ```
   
2. **Run the application:** 
   
   ```
   docker compose up -d --build   
   ```

3. **Open the application:**

    Open your web browser and visit:

    `http://SERVER_NAME:HTTP_WEBSERVER_PORT`
   
4. **SSL certificate for HTTPS:**  

  To enable HTTPS for the site, I manage the SSL certificates using ["Nginx Proxy Manager"](https://nginxproxymanager.com/).
  If you are using a reverse proxy (NPM or other), make sure to comment out HTTP_WEBSERVER_PORT and HTTP_PHPMYADMIN_PORT to prevent exposing them.

5. **Connect to the application:**

    Connect with the password you provided in the .env file.

## Update settings

**If you want to change the following settings:** 

- APP_PASSWORD
- JOURNAL_NAME
- HTTP_WEBSERVER_PORT
- HTTP_PHPMYADMIN_PORT

<details><summary>See instructions here</summary><p><p>
just update your .env file and run the application (docker compose command). This will restart the web container with the new .env config file. Your data are normally untouched but always make a backup first (see next section).
</p></details>

**If you want to change the following settings:** 

- ENTRIES_DATA_PATH
- DB_DATA_PATH

<details><summary>See instructions here</summary><p><p>
Update your .env file and run the application (docker compose command). ⚠️ This will create a new empty directory, so you won’t be able to access your previous data unless you re-import it (see next section).
</p></details>

**If you want to change some database settings:**

<details><summary>See instructions here</summary><p><p>
Simply updating the `.env` file and deleting the database container will not be enough, as the settings and data are stored in a volume. You will also need to delete the volume to recreate the database with the new settings, but this will result in data loss. To avoid losing your data, export the database contents first (see next section), then delete the `DB_DATA_PATH` volume. After running the application again to create a new database, you can re-import the data (see next section). 
</p></details>

## Backup and Restore

To be able to restore all your notes, you need only two things:

- Your notes as html files.
- A dump SQL of your database.

<details><summary>Backup your notes</summary><p><p>

Get your html files from the ENTRIES_DATA_PATH directory defined in your .env config file.

</p></details>

<details><summary>Backup your database</summary><p><p>

There are two ways to create a dump:

**1. Using phpMyAdmin:**

Connect with your MYSQL_USER and MYSQL_PASSWORD credentials (from your .env config file) to phpMyAdmin at http://SERVER_NAME:HTTP_PHPMYADMIN_PORT/ and export your database:

![2024-10-30_06h57_03](https://github.com/user-attachments/assets/63558d9a-bb30-4fce-9308-a1b51929d98c)

**2. Using Git Bash on Windows (preferred over PowerShell due to encoding issues) or bash on Linux:**

Create temporarily another container to create a dump where you run the command:

  Get your database container name:
  ```
   $ docker ps -a
  ```

  Export a backup sql of your database: 
  
  ```
   $ docker run --rm --network container:DATABASE_CONTAINER_NAME -e MYSQL_PWD=MYSQL_ROOT_PASSWORD mysql:latest mysqldump -h127.0.0.1 -uroot MYSQL_DATABASE > dump.sql
  ```
</p></details>

<details><summary>Restore your notes</summary><p><p>

- Copy all your HTML files to your ENTRIES_DATA_PATH directory and ensure that both the user and group ownership are set to www-data for all html files (chown -R www-data: ENTRIES_DATA)
- Import your sql dump. Two ways :

  1. Import with Phpmyadmin.
  2. Copy your dump into your docker instance :

     Get your database container name:
     ```
      $ docker ps -a
     ```

     ```
      $ docker cp dump.sql DATABASE_CONTAINER_NAME:/tmp/dump.sql
     ```

     and enter your database docker instance and import your dump :
     
     ```
      $ docker exec -it DATABASE_CONTAINER_NAME bash
      bash-5.1# mysql -u root -pMYSQL_ROOT_PASSWORD MYSQL_DATABASE < /tmp/dump.sql
     ```
</p></details>

## Contributing 

If you want to contribute to the code, don't hesitate to open a pull request. Thanks!

## Possible errors

### When openning Cnot after first install
 
 ```bash
BDD connection error : Connection refused
 ```

or 

 ```bash
Fatal error: Uncaught Error: Call to a member function execute()
 ```

Possible reasons to these errors:

1. The database is still initializing
3. It is a browser cache issue
4. The server runs out of memory
   
Solution: Wait a few seconds, visit another web page and come back.

### When saving a note

![image](https://github.com/user-attachments/assets/ab68d476-68bc-4d16-b5b7-dfc41480bef9)

Solution:

 ```bash
chown -R www-data: ENTRIES_DATA
 ```

## Inspired from

https://github.com/arkanath/SleekArchive
 
https://github.com/kenshin54/popline

https://github.com/yairEO/tagify
