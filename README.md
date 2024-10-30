# CnoT

With so many note-taking tools out there, finding the right one can be tough. Many are either too complex or miss key features. CnoT focuses on simplicity, essentials, and user data control. Unlike others, it doesn’t use folders—instead, all notes are searchable by tags and keywords and are saved in HTML format.

CnoT can be installed on Windows via Docker Desktop for offline note-taking or deployed on a Linux server using Docker for online access.

⚠️ The application has been optimized for use on a computer with a browser, not on a mobile phone (although it is possible to view your notes in landscape mode). 

## Features

The best way to see what it’s worth is to test it out.<p>

Here is a demo [link](https://cnot.timpoz.com/index.php) with the following password to access:

```
Iwanttotestcnot2024!
```

![image](https://github.com/user-attachments/assets/32093637-d338-4261-a28c-7f31bcc13599)

<details> <summary><strong>See all features here</strong></summary> <p>

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
- Export all notes in HTML format with an index and with images directly embedded in the HTML page for offline viewing or backup
- Trash with recoverable notes
- Filter by tags

</p> </details>

## Installation

1. **Clone the repository:**

   Create a folder (e.g., 'cnot') where you will store your Cnot instance and clone the repository.

2. **Navigate to the project directory:**
 
    ```
    cd cnot
    ```

3. **Create your configuration:**
    
   Copy the `env_template` file to a `.env` file.

   Edit the .env file with your settings, following the instructions provided in the file's comments.


5. **Add a SSL certificate for HTTPS:** 🌏

   Choose the setup that best suits your environment:

   <details> <summary><strong>Local Windows Installation with Docker Desktop</strong></summary> <p>

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

   </p> </details> <details> <summary><strong>Linux Server Installation with Docker</strong></summary> <p><p>
   Create fullchain.pem and privkey.pem for your domain and copy them to the ssl folder.
   
   </p> </details>

   
5. **Run the application:** 🚀
   
   ```
   docker compose up -d --build   
   ```

6. **Open the application:**

    Open your web browser and visit:

    `https://SERVER_NAME:YOUR-HTTPS-PORT`
   

7. **Connect to the application:**

    Connect with the password you provided in the .env file.

## Change settings

If you want to change: 

- APP_PASSWORD
- JOURNAL_NAME
- HTTPS_PORT

<details><summary>See instructions here</summary><p><p>
just update your .env file and run the application (docker compose up -d --build). This will restart the web container with the new .env config file. Your data are normally untouched but always make a backup first (see next section).
</p></details>

If you want to change: 

- ENTRIES_DATA_PATH
- DB_DATA_PATH

<details><summary>See instructions here</summary><p><p>
Update your .env file and run the application (docker compose up -d --build). ⚠️ This will create a new empty directory, so you won’t be able to access your previous data unless you re-import it (see next section).
</p></details>

If you want to change:

- database settings

<details><summary>See instructions here</summary><p><p>
simply updating the `.env` file and deleting the database container will not be enough, as the settings and data are stored in a volume. You will also need to delete the volume to recreate the database with the new settings, but this will result in data loss. To avoid losing your data, export the database contents first (see next section), then delete the `DB_DATA_PATH` volume. After running the application again to create a new database, you can re-import the data (see next section). 
</p></details>

## Backup and Restore

To be able to restore all your notes from a backup, you need only two things:

- Your notes exported as html files.
- A dump SQL of your database.

**To backup your notes:**

Export your notes directly from CnoT as a zip file for offline viewing:

![image](https://github.com/user-attachments/assets/04229b68-0f61-4e19-9c08-489d33357fcb)

Remember also that you can always access, access, copy or modify your notes from the ENTRIES_DATA_PATH directory defined in your .env config file.

**To backup the database:**

There are two ways to create a dump:

1. Using phpMyAdmin:

Connect with your MYSQL_USER and MYSQL_PASSWORD credentials (from your .env config file) to phpMyAdmin at https://SERVER_NAME:8074/ and export your database:

![image](https://github.com/user-attachments/assets/35543084-1bf1-48d5-9ce7-931b48d6863d)

2. Using Git Bash on Windows (preferred over PowerShell due to encoding issues) or bash on Linux:

Create temporarily another container to create a dump where you run the command:

  ```
   $ docker run --rm --network container:MYSQL_DATABASE -e MYSQL_PWD=MYSQL_ROOT_PASSWORD mysql:latest mysqldump -h127.0.0.1 -uroot MYSQL_DATABASE > dump.sql
   ```

**To restore :**

- Copy all the html notes into your ENTRIES_DATA_PATH
- Import your sql dump. Two ways :

  1. Import with Phpmyadmin.
  2. Copy your dump into your docker instance :

     ```
     $ docker cp dump.sql cnot_db:/tmp/dump.sql
     ```

     and enter your docker instance and import your dump :
     
     ```
      $ docker exec -it MYSQL_DATABASE bash
      bash-5.1# mysql -u root -pMYSQL_ROOT_PASSWORD MYSQL_DATABASE < /tmp/dump.sql
     ```

## Contributing 👩‍💻

If you want to contribute to the code, don't hesitate to open a pull request. Thanks!

## Possible errors ☢️

**Case 1**

 ```bash
BDD connection error : Connection refused
 ```

or 

 ```bash
Fatal error: Uncaught Error: Call to a member function execute()
 ```

Three possible reasons to this error:

1. The database is still initializing
3. It is a browser cache issue
4. The server runs out of memory
   
Wait a few seconds, visit another web page and come back.
