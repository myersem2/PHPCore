<?php declare(strict_types=1);
/**
 * PHPCore:Test-Fixture - Global
 *
 * @author    Everett Myers <Me@EverettMyers.com>
 * @copyright Copyright (c) 2022, PHPCore
 */

// -------------------------------------------------------------------------------------------------

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @backupStaticAttributes enabled
 */
final class Test extends TestCase
{

    /**
     * This method is used to perform any setup actions (e.g. connect to db) for
     * the entire test fixture. Method will only be executed once at the 
     * beginning of this test fixture stack.
     */
    public static function setUpBeforeClass(): void
    {

    }

    /**
     * This method is used to perform any tear down actions (e.g. disconnect
     * from db) for the entire test fixture. Method will only be executed once
     * at the end of this test fixture stack.
     */
    public static function tearDownAfterClass(): void
    {

    }
  
    /**
     * This method is used to perform any set up actions for each test. Method
     * will be executed before each test in the fixture stack.
     */
    public function setUp(): void
    {

    }

    /**
     * This method is used to perform any tear down actions for each test.
     * Method will be executed after each test in the fixture stack.
     */
    public function tearDown(): void
    {

    }

    // -----------------------------------------------------------------------------------------

    /**
     * @covers ::core_ini_get
     * @covers ::core_ini_get_all
     * @covers ::core_ini_set
     * @covers ::coreinfo
     * @covers ::parse_dsn
     * @covers ::str_color
     * @covers ::str_style
     * @covers ::xml_encode
     * @dataProvider dataProviderFunctionExists
     */
    public function testFunctionExists(string $function): void
    {
        $this->assertTrue(
            function_exists($function), 
            "$function() function does not exist"
        );
    }
    public static function dataProviderFunctionExists(): array
    {
      return [
        ['core_ini_get'],
        ['core_ini_get_all'],
        ['core_ini_set'],
        ['coreinfo'],
        ['parse_dsn'],
        ['str_color'],
        ['str_style'],
        ['xml_encode'],
      ];
    }

    /**
     * @covers ::core_ini_set
     * @covers ::core_ini_get
     * @depends testFunctionExists
     * @dataProvider dataProviderCoreIniSetGet
     */
    public function testCoreIniSetGet(string $directive, string $value, string $section): void
    {
        if (empty($section)) {
            core_ini_set($directive, $value);
            $this->assertEquals(
                core_ini_get($directive), $value,
                "PHPCore directive `$directive` does not match the value `$value`."
            );
        } else {
            core_ini_set($directive, $value, $section);
            $this->assertEquals(
                core_ini_get($directive, $section), $value,
                "$section directive `$directive` does not match the value `$value`."
            );  
        }
    }
    public static function dataProviderCoreIniSetGet(): array
    {
        return [
            [
                'PHPUnit.temp.test',
                'SAT-1',
                '',
            ],
            [
                'test',
                'SAT-2',
                'PHPUnit.Temp',
            ],
        ];
    }

    /**
     * @covers ::core_ini_get_all
     * @depends testFunctionExists
     * @dataProvider dataProviderCoreIniGetAll
     *
    public function testCoreIniGetAll(array $expected, string|null $section = null, string|null $sub_section = null): void
    {
      $this->assertEquals(
          $expected, core_ini_get_all($section, $sub_section),
          "$section does not match what was set."
      );
    }
    private function setUpCoreIniGetAll(): void
    {
        $GLOBALS['_CORE_INI'] = [
            'FirstSection' => [
                'directive'         => 'A',
                'sub_directive.one' => '1',
                'sub_directive.two' => '2', 
            ],
            'SecondSections' => [
                'direction.one' => '3',
            ],
        ];
    }
    public static function dataProviderCoreIniGetAll(): array
    {
        return [
            [
                [
                    'FirstSection' => [
                        'directive'         => 'A',
                        'sub_directive.one' => '1',
                        'sub_directive.two' => '2', 
                    ],
                    'SecondSections' => [
                        'direction.one' => '3',
                    ],
                ],
            ],
            [
                [
                    'directive'         => 'A',
                    'sub_directive.one' => '1',
                    'sub_directive.two' => '2', 
                ],
                'FirstSection',
            ],
            [
                [
                    'one' => '1',
                    'two' => '2', 
                ],
                'FirstSection',
                'sub_directive',
            ],
        ];
    }
    
    /**
     * @covers ::coreinfo
     * @covers ::xml_encode
     * @covers ::str_style
     * @covers ::str_color
     * @dataProvider dataproviderCoreinfo
     *
    public function testCoreinfo(string $format): void
    {
        $GLOBALS['_CORE']['FORMAT'] = $format;
        if ($format == 'unsupported-format') {
            $this->expectError();
            coreinfo();
            return;
        }
        ob_start();
        coreinfo();
        $output = ob_get_clean();
        switch ($format) {
            default:
                $this->markTestIncomplete(
                  "This test has not been implemented for the format `$format` yet."
                );
            break;
            case 'text':
                $output = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $output);
                $test = 'PHPCore ' . CORE_VERSION . PHP_EOL;
                foreach ($GLOBALS['_CORE_INI'] as $section=>$directives) {
                    $test .= PHP_EOL . $section . PHP_EOL;
                    foreach ($directives as $directive=>$value) {
                        $test .= "$directive => $value" . PHP_EOL;
                    }
                } 
                $test .= PHP_EOL;
                $test .= '$_CORE' . PHP_EOL;
                foreach ($GLOBALS['_CORE'] as $name=>$value) {
                    $test .= "\$_CORE['$name'] => $value" . PHP_EOL;
                }
                $test .= PHP_EOL;
                $this->assertEquals(
                    $output, $test,
                    "Output text does not match."
                );
                return;
            break;
            case 'xml':
                $output = json_encode(simplexml_load_string($output, 'SimpleXMLElement', LIBXML_NOEMPTYTAG ));
                $data = json_decode($output, true);
                foreach ($data['configuration'] as $sections=>$directives) {
                    foreach ($directives as $directive=>$value) {
                        if (is_array($value) and empty($value)) {
                            $data['configuration'][$sections][$directive] = null;
                        }
                    }
                }
                foreach ($data['variables'] as $name=>$value) {
                    if (is_array($value) and empty($value)) {
                        $data['variables'][$name] = null;
                    }
                }
            break;
            case 'json':
                $data = json_decode($output, true);
            break;
        }
        $this->assertEquals(
            $data['PHPCoreVersion'], CORE_VERSION,
            'PHPCoreVersion mismatch from phpinfo'
        );
        $this->assertEquals(
            $data['configuration'], $GLOBALS['_CORE_INI'],
            'configuration array does not match'
        );
        $this->assertEquals(
            $data['variables'], $GLOBALS['_CORE'],
            'variables array does not match'
        );
        $this->assertEquals(
            array_keys($data), ['PHPCoreVersion', 'configuration', 'variables'],
            'phpinof did not return expected array'
        );
    }
    public static function dataProviderCoreinfo(): array
    {
        return [
            [  'text' ],
            [  'json' ],
            [  'xml' ],
            [  'unsupported-format' ],
        ];
    }

    /**
     * @covers ::parse_dsn
     * @dataProvider dataProviderParseDsn
     *
    public function testParseDsn(string $dsn, array $valid_resp, string $assert): void
    {
        switch ($assert) {
            case 'Equals':
                $this->assertEquals(
                    parse_dsn($dsn), $valid_resp
                );
            break;
            case 'InvalidArgumentException':
                $this->expectException(InvalidArgumentException::class);
                parse_dsn($dsn);
            break;
        }
    }
    public static function dataProviderParseDsn(): array
    {
        return [
            [
                'sqlite:',
                [
                    'driver'  => 'sqlite',
                ],
                'Equals',
            ],
            [
                'sqlite:/opt/databases/mydb.sq3',
                [
                    'driver'  => 'sqlite',
                    'path'    => '/opt/databases/mydb.sq3',
                ],
                'Equals',
            ],
            [
                'sqlite::memory:',
                [
                    'driver'  => 'sqlite',
                ],
                'Equals',
            ],
            [
                'mysql:host=localhost;dbname=my_database;charset=utf8mb4',
                [
                    'driver'  => 'mysql',
                    'host'    => 'localhost',
                    'dbname'  => 'my_database',
                    'charset' => 'utf8mb4',
                ],
                'Equals',
            ],
            [
                'host=localhost;dbname=my_database;charset=utf8mb4',
                [],
                'InvalidArgumentException',
            ],
        ];
    }

    /**
     * @covers ::str_color
     * @dataProvider dataProviderStrColor
     *
    public function testStrColor(string $color, string $color_code, $bkg, string $bkg_code): void
    {
        if ($color === 'unsupported-color') {
            $this->expectError();
            $output = str_color('string', $color);
            return;
        }
        if ($bkg === 'unsupported-bkg') {
            $this->expectError();
            $output = str_color('string', $color, $bkg);
            return;
        }
        if (empty($bkg)) {
            $output = str_color('string', $color);
        } else {
            $output = str_color('string', $color, $bkg);
        }
        $this->assertEquals(
            $output, "\e[{$color_code};{$bkg_code}mstring\e[0m"
        );
    }
    public static function dataProviderStrColor(): array
    {
        return [
            [ 'black',         '0;30', 'white',   '47' ],
            [ 'dark_grey',     '1;30', 'red',     '41' ],
            [ 'red',           '0;31', 'green',   '42' ],
            [ 'light_red',     '1;31', 'yellow',  '43' ],
            [ 'green',         '0;32', 'blue',    '44' ],
            [ 'light_green',   '1;32', 'magenta', '45' ],
            [ 'brown',         '0;33', 'cyan',    '46' ],
            [ 'yellow',        '1;33', 'black',   '40' ],
            [ 'blue',          '0;34', 'black',   '40' ],
            [ 'light_blue',    '1;34', 'black',   '40' ],
            [ 'magenta',       '0;35', 'black',   '40' ],
            [ 'light_magenta', '1;35', 'black',   '40' ],
            [ 'cyan',          '0;36', 'black',   '40' ],
            [ 'light_cyan',    '1;36', 'black',   '40' ],
            [ 'light_grey',    '0;37', 'black',   '40' ],
            [ 'white',         '1;37', 'black',   '40' ],
            [ 'white', '1;37', '', '40' ],
            [ 'unsupported-color', '0;39', '', '40' ],
            [ 'white', '1;37', 'unsupported-bkg', '49' ],
        ];
    }

    /**
     * @covers ::str_style
     * @dataProvider dataProviderStrStyle
     *
    public function testStrStyle(string $style, string $code): void
    {
        if ($style === 'unsupported-style') {
            $this->expectError();
            $output = str_style('string', $style);
            return;
        }
        $output = str_style('string', $style);
        $this->assertEquals(
            $output, "\e[{$code}mstring\e[0m"
        );
    }
    public static function dataProviderStrStyle(): array
    {
        return [
            [ 'bold',              '1' ],
            [ 'bright',            '1' ],
            [ 'dim',               '2' ],
            [ 'italic',            '3' ],
            [ 'underline',         '4' ],
            [ 'reverse',           '7' ],
            [ 'hidden',            '8' ],
            [ 'strike',            '9' ],
            [ 'strikethrough',     '9' ],
            [ 'unsupported-style', ''  ],
        ];
    }

    /**
     * @covers ::xml_encode
     * @dataProvider dataProviderXmlEncode
     *
    public function testXmlEncode(array $data, int $flags,  string $valid_resp): void
    {
        $xml = xml_encode($data, $flags);
        if ($flags === XML_ENCODE_AS_XML_OBJ) {
            $this->assertInstanceOf(SimpleXMLElement::class, $xml);
        } else {
            $this->assertEquals($xml, $valid_resp);
        }
    }
    public static function dataProviderXmlEncode(): array
    {
        return [
            [
                [ 'name' => [
                    'first' => 'John',
                    'last' => 'Doe',
                    ]
                ],
                0,
                "<?xml version=\"1.0\"?>\n".
                "<root><name><first>John</first><last>Doe</last></name></root>\n"
            ],
            [
                [ 'numbers' => [ 10, 20, 30 ] ],
                0,
                "<?xml version=\"1.0\"?>\n".
                "<root><numbers><item>10</item><item>20</item><item>30</item></numbers></root>\n"
            ],
            [
                [ 'name' => [
                    'first' => 'John',
                    'last' => 'Doe',
                    ]
                ],
                XML_ENCODE_PRETTY_PRINT,
                "<?xml version=\"1.0\"?>\n".
                "<root>\n".
                "  <name>\n".
                "    <first>John</first>\n".
                "    <last>Doe</last>\n".
                "  </name>\n".
                "</root>\n"
            ],
            [
                [ 'name' => 'John Doe' ],
                XML_ENCODE_AS_XML_OBJ,
                '',
            ],
        ];
    }
    /* */

}

// EOF /////////////////////////////////////////////////////////////////////////////////////////////