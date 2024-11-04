# CnoT

With so many note-taking tools out there, finding the right one can be tough. Many are either too complex or miss key features. CnoT focuses on simplicity, essentials, and user data control. Unlike others, it doesn’t use folders, instead, all notes are searchable by tags and keywords and are saved in HTML format.

CnoT can be installed on Windows via Docker Desktop for offline note-taking or deployed on a Linux server using Docker for online access.

## Features

The best way to see what it’s worth is to test it out.<p>

Here’s a demo [link](https://democnot.timpoz.com/index.php) with the password "password" for access:

<details><summary>See all features here</summary><p>

- Highlight in multiple colors
- Underline
- Change text color and size
- Bold or italicize text
- Strikethrough
- Create links
- Format code
- Highlight or change color within a code block
- Paste images directly from the clipboard
- Define tags
- Create bulleted or numbered lists
- Add a separator
- Search for multiple words across all tags
- Search for multiple words in all titles and content
- Automatic or manual saving
- View on phone or tablet in landscape mode
- Export a note in HTML format
- Trash with recoverable notes
- Filter by tags

</p> </details>

⚠️ The application has been optimized for use on a computer with a browser, not on a mobile phone although it is possible to view your notes in landscape mode.

## Installation

1. **Clone the repository and configure the application:**
 
    ```
    mkdir cnot-project
    cd cnot-project
    git clone https://github.com/timothepoznanski/cnot.git
    cd cnot
    cp env_template .env
    vi .env  # Edit following the instructions provided in the file's comments.
    ```

2. **Add a SSL certificate for HTTPS:** 🌏

   Choose the setup that best suits your environment:

   <details> <summary>Local Windows Installation with Docker Desktop</summary> <p>

   Open Powershell and cd into your cloned repository.

   Run the following command :

   ```
   & "C:\Program Files\Git\usr\bin\openssl.exe" req -x509 -out ssl/fullchain.pem -keyout ssl/privkey.pem -newkey rsa:2048 -nodes -sha256 -days 36500 -subj "/CN=localhost/O=CNOT" -addext "subjectAltName=DNS:localhost" -addext "keyUsage=digitalSignature" -addext "extendedKeyUsage=serverAuth"
   ```

   Install the fullchain.pem into your web browser's certificate store:

   On Chrome (I haven't tried other web browsers):
   
   - Open the following url in Google Chrome:
     ```
     chrome://settings/security
     ```
   - Go to 'Manage certificates'.
   - Go to 'Manage imported certificates from Windows'.
   - Navigate to the "Trusted Root Certification Authorities" tab.
   - Click Import.
   - Locate the fullchain.pem file generated earlier (it won’t appear unless you select "All Files").
   - Next, next, next... (leave default choice)
   - Open the following url in Google Chrome:
     ```
     chrome://restart
     ```

   </p> </details><details><summary>Linux Server Installation with Docker</summary> <p><p>
   Create fullchain.pem and privkey.pem for your domain and copy them to the ssl folder.
   
   </p> </details>

   
3. **Run the application:** 🚀

   Define a project name, for example "mycnot", in case one day you want to run several cnot instances on the same server.
   
   ```
   docker compose -p YOURPROJECTNAME up -d --build   
   ```

4. **Open the application:**

    Open your web browser and visit:

    `https://SERVER_NAME:YOUR-HTTPS-PORT`
   

5. **Connect to the application:**

    Connect with the password you provided in the .env file.

## Update settings

**If you want to change the following settings:** 

- APP_PASSWORD
- JOURNAL_NAME
- HTTPS_PORT

<details><summary>See instructions here</summary><p><p>
just update your .env file and run the application (docker compose up -d --build). This will restart the web container with the new .env config file. Your data are normally untouched but always make a backup first (see next section).
</p></details>

**If you want to change the following settings:** 

- ENTRIES_DATA_PATH
- DB_DATA_PATH

<details><summary>See instructions here</summary><p><p>
Update your .env file and run the application (docker compose up -d --build). ⚠️ This will create a new empty directory, so you won’t be able to access your previous data unless you re-import it (see next section).
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

Connect with your MYSQL_USER and MYSQL_PASSWORD credentials (from your .env config file) to phpMyAdmin at https://SERVER_NAME:8074/ and export your database:

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

- Copy all your html files into your ENTRIES_DATA_PATH
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

## Contributing 👩‍💻

If you want to contribute to the code, don't hesitate to open a pull request. Thanks!

## Possible errors ☢️

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

## Inspired from 🙏

https://github.com/arkanath/SleekArchive
 
https://github.com/kenshin54/popline

https://github.com/yairEO/tagify
