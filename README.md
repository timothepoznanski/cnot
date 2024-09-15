# CnoT

Welcome to the CnoT Project ! 👋

CnoT is a note taking web app. 📝

Among the many note taking tools already available, it's not always easy to find one that suits our needs. Options are either overloaded with features or lack essentials. CnoT was therefore designed with an emphasis on simplicity and the essentials for effective note taking management.

⚠️ CnoT is designed for offline use on a single computer, and it does not synchronize across multiple devices. Although it can be installed on a server and accessed over the internet, we do not recommend this approach due to its limited security, as it is protected only by a password. For enhanced security, we suggest installing CnoT on Docker Desktop for Windows and accessing it locally through a web browser. This method ensures better protection for your notes. Therefore, the following procedure applies within the context of this recommendation (Docker Desktop on Windows).

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

   Create a folder (for instance 'CNOT') where you will store your project.

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

   With a Notepad, modify the `.env` file with your settings following instructions commented in the file.


5. **Add your SSL certificate and private key for HTTPS:** 🌏

   Run the following command to create a ssl certificate :

   ```
   openssl req -x509 -out ssl/fullchain.pem -keyout ssl/privkey.pem -newkey rsa:2048 -nodes -sha256 -days 36500 -subj "/CN=localhost/O=CNOT" -addext "subjectAltName=DNS:localhost" -addext "keyUsage=digitalSignature" -addext "extendedKeyUsage=serverAuth"
   ```

   Install the fullchain.pem into the Web browser certificate store (🔧 I need to write more details about this part).

   Restart your web browser.

   
5. **Run the application:** 🚀
   
   ```
   docker compose up -d --build   
   ```

6. **Open the application:**

    Open your web browser and visit:

    `https://localhost:YOUR-HTTPS-PORT`
   

7. **Connect to the application:**

    Connect with the password you provided in the .env file.

## Updates

If you want to change a setting for example your application password, stop and remove your running web container (don't worry, your data are stored on your host), update your .env file and run the application. This will launch a new web container from the image already present on your host but will also use the new .env config file.

If you want to change configs that are related to the database, you will have to stop and remove your running web and databases containers (don't worry, your data are stored on your host), update your .env file and run the application.

## Backup and Restore

Export your notes from inside CnoT as a zip file for offline viewing.

If you want to be able to restore your notes from a backup, you need : 

- Your notes exported or access to your ENTRIES_DATA_PATH path where you will find all your html notes.
- A dump of your database.

Two ways to create a dump for a local CnoT instance running on Docker Desktop :

1. Connect to phpmyadmin at http://localhost:8074/ and export your database.

2. With git bash (I got enconding problems with powershell), run another container to create a dumb :

  ```
   $ docker run --rm --network container:MYSQL_DATABASE mysql:latest mysqldump -h127.0.0.1 -uroot -pMYSQL_ROOT_PASSWORD MYSQL_DATABASE > 'C:\Users\XXXXXX\Desktop\dump.sql'
   ```
To restore : 

- Copy all the html notes into your ENTRIES_DATA_PATH
- Import your sql dump. Two ways :

  1. Import with Phpmyadmin.
  2. Copy your dump into your docker instance :

     ```bash
     $ docker cp /c/Users/XXXXX/Desktop/dump.sql MYSQL_DATABASE:/tmp/dump.sql
     ```

     and enter your docker instance and import your dump (example with git bash) :
     
     ```bash
      $ winpty docker exec -it MYSQL_DATABASE bash
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
