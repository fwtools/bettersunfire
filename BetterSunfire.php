<?php

namespace BetterSunfire;

use \Arya\Request as Request;
use \Arya\Response as Response;

class BetterSunfire {
	private $db;

	public function __construct(\PDO $db) {
		$this->db = $db;
	}

	public function main(Request $request, Response $response) {
		$css = file_get_contents(__DIR__ . '/style.css');

		foreach(glob(__DIR__ . '/modules/*.css') as $filename) {
			$css.= file_get_contents($filename);
		}

		$body = $response->getBody();
		$response->setBody($body . $css);
	}

	public function image(Request $request, $name, $extension) {
		$response = new Response;
		$response->setHeader('Content-Type', 'image/' . $extension);
		$file = __DIR__ . '/i/' . $name . '.' . $extension;

		if (file_exists($file)) {
			$exp_gmt = gmdate("D, d M Y H:i:s", time() + 86400) ." GMT";
			$mod_gmt = gmdate("D, d M Y H:i:s", filemtime($file)) ." GMT";

			$response->setHeader('Expires', $exp_gmt);
			$response->setHeader('Last-Modified', $mod_gmt);
			$response->setHeader('Cache-Control', 'private, max-age=86400');
			$response->addHeader('Cache-Control', 'pre-check=86400');
			$response->setBody(file_get_contents($file));

			return $response;
		} else {
			return ['status' => 404];
		}
	}

	public function event(Request $request) {
		$response = new Response;

		$world = $request->getStringQueryParameter('world');

		$worlds = [
			'de1', 'de2', 'de3', 'de4', 'de5', 'de6', 'de7', 'de8', 'de9',
			'de10', 'de11', 'de12', 'de13', 'de14', 'af', 'rp'
		];

		if(!in_array($world, $worlds)) {
			return '/* unknown world */';
		}

		$q = $this->db->prepare("SELECT time FROM style_event WHERE event = 'pensal-available' && world = ?");
		$q->execute([$world]);

		if($row = $q->fetch(\PDO::FETCH_OBJ)) {
			$eventMin = (int) date('i', $row->time);
			$currMin = (int) date('i');

			$eventDiff = $eventMin - $eventMin % 30;
			$currDiff = $currMin - $currMin % 30;

			if($row->time > time() - 30 * 60 && $eventDiff == $currDiff) {
				$response->setHeader('Content-Type', 'text/css; charset=utf-8');

				$exp_gmt = gmdate("D, d M Y H:i:s", time() + 60 - time() % 60) ." GMT";
				$mod_gmt = gmdate("D, d M Y H:i:s", time()) ." GMT";

				$response->setHeader('Expires', $exp_gmt);
				$response->setHeader('Last-Modified', $mod_gmt);
				$response->setHeader('Cache-Control', 'private, max-age=' . (60 - time() % 60));
				$response->addHeader('Cache-Control', 'post-check=' . (60 - time() % 60));

				$response->setBody('.positiontext:after{content:"Pensal"}');
				return $response;
			}
		}

		$exp_gmt = gmdate("D, d M Y H:i:s", time() + 30) ." GMT";
		$mod_gmt = gmdate("D, d M Y H:i:s", time()) ." GMT";

		$response->setHeader('Expires', $exp_gmt);
		$response->setHeader('Last-Modified', $mod_gmt);
		$response->setHeader('Cache-Control', 'private, max-age=30');
		$response->addHeader('Cache-Control', 'pre-check=30');

		return $response->setBody('');
	}
}
