<?php

Mock::generate('Ergo_Routing_Controller','Ergo_Routing_MockController');

class Ergo_Routing_RouteTest extends UnitTestCase
{
	private $_exampleRoutes = array(
		'/fruits' => 'fruits',
		'/fruits/{fruitid}' => 'fruit',
		'/fruits/{fruitid}/flavours/{flavourid}' => 'flavour',
	);

	public function testRouteLookup()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map($this->_exampleRoutes);

		$this->_assertRoute($routeMap,'/fruits','fruits',
			array()
		);

		$this->_assertRoute($routeMap,'/fruits/123','fruit',
			array('fruitid' => 123)
		);

		$this->_assertRoute($routeMap,'/fruits/123/flavours/456','flavour',
			array('fruitid' => 123, 'flavourid' => 456)
		);
	}

	public function testRouteMapTrimsTrailingSlashes()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map(array('/fruits'=>'fruits'));

		$this->_assertRoute($routeMap,'/fruits/','fruits',
			array()
		);
	}

	public function testRouteBuild()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map($this->_exampleRoutes);

		$this->assertEqual(
			$routeMap->buildUrl('fruits'),
			'/fruits'
		);

		$this->assertEqual(
			$routeMap->buildUrl('fruit', array('fruitid' => 234)),
			'/fruits/234'
		);

		$this->assertEqual(
			$routeMap->buildUrl('flavour', array('fruitid' => 234, 'flavourid' => 456)),
			'/fruits/234/flavours/456'
		);
	}

	public function testRouteLookupFailsOnNonExistentRouteName()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map($this->_exampleRoutes);
		$this->expectException('Ergo_Routing_LookupException');
		$routeMap->lookup('/blarg');
	}

	public function testRouteLookupFailsWithEmptyTemplateVars()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map($this->_exampleRoutes);
		$this->expectException('Ergo_Routing_LookupException');
		$routeMap->lookup('/fruits//flavours/');
	}

	public function testRouteBuildFailsWithExtraParam()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map($this->_exampleRoutes);
		$this->expectException('Ergo_Routing_BuildException');
		$routeMap->buildUrl('fruits', array('test' => 123));
	}

	public function testRouteBuildFailsWithMissingParam()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map($this->_exampleRoutes);
		$this->expectException('Ergo_Routing_BuildException');
		$routeMap->buildUrl('flavours');
	}

	public function testRouteMapMatchArrayInterface()
	{
		$match = new Ergo_Routing_RouteMapMatch('test', array('a' => 'b'));
		$this->assertEqual($match['a'], 'b');
	}


	public function testRoutesWithStringTypes()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map(array(
			'/fruits/{fruitname:string}' => 'fruit',
			));

		$this->_assertRoute($routeMap,'/fruits/blargh','fruit',
			array('fruitname'=>'blargh')
		);

		$this->_assertNoRoute($routeMap,'/fruits/blargh;.');
	}

	public function testRoutesWithIntegerTypes()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map(array(
			'/fruits/{fruitid:int}' => 'fruit',
			));

		$this->_assertRoute($routeMap,'/fruits/123','fruit',
			array('fruitid'=>'123')
		);

		$this->_assertNoRoute($routeMap,'/fruits/123;blargh');
		$this->_assertNoRoute($routeMap,'/fruits/blargh');
	}

	public function testRoutesWithEnumTypes()
	{
		$routeMap = new Ergo_Routing_RouteMap();
		$routeMap->map(array(
			'/fruits/{fruittype:(orange|apple)}' => 'fruit',
			));

		$this->_assertRoute($routeMap,'/fruits/apple','fruit',
			array('fruittype'=>'apple')
		);

		$this->_assertNoRoute($routeMap,'/fruits/pear');
		$this->_assertNoRoute($routeMap,'/fruits/llama');
	}


	// ----------------------------------------

	private function _assertRoute($map, $template, $name, $parameters=false)
	{
		$match = $map->lookup($template);
		$this->assertEqual($match->getName(), $name);
		if($parameters) $this->assertEqual($match->getParameters(), $parameters);
	}

	private function _assertNoRoute($map, $template)
	{
		try
		{
			$map->lookup($template);
			$this->fail("should fail route lookup for $template");
		}
		catch(Ergo_Routing_LookupException $e)
		{
			$this->assertTrue(true);
		}
	}

}
