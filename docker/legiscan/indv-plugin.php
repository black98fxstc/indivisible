<?php

// No soup for you!
if (version_compare(PHP_VERSION, '5.4.0') < 0)
	die('PHP 5.4.0 or higher is required');

require_once('IndivisiblePlugin.php');

class Indv_Plugin_API
{
	function processRequest()
	{
		$params = array_merge($_POST, $_GET);

		try {
			$indv_plugin_pull = new Indv_Plugin_Pull();
			$resp = $indv_plugin_pull->apiRequest($params);
			header("Content-type: application/json");
			echo $resp;
		} catch (APIException $e) {
			echo "<h2>LegiScan Error</h2>";
			echo 'API Error: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ' on line ' . $e->getLine() . "\n";

		} catch (APIAccessException $e) {
			echo "<h2>LegiScan Error</h2>";
			echo 'API Access: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ' on line ' . $e->getLine() . "\n";

		} catch (APIStatusException $e) {
			echo "<h2>LegiScan Error</h2>";
			echo 'API Status: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ' on line ' . $e->getLine() . "\n";

		} catch (PDOException $e) {
			echo "<h2>LegiScan Error</h2>";
			echo 'Database Error: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ' on line ' . $e->getLine() . "\n";

		} catch (Exception $e) {
			echo "<h2>LegiScan Error</h2>";
			echo 'Error: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ' on line ' . $e->getLine() . "\n";			}
	}
	// }}}
}
// }}}

$indv_plugin_api = new Indv_Plugin_API();

// Do the thing!
$indv_plugin_api->processRequest();
