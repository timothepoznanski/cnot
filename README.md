# CnoT

Welcome to the CnoT Project ! 👋

CnoT is a note taking web app. 📝

Among the many note taking tools already available, it's not always easy to find one that suits our needs. Options are either overloaded with features or lack essentials. CnoT was therefore designed with an emphasis on simplicity and the essentials for effective note taking management.

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

To get started, follow these steps:

1. **Clone the repository:**

    ```bash
    git clone https://github.com/timothepoznanski/cnot.git
    ```

2. **Navigate to the project directory:**
 
    ```bash
    cd cnot
    ```

3. **Create your configuration:**
    
    Copy the `env_template` file to a `.env` file.
    
    Modify the `.env` file with your settings following instructions commented in the file.


4. **Add your SSL certificate and private key for HTTPS:** 🌏
    
    Add your `privkey.pem` and `fullchain.pem` files to the `ssl` folder. 
    
    ⚠️ They have to be named exactly `privkey.pem` and `fullchain.pem`. The private key should not have a passphrase.

   To create them :

   ```bash
   openssl genpkey -algorithm RSA -out privkey.pem
   openssl req -new -key privkey.pem -out demande_csr.csr
   openssl x509 -req -days 365 -in demande_csr.csr -signkey privkey.pem -out fullchain.pem
   ```
   
6. **Run the application:** 🚀
   
     ```bash
    docker compose up -d --build

    or

    podman-compose up -d --build
   
    ```

    Now, the CnoT application should be up and running. 

7. **Open the application:**

    Open your web browser and visit:

    `https://YOUR-SERVER-DOMAIN:YOUR-HTTPS-PORT`

8. **Connect to the application:**

    Connect with the password you provided in the .env file.

## Updates

If you want to change your password or your server name, stop and remove your running web container (don't worry, your data are stored on your host), update your .env file and run the step 5 (Run the application). This will launch a new web container from the image already present on your host but will also use the new .env config file.

If you want to change configs that are related to the database, you will have to stop and remove your running web and databases containers (don't worry, your data are stored on your host), update your .env file and run the step 5 (Run the application).

## Backup

Export your notes from inside CnoT as a zip file for offline viewing.

If you want to be able to restore your notes from a backup, you need : 

- Your notes exported or access to your ENTRIES_DATA_PATH path where you will find all your html notes.
- A dump of your database. Here is how to create a dump for a local CnoT instance running on Docker Desktop :

  ```bash
   $ docker run --rm --network container:MYSQL_DATABASE mysql:latest mysqldump -h127.0.0.1 -uroot -pMYSQL_ROOT_PASSWORD MYSQL_DATABASE > 'C:\Users\XXXXXX\Desktop\dump.sql'
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

**Case 2**

You cannot open the application or you have been able to run it but you have problems saving your notes. 
Are you sure you did step 4 ?
