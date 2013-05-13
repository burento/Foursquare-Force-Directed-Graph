<?php
	
	$FSQ_URL = 'https://api.foursquare.com/v2/venues/';
	$FSQ_CLIENT_ID = 'client_id=ID';
	$FSQ_CLIENT_SECRET = 'client_secret=SECRET';
	$FSQ_VERSION = 'v=20130420';
	$CWD = getcwd();

	class Link{
		public $source = "";
		public $target = "";
		public $value = 1;

		public function __construct($a,$b,$v){
			$this->source = $a;
			$this->target = $b;
			$this->value = $v;
		}

		public function addValue($v){
			$this->value += $v;
		}

		public function getSource(){ return $this->source; }
		public function getTarget(){ return $this->target; }
		public function getValue(){ return $this->value; }
	}

	class Node{
		public $key;
		public $name;
		public $count = 1;
		public $category = '';
		public $icon = '';
		public $cc = '';
		
		public function __construct($k, $n){
			$this->key = $k;
			$this->name = $n;
			
			global $FSQ_URL;
			global $FSQ_CLIENT_ID;
			global $FSQ_CLIENT_SECRET;
			global $FSQ_VERSION;
			global $CWD;

			$loc = $CWD . "/data/" . $k . ".json";
			$f = '';
			if( file_exists($loc) ){
				$f = file_get_contents($loc);
			}else{
				$f = file_get_contents($FSQ_URL . $k . "?" . implode('&', array($FSQ_CLIENT_ID, $FSQ_CLIENT_SECRET, $FSQ_VERSION) ) );
				file_put_contents($loc, $f);
			}

			$json = json_decode($f, TRUE);

			$this->cc = $json['response']['venue']['location']['cc'];

			foreach($json['response']['venue']['categories'] as $i => $v){
				if( isset( $v['primary'] ) ){
					$this->category = $v['name'];
					$this->icon =  substr($v['icon']['prefix'],0,-1) . $v['icon']['suffix'];
					continue;
				}
			}
		}

		public function getKey(){ return $this->key; }
		public function getName(){ return $this->name; }
		public function addCount($c){ $this->count += $c; }
		public function getCategory(){ return $this->category; }
		public function getCount(){ return $this->count; }
		public function getIcon(){ return $this->icon; }
		public function getCC(){ return $this->cc; }
	}

	class Graph{
		public $nodes = array(); // [ key => Node ]
		public $links = array(); // [ index => Link ]

		public function __construct(){
			$nodes = array();
			$links = array();
		}

		public function addNode($n){
			if( !self::hasNode($n) ){
				$this->nodes[$n->getKey()] = $n;
			}else{
				$this->nodes[$n->getKey()]->addCount(1);
			}
		}

		public function hasNode($n){
			foreach($this->nodes as $k => $v){
				if( $k == $n->getKey() ){ return TRUE; }
			}
			return FALSE;
		}

		public function addLink($na, $nb, $v){
			$k = self::hasLink($na,$nb);
			if( isset($k) ){
				$this->links[$k]->addValue($v); // FOUND, add value
			}else{
				array_push($this->links, new Link($na, $nb, 1) ); // Add new link
			}
		}

		public function hasLink($na, $nb){
			foreach($this->links as $k => $l){
				if( ($l->getSource == $na && $l->getTarget() == $nb) || ($l->getSource() == $nb && $l->getTarget() == $na) ){
					return $k;
				}
			}
			return NULL;
		}

		public function getNodeCategories(){
			$cats = array();
			foreach($this->nodes as $k => $n){
				array_push($cats, $n->getCategory() );
			}
			$uniq_cats = array();
			$i = 1;
			foreach( array_unique($cats) as $k => $v ){
				$uniq_cats[$v] = $i;
				$i++;
			}
			return $uniq_cats;

		}

		public function determineHighLevelCategory($c){
			if( preg_match("/airport|plane|terminal/i", $c)){
				return "air-travel";
			}elseif( preg_match("/travel|train|subway|\bbus\b|\brail\b|\bboat\b|\bferry\b|\bpier\b/i", $c)){
				return "travel";
			}elseif( preg_match("/restaurant|food|dessert|\btea\b|bakery|ramen|\bbbq\b|snack|burger|breakfast|cafeteria|salad|chicken|taco/i", $c) ){
				return "restaurant";
			}elseif( preg_match("/\btea\b|coffee|\bcafe\b|café/i", $c) ){
				return "coffee";
			}elseif( preg_match("/\bbar\b|nightclub|\bclub\b|lounge/i", $c)){
				return "bar";
			}elseif( preg_match("/store|market|\bmall\b|\bshop\b/i", $c)){
				return "store";
			}elseif( preg_match("/temple|\bshrine\b/i", $c)){
				return "temple";
			}elseif( preg_match("/hotel|\bspa\b|massage|\bsalon\b/i", $c)){
				return "leisure";
			}elseif( preg_match("/historic|embassy|facility|neighborhood|monument|building|event space|\bpark\b|plaza|\broad\b|\bzoo\b|student|college|laundry/i", $c)){
				return "site";
			}elseif( preg_match("/\bgym\b|beach|trail|hiking|surf|outdoors/i", $c)){
				return "fitness";
			}elseif( preg_match("/multiplex|stadium|theatre|theater|entertainment|bowling|museum/i", $c)){
				return "entertainment";
			}elseif( preg_match("/office|\bhome\b/i", $c)){
				return "home-work";
			}else{
				return "unknown";
			}
		}

		public function createJson(){
			$json = '';
			$nodes = array();
			$links = array();
			$uniq_cats = self::getNodeCategories();
			$nodes_index = array();

			$i = 0;
			foreach($this->nodes as $k => $n){
				array_push($nodes, array("name" => $n->getName(), "group" => $uniq_cats[$n->getCategory()], "size" => $n->getCount()
					, "category" => $n->getCategory(), "topcat" => self::determineHighLevelCategory($n->getCategory())
					, "icon" => $n->getIcon(), "key" => $n->getKey(), "cc" => $n->getCC() ) );
				$nodes_index[$n->getKey()] = $i;
				$i++;
			}
			foreach($this->links as $k => $l){
				array_push($links, array( "source" => $nodes_index[$l->getSource()], "target" => $nodes_index[$l->getTarget()]
					, "value" => $l->getValue() ) );
			}
			
			return json_encode( array("nodes" => $nodes, "links" => $links) );
		}
	}

	function createForceGraphJson($start, $end){
		$fsq = new DOMDocument;
		$fsq->preserveWhiteSpace = FALSE;
		$fsq->strictErrorChecking = FALSE;
		$fsq->load('4sq.kml');
		global $CWD;

		$nodes = array();

		$graph = new Graph();

		$prevKey = '';
		$currKey = '';

		foreach($fsq->getElementsByTagName('Placemark') as $p){
			$name = '';
			$key = '';
			$pub = '';

			foreach($p->childNodes as $c){
				if( $c->nodeName == 'name'){
					$name = $c->nodeValue;
				}elseif( $c->nodeName == 'description'){
					foreach($c->getElementsByTagName('a') as $a){
						preg_match("/.*\/(.*)/", $a->getAttribute('href'), $matches );
						$key = $matches[1];
					}
				}elseif( $c->nodeName == 'published'){
					$pub = strtotime($c->nodeValue);
				}
			}

			if( $start <= $pub && $pub <= $end){
				if( $prevKey <> '' ){
					$prevKey = $currKey;
					$currKey = $key;
				}else{
					$prevKey = $key; $currKey = $key;
				}
				$graph->addNode(new Node($key, $name));
				if( $prevKey <> $currKey ){
					$graph->addLink($prevKey, $currKey, 1);
				}
			}
		}

		$loc = $CWD . "/4sq.json";
		file_put_contents($loc, $graph->createJson() );
	}
	
?>