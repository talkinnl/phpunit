<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function array_merge;
use function preg_match;
use function range;
use function realpath;
use CoverageClassExtendedTest;
use CoverageClassNothingTest;
use CoverageClassTest;
use CoverageCoversOverridesCoversNothingTest;
use CoverageFunctionParenthesesTest;
use CoverageFunctionParenthesesWhitespaceTest;
use CoverageFunctionTest;
use CoverageMethodNothingCoversMethod;
use CoverageMethodNothingTest;
use CoverageMethodOneLineAnnotationTest;
use CoverageMethodParenthesesTest;
use CoverageMethodParenthesesWhitespaceTest;
use CoverageMethodTest;
use CoverageNoneTest;
use CoverageNotPrivateTest;
use CoverageNotProtectedTest;
use CoverageNotPublicTest;
use CoveragePrivateTest;
use CoverageProtectedTest;
use CoveragePublicTest;
use CoverageTwoDefaultClassAnnotations;
use PharIo\Version\VersionConstraint;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;
use PHPUnit\TestFixture\CoverageNamespacedFunctionTest;
use PHPUnit\TestFixture\DuplicateKeyDataProviderTest;
use PHPUnit\TestFixture\MultipleDataProviderTest;
use PHPUnit\TestFixture\NamespaceCoverageClassExtendedTest;
use PHPUnit\TestFixture\NamespaceCoverageClassTest;
use PHPUnit\TestFixture\NamespaceCoverageCoversClassPublicTest;
use PHPUnit\TestFixture\NamespaceCoverageCoversClassTest;
use PHPUnit\TestFixture\NamespaceCoverageMethodTest;
use PHPUnit\TestFixture\NamespaceCoverageNotPrivateTest;
use PHPUnit\TestFixture\NamespaceCoverageNotProtectedTest;
use PHPUnit\TestFixture\NamespaceCoverageNotPublicTest;
use PHPUnit\TestFixture\NamespaceCoveragePrivateTest;
use PHPUnit\TestFixture\NamespaceCoverageProtectedTest;
use PHPUnit\TestFixture\NamespaceCoveragePublicTest;
use PHPUnit\TestFixture\NotExistingCoveredElementTest;
use PHPUnit\TestFixture\ParseTestMethodAnnotationsMock;
use PHPUnit\TestFixture\RequirementsClassDocBlockTest;
use PHPUnit\TestFixture\RequirementsTest;
use PHPUnit\TestFixture\Test3194;
use PHPUnit\TestFixture\VariousDocblockDefinedDataProvider;
use PHPUnit\TestFixture\VariousIterableDataProviderTest;
use PHPUnit\Util\Annotation\DocBlock;
use ReflectionClass;
use ReflectionMethod;

/**
 * @small
 */
final class TestClassTest extends TestCase
{
    /**
     * @var string
     */
    private $fileRequirementsTest;

    /**
     * @testdox Test::getRequirements() for $test
     * @dataProvider requirementsProvider
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Warning
     */
    public function testGetRequirements($test, $result): void
    {
        $this->assertEquals(
            $result,
            Test::getRequirements(RequirementsTest::class, $test)
        );
    }

    public function requirementsProvider(): array
    {
        return [
            [
                'testOne',
                ['__OFFSET' => [
                    '__FILE' => $this->getRequirementsTestClassFile(),
                ]],
            ],

            [
                'testTwo',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 21,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => ''],
                ],
            ],

            [
                'testThree',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 28,
                    ],
                    'PHP' => ['version' => '2.0', 'operator' => ''],
                ],
            ],

            [
                'testFour',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 35,
                        'PHP'     => 36,
                    ],
                    'PHPUnit' => ['version' => '2.0', 'operator' => ''],
                    'PHP'     => ['version' => '1.0', 'operator' => ''],
                ],
            ],

            [
                'testFive',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 43,
                    ],
                    'PHP' => ['version' => '5.4.0RC6', 'operator' => ''],
                ],
            ],

            [
                'testSix',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 50,
                    ],
                    'PHP' => ['version' => '5.4.0-alpha1', 'operator' => ''],
                ],
            ],

            [
                'testSeven',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 57,
                    ],
                    'PHP' => ['version' => '5.4.0beta2', 'operator' => ''],
                ],
            ],

            [
                'testEight',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 64,
                    ],
                    'PHP' => ['version' => '5.4-dev', 'operator' => ''],
                ],
            ],

            [
                'testNine',
                [
                    '__OFFSET' => [
                        '__FILE'            => $this->getRequirementsTestClassFile(),
                        'function_testFunc' => 71,
                    ],
                    'functions' => ['testFunc'],
                ],
            ],

            [
                'testTen',
                [
                    '__OFFSET' => [
                        '__FILE'            => $this->getRequirementsTestClassFile(),
                        'extension_testExt' => 87,
                    ],
                    'extensions' => ['testExt'],
                ],
            ],

            [
                'testEleven',
                [
                    '__OFFSET' => [
                        '__FILE'   => $this->getRequirementsTestClassFile(),
                        'OS'       => 94,
                        'OSFAMILY' => 95,
                    ],
                    'OS'       => 'SunOS',
                    'OSFAMILY' => 'Solaris',
                ],
            ],

            [
                'testSpace',
                [
                    '__OFFSET' => [
                        '__FILE'        => $this->getRequirementsTestClassFile(),
                        'extension_spl' => 173,
                        'OS'            => 174,
                    ],
                    'extensions' => ['spl'],
                    'OS'         => '.*',
                ],
            ],

            [
                'testAllPossibleRequirements',
                [
                    '__OFFSET' => [
                        '__FILE'                  => $this->getRequirementsTestClassFile(),
                        'PHP'                     => 102,
                        'PHPUnit'                 => 103,
                        'OS'                      => 104,
                        'function_testFuncOne'    => 105,
                        'function_testFunc2'      => 106,
                        'extension_testExtOne'    => 107,
                        'extension_testExt2'      => 108,
                        'extension_testExtThree'  => 109,
                        '__SETTING_not_a_setting' => 110,
                    ],
                    'PHP'       => ['version' => '99-dev', 'operator' => ''],
                    'PHPUnit'   => ['version' => '9-dev', 'operator' => ''],
                    'OS'        => 'DOESNOTEXIST',
                    'functions' => [
                        'testFuncOne',
                        'testFunc2',
                    ],
                    'setting' => [
                        'not_a_setting' => 'Off',
                    ],
                    'extensions' => [
                        'testExtOne',
                        'testExt2',
                        'testExtThree',
                    ],
                    'extension_versions' => [
                        'testExtThree' => ['version' => '2.0', 'operator' => ''],
                    ],
                ],
            ],

            ['testSpecificExtensionVersion',
                [
                    '__OFFSET' => [
                        '__FILE'            => $this->getRequirementsTestClassFile(),
                        'extension_testExt' => 181,
                    ],
                    'extension_versions' => ['testExt' => ['version' => '1.8.0', 'operator' => '']],
                    'extensions'         => ['testExt'],
                ],
            ],
            ['testPHPVersionOperatorLessThan',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 189,
                    ],
                    'PHP' => ['version' => '5.4', 'operator' => '<'],
                ],
            ],
            ['testPHPVersionOperatorLessThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 197,
                    ],
                    'PHP' => ['version' => '5.4', 'operator' => '<='],
                ],
            ],
            ['testPHPVersionOperatorGreaterThan',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 205,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '>'],
                ],
            ],
            ['testPHPVersionOperatorGreaterThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 213,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '>='],
                ],
            ],
            ['testPHPVersionOperatorEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 221,
                    ],
                    'PHP' => ['version' => '5.4', 'operator' => '='],
                ],
            ],
            ['testPHPVersionOperatorDoubleEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 229,
                    ],
                    'PHP' => ['version' => '5.4', 'operator' => '=='],
                ],
            ],
            ['testPHPVersionOperatorBangEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 237,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '!='],
                ],
            ],
            ['testPHPVersionOperatorNotEquals',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 245,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '<>'],
                ],
            ],
            ['testPHPVersionOperatorNoSpace',
                [
                    '__OFFSET' => [
                        '__FILE' => $this->getRequirementsTestClassFile(),
                        'PHP'    => 253,
                    ],
                    'PHP' => ['version' => '99', 'operator' => '>='],
                ],
            ],
            ['testPHPUnitVersionOperatorLessThan',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 261,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => '<'],
                ],
            ],
            ['testPHPUnitVersionOperatorLessThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 269,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => '<='],
                ],
            ],
            ['testPHPUnitVersionOperatorGreaterThan',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 277,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '>'],
                ],
            ],
            ['testPHPUnitVersionOperatorGreaterThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 285,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '>='],
                ],
            ],
            ['testPHPUnitVersionOperatorEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 293,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => '='],
                ],
            ],
            ['testPHPUnitVersionOperatorDoubleEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 301,
                    ],
                    'PHPUnit' => ['version' => '1.0', 'operator' => '=='],
                ],
            ],
            ['testPHPUnitVersionOperatorBangEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 309,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '!='],
                ],
            ],
            ['testPHPUnitVersionOperatorNotEquals',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 317,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '<>'],
                ],
            ],
            ['testPHPUnitVersionOperatorNoSpace',
                [
                    '__OFFSET' => [
                        '__FILE'  => $this->getRequirementsTestClassFile(),
                        'PHPUnit' => 325,
                    ],
                    'PHPUnit' => ['version' => '99', 'operator' => '>='],
                ],
            ],
            ['testExtensionVersionOperatorLessThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 339,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '<=']],
                ],
            ],
            ['testExtensionVersionOperatorGreaterThan',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 346,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>']],
                ],
            ],
            ['testExtensionVersionOperatorGreaterThanEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 353,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>=']],
                ],
            ],
            ['testExtensionVersionOperatorEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 360,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '=']],
                ],
            ],
            ['testExtensionVersionOperatorDoubleEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 367,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '1.0', 'operator' => '==']],
                ],
            ],
            ['testExtensionVersionOperatorBangEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 374,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '!=']],
                ],
            ],
            ['testExtensionVersionOperatorNotEquals',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->getRequirementsTestClassFile(),
                        'extension_testExtOne' => 381,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '<>']],
                ],
            ],
            ['testExtensionVersionOperatorNoSpace',
                [
                    '__OFFSET' => [
                        '__FILE'               => $this->fileRequirementsTest,
                        'extension_testExtOne' => 388,
                    ],
                    'extensions'         => ['testExtOne'],
                    'extension_versions' => ['testExtOne' => ['version' => '99', 'operator' => '>=']],
                ],
            ],
        ];
    }

    /**
     * @testdox Test::getRequirements() with constraints for $test
     * @dataProvider requirementsWithVersionConstraintsProvider
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws Warning
     */
    public function testGetRequirementsWithVersionConstraints($test, array $result): void
    {
        $requirements = Test::getRequirements(RequirementsTest::class, $test);

        foreach ($result as $type => $expected_requirement) {
            $this->assertArrayHasKey(
                "{$type}_constraint",
                $requirements
            );
            $this->assertArrayHasKey(
                'constraint',
                $requirements["{$type}_constraint"]
            );
            $this->assertInstanceOf(
                VersionConstraint::class,
                $requirements["{$type}_constraint"]['constraint']
            );
            $this->assertSame(
                $expected_requirement['constraint'],
                $requirements["{$type}_constraint"]['constraint']->asString()
            );
        }
    }

    public function requirementsWithVersionConstraintsProvider(): array
    {
        return [
            [
                'testVersionConstraintTildeMajor',
                [
                    'PHP' => [
                        'constraint' => '~1.0',
                    ],
                    'PHPUnit' => [
                        'constraint' => '~2.0',
                    ],
                ],
            ],
            [
                'testVersionConstraintCaretMajor',
                [
                    'PHP' => [
                        'constraint' => '^1.0',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^2.0',
                    ],
                ],
            ],
            [
                'testVersionConstraintTildeMinor',
                [
                    'PHP' => [
                        'constraint' => '~3.4.7',
                    ],
                    'PHPUnit' => [
                        'constraint' => '~4.7.1',
                    ],
                ],
            ],
            [
                'testVersionConstraintCaretMinor',
                [
                    'PHP' => [
                        'constraint' => '^7.0.17',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^4.7.1',
                    ],
                ],
            ],
            [
                'testVersionConstraintCaretOr',
                [
                    'PHP' => [
                        'constraint' => '^5.6 || ^7.0',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.0 || ^6.0',
                    ],
                ],
            ],
            [
                'testVersionConstraintTildeOr',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ~7.0.17',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.0.5 || ^6.0.6',
                    ],
                ],
            ],
            [
                'testVersionConstraintTildeOrCaret',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ^7.0',
                    ],
                    'PHPUnit' => [
                        'constraint' => '~5.6.22 || ^7.0',
                    ],
                ],
            ],
            [
                'testVersionConstraintCaretOrTilde',
                [
                    'PHP' => [
                        'constraint' => '^5.6 || ~7.0.17',
                    ],
                    'PHPUnit' => [
                        'constraint' => '^5.6 || ~7.0.17',
                    ],
                ],
            ],
            [
                'testVersionConstraintRegexpIgnoresWhitespace',
                [
                    'PHP' => [
                        'constraint' => '~5.6.22 || ~7.0.17',
                    ],
                    'PHPUnit' => [
                        'constraint' => '~5.6.22 || ~7.0.17',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider requirementsWithInvalidVersionConstraintsThrowsExceptionProvider
     *
     * @throws Warning
     */
    public function testGetRequirementsWithInvalidVersionConstraintsThrowsException($test): void
    {
        $this->expectException(Warning::class);
        Test::getRequirements(RequirementsTest::class, $test);
    }

    public function requirementsWithInvalidVersionConstraintsThrowsExceptionProvider(): array
    {
        return [
            ['testVersionConstraintInvalidPhpConstraint'],
            ['testVersionConstraintInvalidPhpUnitConstraint'],
        ];
    }

    public function testGetRequirementsMergesClassAndMethodDocBlocks(): void
    {
        $reflector = new ReflectionClass(RequirementsClassDocBlockTest::class);
        $file      = $reflector->getFileName();

        $expectedAnnotations = [
            '__OFFSET' => [
                '__FILE'                  => $file,
                'PHP'                     => 22,
                'PHPUnit'                 => 23,
                'OS'                      => 24,
                'function_testFuncClass'  => 16,
                'extension_testExtClass'  => 17,
                'function_testFuncMethod' => 25,
                'extension_testExtMethod' => 26,
            ],
            'PHP'       => ['version' => '5.4', 'operator' => ''],
            'PHPUnit'   => ['version' => '3.7', 'operator' => ''],
            'OS'        => 'WINNT',
            'functions' => [
                'testFuncClass',
                'testFuncMethod',
            ],
            'extensions' => [
                'testExtClass',
                'testExtMethod',
            ],
        ];

        $this->assertEquals(
            $expectedAnnotations,
            Test::getRequirements(RequirementsClassDocBlockTest::class, 'testMethod')
        );
    }

    /**
     * @testdox Test::getMissingRequirements() for $test
     * @dataProvider missingRequirementsProvider
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Warning
     */
    public function testGetMissingRequirements($test, $result): void
    {
        $this->assertEquals(
            $result,
            Test::getMissingRequirements(RequirementsTest::class, $test)
        );
    }

    public function missingRequirementsProvider(): array
    {
        return [
            ['testOne',            []],
            ['testNine',           [
                '__OFFSET_LINE=71',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Function testFunc is required.',
            ]],
            ['testTen',            [
                '__OFFSET_LINE=87',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExt is required.',
            ]],
            ['testAlwaysSkip',     [
                '__OFFSET_LINE=145',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 1111111 is required.',
            ]],
            ['testAlwaysSkip2',    [
                '__OFFSET_LINE=152',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 9999999 is required.',
            ]],
            ['testAlwaysSkip3',    [
                '__OFFSET_LINE=159',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Operating system matching /DOESNOTEXIST/i is required.',
            ]],
            ['testAllPossibleRequirements', [
                '__OFFSET_LINE=102',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99-dev is required.',
                'PHPUnit >= 9-dev is required.',
                'Operating system matching /DOESNOTEXIST/i is required.',
                'Function testFuncOne is required.',
                'Function testFunc2 is required.',
                'Setting "not_a_setting" must be "Off".',
                'Extension testExtOne is required.',
                'Extension testExt2 is required.',
                'Extension testExtThree >= 2.0 is required.',
            ]],
            ['testPHPVersionOperatorLessThan', [
                '__OFFSET_LINE=189',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP < 5.4 is required.',
            ]],
            ['testPHPVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=197',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP <= 5.4 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThan', [
                '__OFFSET_LINE=205',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP > 99 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=213',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorNoSpace', [
                '__OFFSET_LINE=253',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorEquals', [
                '__OFFSET_LINE=221',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP = 5.4 is required.',
            ]],
            ['testPHPVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=229',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP == 5.4 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThan', [
                '__OFFSET_LINE=261',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit < 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=269',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit <= 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThan', [
                '__OFFSET_LINE=277',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit > 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=285',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorEquals', [
                '__OFFSET_LINE=293',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit = 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=301',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit == 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorNoSpace', [
                '__OFFSET_LINE=325',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHPUnit >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorLessThan', [
                '__OFFSET_LINE=332',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne < 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorLessThanEquals', [
                '__OFFSET_LINE=339',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne <= 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThan', [
                '__OFFSET_LINE=346',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne > 99 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThanEquals', [
                '__OFFSET_LINE=353',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorEquals', [
                '__OFFSET_LINE=360',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne = 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorDoubleEquals', [
                '__OFFSET_LINE=367',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne == 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorNoSpace', [
                '__OFFSET_LINE=388',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'Extension testExtOne >= 99 is required.',
            ]],
            ['testVersionConstraintTildeMajor', [
                '__OFFSET_LINE=395',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP version does not match the required constraint ~1.0.',
                'PHPUnit version does not match the required constraint ~2.0.',
            ]],
            ['testVersionConstraintCaretMajor', [
                '__OFFSET_LINE=403',
                '__OFFSET_FILE=' . $this->getRequirementsTestClassFile(),
                'PHP version does not match the required constraint ^1.0.',
                'PHPUnit version does not match the required constraint ^2.0.',
            ]],
        ];
    }

    /**
     * @todo This test does not really test functionality of \PHPUnit\Util\Test
     */
    public function testGetProvidedDataRegEx(): void
    {
        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider namespace\namespace\class::method', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('namespace\namespace\class::method', $matches[1]);

        $result = preg_match(DocBlock::REGEX_DATA_PROVIDER, '@dataProvider メソッド', $matches);
        $this->assertEquals(1, $result);
        $this->assertEquals('メソッド', $matches[1]);
    }

    /**
     * Check if all data providers are being merged.
     */
    public function testMultipleDataProviders(): void
    {
        $dataSets = Test::getProvidedData(MultipleDataProviderTest::class, 'testOne');

        $this->assertCount(9, $dataSets);

        $aCount = 0;
        $bCount = 0;
        $cCount = 0;

        for ($i = 0; $i < 9; $i++) {
            $aCount += $dataSets[$i][0] != null ? 1 : 0;
            $bCount += $dataSets[$i][1] != null ? 1 : 0;
            $cCount += $dataSets[$i][2] != null ? 1 : 0;
        }

        $this->assertEquals(3, $aCount);
        $this->assertEquals(3, $bCount);
        $this->assertEquals(3, $cCount);
    }

    public function testMultipleYieldIteratorDataProviders(): void
    {
        $dataSets = Test::getProvidedData(MultipleDataProviderTest::class, 'testTwo');

        $this->assertCount(9, $dataSets);

        $aCount = 0;
        $bCount = 0;
        $cCount = 0;

        for ($i = 0; $i < 9; $i++) {
            $aCount += $dataSets[$i][0] != null ? 1 : 0;
            $bCount += $dataSets[$i][1] != null ? 1 : 0;
            $cCount += $dataSets[$i][2] != null ? 1 : 0;
        }

        $this->assertEquals(3, $aCount);
        $this->assertEquals(3, $bCount);
        $this->assertEquals(3, $cCount);
    }

    public function testWithVariousIterableDataProvidersFromParent(): void
    {
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testFromParent');

        $this->assertEquals([
            ['J'],
            ['K'],
            ['L'],
            ['M'],
            ['N'],
            ['O'],
            ['P'],
            ['Q'],
            ['R'],

        ], $dataSets);
    }

    public function testWithVariousIterableDataProvidersInParent(): void
    {
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testInParent');

        $this->assertEquals([
            ['J'],
            ['K'],
            ['L'],
            ['M'],
            ['N'],
            ['O'],
            ['P'],
            ['Q'],
            ['R'],

        ], $dataSets);
    }

    public function testWithVariousIterableAbstractDataProviders(): void
    {
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testAbstract');

        $this->assertEquals([
            ['S'],
            ['T'],
            ['U'],
            ['V'],
            ['W'],
            ['X'],
            ['Y'],
            ['Z'],
            ['P'],

        ], $dataSets);
    }

    public function testWithVariousIterableStaticDataProviders(): void
    {
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testStatic');

        $this->assertEquals([
            ['A'],
            ['B'],
            ['C'],
            ['D'],
            ['E'],
            ['F'],
            ['G'],
            ['H'],
            ['I'],
        ], $dataSets);
    }

    public function testWithVariousIterableNonStaticDataProviders(): void
    {
        $dataSets = Test::getProvidedData(VariousIterableDataProviderTest::class, 'testNonStatic');

        $this->assertEquals([
            ['S'],
            ['T'],
            ['U'],
            ['V'],
            ['W'],
            ['X'],
            ['Y'],
            ['Z'],
            ['P'],
        ], $dataSets);
    }

    public function testWithDuplicateKeyDataProviders(): void
    {
        $this->expectException(InvalidDataProviderException::class);
        $this->expectExceptionMessage('The key "foo" has already been defined in the data provider "dataProvider".');

        Test::getProvidedData(DuplicateKeyDataProviderTest::class, 'test');
    }

    public function testTestWithEmptyAnnotation(): void
    {
        $result = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'anotherAnnotation'
        ), VariousDocblockDefinedDataProvider::class)->getProvidedData();

        $this->assertNull($result);
    }

    public function testTestWithSimpleCase(): void
    {
        $result = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'testWith1'
        ), VariousDocblockDefinedDataProvider::class)->getProvidedData();

        $this->assertEquals([[1]], $result);
    }

    public function testTestWithMultiLineMultiParameterCase(): void
    {
        $result = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'testWith1234'
        ), VariousDocblockDefinedDataProvider::class)->getProvidedData();

        $this->assertEquals([[1, 2], [3, 4]], $result);
    }

    public function testTestWithVariousTypes(): void
    {
        $result = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'testWithABTrueNull'
        ), VariousDocblockDefinedDataProvider::class)->getProvidedData();

        $this->assertEquals([['ab'], [true], [null]], $result);
    }

    public function testTestWithAnnotationAfter(): void
    {
        $result = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'testWith12AndAnotherAnnotation'
        ), VariousDocblockDefinedDataProvider::class)->getProvidedData();

        $this->assertEquals([[1], [2]], $result);
    }

    public function testTestWithSimpleTextAfter(): void
    {
        $result = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'testWith12AndBlahBlah'
        ), VariousDocblockDefinedDataProvider::class)->getProvidedData();

        $this->assertEquals([[1], [2]], $result);
    }

    public function testTestWithCharacterEscape(): void
    {
        $result = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'testWithEscapedString'
        ), VariousDocblockDefinedDataProvider::class)->getProvidedData();

        $this->assertEquals([['"', '"']], $result);
    }

    public function testTestWithThrowsProperExceptionIfDatasetCannotBeParsed(): void
    {
        $docBlock = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'testWithMalformedValue'
        ), VariousDocblockDefinedDataProvider::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^The data set for the @testWith annotation cannot be parsed:/');

        $docBlock->getProvidedData();
    }

    public function testTestWithThrowsProperExceptionIfMultiLineDatasetCannotBeParsed(): void
    {
        $docBlock = DocBlock::ofMethod(new ReflectionMethod(
            VariousDocblockDefinedDataProvider::class,
            'testWithWellFormedAndMalformedValue'
        ), VariousDocblockDefinedDataProvider::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^The data set for the @testWith annotation cannot be parsed:/');

        $docBlock->getProvidedData();
    }

    /**
     * @todo Not sure what this test tests (name is misleading at least)
     */
    public function testParseAnnotation(): void
    {
        $this->assertEquals(
            ['Foo', 'ほげ'],
            Test::getDependencies(self::class, 'methodForTestParseAnnotation')
        );
    }

    /**
     * @depends Foo
     * @depends ほげ
     *
     * @todo Remove fixture from test class
     */
    public function methodForTestParseAnnotation(): void
    {
    }

    public function testParseAnnotationThatIsOnlyOneLine(): void
    {
        $this->assertEquals(
            ['Bar'],
            Test::getDependencies(self::class, 'methodForTestParseAnnotationThatIsOnlyOneLine')
        );
    }

    /** @depends Bar */
    public function methodForTestParseAnnotationThatIsOnlyOneLine(): void
    {
        // TODO Remove fixture from test class
    }

    /**
     * @dataProvider getLinesToBeCoveredProvider
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws CodeCoverageException
     */
    public function testGetLinesToBeCovered($test, $expected): void
    {
        $this->assertEquals(
            $expected,
            Test::getLinesToBeCovered(
                $test,
                'testSomething'
            )
        );
    }

    public function testGetLinesToBeCovered2(): void
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            NotExistingCoveredElementTest::class,
            'testOne'
        );
    }

    public function testGetLinesToBeCovered3(): void
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            NotExistingCoveredElementTest::class,
            'testTwo'
        );
    }

    public function testGetLinesToBeCovered4(): void
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            NotExistingCoveredElementTest::class,
            'testThree'
        );
    }

    public function testGetLinesToBeCoveredSkipsNonExistentMethods(): void
    {
        $this->assertSame(
            [],
            Test::getLinesToBeCovered(
                NotExistingCoveredElementTest::class,
                'methodDoesNotExist'
            )
        );
    }

    public function testTwoCoversDefaultClassAnnotationsAreNotAllowed(): void
    {
        $this->expectException(CodeCoverageException::class);

        Test::getLinesToBeCovered(
            CoverageTwoDefaultClassAnnotations::class,
            'testSomething'
        );
    }

    public function testFunctionParenthesesAreAllowed(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12)],
            Test::getLinesToBeCovered(
                CoverageFunctionParenthesesTest::class,
                'testSomething'
            )
        );
    }

    public function testFunctionParenthesesAreAllowedWithWhitespace(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12)],
            Test::getLinesToBeCovered(
                CoverageFunctionParenthesesWhitespaceTest::class,
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowed(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => range(29, 33)],
            Test::getLinesToBeCovered(
                CoverageMethodParenthesesTest::class,
                'testSomething'
            )
        );
    }

    public function testMethodParenthesesAreAllowedWithWhitespace(): void
    {
        $this->assertSame(
            [TEST_FILES_PATH . 'CoveredClass.php' => range(29, 33)],
            Test::getLinesToBeCovered(
                CoverageMethodParenthesesWhitespaceTest::class,
                'testSomething'
            )
        );
    }

    public function testNamespacedFunctionCanBeCoveredOrUsed(): void
    {
        $this->assertEquals(
            [
                TEST_FILES_PATH . 'NamespaceCoveredFunction.php' => range(12, 15),
            ],
            Test::getLinesToBeCovered(
                CoverageNamespacedFunctionTest::class,
                'testFunc'
            )
        );
    }

    public function getLinesToBeCoveredProvider(): array
    {
        return [
            [
                CoverageNoneTest::class,
                [],
            ],
            [
                CoverageClassExtendedTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => array_merge(range(27, 44), range(10, 25)),
                ],
            ],
            [
                CoverageClassTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(27, 44),
                ],
            ],
            [
                CoverageMethodTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(29, 33),
                ],
            ],
            [
                CoverageMethodOneLineAnnotationTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(29, 33),
                ],
            ],
            [
                CoverageNotPrivateTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => array_merge(range(29, 33), range(35, 39)),
                ],
            ],
            [
                CoverageNotProtectedTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => array_merge(range(29, 33), range(41, 43)),
                ],
            ],
            [
                CoverageNotPublicTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => array_merge(range(35, 39), range(41, 43)),
                ],
            ],
            [
                CoveragePrivateTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(41, 43),
                ],
            ],
            [
                CoverageProtectedTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(35, 39),
                ],

            ],
            [
                CoveragePublicTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(29, 33),
                ],
            ],
            [
                CoverageFunctionTest::class,
                [
                    TEST_FILES_PATH . 'CoveredFunction.php' => range(10, 12),
                ],
            ],
            [
                NamespaceCoverageClassExtendedTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => array_merge(range(29, 46), range(12, 27)),
                ],
            ],
            [
                NamespaceCoverageClassTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => range(29, 46),
                ],
            ],
            [
                NamespaceCoverageMethodTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => range(31, 35),
                ],
            ],
            [
                NamespaceCoverageNotPrivateTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => array_merge(range(31, 35), range(37, 41)),
                ],
            ],
            [
                NamespaceCoverageNotProtectedTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => array_merge(range(31, 35), range(43, 45)),
                ],
            ],
            [
                NamespaceCoverageNotPublicTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => array_merge(range(37, 41), range(43, 45)),
                ],
            ],
            [
                NamespaceCoveragePrivateTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => range(43, 45),
                ],
            ],
            [
                NamespaceCoverageProtectedTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => range(37, 41),
                ],
            ],
            [
                NamespaceCoveragePublicTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => range(31, 35),
                ],
            ],
            [
                NamespaceCoverageCoversClassTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => array_merge(range(43, 45), range(37, 41), range(31, 35), range(24, 26), range(19, 22), range(14, 17)),
                ],
            ],
            [
                NamespaceCoverageCoversClassPublicTest::class,
                [
                    TEST_FILES_PATH . 'NamespaceCoveredClass.php' => range(31, 35),
                ],
            ],
            [
                CoverageClassNothingTest::class,
                false,
            ],
            [
                CoverageMethodNothingTest::class,
                false,
            ],
            [
                CoverageCoversOverridesCoversNothingTest::class,
                [
                    TEST_FILES_PATH . 'CoveredClass.php' => range(29, 33),
                ],
            ],
            [
                CoverageMethodNothingCoversMethod::class,
                false,
            ],
        ];
    }

    public function testParseTestMethodAnnotationsIncorporatesTraits(): void
    {
        $result = Test::parseTestMethodAnnotations(ParseTestMethodAnnotationsMock::class);

        $this->assertArrayHasKey('class', $result);
        $this->assertArrayHasKey('method', $result);
        $this->assertArrayHasKey('theClassAnnotation', $result['class']);
        $this->assertArrayHasKey('theTraitAnnotation', $result['class']);
    }

    public function testCoversAnnotationIncludesTraitsUsedByClass(): void
    {
        $this->assertSame(
            [
                TEST_FILES_PATH . '3194.php' => array_merge(range(22, 30), range(14, 20)),
            ],
            Test::getLinesToBeCovered(
                Test3194::class,
                'testOne'
            )
        );
    }

    /**
     * @dataProvider canSkipCoverageProvider
     */
    public function testCanSkipCoverage($testCase, $expectedCanSkip): void
    {
        require_once TEST_FILES_PATH . $testCase . '.php';

        $test             = new $testCase('testSomething');
        $coverageRequired = Test::requiresCodeCoverageDataCollection($test);
        $canSkipCoverage  = !$coverageRequired;

        $this->assertEquals($expectedCanSkip, $canSkipCoverage);
    }

    public function canSkipCoverageProvider(): array
    {
        return [
            ['CoverageClassTest', false],
            ['CoverageClassWithoutAnnotationsTest', false],
            ['CoverageCoversOverridesCoversNothingTest', false],
            ['CoverageClassNothingTest', true],
            ['CoverageMethodNothingTest', true],
        ];
    }

    private function getRequirementsTestClassFile(): string
    {
        if (!$this->fileRequirementsTest) {
            $reflector                  = new ReflectionClass(RequirementsTest::class);
            $this->fileRequirementsTest = realpath($reflector->getFileName());
        }

        return $this->fileRequirementsTest;
    }
}
