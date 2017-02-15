<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016  Daniel Ziegler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Statusengine\Test;


use Statusengine\PerfdataParser;
use ValueObjects\Perfdata;


class PerfdataParserTest extends \PHPUnit_Framework_TestCase {

    public function testSplitGaugesRta(){
        $PerfdataParser = new PerfdataParser('rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0');


        $result = $PerfdataParser->splitGauges();
        $assert = [
            'rta=0.069000ms;100.000000;500.000000;0.000000',
            'pl=0%;20;60;0'
        ];
        $this->assertEquals($assert, $result);
    }
    public function testSplitGaugesMinimal(){
        $PerfdataParser = new PerfdataParser('foo=1');
        $result = $PerfdataParser->splitGauges();
        $assert = [
            'foo=1',
        ];
        $this->assertEquals($assert, $result);
    }
    public function testSplitGaugesSingleQuotes(){
        $PerfdataParser = new PerfdataParser("'foo bar'=1");
        $result = $PerfdataParser->splitGauges();
        $assert = [
            "'foo bar'=1",
        ];
        $this->assertEquals($assert, $result);
    }
    public function testSplitGaugesDoubleQuotes(){
        $PerfdataParser = new PerfdataParser('"foo bar"=1');
        $result = $PerfdataParser->splitGauges();
        $assert = [
            "'foo bar'=1",
        ];
        $this->assertEquals($assert, $result);
    }
    public function testParseGaugeRta(){
        $PerfdataParser = new PerfdataParser('1');

        $gauge = 'rta=0.069000ms;100.000000;500.000000;0.000000;150,150';
        $result = $PerfdataParser->parseGauge($gauge);
        $assert = [
            'rta' => [
                'current' => '0.069000',
                'unit' => 'ms',
                'warning' => '100.000000',
                'critical' => '500.000000',
                'min' => '0.000000',
                'max' => '150.150'
            ]
        ];
        $this->assertEquals($assert, $result);
    }
    //Perfdata->parseGauge gets only called after Perfdata->splitGauges which
    //is removing all double quotes
    public function testParseGaugeSingleQuotes(){
        $PerfdataParser = new PerfdataParser('1');

        $gauge = "'foo bar'=1ms";
        $result = $PerfdataParser->parseGauge($gauge);
        $assert = [
            "'foo bar'" => [
                'current' => '1',
                'unit' => 'ms',
                'warning' => null,
                'critical' => null,
                'min' => null,
                'max' => null
            ]
        ];
        $this->assertEquals($assert, $result);
    }
    public function testParseRta(){
        $PerfdataParser = new PerfdataParser('rta=0.069000ms;100.000000;500.000000;0.000000 pl=0%;20;60;0');
        $result = $PerfdataParser->parse();
        $assert = [
            'rta' => [
                'current' => '0.069000',
                'unit' => 'ms',
                'warning' => '100.000000',
                'critical' => '500.000000',
                'min' => '0.000000',
                'max' => null
            ],
            'pl' => [
                'current' => '0',
                'unit' => '%',
                'warning' => '20',
                'critical' => '60',
                'min' => '0',
                'max' => null
            ]
        ];
        $this->assertEquals($assert, $result);
    }
    public function testParseMinimal(){
        $PerfdataParser = new PerfdataParser('foo=1');
        $result = $PerfdataParser->parse();
        $assert = [
            'foo' => [
                'current' => '1',
                'unit' => '',
                'warning' => null,
                'critical' => null,
                'min' => null,
                'max' => null
            ],
        ];
        $this->assertEquals($assert, $result);
    }
    public function testParseUnit(){
        $PerfdataParser = new PerfdataParser('foo=1ms');
        $result = $PerfdataParser->parse();
        $assert = [
            'foo' => [
                'current' => '1',
                'unit' => 'ms',
                'warning' => null,
                'critical' => null,
                'min' => null,
                'max' => null
            ],
        ];
        $this->assertEquals($assert, $result);
    }
    public function testParseSingleQuotes(){
        $PerfdataParser = new PerfdataParser("'foo bar'=1ms");
        $result = $PerfdataParser->parse();
        $assert = [
            "'foo bar'" => [
                'current' => '1',
                'unit' => 'ms',
                'warning' => null,
                'critical' => null,
                'min' => null,
                'max' => null
            ],
        ];
        $this->assertEquals($assert, $result);
    }
    public function testParseDoubleQuotes(){
        $PerfdataParser = new PerfdataParser('"foo bar"=1ms');
        $result = $PerfdataParser->parse();
        $assert = [
            "'foo bar'" => [
                'current' => '1',
                'unit' => 'ms',
                'warning' => null,
                'critical' => null,
                'min' => null,
                'max' => null
            ],
        ];
        $this->assertEquals($assert, $result);
    }
    public function testParseNegativValue(){
        $PerfdataParser = new PerfdataParser('Taupunkt=-7.48C;;;;');
        $result = $PerfdataParser->parse();
        $assert = [
            'Taupunkt' => [
                'current' => '-7.48',
                'unit' => 'C',
                'warning' => null,
                'critical' => null,
                'min' => null,
                'max' => null
            ],
        ];
        $this->assertEquals($assert, $result);
    }

}
