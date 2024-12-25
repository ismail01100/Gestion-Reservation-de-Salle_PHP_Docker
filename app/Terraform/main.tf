
#is
# Specify the required provider
terraform {
  required_providers {
    docker = {
      source = "kreuzwerker/docker"  # Correct provider source
      version = "~> 3.0"             # Specify the version you want to use
    }
  }
}

# Fournisseur Docker
provider "docker" {
  host = "unix:///var/run/docker.sock"
}

# Réseau Docker
resource "docker_network" "mysql_network" {
  name = "mysql_network"
}

# Image MySQL
resource "docker_image" "mysql_image" {
  name         = "mysql:8.0"  # Spécification de la version de l'image
  keep_locally = false
}

# Conteneur MySQL
resource "docker_container" "mysql_container" {
  name  = "mysql_container"
  image = docker_image.mysql_image.name  # Utilisation de l'image MySQL définie plus haut

  networks_advanced {
    name = docker_network.mysql_network.name
  }

  env = [
    "MYSQL_ROOT_PASSWORD=rootpassword",   # Mot de passe root
    "MYSQL_DATABASE=res_db",              # Base de données
    "MYSQL_USER=reservation_user",        # Utilisateur
    "MYSQL_PASSWORD=reservation_password" # Mot de passe utilisateur
  ]

  ports {
    internal = 3306
    external = 3307  # Port externe modifié
  }

  volumes {
    host_path      = "/mnt/c/xampp/htdocs/projet-GDS/app/init.sql"  # Chemin absolu de init.sql
    container_path = "/docker-entrypoint-initdb.d/init.sql"
  }
}

# Image PhpMyAdmin
resource "docker_image" "phpmyadmin_image" {
  name         = "phpmyadmin:latest"  # Utilisation de "latest" car c'est souvent utilisé pour PhpMyAdmin
  keep_locally = false
}

# Conteneur PhpMyAdmin
resource "docker_container" "phpmyadmin_container" {
  name  = "phpmyadmin_container"
  image = docker_image.phpmyadmin_image.name  # Utilisation de l'image PhpMyAdmin définie plus haut

  networks_advanced {
    name = docker_network.mysql_network.name
  }

  env = [
    "PMA_HOST=mysql_container",         # Hôte MySQL
    "PMA_USER=root",                    # Utilisateur root
    "PMA_PASSWORD=rootpassword"         # Mot de passe root
  ]

  ports {
    internal = 80
    external = 8082  # Port externe modifié
  }
}
