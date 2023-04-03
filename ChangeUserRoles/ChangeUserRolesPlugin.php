<?php
class ChangeUserRolesPlugin extends Omeka_Plugin_AbstractPlugin
{
	/**
     * @var liste des hook pour le plugin
     */
    protected $_hooks = array(
        'define_acl',
		'install',
		'uninstall',
		'before_save_user'
    );
	
	 /**
     * @var liste des filtres pour le plugin
     */
    protected $_filters = array('admin_navigation_main');


	/*
	* Permet de définir les droits des utilisateurs et leurs accès aux ressources
	*/
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];

        $acl->allow('admin', array('Users'));
        $acl->deny('admin','Users',array('delete'));
        $acl->deny('contributor','Items',array('deleteSelf','makePublic','makeFeatured'));
        $acl->deny('contributor','Collections',array('deleteSelf','makePublic','makeFeatured'));
		
		$acl->deny('admin', 'Users', 'makeSuperUser');

	
		$acl->deny(null,'Users','editSuper');
		$acl->allow('super','Users','editSuper');
		
		$indexResource = new Zend_Acl_Resource('Guest_Index');
		$pageResource = new Zend_Acl_Resource('Guest_Page');
		
		$indexResourceIIIF = new Zend_Acl_Resource('IIIF_Toolkit');
		$acl->add($indexResource);
		$acl->add($pageResource);
		$acl->add($indexResourceIIIF);
		
		$acl->deny(null,array('Guest_Index',
							'IIIF_Toolkit',
							'Guest_Page'));
		$acl->allow(array('admin','super'),
					array('Guest_Index',
						  'IIIF_Toolkit',
						  'Guest_Page'));

        $acl->allow(null, 'Guest_Page', 'show');
        $acl->deny(null, 'Guest_Page', 'show-unpublished');
    }
	
	/*
	* Permet la déclaration d'items dans le menu administrateur
	*/
	public function filterAdminNavigationMain($navLinks)
    {
        $navLinks['Guest User'] = array('label' => __("Guest Users"),
                                        'uri' => url("guest-user/user/browse?role=guest"),
									   'resource' => 'Guest_Index',
									   'privilege' => 'browse');
		
		$navLinks['IIIF Toolkit'] = array('label' => __("IIIF Toolkit"),
                                        'uri' => url('iiif-items/import'),
									   'resource' => 'IIIF_Toolkit',
									   'privilege' => 'browse');
		
        return $navLinks;
    }
	
	
	/*
	* Permet la modification de la gestion des droits sur les collections
	*/
    public function hookInstall()
    {

		$toSave = file_get_contents(getcwd().'/themes/default/collections/edit.php');
		$file = __DIR__."/editCollectionInit.php";
		file_put_contents($file, $toSave);
		
		$toReturn = file_get_contents(__DIR__."/editCollection.php");
		file_put_contents(getcwd().'/themes/default/collections/edit.php', $toReturn);
		
		$toSaveTwo = file_get_contents(getcwd().'/themes/default/collections/add.php');
		$fileTwo = __DIR__."/addCollectionInit.php";
		file_put_contents($fileTwo, $toSaveTwo);
		
		$toReturn = file_get_contents(__DIR__."/addCollection.php");
		file_put_contents(getcwd().'/themes/default/collections/add.php', $toReturn);
    }	
	
	
	/*
	* Permet de remettre la gestion des droits initial sur les collections
	*/
	  public function hookUninstall()
    {
		$toReturn = file_get_contents(__DIR__."/editCollectionInit.php");
		file_put_contents(getcwd().'/themes/default/collections/edit.php', $toReturn);
		
		$toReturn = file_get_contents(__DIR__."/addCollectionInit.php");
		file_put_contents(getcwd().'/themes/default/collections/add.php', $toReturn);
    }
	
	
	/*
	* Empêche un administrateur de changer le rôle d'un super administrateur
	* @param args : contient trois éléments 
	* 	record : les informations affiché dans le formulaire
	*   post : les informations qui seront envoyé en bdd après l'éxécution de la fonction
	*   insert : un boolean permettant de savoir si il s'agit d'une insertion ou d'une modification d'un utilisateur
	*/
	public function hookBeforeSaveUser($args){		
		if(!$args['insert']){
			$record = $args['record'];
			$data = $args['post'];
		
			$db = Zend_Controller_Action_HelperBroker::getStaticHelper('Db');
			$user = $db->findById($record['id'],'User');
			
			if($user['role']=== 'super' && $record['role'] !== 'super'){
				$message = __("Vous ne pouvez pas modifier le role d'un super administrateur");
        		$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        		$flash->addMessage($message, 'error');
				
				$record['role'] = 'super';
				$data['role'] = 'super';
			}
		}
	}
	
}

