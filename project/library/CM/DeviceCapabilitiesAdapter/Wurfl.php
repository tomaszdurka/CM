<?php
require_once DIR_LIBRARY . 'Tera-Wurfl' . DIRECTORY_SEPARATOR . 'TeraWurfl.php';

class CM_DeviceCapabilitiesAdapter_Wurfl extends CM_DeviceCapabilitiesAdapter_Abstract {

	/**
	 * @var TeraWurfl $_wurfl
	 */
	private $_wurfl;

	/**
	 * @param string $userAgent
	 */
	public function __construct($userAgent) {
		$userAgent = (string) $userAgent;
		self::init();
		$this->_wurfl = new TeraWurfl();
		$this->_wurfl->getDeviceCapabilitiesFromAgent($userAgent);
	}

	/**
	 * @return array
	 */
	public function getCapabilities() {
		if (empty($this->_wurfl->capabilities)) {
			return array('mobile' => false, 'tablet' => false, 'hasTouchscreen' => false);
		}
		return array('mobile' => (boolean) $this->_wurfl->capabilities['product_info']['is_wireless_device'],
			'tablet' => (boolean) $this->_wurfl->capabilities['product_info']['is_tablet'],
			'hasTouschreen' => ($this->_wurfl->capabilities['product_info']['pointing_method'] == 'touchscreen'));
	}

	public static function init() {
		$config = CM_Config::get()->CM_Mysql;
		TeraWurflConfig::$DB_HOST = implode(':', $config->server);
		TeraWurflConfig::$DB_USER = $config->user;
		TeraWurflConfig::$DB_PASS = $config->pass;
		TeraWurflConfig::$DB_SCHEMA = $config->db;
		TeraWurflConfig::$TABLE_PREFIX = 'wurfl';
		TeraWurflConfig::$LOG_LEVEL = LOG_EMERG;
	}

	/**
	 *  xml file repository http://sourceforge.net/projects/wurfl/files/WURFL/
	 */
	public static function setup() {
		self::init();
		$zip = file_get_contents('http://heanet.dl.sourceforge.net/project/wurfl/WURFL/2.3/wurfl-2.3.xml.zip');
		$dataDir = DIR_LIBRARY . 'Tera-Wurfl' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
		$zipFile = CM_File::create($dataDir . 'wurfl.zip', $zip);
		$zipArchive = new ZipArchive();
		$zipArchive->open($zipFile->getPath());
		TeraWurflConfig::$WURFL_FILE = $zipArchive->getNameIndex(0);
		$zipArchive->extractTo($dataDir);
		$zipFile->delete();
		$xmlFile = new CM_File($dataDir . $zipArchive->getNameIndex(0));
		require(DIR_LIBRARY . 'Tera-Wurfl/admin/updatedb.php');
		$xmlFile->delete();
	}
}

class TeraWurflConfig {

	/**
	 * Database Hostname
	 * To specify the MySQL 5 TCP port or use a named pipe / socket, put it at the end of your hostname,
	 * seperated by a colon (ex: "localhost:3310" or "localhost:/var/run/mysqld/mysqld.sock").
	 * For MS SQL Server, use the format HOSTNAME\Instance, like "MYHOSTNAME\SQLEXPRESS".
	 * For MongoDB, enter a hostname or a MongoDB Connection String, like "mongodb:///tmp/mongodb-27017.sock,localhost:27017"
	 * @var String
	 */
	public static $DB_HOST = "localhost";
	/**
	 * Database User
	 * For MongoDB, this may be blank if authentication is not used
	 * @var String
	 */
	public static $DB_USER = "terawurfluser";
	/**
	 * Database Password
	 * For MongoDB, this may be blank if authentication is not used
	 * @var String
	 */
	public static $DB_PASS = 'wurfl';
	/**
	 * Database Name / Schema Name
	 * @var String
	 */
	public static $DB_SCHEMA = "tera_wurfl_demo";
	/**
	 * Database Connector (MySQL4, MySQL5, MSSQL2005, MongoDB)
	 * @var String
	 */
	public static $DB_CONNECTOR = "MySQL5";
	/**
	 * Prefix used for all database tables
	 * @var String
	 */
	public static $TABLE_PREFIX = "TeraWurfl";
	/**
	 * URL of WURFL File.  If you have multiple installations of Tera-WURFL, you can set this to a location on your network.
	 * @var String
	 */
	public static $WURFL_DL_URL = "http://downloads.sourceforge.net/project/wurfl/WURFL/latest/wurfl-latest.zip";
	/**
	 * URL of CVS WURFL File
	 * @var String
	 */
	public static $WURFL_CVS_URL = "http://wurfl.cvs.sourceforge.net/%2Acheckout%2A/wurfl/xml/wurfl.xml";
	/**
	 * Data Directory
	 * @var String
	 */
	public static $DATADIR = 'data/';
	/**
	 * Enable Caching System
	 * @var Bool
	 */
	public static $CACHE_ENABLE = true;
	/**
	 * Enable Patches (must reload WURFL after changing)
	 * @var Bool
	 */
	public static $PATCH_ENABLE = true;
	/**
	 * Filename of patch file.  If you want to use more than one, seperate them with semicolons.  They are loaded in order.
	 * ex: $PATCH_FILE = 'web_browsers_patch.xml;custom_patch_ver2.3.xml';
	 * @var String
	 */
	public static $PATCH_FILE = 'custom_web_patch.xml;web_browsers_patch.xml';
	/**
	 * Filename of main WURFL file (found in DATADIR; default: wurfl.xml)
	 * @var String
	 */
	public static $WURFL_FILE = 'wurfl.xml';
	/**
	 * Filename of Log File (found in DATADIR; default: wurfl.log)
	 * @var String
	 */
	public static $LOG_FILE = 'wurfl.log';
	/**
	 * Log Level as defined by PHP Constants LOG_ERR, LOG_WARNING and LOG_NOTICE.
	 * Should be changed to LOG_WARNING or LOG_ERR for production sites
	 * @var Int
	 */
	public static $LOG_LEVEL = LOG_WARNING;
	/**
	 * Enable to override PHP's memory limit if you are having problems loading the WURFL data like this:
	 * Fatal error: Allowed memory size of 67108864 bytes exhausted (tried to allocate 24 bytes) in TeraWurflLoader.php on line 287
	 * @var Bool
	 */
	public static $OVERRIDE_MEMORY_LIMIT = true;
	/**
	 * PHP Memory Limit.  See OVERRIDE_MEMORY_LIMIT for more info
	 * @var String
	 */
	public static $MEMORY_LIMIT = "768M";
	/**
	 * Enable the SimpleDesktop Matching Engine.  This feature bypasses the advanced detection methods that are normally used while detecting
	 * desktop web browsers; instead, most desktop browsers are detected using simple keywords and expressions.  When enabled, this setting
	 *  will increase performance dramatically (200% in our tests) but could result in some false positives.  This will also reduce the size
	 *  of the cache table dramatically because all the devices detected by the SimpleDesktop Engine will be cached in one cache entry.
	 * @var Bool
	 */
	public static $SIMPLE_DESKTOP_ENGINE_ENABLE = true;
	/**
	 * Allows you to store only the specified capabilities from the WURFL file.  By default, every capability in the WURFL is stored in the
	 * database and made available to your scripts.  If you only want to know if the device is wireless or not, you can store only the
	 * is_wireless_device capability.  To disable the filter, set it zto false, to enable it, you must set it to an array.  This array can
	 * contain the group names (if you want to include the entire group, i.e. "product_info") and/or capability names (if you want just a
	 * specific capability, i.e. "is_wireless_device").
	 *
	 * Usage Example:
	 * <code>
	 *	public static $CAPABILITY_FILTER = array(
	 *		// Complete Capability Groups
	 *		"product_info",
	 *
	 *		// Individual Capabilities
	 *		"max_image_width",
	 *		"max_image_height",
	 *		"chtml_make_phone_call_string",
	 *	);
	 * </code>
	 * @var Mixed
	 */
	public static $CAPABILITY_FILTER = false;
}
