# CnoT

Welcome to the CnoT Project ! 👋 CnoT is a note taking web app. 📝

Among the many note taking tools already available, it's not always easy to find one that suits our needs. Options are either overloaded with features or lack essentials. CnoT was therefore designed with an emphasis on simplicity and the essentials for effective note taking management. The key difference is that we don’t organize notes into folders. Instead, all notes are searchable using multiple tags and keywords. 

⚠️ CnoT is designed for **offline** use on a **single computer**, and it does not synchronize across multiple devices. Although it can be installed on a server and accessed over the internet, we do not recommend this approach due to its limited security, as it is protected only by a password. For enhanced security, we suggest installing CnoT on Docker Desktop for Windows and accessing it locally through a web browser. This method ensures better protection for your notes. Therefore, the following procedure applies within the context of this recommendation (Docker Desktop on Windows). That said, if you still prefer to install it on a server, it functions smoothly. 

## Screenshot

![screen](https://github.com/user-attachments/assets/e7332ba1-7f48-43ff-99b4-44a63ba5b0df)

CnoT remains simple but covers the essentials. The idea was to have a clean interface and to no longer organize notes in a hierarchical structure.

**Features:**

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

## Installation

1. **Clone the repository:**

   Create a folder (e.g., 'CNOT') where you will store your project.

   Open powershell in this folder and clone the project : 

    ```
    git clone https://github.com/timothepoznanski/cnot.git
    ```
    or
   
    ```
    git clone git@github.com:timothepoznanski/cnot.git
    ```

2. **Navigate to the project directory:**
 
    ```
    cd cnot
    ```

3. **Create your configuration:**
    
   Copy the `env_template` file to a `.env` file.

   Using Notepad, edit the .env file with your settings, following the instructions provided in the file's comments.


5. **Add a SSL certificate for HTTPS:** 🌏

   Run the following command to create a ssl certificate :

   ```
   openssl req -x509 -out ssl/fullchain.pem -keyout ssl/privkey.pem -newkey rsa:2048 -nodes -sha256 -days 36500 -subj "/CN=localhost/O=CNOT" -addext "subjectAltName=DNS:localhost" -addext "keyUsage=digitalSignature" -addext "extendedKeyUsage=serverAuth"
   ```

   Here's how you can install the fullchain.pem into your web browser's certificate store:

   On Chrome (I haven't tried other web browsers):
   
   - Open chrome://settings/security.
   - Go to Manage certificates.
   - Navigate to the "Trusted Root Certification Authorities" tab.
   - Click Import.
   - Locate the .pem file generated earlier (it won’t appear unless you select "All Files").
   - Restart your web browser.

   
5. **Run the application:** 🚀
   
   ```
   docker compose up -d --build   
   ```

6. **Open the application:**

    Open your web browser and visit:

    `https://localhost:YOUR-HTTPS-PORT`
   

7. **Connect to the application:**

    Connect with the password you provided in the .env file.

## Change settings

If you want to change: 

- APP_PASSWORD
- JOURNAL_NAME
- HTTPS_PORT

stop and remove your running web container (don't worry, your data are stored on your host), update your .env file and run the application. This will launch a new web container from the image already present on your host but will also use the new .env config file.

If you want to change: 

- ENTRIES_DATA_PATH
- DB_DATA_PATH

Update your .env file and run the application. ⚠️ This will create a new empty directory, so you won’t be able to access your previous data unless you re-import it.

If you want to change database settings, simply updating the `.env` file and deleting the database container will not be enough, as the settings and data are stored in a volume. You will also need to delete the volume to recreate the database with the new settings, but this will result in data loss. To avoid losing your data, export the database contents first, then delete the `DB_DATA_PATH` volume. After running the application again to create a new database, you can re-import the data. 


## Backup and Restore

To be able to restore your notes from a backup, you need:

- Your notes exported.
- A dump of your database.

**To backup your notes:**

Export your notes from inside CnoT as a zip file for offline viewing.

Alternatively, you can access your HTML notes in the ENTRIES_DATA_PATH directory.

**To backup the database:**

There are two ways to create a dump for a local CnoT instance running on Docker Desktop:

1. Using phpMyAdmin:

Connect to phpMyAdmin at http://localhost:8074/ and export your database.

2. Using Git Bash (preferred over PowerShell due to encoding issues):

Run another container to create a dump.

  ```
   $ docker run --rm --network container:MYSQL_DATABASE mysql:latest mysqldump -h127.0.0.1 -uroot -pMYSQL_ROOT_PASSWORD MYSQL_DATABASE > 'C:\Users\XXXXXX\Desktop\dump.sql'
   ```

**To restore :**

- Copy all the html notes into your ENTRIES_DATA_PATH
- Import your sql dump. Two ways :

  1. Import with Phpmyadmin.
  2. Copy your dump into your docker instance :

     ```
     $ docker cp C:\Users\XXXXX\Desktop\dump.sql cnot_db:/tmp/dump.sql
     ```

     and enter your docker instance and import your dump :
     
     ```
      $ docker exec -it MYSQL_DATABASE bash
      bash-5.1# mysql -u root -ppoiuytreza MYSQL_DATABASE < /tmp/dump.sql
     ```

## Contributing 🙏

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
