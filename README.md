# CnoT Perso

- webserver + phpmyadmin dans le DNS (hostinger)
- webserver + phpmyadmin dans le Nginx Proxy Manager
- Créer les réseau docker qui seront nécessaires pour Grafana etc.
- Créer un compte grafana lecture sur la base de données de CNOT
- Déployer les projets ELK et monitoring

    ```
    git clone https://github.com/timothepoznanski/cnot.git
    cd cnot
    cp env_template .env
    vi .env
    ```
   
   ```
   docker compose -f docker-compose.yml -f docker-compose-reverse-proxy.yml up -d --build   
   ```

    `https://cnotperso.timpoz.com`

