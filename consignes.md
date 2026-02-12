# 🏖️ Contexte

Le lido (restaurant de plage) Serena à besoin de vous. Le systeme ancestral de prise de commande (papier stylo ✏️) engendre de plus en plus d'**erreurs** et ralenti le service.

La direction souhaite donc se moderniser pour éviter ces erreurs, et aimerait se dotter de nouveaux outils de pilotage pour gérer le restaurant avec plus de précision notamment au niveau des stocks.

Pour cela elle fait appel à vous afin que vous leur proposiez une solution un peu plus dans l'air du temps ! La direction souhaite voir ses serveurs prendre les commandes via une **tablette**, afin d'**automatiser l'envoi** en cuisine et éviter les incompréhensions. Ces tablettes permettront également aux serveur d'enregistrer le payement (_fictif_) des clients.

La direction souhaite tout de même rester flexible, la carte change régulièrement et un nouvel outil ne doit pas être un obstacle au coeur de metier du restaurant. A ce titre elle vous demande de leur proposer une **interface administrateur** qui leur permettra de **modifier elle même** les _plats, menus, boissons_... proposés à la carte.

De plus elle souhaiterais avoir accès à un **tableau de bord** affichant des graphiques de plusieurs données (plats les plus commandés, jours de la semaine les plus fréquentés ...) afin de mieux comprendre les besoins de leurs clients et mieux piloter leur restaurant.

---

# ⚙️ Réalisation

Vous devrez réaliser une application web pour l'administration du systeme. Cette interface devra permettre d'ajouter, modifier, supprimer, des repas (entrée, plat, desserts boissont) et des menus.

Cette interface communiquera avec une base de données qui enregistrera toutes les modifications. Cette base de données accueillera plus tard les commandes et payements du restaurant. Essayez de prévoir les tables dont vous aurez besoin à l'avenir

Une ébauche de la partie dashboard est également attendu, ajoutez des graphs permettant de visualiser les données importantes du restaurant (CA par jour, plats les plus commandés, addition moyenne...).  Pour le moment vous pouvez vous baser sur des données fictives (mise **dans la BDD quand meme** !) pour afficher vos graphs

Pour les graphs utilisez au moins 3 types différents (pie chart, bar chart, line chart ...). Vous pouvez utiliser la librairie ***Chart.JS*** pour afficher simplement des graphs sur une page web.

L'interface nécéssite une connexion avec un **username et un mot de passe sécurisé**

⚠️ **Attention** les menus doivent être précis, on doit savoir ce qu'il est possible de mettre dans un menu. Par exemple : un menu _Pizza+Boisson_ ne peut pas contenir des pates. A vous de gérer ça.

La partie tablette sera à réaliser plus tard, pas besoin de s'y attelé pour le moment

# 📂 Rendu projet

- Le **code terminé**, sans bug et à jour
- Un fichier `readme.md` contenant
  - Les prérequis pour lancer les applications.
  - Les outils utilisés et pourquoi avoir choisi ceux là
  - Les problèmes rencontrés
  - L'explication des fonctionnalités et leur place dans le code
- Un repo **git propre**

# ☝ Conseils

Jusqu'au rendu je joue le role de votre _tech-lead_, **n'hésitez pas à me soliciter**, ne restez surtout pas bloqué pendant plusieurs heures. N'oubliez pas que pour le rendu je change de role, je deviens **le client**, donc les documents du rendu doivent **s'addresser à un client** (pas à un expert de l'informatique)

Avancez **pas à pas**, ne commencez pas 3 choses en même temps.

Faites des logs et **débuggez**

Prenez le temps de lire les erreurs et essayer de les comprendre avant de les copier-coller bêtement sur internet

Travailler en équipe **prend du temps** ! Prévoyez ce temps, mettre en commun le travail de 3 personnes ça ne se fait pas avec un simple copier coller. Pour vous simplifier la vie mettez le code en commun **régulièrement**, pas au moment d'envoyer le code.

**Communiquez** ! Il faut que vous sachiez qui travaille sur quoi, pour ne pas faire le travail en double ET pour que vos codes se complètent intelligement.
