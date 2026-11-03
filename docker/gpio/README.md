Valeurs des entrées GPIO simulées (une par fichier, nom = numéro BCM, contenu 0 ou 1, défaut 1).
Ex. osmolateur : 23 = capteur "trop haut" (0 par défaut ici = normal), 24 = "niveau OK" ; écumeur : 18 ; bailling : 8, 13, 22.
Lu par le service GPIO (conteneur aqua-gpio) ; état courant : make gpio.
