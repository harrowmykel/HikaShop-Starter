<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?><?php
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';

require_once JPATH_LIBRARIES . '/cms.php';

require_once JPATH_CONFIGURATION . '/configuration.php';

$config = new JConfig;
define('JDEBUG', $config->debug);

class HikaShopCli extends JApplicationCli
{
	public function __construct(JInputCli $input = null, Registry $config = null, JEventDispatcher $dispatcher = null)
	{
		if(!$this->isCLI()) {
			$this->close('This is a command line only application.');
		} else {
			if(!isset($_SERVER['argv'])) {
				$_SERVER['argv'] = array(
					__FILE__
				);
			}
			if(!defined('STDIN'))
				define('STDIN', fopen('php://stdin', 'r'));
			if(!defined('STDOUT'))
				define('STDOUT', fopen('php://stdout', 'w'));
			if(!defined('STDERR'))
				define('STDERR', fopen('php://stderr', 'w'));
		}

		parent::__construct($input, $config, $dispatcher);
	}

	public function isCLI()
	{
		return ( defined('STDIN') || php_sapi_name()==="cli" || (stristr(PHP_SAPI , 'cgi') && getenv('TERM')) ) ? true : false;
	}

	public function doExecute()
	{
		$time = microtime(true);

		@set_time_limit(0);

		$_SERVER['HTTP_HOST'] = 'domain.com';
		JFactory::getApplication('site');


		$this->doCronTasks();

		$this->out(JText::sprintf('Cron task processed %s', round(microtime(true) - $time, 3)), true);
	}

	private function doCronTasks()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';

		$config = JFactory::getConfig();
		$config->set('caching', 0);
		$config->set('cache_handler', 'file');

		$hk_config =& hikashop_config();
		if($hk_config->get('cron') == 'no') {
			$this->out(JText::_('CRON_DISABLED'));
			return false;
		}

		try
		{
			$cronHelper = hikashop_get('helper.cron');
			$cronHelper->report = false;
			$launched = $cronHelper->cron();
			if($launched) {
				$cronHelper->report();

				foreach($cronHelper->messages as $msg) {
					$this->out($msg);
				}
				if(!empty($cronHelper->detailMessages)) {
					$this->out('---- Details ----');
					foreach($cronHelper->detailMessages as $msg) {
						$this->out($msg);
					}
				}
			}
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage(), true);

			$this->close($e->getCode());
		}
	}
}

JApplicationCli::getInstance('HikaShopCli')->execute();
