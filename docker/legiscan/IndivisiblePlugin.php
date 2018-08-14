<?php
/**
 * @copyright 2010-2017 LegiScan LLC
 * 
 */

require_once(__DIR__ . '/' . 'LegiScan.php');

class Indv_Plugin_Pull
{
	// {{{ Class Variables

	/**
	 * LegiScan API key for requests from `config.php`
	 *
	 * @var string Assigned 32 character API hash
	 * @access private
	 */
	protected $api_key;

	/**
	 * Raw response from an API call
	 *
	 * @var string
	 * @access private
	 */
	protected $response;

	/**
	 * Decoded associative array representing the API response
	 *
	 * @var mixed[]
	 * @access private
	 */
	protected $payload;

	/**
	 * Cache layer interface
	 *
	 * @var LegiScan_Cache_File
	 * @access private
	 */
	protected $cache;

	// }}}

	// {{{ __construct()
	/** 
	 * Class constructor that nominally validates the API key
	 *
	 * @throws APIException
	 *
	 * @param string $api_key
	 *   (**OPTIONAL**) Override the api_key from LegiScan::getConfig()
	 *
	 */
	function __construct()
	{
		$this->cache = new LegiScan_Cache_File('api');

		$this->api_key = LegiScan::getConfig('api_key');

		// Sanity checks to avoid sending junk requests
		if (!preg_match('/^[0-9a-f]{32}$/i', $this->api_key))
			throw new APIException('Invalid API key');
	}
	// }}}


	// {{{ apiRequest()
	/** 
	 * Makes the actual request to the LegiScan API server via cURL
	 *
	 * @throws APIException
	 *
	 * @param string $op
	 *   The API operation to actually perform
	 * 
	 * @param array $params
	 *   An associative array of the required parameters to perform the
	 *   needed API operation
	 * 
	 * @return mixed[]
	 *   An associative array representing the API response
	 */
	public function apiRequest($params)
	{
		// Merge in the base parameters
		$query = array_merge($params, array('key'=>$this->api_key));
		$op = $query['op'];
		$query_string = http_build_query($query);

		$url = 'https://api.legiscan.com/?' . $query_string;

		$cache_file = $this->getCacheFilename($op, $params);
		$this->response = $this->cache->get($cache_file);

		if (!$this->response)
		{
			// Initialize curl and make the real request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_BUFFERSIZE, 64000);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, "Indivisible Plugin API Client " . LegiScan::VERSION);

			//LegiScan::fileLog("Requesting $url");
			$this->response = curl_exec($ch);

			// See if something drastic happened
			if ($this->response === false)
				throw new APIException('Could not get response from LegiScan API server');

			$this->cache->set($cache_file, $this->response);
		}

		return $this->response;
	}
	// }}}

	// {{{ getCacheFilename()
	/**
	 * Generate an API cache filename
	 *
	 * @param string $op
	 *   The API operation hook name
	 *
	 * @param array $params
	 *   The API paramaters that were part of the call
	 * 
	 * @return string
	 *   Filename path fragment for the cache location under cache root
	 */
	function getCacheFilename($op, $params)
	{
		$op = strtolower($op);

		switch ($op)
		{
			case 'getbill':
				$filename = 'bill/' . $params['id'] . '.json';
				break;
			case 'getperson':
				$filename = 'people/' . $params['id'] . '.json';
				break;
			case 'getrollcall':
				$filename = 'rollcall/' . $params['id'] . '.json';
				break;
			case 'getbilltext':
				$filename = 'text/' . $params['id'] . '.json';
				break;
			case 'getamendment':
				$filename = 'amendment/' . $params['id'] . '.json';
				break;
			case 'getsupplement':
				$filename = 'supplement/' . $params['id'] . '.json';
				break;
			case 'getsessionlist':
				$filename = 'sessionlist/' . $params['state'] . '.json';
				break;
			case 'getmasterlist':
				// If state is present prefer that
				if (isset($params['state']))
					$filename = 'masterlist/' . $params['state'] . '.json';
				else
					$filename = 'masterlist/' . $params['id'] . '.json';
				break;
			case 'search':
			case 'searchraw':
				$chunks = array();
				if (isset($params['state']))
					$chunks[] = $params['state'];
				if (isset($params['raw']))
					$chunks[] = 'raw';
				if (isset($params['bill']))
					$chunks[] = $params['bill'];
				if (isset($params['year']))
					$chunks[] = 'y' . $params['year'];
				if (isset($params['page']))
					$chunks[] = 'p' . $params['page'];
				if (isset($params['query']))
					$chunks[] = $params['query'];

				$file_chunk = strtolower(implode('_', $chunks));

				// NOTE: To maintain some sort of readability to the file name
				// do a quick transformation.
				$file_chunk = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file_chunk);
				$file_chunk = mb_ereg_replace("([\.]{2,})", '', $file_chunk);
				// Very long searches will break max filename length, check and adjust
				// accounting for extension
				if ((strlen($file_chunk) + 5) > 255)
					$file_chunk = substr($file_chunk, 0, 250);

				$filename = 'search/' . $file_chunk . '.json';
				break;
			default:
				throw new APIException("Cannot determine Pull API cache file for $op");
				break;
		}

		return $filename;
	}
	// }}}
}
// }}}
