<?php
class Build {
	private $root = false;
	private $url = false;
	private $collections = [];

	public static function project ($path, $url='http://separation.localhost') {
		new Build($path, $url);
	}

	public function __construct ($path, $url) {
		$this->root = $path;
		$this->url = $url;
		
		$this->collections();
		$this->forms();
		
		echo 'Built', "\n";
		exit;
	}

	private function packages ($type) {
		if (file_exists($this->root . '/' . $type . '/packages.json')) {
			$packageContainer = json_decode(file_get_contents($this->root . '/' . $type . '/packages.json'));
			foreach ($packageContainer as $package) {
				$package = json_decode(file_get_contents($package));
				foreach ($package as $path) {
					$destination = basename($path);
					if (file_exists($this->root . '/' . $type . '/' . $destination)) {
						continue;
					}
					file_put_contents($destination, file_get_contents($collectionPath));
				}
			}
		}
	}

	private function collectionStubRead ($name, &$collection) {
		$data = file_get_contents(__DIR__ . '/../static/' . $name);
		return str_replace(['{{$url}}', '{{$plural}}', '{{$singular}}'], [$this->url, $collection['p'], $collection['s']], $data);
	}

	private function collections () {
		$this->packages('collections');
		$this->collections = [];
		$dirFiles = glob($this->root . '/collections/*.php');
		foreach ($dirFiles as $collection) {
			require_once($collection);
			$class = basename($collection, '.php');
			$this->collections[] = [
				'p' => $class,
				's' => $class::$singular
			];
		}
		file_put_contents($this->root . '/collections/cache.json', json_encode($this->collections, JSON_PRETTY_PRINT));

		foreach ($this->collections as $collection) {
			$filename = $this->root . '/layouts/' . $collection['p'] . '.html';
			if (!file_exists($filename)) {
				file_put_contents($filename, $this->collectionStubRead('layout-collection.html', $collection));
			}
			$filename = $this->root . '/partials/' . $collection['p'] . '.hbs';
			if (!file_exists($filename)) {
				file_put_contents($filename, $this->collectionStubRead('partial-collection.hbs', $collection));
			}
			$filename = $this->root . '/layouts/' . $collection['s'] . '.html';
			if (!file_exists($filename)) {
				file_put_contents($filename, $this->collectionStubRead('layout-document.html', $collection));
			}
			$filename = $this->root . '/partials/' . $collection['s'] . '.hbs';
			if (!file_exists($filename)) {
				file_put_contents($filename, $this->collectionStubRead('partial-document.hbs', $collection));	
			}
			$filename = $this->root . '/sep/' . $collection['p'] . '.js';
			if (!file_exists($filename)) {
				file_put_contents($filename, $this->collectionStubRead('collection.js', $collection));	
			}
			$filename = $this->root . '/sep/' . $collection['s'] . '.js';
			if (!file_exists($filename)) {
				file_put_contents($filename, $this->collectionStubRead('document.js', $collection));	
			}
		}
	}

	private function forms () {
		$this->packages('forms');
		$this->forms = [];
		$dirFiles = glob($this->root . '/forms/*.php');
		foreach ($dirFiles as $form) {
			$class = basename($form, '.php');
			$this->forms[] = $class;
		}
		file_put_contents($this->root . '/forms/cache.json', json_encode($this->forms, JSON_PRETTY_PRINT));
		
		foreach ($this->forms as $form) {
			$filename = $this->root . '/layouts/form-' . $form . '.html';
			if (!file_exists($filename)) {
				$data = file_get_contents(__DIR__ . '/../static/form.html');
				$data = str_replace(['{{$form}}'], [$form], $data);
				file_put_contents($filename, $data);
			}
			$filename = $this->root . '/partials/form-' . $form . '.hbs';
			if (!file_exists($filename)) {
				$data = file_get_contents(__DIR__ . '/../static/form.hbs');
				require $this->root . '/forms/' . $form . '.php';
				$obj = new $form();
				$generated = '';
				foreach ($obj->fields as $field) {
					$generated .= '
						<div class="form-group">
    						<label for="inputPassword" class="col-lg-2 control-label">' . ucwords(str_replace('_', ' ', $field['name'])) . '</label>
    						<div class="col-lg-10">
    							{{' . $field['name'] . '}}
    						</div>
  						</div>';
				}
				$data = str_replace(['{{$form}}', '{{$generated}}'], [$form, $generated], $data);
				file_put_contents($filename, $data);
			}
			$filename = $this->root . '/sep/form-' . $form . '.js';
			if (!file_exists($filename)) {
				$data = file_get_contents(__DIR__ . '/../static/form.hbs');
				$data = str_replace(['{{$form}}'], [$form], $data);
				file_put_contents($filename, $data);	
			}
		}
	}

	private function intranets () {
		//read admin instranets

		//read packages

		//creeate file cache
	}

	private function events () {
		//read admins

		//read packages

		//creeate file cache
	}

	private function custom () {
		//read mvc setup

		//read packages

		///create file cache
	}
}