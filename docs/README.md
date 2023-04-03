# Manuel d'installation

Nous partons du principe que le CMS Omeka est installé et configuré.

Si ce n'est pas le cas se référer au lien suivant : https://omeka.org/classic/docs/Installation/Installing/

Mise en place des plugins :

* Télécharger les archives `ChangeUsersRole.zip` et `ExportTypes.zip`
* Décompresser les dossiers
* Placer les dossiers dans 'omeka-x.x.x/plugins/' (les x représentant la version d'Omeka choisi)

# Création d'un plugin

La création d’un plugin Omeka comporte quelques contraintes afin de retrouver une certaine homogénéité dans la déclaration des plugins.

Il faut donc respecter au minimum trois éléments pour que le plugin soit accepté par Omeka :
+ un fichier `plugin.ini`
```
[info]
name="Change User Roles"
author="Aurélie Thuriot"
description="change privileges of admin (can create and edit a user) 
and contributor (can't delete item or collection)"
omeka_minimum_version="2.0"
version="1.0"
tags="user, roles, privileges"
```
+ un fichier .php avec un nom respectant ce modèle: `ChangeUserRolesPlugin.php `
```php
<?php
class ChangeUserRolesPlugin extends Omeka_Plugin_AbstractPlugin
{
	/**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array();
	
	 /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array();
}
```
+ le dossier contenant le plugin portant le même nom que celui donné au fichier ici `ChangeUserRoles`

Pour un plugin simple celà suffit, cependant en cas de création d’un plugin plus complexe (comprenant de nouvelles pages
etc…) une arborescence particulière est imposée. (celle-ci est expliquée dans la documentation développeur référencée 
dans la partie ci-dessous).


# Ressources
## Documentation existantes

La documentation développeur d’Omeka : https://omeka.readthedocs.io/en/latest/index.html

La documentation utilisateur d’Omeka : https://omeka.org/classic/docs/

La documentation développeur Omeka comprend plusieurs problèmes. Une grande partie des fonctions disponibles n’ont pas d’exemple et certains exemples ne sont pas complets.

```php
public function filterPublicNavigationMain($nav){
    $nav[] = array(
            'label' => __('My plugin'),
            'uri' => url('my-plugin)
    );
    
    return $nav;
}
```
Par exemple, dans cette fonction il est aussi possible de déclarer une ‘ressource’ pour l’item à ajouter dans le menu. 
Cette ressource permettra d’ajouter l'élément dans la liste des items ayant des droits. 
Il sera donc possible de modifier les droits de l’item en fonction de l’utilisateur.

Le problème vient du fait que la documentation est rédigée par les utilisateurs et que la dernière mise à jour date de 2014.
Il est donc important de vérifier dans d’autres plugins que les informations sont à jour, de préférence avec les plugins
développés par l’équipe d’Omeka.

## Ressources supplémentaires

J’ai principalement recherché des plugins ayant le même comportement ou un comportement similaire à celui que
je voulais mettre en place. Je pouvais ensuite regarder comment ça avait été codé. Si plusieurs plugins ont
un comportement similaire à celui recherché, je comparais leurs codes pour comprendre les subtilités des fonctions et
parfois détecter la raison pour laquelle ça ne fonctionnait pas.

Par exemple, les items du menu dans plusieurs plugins n’étaient pas déclarés complètement (certaines variables ayant été
oubliées), ce qui empêchait souvent la mise en place des droits sur ceux-ci.

# Les Plugins
## ChangeUsersRole

Ce plugin a pour objectif la modification des droits des rôles administrateur et contributeur, ainsi que la simplification
de l'interface pour le contributeur (suppression d'élément du menu).

### Fonctions omeka utilisées
#### Hooks
* `definel_acl` 

Permet la définition des droits des différents types d'utilisateurs sur les pages et objets composant la gallerie.


> Nouvelles ressources : 
> - Page Invité et index Invité (= élément dans le menu administrateur)
> - Page IIIF
>
> Administrateur :
> - peut ajouter ou modifier un utilisateur
> - ne peut pas supprimer un utilisateur
> - ne peut pas rentre un utilisateur super administrateur
> 
> Contributeur : 
> - ne peut pas rendre public, mettre en avant ou supprimer ses items
> - ne peut pas rendre public, mettre en avant ou supprimer ses collections 
> - n'a plus accès au page IIIF Toolkit et Invités

Exemple :

```php
 public function hookDefineAcl($args)
 {
    $acl = $args['acl'];
    $acl->allow('admin', array('Users')); 
    $acl->deny('contributor','Items',array('deleteSelf','makePublic','makeFeatured'));
 }     
```


* `install`

Permet de définir le comportement du plugin à l'installation de celui-ci.

> Il se passe deux choses à l'installation de notre plugin.
> Tous d'abord on récupère le code source des pages addCollection et editCollection que nous plaçons dans les fichiers 
> editCollectionInit et addCollectionInit afin de conserver le fonctionnement initial. Ensuite on réécrit le fichier 
> en utilisant le code stocker dans les fichiers addCollection et editCollection du plugin. (le contenu de ces fichiers
> est explicité un peu plus tard)

* `uninstall`

Permet de modifier le comportement de l'application avant la désinstallation du plugin.

> La fonction de désinstallation nous permet de remettre en place le fonctionnement initial du CMS. Pour celà on récupère
> le code stocké dans addCollectionInit et editCollectionInit et on le réécrit dans les fichiers sources correcpondant.

* `before_save_user`

Permet de modifier le comportement du CMS avant la modification ou l'ajout d'un utilisateur.

> La fonction nous permet d'empêcher un administrateur de changer le rôle d'un super administrateur. Pour celà nous avons
> comparer le rôle de l'utilisateur à modifier stocké en base de données et le rôle attribué dans le form. Si l'utilisateur 
> était super administrateur et que la personne essaye de lui enlever ce rôle nous modifions les données à envoyer à la base
> de données afin qu'il conserve son rôle de super administrateur.

#### Filters

* `admin_navigation_main`

Permet l'ajout d'élément dans le menu de navigation de la partie administration de la galerie Omeka.

> Le contributeur n'a plus accès à la page des utilisateurs invité ni à la page concernant les IIIF.
> Pour celà il a fallu récupérer les deux éléments dans le menu de navigation ($navLink) afin de leur attribuer
> une ressource. Cette ressource sera jouté dans la fonction `define_acl` aux ressources disponible de site. Il sera
> donc possible d'en modifier les droits d'accès.

Exemple : 

```php
public function filterAdminNavigationMain($navLinks)
    {
        $navLinks['Guest User'] = array('label' => __("Guest Users"),
                                        'uri' => url("guest-user/user/browse?role=guest"),
									   'resource' => 'Guest_Index',
									   'privilege' => 'browse');
        return $navLinks;
    }
```




### Override de fichier source

Nous avons découvert des problèmes de gestions des droits dans les fichiers Omeka. En effet, la gestion des droits pour 
mettre en avant les oeuvres ou les rendre public sur le site existent, mais on ne retrouve pas cette gestion pour les collections
qui sont par définition un regroupement d'oeuvres.

Cet oubli étant dans un fichier source et ne permettant aucune modification directe, il a été décidé d'override ceux-ci 
afin d'ajouter la ligne manquante. 

### Fichiers

#### addCollection.php

Ce fichier contient la version corrigée du fichier source d'Omeka addCollection. Pour celà nous avons ajouté la ligne 
`<?php if ( is_allowed('Collections', 'makePublic') && is_allowed('Collections', 'makeFeatured')): ?>`
permettant d'afficher ou non les checkbox. Celles-ci seront affichées lors de la création d'une collection uniquement 
si l'utilisateur a les droits makeFeatured et makePublic sur les collections.

#### addCollectionInit.php

Ce fichier contient une copie du fichier source addCollection avant la modification de celui-ci. Il permettra la récupération
du fonctionnement initial du CMS à la désinstallation du plugin.


#### editCollection.php

Ce fichier contient la version corrigée du fichier source d'Omeka editCollection. Pour celà nous avons ajouté la ligne 
`	<?php if ( is_allowed('Collections', 'makePublic') && is_allowed('Collections', 'makeFeatured')): ?>`permettant 
d'afficher ou non les checkbox. Celles-ci seront affichées lors de la modification d'une collection uniquement 
si l'utilisateur a les droits makeFeatured et makePublic sur les collections. 

#### editCollectionInit.php

Ce fichier contient une copie du fichier source editCollection avant la modification de celui-ci. Il permettra la récupération 
du fonctionnement initial du CMS à la désinstallation du plugin.

## ExportTypes

Ce plugin a pour objectif l'ajout d'un système d'exportation disponible pour les utilisateurs du site public. Il permettra
quatre typees d'export : 
* QR Code
* Impression
* PDF
* Lien (Url)

### JavaScript

Ajouter le bouton permettant l'export de la recherche effectuée ne nous était pas permis par les diverses fonctions d'Omeka 
disponibles. Il a donc fallu contourner le problème en ajoutant le bouton grâce à du JavaScript au chargement de la page.
Pour celà nous avons appelé le script JavaScript depuis le fichier php obligatoire au bon fonctionnement du plugin.

Par la suite il a été décidé d'effectuer toutes les modifications du plugin dans le fichier JavaScript. En effet, les
modifications ne pouvaient pas être faite au travers de fonctions définies par Omeka. Il nous a donc paru plus propre 
de placer les quelques fonctions nécessaire au fonctionnement du plugin dans le même fichier afin de garder une certaine 
cohérence et pour ne pas les éparpiller inutilement.

### Fonctions 

* `window.onload`

    Permet la création du bouton qui affichera les types d'export ainsi que la modale et son contenu, puis de les 
ajouter au chargement de la fenêtre.

(techniquement fil d'ariane aussi a voir si on le fini)

* `popUpShow()`

    Permet l'affichage et la disparition de la pop Up permettant de choisir le type d'export voulu.

* `exportShow(idPopUp)`
    
    Permet l'affichage de la modale d'export dont l'id est passé en paramètre. Permet aussi la modification de certains
éléments en fonction de la modale choisie (génération QR Code, ajout de l'url)
  
  La génération de QRCode se fait grâce à la librairie `qr-code-styling.js` celle-ci permet de passer des paramètres qui 
changeront la couleur, l'aspect du QR Code et permet l'ajout d'un logo en son centre.

* `downloadQRCode`

    Permet le téléchargement du QR Code au format png.

* `downloadPDF`
    
    Permet l'ouverture de la fenêtre d'impression.

  La génération du PDF se fait grâce à la librairie `JsPDF`.

* `copy`
    
    Permet la copie de l'url dans le presse-papier de l'ordinateur.


### Fichiers

#### ExportTypes.html

Comporte le code html des différentes modales d'exportation, ainsi que l'import des librairies utilisées.

#### ExportTypes.js

Comporte l'intégralité du code source du plugin.

#### ExportTypes.css

Comporte le code `CSS` des pop up ainsi que la gestion du @print afin de supprimer les éléments inutiles à l'impression. 
Les autres éléments ajoutés utilisent les classes `CSS` déjà présentes dans le reste du code html présent dans le code d'Omeka 
afin que les éléments visuels restent cohérents avec le reste de la galerie.

