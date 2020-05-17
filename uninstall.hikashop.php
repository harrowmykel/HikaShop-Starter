<?php

function com_uninstall(){
	$uninstallClass = new hikashopUninstall();
	$uninstallClass->unpublishModules();
	$uninstallClass->unpublishPlugins();
}
class hikashopUninstall{
	var $db;
	function hikashopUninstall(){
		$this->db = JFactory::getDBO();
		$this->db->setQuery("DELETE FROM `#__hikashop_config` WHERE `config_namekey` = 'li' LIMIT 1");
		$this->db->execute();

		$this->db->setQuery("DELETE FROM `#__menu` WHERE link LIKE '%com_hikashop%'");
		$this->db->execute();
	}
	function unpublishModules(){
		$this->db->setQuery("UPDATE `#__modules` SET `published` = 0 WHERE `module` LIKE '%hikashop%'");
		$this->db->execute();
	}
	function unpublishPlugins(){
		$this->db->setQuery("UPDATE `#__extensions` SET `enabled` = 0 WHERE `type` = 'plugin' AND `element` LIKE '%hikashop%' AND `folder` NOT LIKE '%hikashop%'");
		$this->db->execute();
	}
}